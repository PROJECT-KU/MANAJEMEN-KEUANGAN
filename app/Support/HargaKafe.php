<?php

namespace App\Support;


/**
 * Harga pemesanan Scopus Kafe, DIHITUNG DI PELADEN.
 *
 * Sebelum ini `biaya`, `kode_unik_pembayaran`, `subtotal_pembayaran`, dan
 * `total_keseluruhan_pembayaran` datang dari kiriman borang lalu disimpan
 * mentah — tujuh medan uang yang seluruhnya ditentukan sisi yang tidak bisa
 * dipercaya. Siapa pun bisa memesan tiga sesi dengan total nol tanpa satu
 * baris pun yang menolak, dan panitia baru tahu saat mencocokkan mutasi.
 *
 * Perkara yang sama sudah dibetulkan di Scopus Camp lewat HargaUmum. Kelas
 * ini pasangannya untuk layanan yang TIDAK berangkatan: harganya tidak
 * datang dari satu baris angkatan melainkan dari sesi-sesi yang dipilih.
 *
 * Tidak memakai HargaUmum: rumus di sana menuntut KategoriLayanan — satu
 * angkatan, satu harga satuan, dikali jumlah peserta. Scopus Kafe tidak punya
 * angkatan sama sekali; yang dijumlahkan beberapa sesi yang harganya boleh
 * berbeda-beda.
 */
class HargaKafe
{
    /** Paling banyak tiga sesi dalam satu pemesanan; tabelnya memang segitu. */
    public const MAKS_SESI = 3;

    /**
     * Varian yang boleh dipesan, beserta tarifnya — offline dan online.
     *
     * Sampai 31 Okt 2026 Scopus Kafe tidak mengenal varian sama sekali:
     * sesinya dibaca dari tabel `meme` (layar "Data Meme"), satu daftar
     * untuk semua orang, dan harganya ikut dari kolom `biaya` di sana.
     * Padahal layanannya berjalan dalam DUA bentuk dengan sesi dan harga
     * yang berbeda, dan keduanya sudah lama ada di layar Tarif Layanan.
     *
     * Keputusan pemiliknya 31 Okt 2026: Tarif Layanan yang jadi sumbernya.
     * `meme` tidak dibaca lagi oleh jalur publik — tabelnya dibiarkan hidup
     * sebab layar "Data Meme" masih memakainya.
     *
     * @return array<string, \App\ClinikScopusBiayaPersesi>  berkunci varian
     */
    public static function varianTersedia(): array
    {
        $keluar = [];

        foreach (self::VARIAN as $varian) {
            $tarif = \App\ClinikScopusBiayaPersesi::berlaku('scopus_kafe', $varian);

            /*
             * Varian yang tarifnya tidak aktif TIDAK ditawarkan. Menawarkannya
             * dengan harga nol berarti menerima pemesanan yang tidak bisa
             * ditagih; menawarkannya dengan harga varian lain berarti menagih
             * angka yang tidak pernah disetel siapa pun.
             */
            if ($tarif === null) {
                continue;
            }

            // Tarif tanpa sesi sama sekali juga dilewati: borangnya akan
            // menampilkan varian yang tidak punya satu pun pilihan di
            // bawahnya, dan orangnya mentok tanpa tahu kenapa.
            if (self::sesiTersedia($varian) === []) {
                continue;
            }

            $keluar[$varian] = $tarif;
        }

        return $keluar;
    }

    /** Varian yang dikenal layanan ini; urutannya urutan tampil di layar. */
    public const VARIAN = ['offline', 'online'];

    /** Nama varian sebagaimana ditulis di layar. */
    public static function namaVarian(?string $varian): string
    {
        return match ((string) $varian) {
            'offline' => 'Tatap muka',
            'online' => 'Daring',
            default => (string) $varian,
        };
    }

