<?php

namespace App\Actions\Pendaftaran;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\DB;

/**
 * Menyunting data satu pendaftaran, layanan apa pun.
 *
 * Medannya berbeda per layanan dan tidak bisa diseragamkan jadi satu borang:
 * Scopus Camp dan Bibliometrik berangkatan dan berombongan, Scopus Kafe
 * memuat tiga sesi dalam satu baris, Clinik Scopus bersesi tunggal berjam,
 * Webinar Eksklusif berangkatan tanpa nominal yang boleh disunting.
 *
 * Jadi yang diseragamkan bukan borangnya melainkan ATURANNYA: satu daftar
 * medan per layanan di bawah, satu pembersih nominal, dan satu aturan kuota.
 *
 * Statusnya TIDAK disunting di sini. Ia punya tindakan sendiri sebab
 * memindahkannya mengirim email dan menggeser kuota — dua hal yang tidak
 * boleh ikut terjadi hanya karena seseorang membetulkan ejaan nama.
 */
class UbahDataPendaftaran
{
    /**
     * Medan yang boleh disunting per layanan, dan cara membacanya.
     *
     * 'teks' apa adanya, 'uang' dibersihkan dari pemisah ribuan, 'angka'
     * bilangan bulat, 'tanggal' dan 'waktu' dibiarkan kosong kalau kosong.
     *
     * Daftar PUTIH, bukan `$request->all()`: kolom seperti `status`,
     * `gambar`, `token`, dan `id_transaksi` tidak boleh ikut berubah hanya
     * karena namanya muncul di kiriman.
     */
    private const MEDAN = [
        /*
         * tanggal_reschedule DIBUANG dari borang, dan group_wa ikut dibuang
         * KHUSUS di sini.
         *
         * Keduanya tidak pernah dibaca apa pun untuk Scopus Camp: ditelusuri
         * ke seluruh kode, surat "diterima" maupun "reschedule" mengambil
         * tautan grupnya dari ANGKATAN (CategoriesScopusCamp->group_wa), bukan
         * dari baris pendaftarannya. Dua isian yang harus diisi panitia tanpa
         * ada satu pun yang membacanya.
         *
         * Kolomnya tetap ada di basis data — yang dibuang pintu masuknya, bukan
         * datanya.
         */
        'scopus_camp' => [
            'nama' => 'teks', 'email' => 'teks', 'telp' => 'teks', 'affiliasi' => 'teks',
            'kategori_id' => 'teks', 'jumlah_pendaftar' => 'angka',
            'ppn' => 'uang', 'kode_unik' => 'uang', 'nominal_diskon' => 'uang',
            'total_pembayaran' => 'uang', 'kode_diskon' => 'teks',
            'note' => 'teks',
        ],
        /*
         * group_wa DIPERTAHANKAN di sini, dan hanya di sini.
         *
         * Berbeda dengan Scopus Camp, surat Bibliometrik membaca tautan grup
         * dari BARIS PENDAFTARANNYA — AnalisisBibliometrik->group_wa muncul di
         * surat "diterima" dan "reschedule" sebagai "Grup WhatsApp peserta".
         * Membuangnya di sini berarti kolom yang terbit di surat tidak bisa
         * diisi dari mana pun.
         *
         * tanggal_reschedule tetap dibuang: bahkan surat yang bernama
         * mail_reschedule pun tidak membacanya.
         */
        'bibliometrik' => [
            'nama' => 'teks', 'email' => 'teks', 'telp' => 'teks', 'affiliasi' => 'teks',
            'kategori_id' => 'teks', 'jumlah_pendaftar' => 'angka',
            'ppn' => 'uang', 'kode_unik' => 'uang', 'nominal_diskon' => 'uang',
            'total_pembayaran' => 'uang', 'kode_diskon' => 'teks',
            'group_wa' => 'teks', 'note' => 'teks',
        ],
        'webinar_eksklusif' => [
            // Nominalnya TIDAK disunting: totalnya memuat kode unik
            // Rp 500-1.500 yang dipakai mencocokkan transfer, dan mengubahnya
            // dengan tangan membuat pencocokan itu tidak mungkin lagi.
            'nama' => 'teks', 'email' => 'teks', 'telp' => 'teks', 'affiliasi' => 'teks',
            'kategori_id' => 'teks', 'note' => 'teks',
        ],
        'scopus_kafe' => [
            'nama' => 'teks', 'email' => 'teks', 'telp' => 'teks',
            'tanggal_pemesanan' => 'tanggal',
            'sesi' => 'teks', 'waktu_mulai' => 'waktu', 'waktu_selesai' => 'waktu',
            'lokasi' => 'teks', 'biaya' => 'uang', 'kode_unik_pembayaran' => 'uang',
            'subtotal_pembayaran' => 'uang',
            'sesi_kedua' => 'teks', 'waktu_mulai_kedua' => 'waktu', 'waktu_selesai_kedua' => 'waktu',
            'lokasi_kedua' => 'teks', 'biaya_kedua' => 'uang',
            'kode_unik_pembayaran_kedua' => 'uang', 'subtotal_pembayaran_kedua' => 'uang',
            'sesi_ketiga' => 'teks', 'waktu_mulai_ketiga' => 'waktu', 'waktu_selesai_ketiga' => 'waktu',
            'lokasi_ketiga' => 'teks', 'biaya_ketiga' => 'uang',
            'kode_unik_pembayaran_ketiga' => 'uang', 'subtotal_pembayaran_ketiga' => 'uang',
            'total_keseluruhan_pembayaran' => 'uang',
        ],
        'clinik_scopus' => [
            'nama_pemesan' => 'teks', 'email_pemesan' => 'teks', 'telp_pemesan' => 'teks',
            'afiliasi_pemesan' => 'teks', 'sesi' => 'teks', 'jam_sesi' => 'teks',
            'kendala' => 'teks', 'desc_kendala' => 'teks',
        ],
    ];

