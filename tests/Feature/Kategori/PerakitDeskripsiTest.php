<?php

namespace Tests\Feature\Kategori;

use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Support\PerakitDeskripsi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Perakit deskripsi angkatan.
 *
 * Tujuannya satu: admin tidak mengetik ulang teks pengumuman tiap angkatan.
 * Yang berganti cuma nomor, tanggal, lokasi, harga, dan kontak — dan semuanya
 * sudah jadi kolom, jadi tidak ada yang perlu diketik dua kali.
 */
class PerakitDeskripsiTest extends TestCase
{
    use DatabaseTransactions;

    private function tarifDenganCetakan(string $layanan, string $cetakan, array $lain = []): ClinikScopusBiayaPersesi
    {
        $t = ClinikScopusBiayaPersesi::create(array_merge([
            'layanan' => $layanan,
            'biaya_persesi' => 5500000,
            'template_deskripsi' => $cetakan,
            'fasilitas' => ['Konsumsi selama kegiatan', 'Sertifikat'],
            'kegiatan' => ['Penulisan artikel', 'Pemilihan jurnal'],
            'kontak' => "📞 Kumala: 0889-8356-7819",
            'status' => ClinikScopusBiayaPersesi::NONAKTIF,
        ], $lain));

        $t->jadikanBerlaku();

        return $t;
    }

