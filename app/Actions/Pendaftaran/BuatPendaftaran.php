<?php

namespace App\Actions\Pendaftaran;

use App\KategoriLayanan;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\DB;

/**
 * Mendaftarkan seseorang dari sisi panitia, untuk layanan mana pun.
 *
 * Sebelum ini TIDAK ADA jalurnya sama sekali: yang mendaftar lewat WhatsApp,
 * datang langsung, atau membayar di tempat tidak bisa dimasukkan ke sistem,
 * sehingga daftar pendaftar tidak pernah lengkap dan kuota angkatan tidak
 * mencerminkan kursi yang sebenarnya terpakai.
 *
 * Barisnya dibuat SERUPA dengan yang datang dari jalur publik — nomor
 * mengikuti pola layanan yang sama, status awal yang sama, kode unik dalam
 * rentang yang sama — supaya tidak ada dua jenis pendaftaran yang harus
 * diperlakukan berbeda di layar mana pun.
 *
 * Yang DIKERJAKAN sendiri oleh sistem, bukan diketik panitia: nomor
 * pendaftaran, status awal, kode unik, dan total bayar untuk layanan
 * berangkatan. Itu inti "minim isian" — yang tersisa diketik hanya nama,
 * email, nomor telepon, dan pilihan angkatannya.
 */
