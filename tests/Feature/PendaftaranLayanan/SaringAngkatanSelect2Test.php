<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Saringan Angkatan memakai Select2 — menu bawaan peramban tidak bisa dicari.
 *
 * Menunya memuat angkatan yang punya pendaftar, dan belasan di antaranya
 * bernama SAMA PERSIS ("Angkatan Penuh …") sehingga yang membedakan cuma kode
 * acak dan bulannya. Di menu bawaan, menemukan satu di antaranya berarti
 * menggulung sambil membaca satu per satu.
 */
class SaringAngkatanSelect2Test extends TestCase
{
    use DatabaseTransactions;

    private function halaman(): string
    {
        $u = User::create([
            'full_name' => 'Rina Panitia', 'username' => 's2_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        return $this->actingAs($u)
            ->get(route('account.pendaftaran-layanan.index'))->assertOk()->getContent();
    }

    private function skrip(): string
    {
        $isi = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/index.blade.php')
        );

        // Komentarnya dibuang: catatan di berkas itu menyebut aturan yang
        // SENGAJA tidak dipakai beserta alasannya, dan pemindai yang
        // menghitung komentar akan membacanya sebagai aturan yang berlaku.
        $isi = (string) preg_replace('#\{\{--.*?--\}\}#s', '', $isi);

        return (string) preg_replace('#/\*.*?\*/#s', '', $isi);
    }

    #[Test]
    public function halamannya_tidak_500(): void
    {
        /*
         * Penjaga paling dasar, dan ia PERNAH merah.
         *
         * Catatan di skripnya sempat menyebut nama direktif Blade apa adanya,
         * dan Blade tetap mengompilasinya walau ia di dalam komentar JS:
         * halamannya 500 dengan pesan "Undefined property:
         * Factory::$yieldPushContent" yang tidak menyebut-nyebut komentar itu
         * sama sekali.
         */
        $this->assertStringContainsString('id="pdl-angkatan"', $this->halaman());
    }

    #[Test]
    public function select2_dipasang_pada_saringan_angkatan(): void
    {
        $skrip = $this->skrip();

        $this->assertStringContainsString('.select2({', $skrip,
            'Saringan angkatan tidak lagi memakai Select2; menunya kembali tidak bisa dicari.');

        $this->assertStringContainsString("placeholder: 'Semua angkatan'", $skrip);

        // Kotak carinya SELALU ada. Bawaannya menyembunyikan kotak itu untuk
        // menu pendek, dan kotak yang kadang ada kadang tidak membuat orang
        // ragu apakah ia boleh mengetik.
        $this->assertStringContainsString('minimumResultsForSearch: 0', $skrip,
            'Kotak carinya bisa hilang untuk menu pendek.');
    }

    #[Test]
    public function dipasang_di_dalam_document_ready(): void
    {
        /*
         * scripts.js menjalankan `$(".select2").select2()` di dalam
         * document.ready — dan Select2 menamai SPAN buatannya sendiri dengan
         * kelas `select2`. Dipasang langsung, skrip layar ini jalan lebih
         * dulu, lalu scripts.js menemukan span itu dan memasang Select2 di
         * ATAS container yang baru dibuat: terukur DUA container untuk satu
         * isian, dan yang terlihat di layar yang kosong.
         */
        $skrip = $this->skrip();

        $ada = preg_match(
            '/window\.jQuery\(function \(\) \{\s*window\.jQuery\(menuAngkatan\)\.select2\(/s',
            $skrip
        );

        $this->assertSame(1, $ada,
            'Select2 tidak lagi dipasang di dalam document.ready; scripts.js akan '
            . 'memasang Select2 kedua di atas container yang pertama.');

        // Prasyaratnya: scripts.js memang masih menjaring kelas itu.
        $this->assertStringContainsString('$(".select2").select2();',
            file_get_contents(public_path('assets/js/scripts.js')),
            'scripts.js tidak lagi memasang Select2 global; alasan di atas perlu ditinjau.');
    }

    #[Test]
    public function kelompok_angkatan_dicopot_bukan_disembunyikan(): void
    {
        /*
         * Sebelum Select2, kelompok yang tidak cocok cuma diberi `hidden` dan
         * `disabled` — sebab Safari mengabaikan `display` pada <optgroup>.
         * Select2 TIDAK membaca keduanya seperti peramban: kelompok disabled
         * tetap tergambar di daftarnya, cuma kelabu. Angkatan layanan lain
         * jadi tetap memenuhi daftar yang justru hendak disempitkan.
         */
        $skrip = $this->skrip();

        $this->assertStringContainsString('removeChild(g)', $skrip,
            'Kelompok yang tidak cocok tidak dicopot dari DOM; di Select2 ia tetap tergambar.');

        $this->assertStringContainsString("trigger('change.select2')", $skrip,
            'Select2 tidak diberi tahu saat pilihannya berubah; kotaknya akan menampilkan '
            . 'angkatan yang sudah dicopot.');

        /*
         * 'change.select2', BUKAN 'change'. Yang biasa akan membangunkan
         * penyaring hidup dan mengirim permintaan kedua yang tidak diminta
         * siapa pun.
         */
        $this->assertSame(0, preg_match("/trigger\('change'\)/", $skrip),
            "trigger('change') biasa membangunkan penyaring hidup; pakai 'change.select2'.");
    }

    #[Test]
    public function kotaknya_serupa_isian_lain_di_deret_saringan(): void
    {
        /*
         * Terukur sebelum digayai: kotaknya 62px tinggi dan 1130px lebar,
         * sementara isian di sebelahnya 40x211px — satu saringan selebar
         * seluruh baris, dan sisanya terdorong keluar.
         *
         * Dua sebabnya terpisah dan keduanya perlu diperbaiki:
         *   - lebar: containernya inline-block dan selnya melar mengikuti
         *     pilihan terpanjang, jadi min-width/max-width-nya wajib dipatok;
         *   - tinggi: `.select2-selection__rendered` membawa min-height 42px
         *     dari gaya lain, dan 42 + bantalan 18 + garis 2 = tepat 62.
         */
        $gaya = $this->skrip();

        $ada = preg_match('/#pdl-borang \.select2-container \{(?<isi>[^}]*)\}/', $gaya, $c);
        $this->assertSame(1, $ada, 'Aturan lebar container Select2 hilang.');
        $this->assertMatchesRegularExpression('/min-width:\s*0/', $c['isi']);
        $this->assertMatchesRegularExpression('/max-width:\s*100%/', $c['isi']);

        $ada = preg_match('/\.select2-selection__rendered \{(?<isi>[^}]*)\}/s', $gaya, $r);
        $this->assertSame(1, $ada, 'Aturan isi kotak Select2 hilang.');
        $this->assertMatchesRegularExpression('/min-height:\s*0/', $r['isi'],
            'min-height 42px dari gaya lain tidak dinolkan; kotaknya kembali 62px.');
    }

    #[Test]
    public function popupnya_tidak_dilebarkan_melewati_tepi_layar(): void
    {
        /*
         * Popupnya sempat dilebarkan jadi 330px supaya nama angkatan muat
         * sebaris. DIBATALKAN: saringan ini isian paling kanan, jadi popupnya
         * tumbuh ke luar layar — terukur di 1440px, tepi kanannya 1512 dan
         * halamannya menerbitkan penggulung MENDATAR yang menggeser seluruh
         * halaman.
         */
        $ada = preg_match('/\.select2-container--default \.select2-dropdown \{(?<isi>[^}]*)\}/s',
            $this->skrip(), $c);

        $this->assertSame(1, $ada, 'Aturan popup Select2 hilang.');

        $this->assertDoesNotMatchRegularExpression('/min-width/', $c['isi'],
            'Popupnya dilebarkan lagi; di 1440px ia melewati tepi kanan layar dan '
            . 'menerbitkan penggulung mendatar.');
    }
}
