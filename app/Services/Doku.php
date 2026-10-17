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

        /*
         * Rincian tagihan HARUS berjumlah sama dengan totalnya.
         *
         * DOKU menolak dengan "AMOUNT NOT MATCH" kalau order.amount berbeda
         * dari jumlah line_items-nya. Selama ini tidak pernah ketahuan sebab
         * jalur DOKU belum pernah hidup — dan yang akan menabraknya justru
         * pendaftaran yang PAKAI POTONGAN: totalnya sudah dikurangi, tetapi
         * rinciannya masih harga penuh dikali jumlah peserta.
         *
         * Potongannya dikirim sebagai baris tersendiri bernilai minus — sudah
         * diuji dan diterima DOKU — bukan dengan memperkecil harga satuannya.
         * Dengan begitu yang membayar melihat harga asli DAN potongannya di
         * halaman bayar, sama seperti yang tertulis di borangnya.
         */
        $baris = [[
            'name' => $this->bersihkan((string) $pesanan['judul']),
            'price' => (int) $pesanan['harga_satuan'],
            'quantity' => (int) $pesanan['jumlah_peserta'],
        ]];

        if ((int) ($pesanan['potongan'] ?? 0) > 0) {
            $baris[] = [
                'name' => $this->bersihkan((string) ($pesanan['label_potongan'] ?? 'Potongan')),
                'price' => -1 * (int) $pesanan['potongan'],
                'quantity' => 1,
            ];
        }

        // Kode unik transfer biasanya 0 di jalur DOKU — pencocokannya memakai
        // nomor rujukan. Tetap dihitung di sini supaya rinciannya tidak
        // pernah meleset kalau suatu saat dipakai juga.
        if ((int) ($pesanan['kode_unik'] ?? 0) > 0) {
            $baris[] = [
                // "pembayaran", bukan "transfer": lewat gerbang ini orang
                // boleh membayar dengan OVO, kartu, atau gerai — menyebutnya
                // transfer membuat baris ini terbaca seperti salah tempat.
                'name' => 'Kode unik pembayaran',
                'price' => (int) $pesanan['kode_unik'],
                'quantity' => 1,
            ];
        }

        $jumlahBaris = array_sum(array_map(
            fn ($b) => $b['price'] * $b['quantity'],
            $baris
        ));

        /*
         * Kalau rinciannya tidak berjumlah sama dengan total yang tersimpan,
         * tagihannya TIDAK dikirim.
         *
         * Mengirimkan jumlah rinciannya begitu saja akan menagih orang dengan
         * angka yang berbeda dari yang tercatat di basis data — kekeliruan
         * yang jauh lebih buruk daripada gagal membuat tagihan, sebab
         * pemanggilnya sudah menyiapkan jalan mundur ke transfer manual.
         */
        if ($jumlahBaris !== (int) $pesanan['jumlah']) {
            Log::error('Rincian tagihan DOKU tidak berjumlah sama dengan totalnya', [
                'nomor' => $pesanan['nomor'],
                'total_tersimpan' => (int) $pesanan['jumlah'],
                'jumlah_rincian' => $jumlahBaris,
            ]);

            return [
                'berhasil' => false, 'url' => null, 'rujukan' => null,
                'pesan' => 'Rincian tagihannya tidak cocok dengan totalnya.',
            ];
        }

        $isi = [
            'order' => [
                'amount' => (int) $pesanan['jumlah'],
                'invoice_number' => $pesanan['nomor'],
                'currency' => 'IDR',
                'callback_url' => $pesanan['kembali'],

                /*
                 * Langsung kembali ke situs kita sesudah bayar.
                 *
                 * Bawaan DOKU auto_redirect = false: halaman hasil DOKU
                 * ditampilkan, lalu MENUNGGU orang menekan tombol "Back to
                 * Merchant". Banyak yang tidak menekannya — mereka sudah
                 * melihat "Payment Successful" dan menutup tabnya, lalu
                 * tidak pernah melihat nomor pendaftaran, tautan grup, atau
                 * kabar bahwa tautan masuk dikirim ke emailnya.
                 *
                 * Dengan true, halaman hasilnya dilewati dan orangnya
                 * mendarat di halaman status kita sendiri.
                 */
                'auto_redirect' => true,

                'line_items' => $baris,
            ],
            'payment' => [
                'payment_due_date' => self::MENIT_KEDALUWARSA,
            ],
            'customer' => [
                'id' => Str::limit((string) $pesanan['nomor'], 60, ''),
                'name' => $this->bersihkan((string) $pesanan['nama']),
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
     * Membersihkan teks dari huruf yang DITOLAK DOKU.
     *
     * DOKU hanya menerima a-z A-Z 0-9 dan . - / + , = _ : ' @ % ( ) beserta
     * spasi. Satu huruf di luar itu membuat SELURUH tagihan ditolak dengan
     * "Invalid character", dan yang mendaftar cuma melihat "pembayaran daring
     * sedang bermasalah" tanpa petunjuk apa pun.
     *
     * Bukan kemungkinan yang jauh: judul sesi dan nama pendaftar diketik
     * bebas, dan naskah di sistem ini penuh emoji serta tanda pisah panjang —
     * 📘 🗓 – … semuanya ditolak. Terjadi persis begitu 17 Okt 2026, saat
     * nama item diberi tanda pisah panjang "—" dan elipsis "…".
     *
     * Yang mirip DIGANTI padanannya, bukan dibuang: "–" jadi "-" dan "…" jadi
     * "...", supaya tulisannya tetap terbaca. Sisanya dibuang.
     */
    private function bersihkan(string $teks): string
    {
        $padanan = [
            '—' => '-', '–' => '-', '‒' => '-', '―' => '-',
            '…' => '...',
            '’' => "'", '‘' => "'", '‛' => "'",
            '“' => "'", '”' => "'", '„' => "'", '«' => "'", '»' => "'",
            '•' => '-', '·' => '-', '×' => 'x',
            '&' => 'dan',
        ];

        $teks = strtr($teks, $padanan);
        $teks = preg_replace('/[^a-zA-Z0-9 .\-\/+,=_:\x27@%()]/u', '', $teks);

        // Spasi rangkap muncul sendiri begitu emoji di antaranya dibuang.
        return trim((string) preg_replace('/\s{2,}/', ' ', (string) $teks));
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