    /**
     * Medan yang nilainya DIHITUNG peladen, bukan diterima dari borang.
     *
     * Kotaknya di layar dimatikan (disabled), jadi nilainya tidak pernah ikut
     * terkirim sama sekali — dan itu memang yang diinginkan: angka yang
     * menentukan berapa orang harus membayar tidak boleh bergantung pada apa
     * yang sempat dititipkan peramban.
     *
     * Rumusnya sama persis dengan yang dulu dijalankan di layar:
     *
     *     total = dasar + PPN + kode unik − potongan
     *
     * Dasarnya TIDAK ditebak dari tarif angkatan dikali jumlah orang. Angka
     * tersimpan bisa lahir dari potongan alumni, promo rombongan, atau harga
     * yang dirundingkan, dan menghitung ulang dari tarif akan menimpanya
     * diam-diam. Yang dipakai selisih dari nilai yang SUDAH tersimpan, jadi
     * menyunting medan lain tidak menggeser totalnya sepeser pun.
     */
    private const HITUNG = [
        'total_pembayaran' => [
            'tambah' => ['ppn', 'kode_unik'],
            'kurang' => ['nominal_diskon'],
        ],
    ];

    /** @return array<string, string> medan => jenisnya */
    public static function medan(string $layanan): array
    {
        return self::MEDAN[$layanan] ?? [];
    }

