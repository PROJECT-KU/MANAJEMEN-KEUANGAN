<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tata letak daftar sesi: satu sesi tidak boleh jadi kartu raksasa.
 *
 * Lajur kisinya memakai auto-fit, dan auto-fit MENGUNCUPKAN lajur yang
 * kosong — jadi satu sesi berarti satu kartu selebar seluruh wadah.
 * Terukur sebelum diperbaiki: kartu 1.056 x 821px dengan sampul setinggi
 * 593px, memakan dua layar penuh, dan rincian sesinya — tanggal, pemateri,
 * harga, tombol daftar — terdorong ke bawah lipatan.
 *
 * Satu sesi adalah keadaan yang BIASA di layanan ini, bukan keadaan tepi.
 */
class TataDaftarSesiTest extends TestCase
{
    use DatabaseTransactions;

    private const TAMPILAN = 'resources/views/public/webinar_eksklusif/index.blade.php';

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Tata ' . Str::random(5),
            'nama_ke' => (string) random_int(3000, 3999),
            'mulai' => now()->addDays(15)->toDateString(),
            'selesai' => now()->addDays(15)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    private function sumber(): string
    {
        return (string) file_get_contents(base_path(self::TAMPILAN));
    }

    /**
     * Tampilannya dirakit LANGSUNG, bukan lewat rutenya.
     *
     * Lewat rute, menguji "berapa sesi yang tergambar" menuntut tabelnya
     * dikosongkan dulu — dan sesi yang nyata ditunjuk pendaftaran yang
     * nyata, jadi menghapusnya ditolak kunci asing. Dipaksa pun itu
     * menyapu data sungguhan di basis data lokal, yang tidak boleh.
     *
     * Yang diuji di sini memang keputusan tampilannya, bukan kuerinya.
     */
    private function rakit(int $banyak): string
    {
        $sesi = collect(range(1, $banyak))->map(fn () => $this->sesi());

        return view('public.webinar_eksklusif.index', ['sesi' => $sesi])->render();
    }

    /**
     * Penandanya dicari di ATRIBUT KELAS divnya, bukan di mana saja.
     *
     * Nama kelasnya muncul juga di blok <style> halaman yang sama — ia
     * memang harus, sebab di situlah aturannya ditulis. Dicari apa adanya,
     * "tidak memuat" jadi mustahil terpenuhi dan ujinya merah selamanya
     * tanpa ada yang salah pada halamannya.
     */
    private function mendatar(string $html): bool
    {
        return (bool) preg_match('/class="sel-kisi\s+sel-kisi-tunggal/', $html);
    }

    #[Test]
    public function satu_sesi_digambar_mendatar(): void
    {
        /*
         * Penandanya dipasang DARI PELADEN, bukan diserahkan ke @container.
         * Peramban yang belum mengenal @container akan memulangkan kartu
         * tegak selebar penuh — yaitu persis keadaan yang diperbaiki.
         */
        $this->assertTrue($this->mendatar($this->rakit(1)),
            'Satu sesi tidak lagi digambar mendatar; kartunya berdiri sendiri '
            . 'selebar 360px di tengah wadah 1.056px.');
    }

    #[Test]
    public function dua_sesi_tetap_berjajar_sebagai_kartu(): void
    {
        $this->assertFalse($this->mendatar($this->rakit(2)),
            'Dua sesi ikut digambar mendatar; yang kedua akan menumpuk '
            . 'sebagai baris penuh, bukan berjajar.');
    }

    #[Test]
    public function kartunya_menampilkan_wajah_dan_jabatan_pemateri(): void
    {
        /*
         * Foto dan jabatan pemateri sudah ada di data sejak lama dan tidak
         * pernah tergambar di mana pun. "Siapa yang mengajar" termasuk yang
         * dicari orang sebelum memutuskan ikut, dan wajah menjawabnya lebih
         * cepat daripada nama.
         *
         * Menggantikan baris "Tersisa N dari M kursi" yang sempat dipasang
         * di sini: angkanya benar, tetapi deskripsi sesi yang sama memuat
         * kuota tulisan tangan yang sudah basi, dan dua angka berbeda
         * tentang hal yang sama lebih buruk daripada satu pun tidak ada.
         */
        $sesi = $this->sesi();

        $html = view('public.webinar_eksklusif.index', ['sesi' => collect([$sesi])])->render();

        $this->assertStringContainsString('Trainer', $html,
            'Jabatan pemateri tidak tergambar.');

        $this->assertStringContainsString('sel-pemateri-foto', $html,
            'Tempat wajah pemateri tidak tergambar sama sekali.');
    }