    /**
     * Tempatnya, menurut variannya.
     *
     * BELUM ADA di Tarif Layanan — tabel tarif tidak punya kolom tempat —
     * jadi disetel di sini dan disebut terang supaya tidak terbaca seperti
     * data yang datang dari layar panitia. Kolom `lokasi` di baris pemesanan
     * tetap diisi, sebab faktur dan surat membacanya.
     */
    public static function lokasiVarian(?string $varian): string
    {
        return match ((string) $varian) {
            'online' => 'Daring — tautannya dikirim lewat WhatsApp',
            default => 'Rumah Scopus Pusat, Yogyakarta',
        };
    }

    /**
     * Sesi yang boleh dipesan untuk satu varian, dibaca dari Tarif Layanan.
     *
     * Jamnya diatur panitia di layar Tarif Layanan; kalau baris tarifnya
     * tidak menyetel sesi sendiri, PendaftaranSemuaLayanan jatuh kembali ke
     * daftar bawaannya. Yang TIDAK terjadi lagi: satu daftar sesi yang sama
     * untuk tatap muka dan daring.
     *
     * @return list<array{nilai: string, nama: string, mulai: string, selesai: string}>
     */
    public static function sesiTersedia(?string $varian): array
    {
        return PendaftaranSemuaLayanan::sesiPilihan('scopus_kafe', $varian);
    }

    /**
     * Rincian harga untuk sesi-sesi yang dipilih.
     *
     * HARGANYA DIBACA DARI KATALOG, bukan dari kiriman borang. Yang diterima
     * dari borang cuma KUNCI sesinya — dan kunci yang tidak ada di katalog
     * dilewati, bukan dihargai nol.
     *
     * @param  array<int, string>  $kunciSesi
     * @return array{sesi: list<array<string, mixed>>, subtotal: int, kode_unik: int, total: int}
     */
    public static function hitung(?string $varian, array $kunciSesi, $kodeUnikDikirim = null): array
    {
        $tarif = self::varianTersedia()[(string) $varian] ?? null;

        /*
         * Varian yang tidak ditawarkan memulangkan pemesanan KOSONG, bukan
         * pemesanan berharga nol. Pemanggilnya menolak yang kosong; kalau
         * yang dipulangkan nol, kiriman dengan varian karangan tersimpan
         * lengkap dengan tagihan DOKU senilai kode uniknya saja.
         */
        if ($tarif === null) {
            return ['sesi' => [], 'subtotal' => 0, 'kode_unik' => 0, 'total' => 0];
        }

        $satuan = self::rupiah($tarif->biaya_persesi);

        $katalog = [];

        foreach (self::sesiTersedia($varian) as $sesi) {
            $katalog[$sesi['nilai']] = $sesi;
        }

        $dipilih = [];

        foreach ($kunciSesi as $kunci) {
            $kunci = (string) $kunci;

            /*
             * Kunci yang tidak ada di katalog DILEWATI, bukan dihargai nol:
             * dihargai nol, pemesanan berisi satu sesi sungguhan dan dua sesi
             * karangan tetap tersimpan dan tagihannya kurang.
             *
             * Katalognya milik VARIAN ini saja, jadi kunci sesi milik varian
             * lain ikut terlewat — tanpa itu orang bisa memesan sesi tatap
             * muka dengan harga daring yang lebih murah.
             *
             * Sesi kembar tidak perlu ditolak terpisah — $dipilih DIKUNCI
             * pada kunci sesinya, jadi yang sama menimpa dirinya sendiri.
             */
            if ($kunci === '' || ! isset($katalog[$kunci])) {
                continue;
            }

            $dipilih[$kunci] = $katalog[$kunci] + [
                'biaya' => $satuan,
                'lokasi' => self::lokasiVarian($varian),
            ];

            if (count($dipilih) >= self::MAKS_SESI) {
                break;
            }
        }

        $subtotal = count($dipilih) * $satuan;

        /*
         * Kode uniknya dihitung SEKALI ke dalam peubah. Dipanggil dua kali —
         * sekali untuk kolomnya, sekali di dalam penjumlahan total — keduanya
         * akan memberi angka berbeda, dan nominal yang ditagihkan tidak
         * berakhir dengan kode yang tersimpan di sebelahnya.
         */
        $kodeUnik = self::kodeUnik($kodeUnikDikirim);

        return [
            'sesi' => array_values($dipilih),
            'subtotal' => $subtotal,
            'kode_unik' => $kodeUnik,
            'total' => max(0, $subtotal + $kodeUnik),
        ];
    }