    /** Medan yang dihitung peladen untuk satu layanan, beserta penyusunnya. */
    public static function medanHitungan(string $layanan): array
    {
        $punya = self::medan($layanan);

        return array_filter(
            self::HITUNG,
            fn ($rumus, $hasil) => array_key_exists($hasil, $punya)
                && array_diff(array_merge($rumus['tambah'], $rumus['kurang']), array_keys($punya)) === [],
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @param  array<string, mixed>  $kiriman
     * @return array{berhasil:bool, pesan:string}
     */
    public function jalankan(string $layanan, string $id, array $kiriman): array
    {
        $medan = self::medan($layanan);

        if ($medan === []) {
            return ['berhasil' => false, 'pesan' => 'Layanan itu tidak dikenali.'];
        }

        $pendaftaran = Pendaftaran::temukan($layanan, $id);

        if ($pendaftaran === null) {
            return ['berhasil' => false, 'pesan' => 'Pendaftarannya tidak ditemukan.'];
        }

        $isi = [];

        foreach ($medan as $kolom => $jenis) {
            if (! array_key_exists($kolom, $kiriman)) {
                continue;
            }

            $isi[$kolom] = $this->baca($kiriman[$kolom], $jenis);
        }

        if ($isi === []) {
            return ['berhasil' => false, 'pesan' => 'Tidak ada yang diubah.'];
        }

        $this->hitungkan($layanan, $pendaftaran, $isi);

        /*
         * Nilai SEBELUM disimpan, untuk jejaknya.
         *
         * Dibaca di sini, bukan sesudah forceFill: sesudah itu modelnya sudah
         * memuat nilai baru dan yang lama tidak bisa diambil lagi.
         */
        $sebelum = [];

        foreach (array_keys($isi) as $kolom) {
            $sebelum[$kolom] = $pendaftaran->{$kolom};
        }

        $galat = null;

        DB::transaction(function () use ($layanan, $pendaftaran, $sebelum, &$isi, &$galat) {
            if (Pendaftaran::berangkatan($layanan)) {
                $hasil = $this->sesuaikanKuota($layanan, $pendaftaran, $isi);

                if ($hasil !== null) {
                    $galat = $hasil;

                    return;
                }
            }

            $pendaftaran->forceFill($isi)->save();

            // Di dalam transaksi yang sama dengan simpanannya: kalau salah
            // satunya gagal, keduanya mundur. Jejak tanpa perubahan — atau
            // perubahan tanpa jejak — sama-sama menyesatkan yang membacanya.
            $this->catatJejak($layanan, $pendaftaran, $sebelum, $isi);
        });

        if ($galat !== null) {
            return ['berhasil' => false, 'pesan' => $galat];
        }

        return ['berhasil' => true, 'pesan' => 'Perubahannya tersimpan.'];
    }

    /**
     * Medan hitungan diisi ulang peladen, apa pun yang dikirim borang.
     *
     * Yang dikirim DIBUANG lebih dulu, bukan dipakai kalau ada. Kotaknya
     * memang dimatikan di layar, tetapi kiriman tidak datang dari layar saja —
     * dan satu baris yang menerima total apa adanya meniadakan seluruh
     * gunanya kotak itu dimatikan.
     *
     * @param  array<string, mixed>  $isi
     */
    private function hitungkan(string $layanan, $pendaftaran, array &$isi): void
    {
        foreach (self::medanHitungan($layanan) as $hasil => $rumus) {
            unset($isi[$hasil]);

            $nilai = function (string $kolom) use ($pendaftaran, $isi): int {
                $mentah = array_key_exists($kolom, $isi) ? $isi[$kolom] : $pendaftaran->{$kolom};

                return (int) preg_replace('/\D+/', '', (string) $mentah);
            };

            $lama = fn (string $kolom) => (int) preg_replace('/\D+/', '', (string) $pendaftaran->{$kolom});

            $dasar = $lama($hasil);

            foreach ($rumus['tambah'] as $kolom) {
                $dasar -= $lama($kolom);
            }

            foreach ($rumus['kurang'] as $kolom) {
                $dasar += $lama($kolom);
            }

            $baru = $dasar;

            foreach ($rumus['tambah'] as $kolom) {
                $baru += $nilai($kolom);
            }

            foreach ($rumus['kurang'] as $kolom) {
                $baru -= $nilai($kolom);
            }

            // Tidak pernah minus: potongan yang melebihi tagihan menahan
            // totalnya di nol, bukan menyimpan angka negatif yang lalu terbit
            // di faktur sebagai utang peladen kepada pendaftarnya.
            $isi[$hasil] = (string) max(0, $baru);
        }
    }

    /**
     * Satu jejak untuk tiap medan yang BENAR-BENAR berubah.
     *
     * Satu baris per medan, bukan satu baris berisi daftar: tiap baris jadi
     * satu kalimat utuh yang bisa dibaca sendiri, dan kalimatnya dirakit saat
     * ditampilkan sehingga boleh diperbaiki kapan saja tanpa menyentuh baris
     * yang sudah tersimpan.
     *
     * Yang nilainya tidak berubah TIDAK dicatat. Borang mengirim seluruh
     * medan satu tab sekaligus, jadi tanpa penyaring ini satu tekan Simpan
     * meninggalkan belasan jejak yang semuanya berbunyi "A → A".
     *
     * @param  array<string, mixed>  $sebelum
     * @param  array<string, mixed>  $isi
     */
    private function catatJejak(string $layanan, $pendaftaran, array $sebelum, array $isi): void
    {
        $oleh = \Illuminate\Support\Facades\Auth::user();
        $medan = self::medan($layanan);

        foreach ($isi as $kolom => $baru) {
            $lama = $sebelum[$kolom] ?? null;

            // Dibandingkan sebagai untaian: kolomnya bercampur angka, uang,
            // dan teks, dan "1250000" dari borang tidak pernah identik dengan
            // 1250000 dari basis data.
            if ((string) $lama === (string) $baru) {
                continue;
            }

            \App\PendaftaranJejak::create([
                'layanan' => $layanan,
                'pendaftaran_id' => (string) $pendaftaran->getKey(),
                'aksi' => 'ubah',
                'medan' => $kolom,
                'dari' => $this->terbaca($layanan, $kolom, $medan[$kolom] ?? 'teks', $lama),
                'ke' => $this->terbaca($layanan, $kolom, $medan[$kolom] ?? 'teks', $baru),
                'oleh_id' => $oleh?->getKey(),
                'oleh_nama' => $oleh?->full_name ?? $oleh?->username,
            ]);
        }
    }

    /**
     * Nilai yang bisa dibaca panitia, bukan nilai mentah.
     *
     * Angkatan tersimpan sebagai UUID. Jejak yang berbunyi
     * "Angkatan "e111be54-…" → "a9d2f8b3-…"" tidak memberi tahu apa pun
     * kepada yang membacanya — padahal justru perpindahan angkatan yang
     * paling perlu bisa ditelusuri.
     *
     * Jenisnya diambil dari peta MEDAN yang sama yang dipakai membaca
     * kiriman, bukan dari daftar nama kolom tersendiri: satu daftar, jadi
     * medan uang yang baru ditambahkan ikut terformat tanpa disebut dua kali.
     * Terbaca mentah, jejaknya berbunyi "Total bayar "3000000" → "3250000"" —
     * angka yang harus dihitung sendiri digitnya oleh yang membacanya.
     */
    private function terbaca(string $layanan, string $kolom, string $jenis, $nilai): ?string
    {
        $nilai = $nilai === null ? null : trim((string) $nilai);

        if ($nilai === null || $nilai === '') {
            return null;
        }

        if ($jenis === 'uang') {
            return 'Rp ' . number_format((int) preg_replace('/\D+/', '', $nilai), 0, ',', '.');
        }

        if ($jenis === 'tanggal') {
            /*
             * Tanggal dibaca dalam bahasa Indonesia, bukan "2026-11-14":
             * APP_LOCALE=en, jadi translatedFormat perlu locale('id') di
             * depannya — pola yang sama dipakai seluruh pengakses tanggal di
             * aplikasi ini.
             */
            try {
                return \Carbon\Carbon::parse($nilai)->locale('id')->translatedFormat('d F Y');
            } catch (\Throwable) {
                return $nilai;
            }
        }

        if ($kolom !== 'kategori_id') {
            return \Illuminate\Support\Str::limit($nilai, 250);
        }

        $angkatanModel = Pendaftaran::angkatanModel($layanan);
        $angkatan = $angkatanModel === null ? null : $angkatanModel::whereKey($nilai)->first();

        if ($angkatan === null) {
            return $nilai;
        }

        $teks = (string) $angkatan->nama;

        if (($angkatan->nama_ke ?? '') !== '') {
            $teks .= ' ke-' . $angkatan->nama_ke;
        }

        return \Illuminate\Support\Str::limit($teks, 250);
    }

    /**
     * Kuota angkatan lama dan baru disesuaikan, dan kirimannya ditolak kalau
     * jumlahnya melebihi sisa.
     *
     * Aturannya disalin apa adanya dari pengendali Scopus Camp lama, termasuk
     * satu hal yang mudah salah: sisa kuota angkatan BARU dihitung dengan
     * MENAMBAHKAN kembali jumlah pendaftar lama bila angkatannya tidak
     * berpindah — tanpa itu, menyunting pendaftaran berisi 6 orang tanpa
     * mengubah apa pun akan ditolak karena 6 dianggap tambahan baru.
     *
     * @param  array<string, mixed>  $isi
     * @return string|null pesan penolakan, atau null kalau boleh lanjut
     */
    private function sesuaikanKuota(string $layanan, $pendaftaran, array &$isi): ?string
    {
        $angkatanModel = Pendaftaran::angkatanModel($layanan);

        if ($angkatanModel === null) {
            return null;
        }

        $idLama = (string) $pendaftaran->kategori_id;
        $idBaru = (string) ($isi['kategori_id'] ?? $idLama);

        $jumlahLama = (int) $pendaftaran->jumlah_pendaftar;
        $jumlahBaru = array_key_exists('jumlah_pendaftar', $isi)
            ? (int) $isi['jumlah_pendaftar']
            : $jumlahLama;

        if ($jumlahBaru < 1) {
            return 'Jumlah pendaftarnya minimal satu orang.';
        }

        if ($idBaru === $idLama && $jumlahBaru === $jumlahLama) {
            return null;
        }

        $baru = $angkatanModel::whereKey($idBaru)->lockForUpdate()->first();

        if ($baru === null) {
            return 'Angkatan yang dipilih tidak ditemukan.';
        }

        if ($baru->total_kuota !== null) {
            $sisaTersedia = (int) $baru->sisa_kuota + ($idBaru === $idLama ? $jumlahLama : 0);

            if ($jumlahBaru > $sisaTersedia) {
                return 'Jumlah pendaftarnya melebihi sisa kuota angkatan itu — tersisa '
                    . $sisaTersedia . ' kursi.';
            }
        }

        if ($idBaru !== $idLama) {
            $lama = $angkatanModel::whereKey($idLama)->lockForUpdate()->first();

            if ($lama !== null && $lama->total_kuota !== null) {
                $lama->forceFill([
                    'sisa_kuota' => (string) min(
                        (int) $lama->total_kuota,
                        (int) $lama->sisa_kuota + $jumlahLama
                    ),
                ])->save();
            }

            if ($baru->total_kuota !== null) {
                $baru->forceFill([
                    'sisa_kuota' => (string) max(0, (int) $baru->sisa_kuota - $jumlahBaru),
                ])->save();
            }

            return null;
        }

        $selisih = $jumlahBaru - $jumlahLama;

        if ($selisih !== 0 && $baru->total_kuota !== null) {
            $baru->forceFill([
                'sisa_kuota' => (string) max(0, min(
                    (int) $baru->total_kuota,
                    (int) $baru->sisa_kuota - $selisih
                )),
            ])->save();
        }

        return null;
    }

    /**
     * Satu nilai kiriman dibaca sesuai jenisnya.
     *
     * Nominal dibersihkan dari SEMUA yang bukan angka, bukan dari satu tanda
     * tertentu: pengendali lama membuang titik untuk Scopus Camp dan koma
     * untuk Scopus Kafe, jadi borang yang menulis "4.275.028" di layar Kafe
     * dulu tersimpan sebagai nol. Membuang yang bukan angka menerima keduanya.
     */
    private function baca($nilai, string $jenis)
    {
        if ($jenis === 'uang') {
            $angka = preg_replace('/\D+/', '', (string) $nilai) ?? '';

            return $angka === '' ? 0 : (int) $angka;
        }

        if ($jenis === 'angka') {
            return (int) preg_replace('/\D+/', '', (string) $nilai);
        }

        $teks = is_string($nilai) ? trim($nilai) : $nilai;

        // Tanggal dan waktu kosong disimpan NULL, bukan untaian kosong:
        // kolom date/time menolak '' di MySQL mode ketat.
        if (in_array($jenis, ['tanggal', 'waktu'], true) && ($teks === '' || $teks === null)) {
            return null;
        }

        return $teks === '' ? null : $teks;
    }
}