    private function angkatan(string $layanan, array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => $layanan,
            'token' => 'uji' . uniqid(),
            'nama' => 'SCOPUS CAMP YOGYAKARTA #202',
            'nama_ke' => '202',
            'mulai' => '2026-10-30 00:00:00',
            'selesai' => '2026-11-01 00:00:00',
            'lokasi' => 'Yogyakarta',
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'draft',
        ], $lain));
    }

    // ----------------------------------------------------------- penggantian

    #[Test]
    public function penanda_diisi_dari_kolom_angkatannya(): void
    {
        $this->tarifDenganCetakan('scopus_kafe',
            "{nama}\n{tanggal}\n{lokasi}\n{nomor}\n{kuota} peserta");

        $hasil = PerakitDeskripsi::rakit($this->angkatan('scopus_kafe'));

        $this->assertSame(
            "SCOPUS CAMP YOGYAKARTA #202\n30 Oktober – 1 November 2026\nYogyakarta\n202\n20 peserta",
            $hasil
        );
    }

    #[Test]
    public function daftar_fasilitas_dan_kegiatan_dirakit_bernomor(): void
    {
        // Disimpan sebagai larik tanpa nomor, supaya bisa ditampilkan sebagai
        // centang di kartu tarif; nomornya baru dipasang di teks pengumuman.
        $this->tarifDenganCetakan('scopus_kafe', "Fasilitas\n{fasilitas}\n\nKegiatan\n{kegiatan}");

        $this->assertSame(
            "Fasilitas\n1. Konsumsi selama kegiatan\n2. Sertifikat\n\nKegiatan\n1. Penulisan artikel\n2. Pemilihan jurnal",
            PerakitDeskripsi::rakit($this->angkatan('scopus_kafe'))
        );
    }

    #[Test]
    public function harga_angkatan_didahulukan_atas_tarif_induk(): void
    {
        /*
         * Angkatan yang sudah berjalan memakai harga saat itu. Kalau tarif
         * induk yang dipakai, menaikkan harga akan diam-diam mengubah
         * pengumuman angkatan yang pesertanya sudah mendaftar.
         */
        $this->tarifDenganCetakan('scopus_kafe', '{harga}', ['biaya_persesi' => 5500000]);

        $this->assertSame('Rp 4.500.000',
            PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', ['biaya' => '4500000'])));
    }

    #[Test]
    public function harga_induk_dipakai_kalau_angkatannya_belum_mengisi(): void
    {
        $this->tarifDenganCetakan('scopus_kafe', '{harga}', ['biaya_persesi' => 5500000]);

        $this->assertSame('Rp 5.500.000',
            PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', ['biaya' => null])));
    }

    #[Test]
    public function tiap_varian_scopus_camp_merakit_fasilitasnya_sendiri(): void
    {
        /*
         * Inti permintaan 30 Sep 2026. Pengumuman Yogyakarta memuat penginapan
         * dan mushola/kolam renang karena acaranya di rumah sendiri; Medan
         * tidak, karena menyewa tempat.
         *
         * Harganya sama-sama 5,5jt, jadi uji yang cuma membandingkan harga
         * tidak akan menangkap kalau kedua varian tertukar. Yang diperiksa
         * di sini isinya.
         */
        $jawa = PerakitDeskripsi::rakit(KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'varian' => 'jawa',
            'token' => 'uji' . uniqid(), 'nama' => 'SCOPUS CAMP YOGYAKARTA',
            'lokasi' => 'Yogyakarta', 'mulai' => '2026-10-30', 'selesai' => '2026-11-01',
            'status' => 'draft',
        ]));

        $luar = PerakitDeskripsi::rakit(KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'varian' => 'luar_jawa',
            'token' => 'uji' . uniqid(), 'nama' => 'SCOPUS CAMP MEDAN',
            'lokasi' => 'Medan', 'mulai' => '2026-09-18', 'selesai' => '2026-09-20',
            'status' => 'draft',
        ]));

        // Kalimatnya ditulis ulang supaya terbaca orang awam; yang dijaga uji
        // ini tetap sama, yaitu APA yang membedakan kedua varian.
        $this->assertStringContainsString('Penginapan di tempat acara', $jawa);
        $this->assertStringContainsString('Mushola, kolam renang, dan treadmill', $jawa);

        $this->assertStringNotContainsString('Penginapan di tempat acara', $luar);
        $this->assertStringNotContainsString('Mushola, kolam renang', $luar);

        // Yang sama tetap sama — keduanya program yang sama, bukan dua produk.
        foreach (['Makan dan minum selama acara', 'Sertifikat dan perlengkapan peserta',
            'cek plagiasi'] as $sama) {
            $this->assertStringContainsString($sama, $jawa);
            $this->assertStringContainsString($sama, $luar);
        }

        $this->assertStringContainsString('30 Oktober – 1 November 2026', $jawa);
        $this->assertStringContainsString('18 – 20 September 2026', $luar);
    }

    // ------------------------------------------------------ baris yang kosong

    #[Test]
    public function baris_yang_seluruh_penandanya_kosong_dibuang(): void
    {
        /*
         * Angkatan Bibliometrik tidak punya lokasi. Tanpa aturan ini,
         * pengumumannya mencetak "📍 Lokasi:" menggantung tanpa isi — dan itu
         * justru terbaca seperti data yang hilang.
         */
        $this->tarifDenganCetakan('scopus_kafe', "{nama}\n📍 Lokasi: {lokasi}\nSelesai");

        $hasil = PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', ['lokasi' => null]));

        $this->assertStringNotContainsString('Lokasi:', $hasil);
        $this->assertStringContainsString('Selesai', $hasil);
    }

    #[Test]
    public function baris_dengan_penanda_terisi_tetap_dipertahankan(): void
    {
        $this->tarifDenganCetakan('scopus_kafe', "📍 Lokasi: {lokasi}");

        $this->assertSame('📍 Lokasi: Yogyakarta',
            PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', ['lokasi' => 'Yogyakarta'])));
    }

    #[Test]
    public function promo_hanya_muncul_kalau_memang_ada_diskon(): void
    {
        $this->tarifDenganCetakan('scopus_kafe', "{harga}\n💸 Promo: {harga_promo} kode {kode_promo}");

        $tanpa = PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', [
            'biaya' => '5500000', 'total_biaya' => '5500000', 'kode_diskon' => null,
        ]));
        $this->assertStringNotContainsString('Promo', $tanpa);

        $dengan = PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', [
            'biaya' => '5500000', 'total_biaya' => '5060000', 'kode_diskon' => 'SalamQ1',
        ]));
        $this->assertStringContainsString('Rp 5.060.000', $dengan);
        $this->assertStringContainsString('SalamQ1', $dengan);
    }

    // ------------------------------------------------------------- jaga-jaga

    #[Test]
    public function penanda_salah_ketik_dibiarkan_kelihatan(): void
    {
        /*
         * Diganti kosong, "{tanggl}" menghilang diam-diam dan admin baru sadar
         * setelah pengumumannya tersebar. Dibiarkan, ia kelihatan di pratinjau
         * sebelum disimpan.
         */
        $this->tarifDenganCetakan('scopus_kafe', 'Tanggal: {tanggl}');

        $this->assertSame('Tanggal: {tanggl}',
            PerakitDeskripsi::rakit($this->angkatan('scopus_kafe')));
    }

    #[Test]
    public function layanan_tanpa_cetakan_menghasilkan_kosong_bukan_galat(): void
    {
        // Online Training belum punya cetakan; layarnya harus tetap terbuka.
        ClinikScopusBiayaPersesi::untuk('online_training')
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        $this->assertSame('', PerakitDeskripsi::rakit($this->angkatan('online_training')));
    }

    #[Test]
    public function baris_kosong_beruntun_dirapatkan(): void
    {
        // Sisa pembuangan baris tidak boleh meninggalkan lubang tiga baris.
        $this->tarifDenganCetakan('scopus_kafe', "Judul\n\n{lokasi}\n{lokasi}\n\nPenutup");

        $this->assertSame("Judul\n\nPenutup",
            PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', ['lokasi' => null])));
    }

    #[Test]
    public function semua_penanda_yang_didaftarkan_benar_benar_dikenali(): void
    {
        /*
         * PENANDA dipakai sebagai daftar bantuan di layar. Kalau ada yang
         * didaftarkan tetapi tidak pernah diganti, admin memakainya lalu
         * mendapati namanya sendiri tercetak di pengumuman.
         */
        $cetakan = implode("\n", array_map(
            fn ($p) => 'x' . $p,
            array_keys(PerakitDeskripsi::PENANDA)
        ));

        $this->tarifDenganCetakan('scopus_kafe', $cetakan);

        $hasil = PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', [
            'group_wa' => 'https://chat.whatsapp.com/uji',
            'kode_diskon' => 'UJI', 'biaya' => '5500000', 'total_biaya' => '5000000',
        ]));

        foreach (array_keys(PerakitDeskripsi::PENANDA) as $penanda) {
            $this->assertStringNotContainsString($penanda, $hasil,
                "Penanda $penanda terdaftar tapi tidak pernah diganti.");
        }
    }

    // -------------------------------------------------- blok menggantung

    #[Test]
    public function judul_ikut_terbuang_kalau_daftarnya_kosong(): void
    {
        /*
         * Aturan per baris saja tidak cukup: "🔹 Yang dipelajari" tidak
         * berpenanda, jadi ia bertahan walau daftarnya kosong — dan untuk
         * layanan yang kegiatannya belum diisi, judul itu menggantung tanpa
         * isi apa pun di bawahnya.
         */
        $this->tarifDenganCetakan('scopus_kafe',
            "{nama}\n\n🔹 Yang dipelajari\n{kegiatan}\n\n💰 {harga}",
            ['kegiatan' => []]);

        $hasil = PerakitDeskripsi::rakit($this->angkatan('scopus_kafe'));

        $this->assertStringNotContainsString('Yang dipelajari', $hasil);
        $this->assertStringContainsString('💰', $hasil);
    }

    #[Test]
    public function judul_tetap_ada_selama_daftarnya_terisi(): void
    {
        $this->tarifDenganCetakan('scopus_kafe',
            "{nama}\n\n🔹 Yang dipelajari\n{kegiatan}",
            ['kegiatan' => ['Diskusi kelompok']]);

        $hasil = PerakitDeskripsi::rakit($this->angkatan('scopus_kafe'));

        $this->assertStringContainsString('Yang dipelajari', $hasil);
        $this->assertStringContainsString('1. Diskusi kelompok', $hasil);
    }

    #[Test]
    public function blok_yang_sebagian_penandanya_terisi_tetap_utuh(): void
    {
        // Satu blok berisi tanggal DAN lokasi. Tanggalnya terisi, jadi bloknya
        // bertahan — yang dibuang cuma baris lokasinya.
        $this->tarifDenganCetakan('scopus_kafe', "🗓 {tanggal}\n📍 {lokasi}");

        $hasil = PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', ['lokasi' => null]));

        $this->assertStringContainsString('🗓 30 Oktober – 1 November 2026', $hasil);
        $this->assertStringNotContainsString('📍', $hasil);
    }

    #[Test]
    public function kalimat_tanpa_penanda_tidak_pernah_disentuh(): void
    {
        // Alinea yang memang ditulis admin, tanpa penanda sama sekali, bukan
        // urusan aturan ini.
        $this->tarifDenganCetakan('scopus_kafe',
            "{nama}\n\nKelas ini dibuka untuk umum.\n\n{kegiatan}",
            ['kegiatan' => []]);

        $this->assertStringContainsString('Kelas ini dibuka untuk umum.',
            PerakitDeskripsi::rakit($this->angkatan('scopus_kafe')));
    }

    // ------------------------------------------------------------ durasi

    #[Test]
    public function durasi_dihitung_dari_tanggalnya(): void
    {
        /*
         * Cetakan Scopus Camp dulu menulis "Selama 3 hari 2 malam" apa adanya.
         * Angkanya lalu ikut tercetak di angkatan 1–2 Oktober — dua hari, bukan
         * tiga — dan pembacanya tidak punya cara tahu mana yang benar.
         */
        foreach ([
            ['2026-10-01', '2026-10-02', '2 hari 1 malam'],
            ['2026-10-01', '2026-10-03', '3 hari 2 malam'],
            ['2026-10-01', '2026-10-05', '5 hari 4 malam'],
        ] as [$mulai, $selesai, $harapan]) {
            $this->assertSame($harapan, $this->rakitDurasi($mulai, $selesai),
                $mulai . ' s/d ' . $selesai);
        }
    }

    #[Test]
    public function acara_sehari_tidak_menyebut_malam(): void
    {
        // "1 hari 0 malam" bukan cara orang menulisnya.
        $this->assertSame('sehari penuh', $this->rakitDurasi('2026-10-01', null));
        $this->assertSame('sehari penuh', $this->rakitDurasi('2026-10-01', '2026-10-01'));
    }

    #[Test]
    public function tanggal_selesai_sebelum_mulai_tidak_bikin_angka_ganjil(): void
    {
        // Datanya memang tidak boleh begitu, tetapi kalau terlanjur ada, yang
        // tercetak harus kosong — bukan "-2 hari -3 malam".
        $this->assertSame('', $this->rakitDurasi('2026-10-05', '2026-10-01'));
        $this->assertSame('', $this->rakitDurasi(null, null));
    }

    private function rakitDurasi(?string $mulai, ?string $selesai): string
    {
        $this->tarifDenganCetakan('scopus_kafe', '{durasi}');

        return PerakitDeskripsi::rakit($this->angkatan('scopus_kafe', [
            'mulai' => $mulai,
            'selesai' => $selesai,
        ]));
    }
}
