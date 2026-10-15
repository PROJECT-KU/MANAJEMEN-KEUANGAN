<?php

namespace Tests\Feature\PromoLayanan;

use App\KategoriLayanan;
use App\PromoLayanan;
use App\Support\HargaDiDeskripsi;
use App\Support\PromoAngkatan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Harga yang TERTULIS di naskah deskripsi ikut menyesuaikan promo.
 *
 * Naskah deskripsi memuat harganya sebagai huruf, bukan sebagai kolom —
 * "💵 Rp 150.000 per peserta" — karena naskah itu disalin apa adanya ke
 * WhatsApp. Promo tidak bisa menurunkan huruf.
 *
 * Akibatnya 15 Okt 2026 satu halaman menyebut DUA harga untuk satu sesi:
 * kotak pendaftarannya Rp 129.000, naskah di sebelahnya Rp 150.000. Yang
 * membacanya tidak punya cara tahu mana yang berlaku, dan halaman itu yang
 * dituju iklan berbayar.
 */
class HargaDiDeskripsiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        PromoAngkatan::lupakan();
    }

    private function sesi(array $ubah = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'webinar_eksklusif',
            'nama' => 'Sesi Naskah ' . Str::random(6),
            'nama_ke' => '9',
            'mulai' => now()->addDays(10)->toDateString(),
            'status' => 'active',
            'biaya' => '150000',
            'desc' => "Yang didapat peserta\n\n💵 Rp 150.000 per peserta\n\nInfo: 0889-8356-7819",
        ], $ubah));
    }

    private function promo(KategoriLayanan $sesi, array $ubah = []): PromoLayanan
    {
        $promo = PromoLayanan::create(array_merge([
            'nama' => 'Promo Uji ' . Str::random(4),
            'tipe' => 'nominal',
            'nominal' => '21000',
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(20)->toDateString(),
            'aktif' => true,
        ], $ubah));

        $promo->angkatan()->attach($sesi->getKey());

        PromoAngkatan::lupakan();

        return $promo;
    }

    #[Test]
    public function harga_di_naskah_ikut_turun_tanpa_admin_menyuntingnya(): void
    {
        $sesi = $this->sesi();
        $this->promo($sesi);

        $naskah = $sesi->fresh()->desc_promo;

        $this->assertStringContainsString('Rp 129.000', $naskah,
            'Harga promonya tidak masuk ke naskah; halaman itu akan menyebut '
            . 'dua harga berbeda untuk satu sesi.');

        $this->assertStringContainsString('harga normal Rp 150.000', $naskah,
            'Harga normalnya hilang sama sekali; yang membaca tidak tahu ia '
            . 'sedang mendapat potongan.');
    }

    #[Test]
    public function kolom_aslinya_tidak_ikut_diubah(): void
    {
        /*
         * INI yang membuat perubahan ini aman: naskah yang tersimpan tetap
         * apa yang diketik admin. Promo yang berakhir mengembalikan
         * tulisannya sendiri, tanpa perlu ada yang membetulkannya.
         */
        $sesi = $this->sesi();
        $promo = $this->promo($sesi);

        $sesi->fresh()->desc_promo;

        $this->assertStringContainsString('Rp 150.000', $sesi->fresh()->desc);
        $this->assertStringNotContainsString('129.000', $sesi->fresh()->desc,
            'Kolom desc ikut tertulis ulang; suntingan admin akan hilang.');

        // Promonya dimatikan — naskahnya kembali seperti semula dengan
        // sendirinya.
        $promo->forceFill(['aktif' => false])->save();
        PromoAngkatan::lupakan();

        $this->assertStringNotContainsString('harga normal',
            $sesi->fresh()->desc_promo,
            'Promo sudah mati tetapi naskahnya masih memajang potongan.');
    }

    #[Test]
    public function tanpa_promo_naskahnya_tidak_disentuh(): void
    {
        $sesi = $this->sesi();

        $this->assertSame($sesi->desc, $sesi->fresh()->desc_promo);
    }

    #[Test]
    public function angka_lain_di_naskah_tidak_ikut_tergaruk(): void
    {
        /*
         * Yang diganti HANYA angka yang persis sama dengan harga normalnya.
         * Nomor telepon, nominal lain, dan harga yang kebetulan memuat
         * angkanya sebagai bagian — "Rp 1.150.000" — tidak boleh tersentuh.
         */
        $sesi = $this->sesi([
            'desc' => "Harga Rp 150.000 per peserta.\n"
                . "Paket lengkap Rp 1.150.000.\n"
                // INI yang benar-benar menguji batasnya: harga normalnya
                // adalah awalan angka ini. Tanpa penjaga di belakang,
                // "Rp 150.000" tergaruk dari dalam "Rp 150.000.000" dan yang
                // tercetak jadi "Rp 129.000 (harga normal Rp 150.000).000".
                . "Dana hibah Rp 150.000.000 untuk riset.\n"
                . "Hubungi 0889-8356-7819.",
        ]);
        $this->promo($sesi);

        $naskah = $sesi->fresh()->desc_promo;

        $this->assertStringContainsString('Rp 1.150.000', $naskah,
            'Harga lain ikut diubah karena angkanya memuat harga normalnya.');
        $this->assertStringContainsString('Rp 150.000.000', $naskah,
            'Angka yang DIAWALI harga normalnya ikut tergaruk; yang tercetak '
            . 'jadi nominal yang tidak pernah ada.');
        $this->assertStringContainsString('0889-8356-7819', $naskah);
        $this->assertSame(1, substr_count($naskah, 'harga normal'),
            'Penggantiannya terjadi lebih dari sekali di tempat yang salah.');
    }

    #[Test]
    public function naskah_yang_memakai_rp_bertitik_ikut_dikenali(): void
    {
        // Naskah lama menulis "Rp." — keduanya dipakai di data sungguhan.
        $sesi = $this->sesi(['desc' => 'Biaya Rp. 150.000 saja.']);
        $this->promo($sesi);

        $this->assertStringContainsString('Rp 129.000', $sesi->fresh()->desc_promo);
    }

    private function admin(): User
    {
        $t = Str::random(8);

        $orang = User::create([
            'full_name' => 'Uji Naskah ' . $t, 'username' => 'nsk_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $orang->forceFill([
            'status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        return $orang;
    }

    #[Test]
    public function layar_ubah_memberi_tahu_promonya_tanpa_mengubah_kotaknya(): void
    {
        /*
         * Kotak suntingnya HARUS tetap berisi naskah asli.
         *
         * Kalau ia ikut memajang harga promo, sekali tekan Simpan angka
         * promonya tertulis permanen ke naskah — dan tidak akan kembali saat
         * promonya berakhir. Cacat itu jauh lebih sulit disadari daripada
         * yang sedang diperbaiki, sebab tidak ada yang kelihatan salah.
         *
         * Jadi adminnya diberi tahu, bukan kotaknya yang diubah.
         */
        $sesi = $this->sesi();
        $promo = $this->promo($sesi);

        $jawab = $this->actingAs($this->admin())
            ->get(route('account.kategori-layanan.edit', $sesi->getKey()))
            ->assertOk();

        $jawab->assertSee($promo->nama . ' sedang berjalan', false);
        $jawab->assertSee('Rp 129.000 (harga normal Rp 150.000) per peserta', false);

        // Isi <textarea>-nya diperiksa tersendiri: yang di dalamnya harus
        // masih naskah asli, bukan yang sudah disesuaikan.
        preg_match('/<textarea[^>]*name="desc".*?>(.*?)<\/textarea>/s', $jawab->getContent(), $kotak);

        $this->assertNotEmpty($kotak, 'Kotak deskripsinya tidak ketemu di halaman.');
        $this->assertStringContainsString('Rp 150.000', $kotak[1]);
        $this->assertStringNotContainsString('129.000', $kotak[1],
            'Kotak suntingnya memajang harga promo; sekali Simpan, angka itu '
            . 'tertulis permanen dan tidak kembali saat promonya berakhir.');
    }

    #[Test]
    public function tanpa_promo_layar_ubah_tidak_memberi_kabar_apa_pun(): void
    {
        $sesi = $this->sesi();

        $this->actingAs($this->admin())
            ->get(route('account.kategori-layanan.edit', $sesi->getKey()))
            ->assertOk()
            ->assertDontSee('sedang berjalan', false);
    }

    #[Test]
    public function naskah_kosong_tetap_kosong(): void
    {
        $sesi = $this->sesi(['desc' => null]);
        $this->promo($sesi);

        $this->assertSame('', HargaDiDeskripsi::sesuaikan($sesi->fresh(), null));
    }
}
