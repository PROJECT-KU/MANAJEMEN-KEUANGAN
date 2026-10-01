<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Pembayaran lewat DOKU Checkout.
 *
 * Satu kelas, bukan dipanggil langsung dari pengendali: pembuatan tanda tangan
 * dan pemeriksaan pemberitahuan balik memakai aturan yang SAMA, dan menulisnya
 * dua kali berarti satu di antaranya akan ketinggalan saat aturannya berubah.
 *
 * ---------------------------------------------------------------------------
 * Kredensialnya belum ada
 * ---------------------------------------------------------------------------
 *
 * Sampai `DOKU_CLIENT_ID` dan `DOKU_SECRET_KEY` diisi di berkas .env, kelas ini
 * menjawab dengan terus terang lewat `siap()` dan pengendalinya menawarkan
 * transfer manual sebagai gantinya. Dipilih begitu daripada melempar galat:
 * yang membukanya calon peserta, dan halaman galat di tengah pendaftaran
 * adalah pendaftar yang hilang.
 */
class Doku
{
    /** Berapa lama tagihan berlaku, dalam MENIT. */
    public const MENIT_KEDALUWARSA = 60;

    public function siap(): bool
    {
        return $this->clientId() !== '' && $this->rahasia() !== '';
    }

    private function clientId(): string
    {
        return trim((string) config('services.doku.client_id'));
    }

    private function rahasia(): string
    {
        return trim((string) config('services.doku.secret_key'));
    }

    private function pangkalan(): string
    {
        return config('services.doku.produksi')
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    /**
     * Membuat tagihan dan mengembalikan alamat halaman bayarnya.
     *
     * @param  array<string, mixed>  $pesanan
     * @return array{berhasil: bool, url: ?string, rujukan: ?string, pesan: ?string}
     */
    public function buatTagihan(array $pesanan): array
    {
        if (! $this->siap()) {
            return [
                'berhasil' => false,
                'url' => null,
                'rujukan' => null,
                'pesan' => 'Pembayaran daring belum aktif.',
            ];
        }

        $jalur = '/checkout/v1/payment';
        $requestId = (string) Str::uuid();
        $waktu = gmdate('Y-m-d\TH:i:s\Z');

        $isi = [
            'order' => [
                'amount' => (int) $pesanan['jumlah'],
                'invoice_number' => $pesanan['nomor'],
                'currency' => 'IDR',
                'callback_url' => $pesanan['kembali'],
                'line_items' => [[
                    'name' => $pesanan['judul'],
                    'price' => (int) $pesanan['harga_satuan'],
                    'quantity' => (int) $pesanan['jumlah_peserta'],
                ]],
            ],
            'payment' => [
                'payment_due_date' => self::MENIT_KEDALUWARSA,
            ],
            'customer' => [
                'id' => Str::limit((string) $pesanan['nomor'], 60, ''),
                'name' => $pesanan['nama'],
                'email' => $pesanan['email'],
                'phone' => $pesanan['telp'],
            ],
        ];

        $json = json_encode($isi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        try {
            $jawab = Http::timeout(20)
                ->withHeaders([
                    'Client-Id' => $this->clientId(),
                    'Request-Id' => $requestId,
                    'Request-Timestamp' => $waktu,
                    'Signature' => $this->tandaTangan($jalur, $requestId, $waktu, $json),
                    'Content-Type' => 'application/json',
                ])
                ->withBody($json, 'application/json')
                ->post($this->pangkalan() . $jalur);
        } catch (\Throwable $e) {
            report($e);

            return [
                'berhasil' => false, 'url' => null, 'rujukan' => null,
                'pesan' => 'Tidak bisa menghubungi penyedia pembayaran.',
            ];
        }

        if (! $jawab->successful()) {
            /*
             * Isi jawabannya ikut dicatat, TANPA tanda tangan dan kunci.
             * Galat dari gerbang pembayaran hampir selalu soal bentuk data,
             * dan tanpa isinya yang tercatat cuma "gagal".
             */
            Log::error('DOKU menolak tagihan', [
                'status' => $jawab->status(),
                'nomor' => $pesanan['nomor'],
                'jawaban' => Str::limit($jawab->body(), 500),
            ]);

            return [
                'berhasil' => false, 'url' => null, 'rujukan' => null,
                'pesan' => 'Penyedia pembayaran menolak tagihan ini.',
            ];
        }

        $data = $jawab->json();

        return [
            'berhasil' => true,
            'url' => data_get($data, 'response.payment.url'),
            'rujukan' => data_get($data, 'response.order.invoice_number', $pesanan['nomor']),
            'pesan' => null,
        ];
    }

    /**
     * Memeriksa apakah pemberitahuan balik memang datang dari DOKU.
     *
     * WAJIB dipanggil sebelum satu pun status diubah. Tanpa ini, siapa pun
     * yang tahu alamat pemberitahuannya bisa menandai pendaftaran mana pun
     * sebagai lunas hanya dengan satu permintaan POST.
     */
    public function pemberitahuanSah(
        string $jalur,
        ?string $requestId,
        ?string $waktu,
        ?string $kiriman,
        string $isiMentah
    ): bool {
        if (! $this->siap()) {
            return false;
        }

        /*
         * Ketiganya diminta satu per satu, BUKAN sebagai satu larik kepala.
         *
         * $request->headers->all() mengembalikan LARIK nilai per kepala — satu
         * kepala boleh muncul lebih dari sekali — jadi mengambilnya begitu
         * memberi array, bukan untaian, dan tanda tangannya gagal dihitung
         * dengan galat yang menunjuk ke tempat lain.
         */
        $requestId = trim((string) $requestId);
        $waktu = trim((string) $waktu);
        $kiriman = trim((string) $kiriman);

        if ($requestId === '' || $waktu === '' || $kiriman === '') {
            return false;
        }

        // hash_equals, bukan ===: pembandingan untaian biasa berhenti di huruf
        // pertama yang berbeda, dan selisih waktunya bisa dipakai menebak
        // tanda tangan satu huruf demi satu huruf.
        return hash_equals(
            $this->tandaTangan($jalur, $requestId, $waktu, $isiMentah),
            $kiriman
        );
    }

    /**
     * Tanda tangan HMAC-SHA256 sesuai aturan DOKU.
     *
     * Urutan barisnya MENGIKAT dan tidak boleh diubah — tanda tangannya
     * dihitung dari untaian yang persis seperti ini, jadi satu baris yang
     * tertukar membuat semua permintaan ditolak tanpa penjelasan.
     */
    private function tandaTangan(string $jalur, string $requestId, string $waktu, string $isi): string
    {
        $ringkasan = base64_encode(hash('sha256', $isi, true));

        $bahan = implode("\n", [
            'Client-Id:' . $this->clientId(),
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $waktu,
            'Request-Target:' . $jalur,
            'Digest:' . $ringkasan,
        ]);

        return 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $bahan, $this->rahasia(), true)
        );
    }
}
