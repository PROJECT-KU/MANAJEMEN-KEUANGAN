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
     * @return array{berhasil: bool, url: ?string, rujukan: ?string, permintaan_id: ?string, pesan: ?string}
     */
    public function buatTagihan(array $pesanan): array
    {
        if (! $this->siap()) {
            return [
                'berhasil' => false,
                'url' => null,
                'rujukan' => null, 'permintaan_id' => null,
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

        /*
         * PPN dikirim sebagai BARIS TERSENDIRI, bukan dilipat ke harga
         * satuannya.
         *
         * Scopus Camp menghitungnya dari SATU kursi, bukan dari subtotal
         * (lihat App\Support\HargaUmum) — jadi melipatnya ke harga satuan
         * membuat rinciannya meleset begitu pesertanya lebih dari satu, dan
         * DOKU menolak seluruh tagihan dengan "AMOUNT NOT MATCH". Yang
         * menanggungnya pendaftar yang melihat "pembayaran daring sedang
         * bermasalah" tanpa petunjuk apa pun.
         *
         * Nol untuk Webinar Eksklusif, yang memang tidak memakai PPN; baris
         * ini tidak digambar sama sekali di sana.
         */
        if ((int) ($pesanan['ppn'] ?? 0) > 0) {
            $baris[] = [
                'name' => 'PPN',
                'price' => (int) $pesanan['ppn'],
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
                'berhasil' => false, 'url' => null, 'rujukan' => null, 'permintaan_id' => null,
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
                'berhasil' => false, 'url' => null, 'rujukan' => null, 'permintaan_id' => null,
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
                'berhasil' => false, 'url' => null, 'rujukan' => null, 'permintaan_id' => null,
                'pesan' => 'Penyedia pembayaran menolak tagihan ini.',
            ];
        }

        $data = $jawab->json();

        return [
            'berhasil' => true,
            'url' => data_get($data, 'response.payment.url'),
            'rujukan' => data_get($data, 'response.order.invoice_number', $pesanan['nomor']),

            /*
             * Request-Id-nya IKUT DIPULANGKAN, bukan dibuang sesudah dipakai
             * menandatangani.
             *
             * Cancel Order API DOKU mewajibkannya sebagai
             * payment.original_request_id — nomor faktur saja tidak cukup.
             * Tanpa menyimpannya, tagihan yang pendaftarannya sudah
             * dibatalkan panitia tidak akan pernah bisa ditutup dari sisi
             * kita, dan orangnya masih bisa membayar kursi yang sudah
             * diberikan kepada orang lain.
             */
            'permintaan_id' => $requestId,

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
    /**
     * Menutup tagihan yang belum dibayar, supaya tidak bisa dibayar lagi.
     *
     * Membatalkan di layar panitia tidak dengan sendirinya sampai ke DOKU:
     * tagihannya di sana hidup sampai tenggatnya habis, dan pendaftar yang
     * tabnya masih terbuka bisa tetap membayar kursi yang sudah diberikan
     * kepada orang lain.
     *
     * DUA HAL YANG TIDAK BISA DILAKUKAN API INI, dan keduanya sudah terbaca
     * di dasbor DOKU sendiri:
     *
     *   1. hanya pesanan Virtual Account dan QRIS yang bisa dibatalkan.
     *      Kartu, e-wallet, PayLater, dan digital banking TIDAK. Jadi
     *      penjagaan di sisi kita — menolak menaikkan status pendaftaran
     *      yang sudah batal — tetap wajib ada selamanya, bukan sementara
     *      sampai ini jalan;
     *   2. pesanan yang sudah dibayar atau sudah kedaluwarsa tidak bisa
     *      dibatalkan. Jawaban "gagal" untuk keduanya bukan kesalahan kita.
     *
     * TIDAK PERNAH melempar. Pemanggilnya sedang memindahkan status atas
     * perintah panitia, dan kegagalan di sini tidak boleh membatalkan
     * perpindahan itu — yang menanggung panitia yang menekan tombolnya.
     *
     * @return array{berhasil: bool, pesan: ?string}
     */
    public function batalkanTagihan(
        ?string $nomor,
        ?string $permintaanIdAsli,
        string $catatan = 'Dibatalkan panitia'
    ): array {
        if (! $this->siap()) {
            return ['berhasil' => false, 'pesan' => 'Pembayaran daring belum aktif.'];
        }

        $nomor = trim((string) $nomor);
        $permintaanIdAsli = trim((string) $permintaanIdAsli);

        /*
         * original_request_id WAJIB, dan itulah satu-satunya sebab kolom
         * bayar_permintaan_id ada. Pendaftaran yang tagihannya dibuat
         * sebelum kolom itu lahir tidak memilikinya, dan tidak akan pernah
         * bisa dibatalkan dari sini — disebutkan apa adanya supaya lognya
         * tidak terbaca seperti gangguan jaringan.
         */
        if ($nomor === '' || $permintaanIdAsli === '') {
            return [
                'berhasil' => false,
                'pesan' => 'Tagihan ini dibuat sebelum nomor permintaannya disimpan, '
                    . 'jadi tidak bisa ditutup lewat DOKU.',
            ];
        }

        $jalur = '/checkout/v3/cancellations';
        $requestId = (string) Str::uuid();
        $waktu = gmdate('Y-m-d\TH:i:s\Z');

        $json = json_encode([
            'order' => ['invoice_number' => $nomor],
            'payment' => ['original_request_id' => $permintaanIdAsli],
            'note' => $this->bersihkan($catatan),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        try {
            /*
             * Sepuluh detik, bukan dua puluh seperti saat membuat tagihan.
             * Yang menunggu jawabannya panitia yang baru menekan "Dibatalkan"
             * di layarnya, bukan proses latar.
             */
            $jawab = Http::timeout(10)
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

            return ['berhasil' => false, 'pesan' => 'Tidak bisa menghubungi penyedia pembayaran.'];
        }

        if (! $jawab->successful()) {
            /*
             * Dicatat UTUH (tanpa tanda tangan maupun kunci), sebab jawaban
             * inilah satu-satunya cara tahu mana yang "kanalnya tidak
             * didukung" dan mana yang "fiturnya belum dinyalakan di dasbor".
             * Keduanya gagal dengan cara yang sama dari luar.
             */
            Log::warning('DOKU menolak pembatalan tagihan', [
                'status' => $jawab->status(),
                'nomor' => $nomor,
                'jawaban' => Str::limit($jawab->body(), 500),
            ]);

            return [
                'berhasil' => false,
                'pesan' => 'Penyedia pembayaran menolak pembatalan tagihan ini.',
            ];
        }

        return ['berhasil' => true, 'pesan' => null];
    }

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
     * Nama kanal pembayaran yang benar-benar dipakai, siap dibaca orang.
     *
     * `cara_bayar` di basis data hanya membedakan transfer manual dari
     * gerbang — lewat gerbang ia selalu "DOKU", padahal orangnya bisa
     * membayar dengan VA Mandiri, QRIS, OVO, atau kartu. Itu justru
     * pertanyaan pertama panitia saat satu pembayaran perlu ditelusuri.
     *
     * DOKU mengirimkannya di tiga medan sekaligus:
     *
     *     service.id     VIRTUAL_ACCOUNT
     *     acquirer.name  Bank Mandiri
     *     channel.id     VIRTUAL_ACCOUNT_BANK_MANDIRI
     *
     * Yang dipakai gabungan dua yang pertama — "VA Bank Mandiri" terbaca
     * jauh lebih cepat daripada channel.id yang berhuruf besar semua dengan
     * garis bawah. channel.id baru dipakai kalau keduanya kosong, dan
     * dirapikan dulu.
     *
     * @param  array<string, mixed>|null  $data
     */
    /**
     * Kapan transaksinya terjadi di gerbang, kalau disebutkan.
     *
     * @param  array<string, mixed>|null  $data
     */
    private function waktuTransaksi(?array $data): ?\Carbon\Carbon
    {
        $waktu = trim((string) data_get($data, 'transaction.date'));

        if ($waktu === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($waktu);
        } catch (\Throwable $e) {
            // Bentuk tanggal yang tidak dikenali diperlakukan seperti tidak
            // disebutkan: lebih baik kehilangan satu lapis penjagaan
            // daripada menolak pembayaran yang sah.
            return null;
        }
    }

    public function namaKanal(?array $data): ?string
    {
        $bank = trim((string) data_get($data, 'acquirer.name'));
        $layanan = mb_strtoupper(trim((string) data_get($data, 'service.id')));

        if ($layanan === 'VIRTUAL_ACCOUNT') {
            return $bank === '' ? 'Virtual Account' : 'VA ' . $bank;
        }

        if ($bank !== '') {
            return $bank;
        }

        $kanal = trim((string) data_get($data, 'channel.id'));

        if ($kanal === '') {
            return $layanan === '' ? null : ucwords(mb_strtolower(str_replace('_', ' ', $layanan)));
        }

        /*
         * QRIS tetap QRIS: huruf besar semuanya memang namanya, dan
         * "Qris" terbaca seperti salah ketik.
         */
        if (in_array($kanal, ['QRIS', 'EMONEY_QRIS'], true)) {
            return 'QRIS';
        }

        return ucwords(mb_strtolower(str_replace('_', ' ', $kanal)));
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
    private function tandaTangan(string $jalur, string $requestId, string $waktu, ?string $isi = null): string
    {
        $bahan = [
            'Client-Id:' . $this->clientId(),
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $waktu,
            'Request-Target:' . $jalur,
        ];

        /*
         * Digest HANYA untuk permintaan yang berbadan.
         *
         * Aturan DOKU: permintaan GET — dan Check Status memakai GET — tidak
         * menyertakan Digest sama sekali. Menyertakannya dengan badan kosong
         * membuat tanda tangannya tidak cocok, dan jawabannya 401 tanpa
         * penjelasan apa pun tentang bagian mana yang salah.
         */
        if ($isi !== null) {
            $bahan[] = 'Digest:' . base64_encode(hash('sha256', $isi, true));
        }

        return 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', implode("\n", $bahan), $this->rahasia(), true)
        );
    }

    /**
     * Menanyakan LANGSUNG ke DOKU: tagihan ini sudah dibayar atau belum.
     *
     * Dipakai halaman status saat pembayar mendarat kembali, supaya ia tidak
     * perlu menunggu pemberitahuan dari peladen DOKU. Pemberitahuan itu
     * tetap jalan dan tetap jadi pegangan utama — ia yang datang walau
     * halamannya sudah ditutup — tetapi ia tidak selalu cepat, dan di mesin
     * pengembang tidak pernah sampai sama sekali.
     *
     * @return array{tahu: bool, lunas: bool, gagal: bool, status: ?string}
     *         tahu=false berarti DOKU tidak menjawab atau belum mengenal
     *         nomornya — BUKAN berarti belum dibayar.
     */
    public function periksaStatus(string $nomor): array
    {
        $gagalTahu = [
            'tahu' => false, 'lunas' => false, 'gagal' => false,
            'status' => null, 'kanal' => null, 'waktu' => null,
        ];

        if (! $this->siap() || trim($nomor) === '') {
            return $gagalTahu;
        }

        $jalur = '/orders/v1/status/' . rawurlencode(trim($nomor));
        $requestId = (string) Str::uuid();
        $waktu = gmdate('Y-m-d\TH:i:s\Z');

        try {
            $jawab = Http::timeout(10)
                ->withHeaders([
                    'Client-Id' => $this->clientId(),
                    'Request-Id' => $requestId,
                    'Request-Timestamp' => $waktu,
                    // Tanpa isi: GET tidak membawa Digest.
                    'Signature' => $this->tandaTangan($jalur, $requestId, $waktu),
                ])
                ->get($this->pangkalan() . $jalur);
        } catch (\Throwable $e) {
            report($e);

            return $gagalTahu;
        }

        if (! $jawab->successful()) {
            /*
             * TIDAK dicatat sebagai galat.
             *
             * Nomor yang belum pernah dibayar wajar belum dikenal DOKU, dan
             * halaman status menanyakannya berkali-kali — dicatat sebagai
             * galat, lognya penuh oleh keadaan yang sepenuhnya normal.
             */
            return $gagalTahu;
        }

        $data = $jawab->json();

        $status = mb_strtolower((string) (
            data_get($data, 'transaction.status')
            ?? data_get($data, 'order.status')
            ?? ''
        ));

        return [
            'tahu' => $status !== '',
            'lunas' => in_array($status, ['success', 'settlement', 'paid'], true),
            'gagal' => in_array($status, ['failed', 'expired', 'cancel', 'canceled'], true),
            'status' => $status ?: null,
            'kanal' => $this->namaKanal($data),

            /*
             * Kapan transaksinya terjadi di gerbang.
             *
             * Dipakai pemanggilnya untuk menolak pembayaran yang LEBIH TUA
             * daripada pendaftarannya sendiri — nomor tagihan bisa terpakai
             * ulang, dan pembayaran milik pendaftaran lain tidak boleh
             * diwariskan.
             */
            'waktu' => $this->waktuTransaksi($data),
        ];
    }
}