class BuatPendaftaran
{
    /**
     * @param  array<string, mixed>  $isian
     * @return array{berhasil:bool, pesan:string, model:mixed}
     */
    public function jalankan(string $layanan, array $isian, ?string $olehSiapa = null): array
    {
        $sumber = Pendaftaran::sumber($layanan);

        if ($sumber === null) {
            return ['berhasil' => false, 'pesan' => 'Layanan itu tidak dikenali.', 'model' => null];
        }

        $jumlah = max(1, (int) ($isian['jumlah'] ?? 1));
        $angkatan = null;
        $galat = null;
        $dibuat = null;

        DB::transaction(function () use (
            $layanan, $sumber, $isian, $jumlah, $olehSiapa, &$angkatan, &$galat, &$dibuat
        ) {
            /*
             * Angkatannya DIKUNCI sebelum kuotanya dibaca.
             *
             * Tanpa lockForUpdate, dua panitia yang mendaftarkan orang pada
             * detik yang sama sama-sama membaca sisa kuota yang belum
             * dikurangi, dan keduanya lolos walau kursinya tinggal satu.
             */
            if (Pendaftaran::berangkatan($layanan)) {
                $angkatan = KategoriLayanan::whereKey($isian['kategori_id'] ?? null)
                    ->lockForUpdate()->first();

                if ($angkatan === null) {
                    $galat = 'Angkatan yang dipilih tidak ditemukan.';

                    return;
                }

                if ($angkatan->layanan !== $layanan) {
                    // Angkatan milik layanan lain membuat kuota keduanya salah
                    // tanpa ada yang menolak.
                    $galat = 'Angkatan itu bukan milik layanan yang dipilih.';

                    return;
                }

                if ($angkatan->total_kuota !== null && (int) $angkatan->sisa_kuota < $jumlah) {
                    $galat = 'Kursinya tidak cukup — tersisa ' . (int) $angkatan->sisa_kuota
                        . ' dari ' . (int) $angkatan->total_kuota . '.';

                    return;
                }
            }

            $subtotal = $this->hitungTotal($layanan, $angkatan, $jumlah, $isian);

            /*
             * Potongan khusus, DI ATAS potongan bawaan angkatannya.
             *
             * Angkatan sudah punya diskonnya sendiri dan itu sudah terhitung
             * di `total_biaya`; yang ini untuk hal yang tidak bisa diketahui
             * angkatan — peserta yang disponsori, harga mitra, atau
             * kesepakatan di tempat. Dibatasi subtotalnya: potongan yang
             * melebihi tagihan menghasilkan total negatif, dan nominal
             * transfer negatif tidak berarti apa-apa.
             */
            /*
             * Potongan ALUMNI dan potongan khusus TIDAK BISA DIGABUNG.
             *
             * Diminta pemilik, dan memang begitu mestinya: dua potongan yang
             * ditumpuk pada satu pendaftaran membuat harga akhirnya tidak bisa
             * dijelaskan dari salah satunya, dan panitia yang memberi potongan
             * khusus kepada seorang alumni hampir selalu bermaksud
             * MENGGANTIKAN potongan alumninya, bukan menambahnya.
             *
             * Yang alumni menang, dan itu disengaja: ia datang dari aturan
             * yang disetel sekali di Tarif Layanan, sementara potongan khusus
             * diketik per pendaftaran. Aturan mengalahkan ketikan.
             */
            $alumni = ! empty($isian['alumni']);
            $persenAlumni = $alumni ? $this->persenAlumni($layanan, $angkatan) : 0;

            $persenRombongan = $this->persenRombongan($layanan, $angkatan, $jumlah);

            /*
             * Urutannya: alumni, lalu rombongan, lalu potongan khusus — dan
             * hanya SATU yang berlaku.
             *
             * Alumni di depan sebab ia milik orangnya, bukan pesanannya.
             * Rombongan sesudahnya sebab ia berlaku sendiri begitu jumlahnya
             * mencapai ambang, tanpa panitia perlu mengingat besarannya.
             * Potongan khusus terakhir: ia yang diketik tangan, dan sesuatu
             * yang diketik tangan tidak boleh diam-diam ditumpuk di atas
             * potongan yang dihitung sistem.
             */
            if ($persenAlumni > 0) {
                $potongan = (int) round($subtotal * $persenAlumni / 100);
                $kodePotongan = 'ALUMNI';
            } elseif ($persenRombongan > 0) {
                $potongan = (int) round($subtotal * $persenRombongan / 100);
                $kodePotongan = 'ROMBONGAN';
            } else {
                $potongan = max(0, (int) preg_replace('/\D+/', '', (string) ($isian['potongan'] ?? 0)));
                $kodePotongan = trim((string) ($isian['kode_potongan'] ?? '')) ?: 'KHUSUS';
            }

            // Dibatasi subtotalnya: potongan yang melebihi tagihan
            // menghasilkan total negatif, dan nominal transfer negatif tidak
            // berarti apa-apa.
            $potongan = min($subtotal, $potongan);
            $total = $subtotal - $potongan;

            $kodeUnik = $this->kodeUnikBebas($layanan, $isian['kategori_id'] ?? null, $total);

            /*
             * Kode uniknya DITAMBAHKAN ke total, bukan disimpan di sebelahnya.
             *
             * Begitulah jalur pendaftaran umum menyimpannya — terukur di basis
             * data: total 4.500.072 dengan kode 72, 4.275.028 dengan kode 28.
             * Jalur panitia tidak melakukannya, jadi barisnya tersimpan
             * 4.950.000 dengan kode 50: nominal yang diminta ke pendaftar
             * tidak pernah memuat penandanya, dan tidak ada transfer yang bisa
             * dicocokkan dengan kode itu. Surat ke pendaftarnya bahkan
             * menuliskan "angka 50 di ujungnya" pada angka yang berakhir 000.
             */
            if (isset($sumber['kolom']['kode_unik'])) {
                $total += $kodeUnik;
            }

            $baris = $this->rakitKolom(
                $layanan, $sumber, $isian, $jumlah, $total, $kodeUnik, $olehSiapa,
                $potongan, $kodePotongan
            );

            $model = $sumber['model'];
            $dibuat = $model::create($baris);

            $this->simpanBukti($layanan, $sumber, $dibuat, $isian['bukti'] ?? null);
            $this->simpanPeserta($layanan, $dibuat, $isian['peserta'] ?? null, $jumlah);

            /*
             * Diikat ke pesanan lembaganya, kalau ada.
             *
             * Kuota tiap angkatan 20 kursi sedangkan lembaga rutin memesan
             * lebih, jadi pesanannya terpaksa dipecah ke beberapa angkatan.
             * Tanpa tali ini, hasil pecahannya tidak saling tahu bahwa mereka
             * satu pesanan — merekap dan menagihnya berarti mengumpulkan
             * barisnya satu per satu dari ingatan.
             */
            $pesanan = trim((string) ($isian['pemesanan_id'] ?? ''));

            if ($pesanan !== '') {
                $lembaga = \App\PemesananLembaga::find($pesanan);

                if ($lembaga !== null) {
                    $lembaga->ikat($layanan, (string) $dibuat->getKey());
                }
            }

            if ($angkatan !== null && $angkatan->total_kuota !== null) {
                $angkatan->forceFill([
                    'sisa_kuota' => (string) max(0, (int) $angkatan->sisa_kuota - $jumlah),
                ])->save();
            }
        });

        if ($galat !== null) {
            return ['berhasil' => false, 'pesan' => $galat, 'model' => null];
        }

        $this->kabariPendaftar($layanan, $dibuat, $isian);

        return [
            'berhasil' => true,
            'model' => $dibuat,
            'pesan' => 'Pendaftaran ' . $dibuat->{Pendaftaran::kolomNomor($layanan)}
                . ' atas nama ' . ($isian['nama'] ?? '-') . ' tersimpan.',
        ];
    }