    /**
     * "1.000.000" jadi 1000000.
     *
     * Kolom `biaya` di katalog sesinya varchar dan isinya memang bertitik —
     * terukur: "1.000.000". Dibaca apa adanya sebagai angka, PHP memulangkan
     * 1 dan seluruh sesi berharga satu rupiah.
     */
    public static function rupiah($nilai): int
    {
        return (int) preg_replace('/\D+/', '', (string) $nilai);
    }

    /**
     * Kode unik yang dipakai: yang dikirim borangnya kalau masuk akal, kalau
     * tidak dibuat baru.
     *
     * Dipercaya mentah, siapa pun bisa mengirim 0 dan menghapus satu-satunya
     * penanda yang membedakan dua pemesanan bernominal sama di mutasi
     * rekening — atau mengirim angka besar dan menaikkan tagihannya sendiri.
     */
    private static function kodeUnik($dikirim): int
    {
        [$min, $maks] = PendaftaranSemuaLayanan::KODE_UNIK;

        $angka = (int) preg_replace('/\D+/', '', (string) $dikirim);

        return $angka >= $min && $angka <= $maks ? $angka : random_int($min, $maks);
    }

    /**
     * Hari penyelenggaraannya: Jumat, Sabtu, Minggu.
     *
     * Keputusan pemiliknya 26 Okt 2026. Layanan ini tidak berangkatan — ia
     * terbuka terus — tetapi hanya berjalan di akhir pekan, jadi tanggal yang
     * dipilih pemesan harus salah satu dari ketiganya. Tanpa aturan ini
     * orang memesan hari Selasa, panitia menolaknya dengan tangan lewat
     * WhatsApp, dan pemesannya mengulang dari nol.
     *
     * @return array<int, int>  nomor hari ISO (1=Senin … 7=Minggu)
     */
    public const HARI_BUKA = [5, 6, 7];

    /**
     * Nama sesi sebagaimana ditulis di layar.
     *
     * Isinya di basis data berhuruf kecil semua ("sesi 1"), jadi kalau
     * dicetak mentah tiga halaman publik menampilkan huruf kecil di tengah
     * kalimat berhuruf besar. Yang DIBESARKAN hanya nama yang memang tidak
     * punya satu huruf besar pun: Str::title tanpa penjaga ini merusak nama
     * yang sengaja ditulis panitia dengan singkatan — "Sesi SEO" keluar
     * jadi "Sesi Seo" dan tidak ada yang memberi tahu kalau itu salah.
     */
    public static function namaSesi(?string $nama): string
    {
        $nama = trim((string) $nama);

        if ($nama === '' || preg_match('/\p{Lu}/u', $nama)) {
            return $nama;
        }

        return \Illuminate\Support\Str::title($nama);
    }

    /** Nama harinya untuk ditulis di layar. */
    public static function namaHariBuka(): string
    {
        return 'Jumat, Sabtu, atau Minggu';
    }

    /** Tanggal itu hari penyelenggaraan atau bukan. */
    public static function hariBuka($tanggal): bool
    {
        try {
            return in_array(
                (int) \Illuminate\Support\Carbon::parse($tanggal)->dayOfWeekIso,
                self::HARI_BUKA,
                true
            );
        } catch (\Throwable) {
            return false;
        }
    }
}
