<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Angka yang terlalu pendek TIDAK dicocokkan ke nomor telepon.
 *
 * Pencarian mengambil angka dari apa pun yang diketik, termasuk dari nomor
 * pendaftaran. Mencari "T-8AMjKk1P" menyisakan "81", dan "81" ada di hampir
 * setiap nomor telepon Indonesia — jadi mencari SATU nomor pendaftaran
 * memulangkan hampir seluruh tabel.
 *
 * Terukur pada 268 baris yang ada: satu angka mencocoki 100%, dua angka
 * ("81") 72%, tiga angka 1%. Ambangnya empat — itu cara orang mencari nomor
 * telepon, yaitu empat angka terakhirnya.
 *
 * Cacat ini ketahuan dari uji yang merahnya berpindah-pindah: Str::random
 * kadang menghasilkan untaian berangka, dan di putaran itulah pencariannya
 * membanjir. Yang tampak seperti uji rapuh ternyata bug produk.
 */
class CariNomorTeleponTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function mencari_nomor_pendaftaran_tidak_menarik_baris_lain(): void
    {
        [$orang, $saya, $lain] = $this->dua();

        $hasil = $this->cari($orang, $saya->id_transaksi);

        $this->assertContains($saya->id_transaksi, $hasil,
            'prasyarat: barisnya sendiri harus ketemu');

        $this->assertNotContains($lain->id_transaksi, $hasil,
            'Baris lain ikut terjaring hanya karena nomor teleponnya memuat '
            . 'angka yang sama dengan yang tersisa dari nomor pendaftaran.');
    }

    /**
     * Empat angka terakhir TETAP bisa dipakai mencari — itu cara orang
     * mencari nomor telepon, dan membuang kemampuannya demi menambal
     * banjirnya adalah memperbaiki satu hal dengan merusak hal lain.
     */
    #[Test]
    public function empat_angka_terakhir_tetap_menemukan_orangnya(): void
    {
        [$orang, $saya] = $this->dua();

        $this->assertContains($saya->id_transaksi, $this->cari($orang, '7391'),
            'Mencari empat angka terakhir nomornya tidak menemukan siapa pun.');
    }

    #[Test]
    public function dua_angka_tidak_lagi_menjaring_nomor_telepon(): void
    {
        [$orang, $saya, $lain] = $this->dua();

        $hasil = $this->cari($orang, '73');

        /*
         * Yang diperiksa HANYA baris yang tidak punya "73" di nomor
         * pendaftarannya.
         *
         * Versi pertama uji ini juga menuntut CAMP-73-… tidak ketemu, dan
         * itu salah: nomornya memang memuat "73", jadi ia tertarik oleh
         * `nomor LIKE` — pencocokan yang justru benar. Penjaga yang
         * menuntutnya hilang akan memaksa pencarian nomor pendaftaran
         * ikut dimatikan.
         */
        $this->assertNotContains($lain->id_transaksi, $hasil,
            'Dua angka masih menjaring nomor telepon; terukur "81" mencocoki '
            . '72% tabel, dan "8" mencocoki seluruhnya.');

        $this->assertContains($saya->id_transaksi, $hasil,
            'Yang nomor PENDAFTARANNYA memuat "73" justru hilang; '
            . 'pencarian nomor pendaftaran ikut mati.');
    }

    // ------------------------------------------------------------- pembantu

    /** @return list<string> nomor pendaftaran yang tergambar di tabelnya */
    private function cari(User $orang, string $kata): array
    {
        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $kata]))
            ->assertOk()->getContent();

        /*
         * Dipanen dari salinan TABEL saja.
         *
         * Nomornya dirender dua kali — satu untuk tabel lebar, satu untuk
         * modus kartu — dan yang tidak berlaku disembunyikan lewat CSS,
         * yang tidak terbaca dari isi halaman. Memanen keduanya membuat tiap
         * pendaftaran terhitung dua kali.
         *
         * Dicocokkan pada kelasnya yang lengkap, bukan pada `class="pdl-nomor"`
         * persis: penjaga versi pertama memakai pola itu dan jadi merah
         * seluruhnya begitu kelas penandanya ditambahkan — merah oleh sebab
         * yang bukan cacat pencariannya.
         */
        preg_match_all('/<span class="pdl-nomor pdl-nomor-tabel"[^>]*>([^<]*)</', $isi, $cocok);

        return array_map('trim', $cocok[1] ?? []);
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp, 2: PendaftaranScopusCamp} */
    private function dua(): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'cri_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        // Nomor pendaftarannya SENGAJA berangka: itu bentuk nomor sungguhan
        // (CAMP-20261005-0007), dan justru angka di dalamnya yang dulu bocor
        // ke pencarian nomor telepon.
        $saya = PendaftaranScopusCamp::create([
            'id_transaksi' => 'CAMP-73-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Satu ' . $t, 'email' => 's' . $t . '@contoh.test',
            'telp' => '0812-3456-7391', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        /*
         * Nomor pendaftarannya SENGAJA tanpa angka acak.
         *
         * Str::random(8) memuat angka, dan cukup sering ia memuat "73" —
         * angka yang dicari uji di bawah. Begitu itu terjadi, baris ini
         * memang tertarik oleh `nomor LIKE '%73%'`, pencocokan yang justru
         * BENAR, dan assertNotContains-nya merah tanpa satu baris kode pun
         * berubah. Tertangkap 31 Okt 2026 sebagai uji yang merah di
         * rangkaian penuh lalu hijau di jalan berikutnya; sebabnya dibuktikan
         * dengan memaksa "73" masuk ke untaiannya.
         *
         * Str::upper() tidak menolong — membesarkan huruf tidak membuang
         * angka — dan itu sudah dicoba.
         */
        $lain = PendaftaranScopusCamp::create([
            'id_transaksi' => 'CAMP-99-' . self::hurufSaja(8),
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Dua ' . $t, 'email' => 'd' . $t . '@contoh.test',
            'telp' => '0873-1111-2222', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        return [$u, $saya, $lain];
    }

    /** Untaian acak TANPA ANGKA, supaya tidak pernah memuat angka yang dicari. */
    private static function hurufSaja(int $panjang): string
    {
        $huruf = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $keluar = '';

        for ($i = 0; $i < $panjang; $i++) {
            $keluar .= $huruf[random_int(0, 25)];
        }

        return $keluar;
    }
}