    /**
     * Menyimpan bukti bayar yang ikut diunggah, kalau ada.
     *
     * Ditaruh di `public/<folder>/`, bukan di cakram 'unggahan': di situlah
     * seluruh bukti lama berada, dan layar yang menampilkannya serta
     * penghapusnya sama-sama merakit alamatnya dari sana. Menaruh yang baru
     * di tempat lain berarti dua konvensi yang harus diingat selamanya.
     *
     * Kegagalan menyimpan berkas TIDAK menggagalkan pendaftarannya: barisnya
     * sudah benar, dan bukti yang bisa diunggah ulang kapan saja dari halaman
     * rincian tidak sepadan dengan membatalkan pendaftaran orang.
     */
    private function simpanBukti(string $layanan, array $sumber, $model, $berkas): void
    {
        $folder = $sumber['bukti_folder'] ?? null;

        if ($folder === null || ! isset($sumber['kolom']['bukti']) || $berkas === null) {
            return;
        }

        if (! $berkas instanceof \Illuminate\Http\UploadedFile || ! $berkas->isValid()) {
            return;
        }

        try {
            $tujuan = public_path($folder);

            if (! is_dir($tujuan)) {
                mkdir($tujuan, 0755, true);
            }

            /*
             * Namanya dirakit sistem, bukan memakai nama asli kiriman.
             *
             * Nama berkas dari ponsel sering memuat spasi dan tanda kutip, dan
             * firewall hosting menolak alamat berapostrof dengan 403 sebelum
             * PHP sempat jalan — buktinya tersimpan tetapi tidak pernah bisa
             * dibuka.
             */
            $nama = $layanan . '-' . now()->format('Ymd-His') . '-'
                . \Illuminate\Support\Str::random(6) . '.'
                . strtolower($berkas->getClientOriginalExtension() ?: 'jpg');

            $berkas->move($tujuan, $nama);

            $model->forceFill([$sumber['kolom']['bukti'] => $nama])->save();
        } catch (\Throwable $e) {
            \Log::error('Bukti bayar gagal disimpan', [
                'layanan' => $layanan,
                'sebab' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Menyimpan nama peserta lain pada pendaftaran rombongan.
     *
     * Kelima tabel pendaftaran hanya punya SATU nama, sementara jumlahnya
     * bisa lebih dari satu — jadi sebelum ini nama peserta selain pemesannya
     * tidak tercatat di mana pun, dan daftar hadir rombongan tidak bisa
     * dibuat dari sistem.
     *
     * Dibaca dari satu kotak teks, satu nama per baris. Bukan sederet isian
     * terpisah: yang mengisinya panitia yang sedang menghadapi antrean, dan
     * menempelkan daftar nama dari pesan WhatsApp jauh lebih cepat daripada
     * mengetik ke lima kotak.
     *
     * Dibatasi jumlah yang dibayar: nama ke-enam pada rombongan berbayar lima
     * adalah orang yang kursinya tidak pernah dibeli.
     */
    private function simpanPeserta(string $layanan, $model, $teks, int $jumlah): void
    {
        $baris = preg_split('/\r\n|\r|\n/', (string) $teks) ?: [];
        $nama = [];

        foreach ($baris as $b) {
            // Penomoran yang ikut tersalin dari WhatsApp ("1. Budi") dibuang;
            // nama orang tidak berawalan angka dan titik.
            $bersih = trim(preg_replace('/^\s*\d+\s*[.)-]\s*/', '', $b));

            if ($bersih !== '') {
                $nama[] = mb_substr($bersih, 0, 255);
            }
        }

        if ($nama === []) {
            return;
        }

        /*
         * Pemesannya sendiri sudah tercatat di baris pendaftarannya, jadi
         * kotak ini untuk SISANYA — paling banyak jumlah dikurangi satu.
         */
        $nama = array_slice($nama, 0, max(0, $jumlah - 1));

        foreach ($nama as $ke => $n) {
            \App\PendaftaranPeserta::create([
                'layanan' => $layanan,
                'pendaftaran_id' => (string) $model->getKey(),
                // Mulai dari 1: urutan 0 disediakan untuk pemesannya, yang
                // tersimpan di baris pendaftarannya sendiri.
                'urutan' => $ke + 1,
                'nama' => $n,
            ]);
        }
    }

    /**
     * Mengirim nomor pendaftaran dan kode uniknya ke orang yang didaftarkan.
     *
     * Keduanya baru dibuat saat barisnya disimpan, dan justru itu yang harus
     * sampai ke orangnya — kode uniknya yang membuat nominal transfernya bisa
     * dicocokkan dengan mutasi rekening. Sebelum ini tidak ada surat sama
     * sekali dari jalur panitia, jadi keduanya disalin manual ke WhatsApp.
     *
     * Gagal kirim TIDAK menggagalkan pendaftarannya: barisnya sudah tersimpan
     * dan benar, sedangkan surat bisa diulang dari halaman rincian. Melempar
     * galat di sini berarti panitia melihat halaman galat untuk pendaftaran
     * yang sebenarnya berhasil, lalu mendaftarkannya lagi.
     */
    private function kabariPendaftar(string $layanan, $model, array $isian): void
    {
        $email = trim((string) ($isian['email'] ?? ''));

        if ($email === '' || empty($isian['kabari'])) {
            return;
        }

        try {
            $sumber = Pendaftaran::sumber($layanan);
            $kolom = $sumber['kolom'];
            $angkatan = null;
            $tanggal = null;

            if (! empty($isian['kategori_id'])) {
                $a = KategoriLayanan::find($isian['kategori_id']);

                if ($a !== null) {
                    $angkatan = trim($a->nama . ($a->nama_ke ? ' (angkatan ke-' . $a->nama_ke . ')' : ''));
                    $tanggal = $a->mulai
                        ? \Illuminate\Support\Carbon::parse($a->mulai)->locale('id')->translatedFormat('j F Y')
                        : null;
                }
            }

            \Mail::to($email)->send(new \App\Mail\PendaftaranDicatatMail(
                nama: (string) ($isian['nama'] ?? '-'),
                layanan: $sumber['nama'],
                nomor: (string) $model->{$sumber['kolom_nomor']},
                total: (int) $model->{$kolom['total']},
                kodeUnik: isset($kolom['kode_unik']) ? (int) $model->{$kolom['kode_unik']} : 0,
                angkatan: $angkatan,
                tanggal: $tanggal,
                caraBayar: (string) ($isian['cara_bayar'] ?? 'transfer'),
                sudahLunas: Pendaftaran::keadaanDari((string) $model->status) === 'lunas',
            ));
        } catch (\Throwable $e) {
            \Log::error('Surat pendaftaran gagal dikirim', [
                'layanan' => $layanan,
                'sebab' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Total bayar: dari angkatannya kalau ada, dari ketikan panitia kalau tidak.
     *
     * Scopus Kafe dan Clinik Scopus tidak berangkatan — tarifnya per sesi dan
     * berbeda-beda — jadi nominalnya memang harus diketik. Tiga layanan
     * lainnya mengambilnya dari angkatan, dan itu yang membuat borangnya
     * minim isian: panitia memilih angkatan, harganya ikut.
     */
    private function hitungTotal(string $layanan, ?KategoriLayanan $angkatan, int $jumlah, array $isian): int
    {
        if ($angkatan === null) {
            return max(0, (int) preg_replace('/\D+/', '', (string) ($isian['total'] ?? 0)));
        }

        // total_biaya sudah memperhitungkan diskon angkatannya; biaya adalah
        // harga sebelum potongan. Yang dipakai yang sudah berdiskon, sebab
        // itulah yang ditagihkan ke orangnya.
        $satuan = (int) ($angkatan->total_biaya ?: $angkatan->biaya);

        return $satuan * $jumlah;
    }

    /**
     * Berapa persen potongan alumni untuk layanan dan angkatan ini.
     *
     * Dicari lewat VARIAN angkatannya, bukan layanannya saja: satu layanan
     * bisa punya beberapa tarif dengan varian berbeda — Scopus Camp punya
     * jawa dan luar_jawa dengan harga Rp 5,5jt dan Rp 6,5jt — dan potongan
     * alumninya disetel per tarif. Memakai tarif mana pun yang kebetulan
     * ketemu duluan berarti memberi potongan varian lain.
     *
     * Nol kalau tarifnya belum menyetel potongan alumni; borangnya memang
     * tidak menawarkan pilihan alumni untuk layanan seperti itu, tetapi
     * kiriman tetap diperiksa di sini — yang menentukan peladen, bukan
     * markah yang bisa diubah dari peramban.
     */
    private function persenAlumni(string $layanan, ?KategoriLayanan $angkatan): int
    {
        return (int) ($this->tarifBerlaku($layanan, $angkatan)->diskon_alumni_persen ?? 0);
    }

    /**
     * Potongan rombongan, atau 0 kalau jumlahnya belum mencapai ambangnya.
     *
     * Sebelum ini satu-satunya cara memberi harga rombongan adalah potongan
     * khusus berupa rupiah — panitia menghitung sendiri diskon 30 orangnya
     * lalu mengetik hasilnya, dan besarannya bergantung ingatan orang.
     */
    private function persenRombongan(string $layanan, ?KategoriLayanan $angkatan, int $jumlah): int
    {
        $tarif = $this->tarifBerlaku($layanan, $angkatan);

        $min = (int) ($tarif->diskon_rombongan_min ?? 0);
        $persen = (int) ($tarif->diskon_rombongan_persen ?? 0);

        if ($min < 2 || $persen < 1 || $jumlah < $min) {
            return 0;
        }

        return $persen;
    }

    /**
     * Tarif aktif untuk satu layanan, menurut varian angkatannya.
     *
     * Dibaca sekali dan dipakai ulang: dua potongan berbeda membacanya, dan
     * dua kueri untuk baris yang sama berarti dua kesempatan keduanya
     * membaca tarif yang berbeda kalau ada yang menyuntingnya di antaranya.
     */
    private function tarifBerlaku(string $layanan, ?KategoriLayanan $angkatan): object
    {
        $kunci = $layanan . '|' . ($angkatan->varian ?? '');

        if (! isset($this->tarifTersimpan[$kunci])) {
            $this->tarifTersimpan[$kunci] = \App\ClinikScopusBiayaPersesi::query()
                ->where('status', \App\ClinikScopusBiayaPersesi::AKTIF)
                ->where('layanan', $layanan)
                ->when(
                    $angkatan !== null && $angkatan->varian !== null && $angkatan->varian !== '',
                    fn ($q) => $q->where('varian', $angkatan->varian),
                    fn ($q) => $q->whereNull('varian')
                )
                ->first(['diskon_alumni_persen', 'diskon_rombongan_min', 'diskon_rombongan_persen'])
                ?? (object) [];
        }

        return $this->tarifTersimpan[$kunci];
    }

    /** @var array<string, object> */
    private array $tarifTersimpan = [];

    /**
     * Kode unik yang membuat TOTAL-nya belum terpakai.
     *
     * Gunanya mencocokkan mutasi rekening: dua orang yang membayar nominal
     * yang sama persis tidak bisa dibedakan. Jadi yang harus unik bukan
     * kodenya melainkan HASIL PENJUMLAHANNYA — dan itu yang diperiksa.
     *
     * Dibandingkan hanya terhadap pendaftaran yang masih menunggu: yang sudah
     * lunas uangnya sudah masuk dan tidak perlu dicocokkan lagi, dan
     * membandingkan terhadap seluruh riwayat akan kehabisan kode.
     */
    private function kodeUnikBebas(string $layanan, $kategoriId, int $total): int
    {
        [$min, $maks] = Pendaftaran::rentangKodeUnik($layanan);

        $terpakai = Pendaftaran::kueri()
            ->where('layanan', $layanan)
            ->when($kategoriId, fn ($q) => $q->where('angkatan_id', $kategoriId))
            ->whereIn('status', Pendaftaran::KEADAAN['menunggu']['nilai'])
            /*
             * Yang diadu NOMINAL TRANSFERNYA, dan nominal itu sudah termasuk
             * kode uniknya di kolom `total` — menambahkannya sekali lagi
             * menghitung kodenya dua kali, sehingga bentrokan yang sebenarnya
             * tidak pernah terlihat.
             */
            ->selectRaw('CAST(total AS UNSIGNED) as jumlahnya')
            ->pluck('jumlahnya')
            ->map(fn ($x) => (int) $x)
            ->all();

        $terpakai = array_flip($terpakai);

        // Empat puluh lemparan acak dulu supaya kodenya tidak berurutan —
        // kode yang bisa ditebak membuat orang mengarang bukti transfer.
        for ($i = 0; $i < 40; $i++) {
            $kode = random_int($min, $maks);

            if (! isset($terpakai[$total + $kode])) {
                return $kode;
            }
        }

        // Baru menyisir berurutan kalau yang acak gagal terus.
        for ($kode = $min; $kode <= $maks; $kode++) {
            if (! isset($terpakai[$total + $kode])) {
                return $kode;
            }
        }

        throw new \RuntimeException(
            'Seluruh kode unik ' . $min . '-' . $maks . ' sudah terpakai untuk nominal itu.'
        );
    }

    /**
     * Kolom yang ditulis, dipetakan dari nama seragam ke nama kolom aslinya.
     *
     * @return array<string, mixed>
     */
    private function rakitKolom(
        string $layanan,
        array $sumber,
        array $isian,
        int $jumlah,
        int $total,
        int $kodeUnik,
        ?string $olehSiapa,
        int $potongan = 0,
        string $kodePotongan = 'KHUSUS'
    ): array {
        $kolom = $sumber['kolom'];

        /*
         * Cara bayar dibatasi ke yang BOLEH dipilih panitia, bukan ke seluruh
         * daftar: 'doku' ditulis jalur pendaftaran umum saat tagihannya
         * dibuat, dan panitia yang memilihnya berarti baris bertanda sudah
         * dibayar daring padahal tidak ada tagihan yang pernah dibuat.
         */
        $caraBayar = (string) ($isian['cara_bayar'] ?? 'transfer');

        if (! isset(Pendaftaran::caraBayarPilihan()[$caraBayar])) {
            $caraBayar = 'transfer';
        }

        $status = Pendaftaran::statusAwal($layanan);

        /*
         * Uang tunai yang SUDAH diterima panitia langsung dicatat lunas.
         *
         * Tanpa ini pendaftar yang membayar di tempat tetap duduk di
         * "menunggu bayar" sampai ada yang ingat mengubahnya satu per satu —
         * padahal uangnya sudah di tangan, dan panitia yang mencatatnya
         * adalah orang yang sama yang menerimanya.
         *
         * Hanya untuk tunai: transfer masih perlu dicocokkan dengan mutasi
         * rekening, dan melunaskannya dari borang berarti melunaskan sebelum
         * ada yang memeriksa buktinya.
         */
        $lunasTunai = $caraBayar === 'tunai' && ! empty($isian['uang_diterima']);

        if ($lunasTunai) {
            $status = Pendaftaran::statusLunas($layanan) ?? $status;
        }

        $baris = [
            $sumber['kolom_nomor'] => Pendaftaran::nomorBaru($layanan),
            'status' => $status,
        ];

        if (isset($kolom['cara_bayar'])) {
            $baris[$kolom['cara_bayar']] = $caraBayar;
        }

        /*
         * Varian yang dipilih sendiri, hanya untuk layanan tanpa angkatan.
         *
         * Dicocokkan ke daftar varian layanannya, bukan diterima apa adanya:
         * daftarnya hidup di kolom JSON `layanan.varian`, dan nilai di luar
         * daftar berarti baris yang tarifnya tidak akan pernah ketemu.
         */
        /*
         * Nama sesi, untuk tabel yang memang punya kolomnya (Scopus Kafe).
         *
         * Tabelnya menyimpan sampai tiga sesi beserta biaya masing-masing;
         * borang panitia mengisi yang PERTAMA saja, dan itu yang selalu
         * terisi di baris mana pun. Tanpa ini, pendaftaran Scopus Kafe lewat
         * jalur panitia tidak pernah menyebut sesi apa yang diambil.
         */
        if (isset($kolom['sesi']) && $kolom['sesi'] === 'sesi') {
            $sesi = trim((string) ($isian['sesi'] ?? ''));

            if ($sesi !== '') {
                $baris['sesi'] = $sesi;
            }
        }

        if (isset($kolom['varian'])) {
            $pilihan = \App\Layanan::katalog()[$layanan]['varian'] ?? [];
            $varian = (string) ($isian['varian'] ?? '');

            $baris[$kolom['varian']] = array_key_exists($varian, $pilihan) ? $varian : null;
        }

        // Nama kolom berbeda di tiap tabel — nama vs nama_pemesan, telp vs
        // telp_pemesan — jadi dipetakan lewat katalog, bukan ditulis lima kali.
        foreach (['nama_orang' => 'nama', 'email' => 'email', 'telp' => 'telp', 'affiliasi' => 'affiliasi'] as $seragam => $dariIsian) {
            if (isset($kolom[$seragam]) && ($isian[$dariIsian] ?? null) !== null) {
                $baris[$kolom[$seragam]] = $isian[$dariIsian];
            }
        }

        if (isset($kolom['angkatan_id']) && ! empty($isian['kategori_id'])) {
            $baris[$kolom['angkatan_id']] = $isian['kategori_id'];
        }

        // jumlah_pendaftar hanya dipunyai tiga tabel; dua lainnya memang
        // selalu satu orang per baris.
        if (isset($kolom['jumlah'])) {
            $baris[$kolom['jumlah']] = $jumlah;
        }

        $baris[$kolom['total']] = $total;

        if (isset($kolom['kode_unik'])) {
            $baris[$kolom['kode_unik']] = $kodeUnik;
        }

        /*
         * Potongan khusus hanya ditulis ke tabel yang punya kolomnya —
         * Scopus Kafe tidak punya, dan di sana nominalnya memang diketik
         * langsung sehingga potongannya sudah termasuk di dalamnya.
         *
         * Kodenya ikut disimpan: tanpa keterangan, potongan Rp 500.000 pada
         * satu pendaftaran tidak bisa dijelaskan siapa pun enam bulan
         * kemudian.
         */
        if ($potongan > 0 && isset($kolom['nominal_diskon'])) {
            $baris[$kolom['nominal_diskon']] = $potongan;

            if (isset($kolom['kode_diskon'])) {
                $baris[$kolom['kode_diskon']] = $kodePotongan;
            }
        }

        $catatan = trim((string) ($isian['note'] ?? ''));
        $kolomCatatan = Pendaftaran::kolomCatatan($layanan);

        if ($kolomCatatan !== null) {
            // Jejak SIAPA yang mendaftarkan: baris yang dibuat panitia tidak
            // punya alamat IP maupun jejak peramban seperti yang dari jalur
            // publik, jadi tanpa ini tidak ada keterangan asal-usulnya.
            $jejak = 'Didaftarkan panitia' . ($olehSiapa !== null ? ' oleh ' . $olehSiapa : '')
                . ' pada ' . now()->format('d M Y H:i')
                . ', ' . Pendaftaran::caraBayar($caraBayar)['label']
                . ($lunasTunai ? ' (uang sudah diterima)' : '')
                . ($potongan > 0
                    ? ' dengan potongan Rp ' . number_format($potongan, 0, ',', '.')
                        . ' (' . $kodePotongan . ')'
                    : '');

            $baris[$kolomCatatan] = $catatan !== '' ? $catatan . ' | ' . $jejak : $jejak;
        }

        return $baris + $this->kolomWajibKhusus($layanan, $isian);
    }

    /**
     * Kolom NOT NULL tanpa nilai bawaan yang hanya dipunyai satu tabel.
     *
     * Clinik Scopus menuntut sesi, trainer, dan pelanggannya; tanpa ketiganya
     * MySQL menolak barisnya dengan galat yang hanya menyebut nama kolomnya.
     *
     * @return array<string, mixed>
     */
    private function kolomWajibKhusus(string $layanan, array $isian): array
    {
        if ($layanan !== 'clinik_scopus') {
            return [];
        }

        return [
            'clinikscopus_id' => $isian['clinikscopus_id'] ?? null,
            'trainer_id' => $isian['trainer_id'] ?? null,
            'customer_id' => $isian['customer_id'] ?? null,
            'kode_booking' => $isian['kode_booking'] ?? ('BOOK-' . now()->format('dmYHis')),
            'sesi' => $isian['sesi'] ?? null,
            'jam_sesi' => $isian['jam_sesi'] ?? null,
        ];
    }
}