    #[Test]
    public function foto_pemateri_yang_hilang_tidak_jadi_gambar_rusak(): void
    {
        /*
         * Kolomnya bisa terisi sementara berkasnya sudah tidak ada — itu
         * keadaan yang nyata di aplikasi ini, dan accessor sampulnya memang
         * dibuat untuk itu. Dipasang apa adanya, yang tergambar ikon gambar
         * rusak di sebelah nama pemateri, dan tidak ada yang tahu sampai ada
         * yang melapor.
         *
         * Penggantinya lingkaran seukuran sama, jadi barisnya pun tidak
         * bergeser antara sesi yang berfoto dan yang tidak.
         */
        $sesi = $this->sesi();
        $sesi->forceFill(['pemateri_foto' => 'tidak-ada-berkasnya-' . Str::random(8) . '.webp'])->save();

        $this->assertNull($sesi->refresh()->alamat_pemateri,
            'Accessor-nya memulangkan alamat untuk berkas yang tidak ada.');

        $html = view('public.webinar_eksklusif.index', ['sesi' => collect([$sesi])])->render();

        $this->assertStringNotContainsString('tidak-ada-berkasnya-', $html,
            'Alamat berkas yang hilang tetap dipasang; yang tergambar gambar rusak.');

        $this->assertStringContainsString('sel-pemateri-ikon', $html,
            'Tidak ada pengganti saat fotonya tidak ada.');
    }

    #[Test]
    public function lajurnya_berhenti_tumbuh_supaya_jaraknya_benar(): void
    {
        /*
         * Batas atas lajurnya harus LEBAR TETAP, bukan 1fr.
         *
         * Dengan 1fr, auto-fit membuat lajur sebanyak yang muat lalu MEMBAGI
         * RATA seluruh lebar kepada yang tersisa. Dua sesi berarti tiga lajur
         * dibuat, yang ketiga menguncup, dan dua sisanya jadi 517px
         * masing-masing. Akibatnya satu dari dua, tergantung kartunya
         * dibatasi atau tidak — dan keduanya pernah terjadi di sini:
         *
         *   - kartunya dibatasi 360px: ia mengambang di tengah lajur 517px,
         *     dan jarak antar kartu terukur 179px padahal gap-nya 22px;
         *   - kartunya tidak dibatasi: ia melar jadi 517px dan sampul 4:5-nya
         *     ikut setinggi 646px, yaitu kartu raksasa yang sudah diperbaiki.
         *
         * Batas di lajurnya menutup keduanya sekaligus, jadi tidak perlu lagi
         * ada batas di kartunya.
         *
         * 330, bukan 360: tiga lajur 330px ditambah dua jarak 22px = 1.034px
         * dan masih muat di wadah 1.056px, sementara 360px berarti 1.124px
         * dan tiga sesi tidak pernah sebaris.
         *
         * Pin kontrak atas sumbernya: CSS tidak bisa dijalankan dari PHPUnit,
         * jadi jaraknya sendiri diukur lewat peramban, bukan di sini.
         */
        $sumber = $this->sumber();

        $this->assertStringContainsString(
            'grid-template-columns: repeat(auto-fit, minmax(280px, 330px));',
            $sumber,
            'Lajurnya kembali boleh tumbuh; kartunya akan mengambang di '
            . 'tengah lajurnya dan jaraknya melar jauh melebihi gap-nya.'
        );

        $this->assertStringContainsString(
            'justify-content: center;',
            $sumber,
            'Kelompok kartunya tidak dipusatkan; satu-dua kartu akan menempel '
            . 'ke tepi kiri dengan ruang menganga di kanannya.'
        );
    }

    #[Test]
    public function sampulnya_tidak_dipotong_jadi_pita_lebar(): void
    {
        /*
         * Sampulnya TEGAK. Diukur pada 50 sampul yang benar-benar ada di
         * aplikasi ini: semuanya tegak, dan 48 di antaranya tepat 1080x1350,
         * yaitu 4:5. Tidak satu pun melebar.
         *
         * Dipaksa ke 16:9 dengan object-fit: cover, yang tersisa cuma pita
         * tengahnya — judul besar di puncak poster terpenggal dan tawaran di
         * kakinya terpotong di tengah kata. Sekitar 55% tinggi gambar hilang,
         * pada gambar yang seluruhnya dirancang sebagai satu kesatuan.
         */
        $sumber = $this->sumber();

        $this->assertMatchesRegularExpression(
            '/\.sel-flyer \{[^}]*aspect-ratio:\s*4\s*\/\s*5/s',
            $sumber,
            'Sampulnya kembali dipotong ke rasio yang bukan rasio posternya.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\.sel-flyer \{[^}]*aspect-ratio:\s*16\s*\/\s*9/s',
            $sumber
        );
    }
}
