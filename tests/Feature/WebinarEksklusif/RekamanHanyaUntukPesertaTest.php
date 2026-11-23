<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Siapa yang boleh menonton rekaman webinar.
 *
 * Alamat halaman tontonannya bisa ditebak-tempel oleh siapa pun yang punya
 * akun — dan sejak fitur ini ada, yang punya akun persis sebanyak orang yang
 * pernah membayar webinar apa pun. Peserta angkatan A yang membuka tautan
 * angkatan B harus DITOLAK, bukan sekadar "tidak melihatnya di daftar".
 *
 * Daftar yang rapi bukan penjagaan.
 */
class RekamanHanyaUntukPesertaTest extends TestCase
{
    use DatabaseTransactions;

    private const REKAMAN = 'https://youtu.be/dQw4w9WgXcQ';

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function angkatan(array $ganti = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'token' => Str::random(40),
            'layanan' => 'webinar_eksklusif',
            'nama' => 'Webinar Uji ' . Str::upper(Str::random(5)),
            'mulai' => now()->subWeek()->toDateString(),
            'status' => 'active',
            'rekaman_url' => self::REKAMAN,
        ], $ganti));
    }

    /**
     * Akun untuk satu alamat — DIAMBIL kalau sudah ada.
     *
     * Pendaftaran yang lunas sudah membuatkan akunnya sendiri lewat kait di
     * model, jadi penolong yang selalu membuat baru akan menabrak indeks unik
     * email. Ini bukan kelemahan ujinya; ini bentuk yang sebenarnya terjadi.
     */
    private function akun(string $email): User
    {
        $ada = User::where('email', $email)->first();

        if ($ada !== null) {
            /*
             * Penanda sandi bawaan DILEPAS di sini.
             *
             * Akun yang baru dibuatkan sistem ditahan middleware di halaman
             * ganti sandi, jadi tanpa ini seluruh uji di berkas ini cuma
             * mengukur pengalihan itu — bukan hak menontonnya. Penahannya
             * sendiri diuji terpisah di SandiBawaanDitahanTest.
             */
            $ada->forceFill(['wajib_ganti_sandi' => false])->save();

            return $ada;
        }

        $u = User::create([
            'full_name' => 'Peserta ' . Str::upper(Str::random(4)),
            'username' => 'rek_' . Str::random(10),
            'email' => $email,
            'password' => bcrypt('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        return $u;
    }

    private function pendaftaran(KategoriLayanan $a, string $email, string $status = 'paid'): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create([
            'token' => Str::random(30),
            'kategori_id' => $a->id,
            'nama' => 'Peserta Uji',
            'email' => $email,
            'telp' => '081234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => 129000,
            'status' => $status,
        ]);
    }

    #[Test]
    public function peserta_yang_sudah_bayar_bisa_menonton(): void
    {
        $a = $this->angkatan();
        $email = 'ikut' . Str::random(8) . '@contoh.test';
        $this->pendaftaran($a, $email);

        $r = $this->actingAs($this->akun($email))
            ->get(route('account.rekaman.tonton', $a->token));

        $r->assertOk();

        /*
         * Yang dipasang HARUS bentuk /embed/. Tautan youtu.be yang dipasang
         * apa adanya di <iframe src> menghasilkan pemutar kosong — rapi,
         * seukuran benar, tanpa galat apa pun.
         */
        $r->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
        $r->assertDontSee('src="https://youtu.be', false);
    }

    #[Test]
    public function peserta_angkatan_lain_ditolak(): void
    {
        $a = $this->angkatan();
        $b = $this->angkatan();

        $email = 'angkatanlain' . Str::random(8) . '@contoh.test';
        $this->pendaftaran($b, $email);

        $this->actingAs($this->akun($email))
            ->get(route('account.rekaman.tonton', $a->token))
            ->assertForbidden();
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('statusBelumLunas')]
    public function yang_belum_lunas_ditolak(string $status): void
    {
        /*
         * Satu status per jalan uji, lewat DataProvider — BUKAN perulangan di
         * dalam satu uji.
         *
         * AuthenticateSession mengikat sesi ke hash kata sandi pemiliknya.
         * Dua kali actingAs dengan akun BERBEDA di dalam satu uji membuat
         * yang kedua dianggap sesi curian dan dilempar ke halaman masuk —
         * jadi yang terukur pengalihan itu, bukan penolakan yang dimaksud.
         */
        $a = $this->angkatan();
        $email = 'belumbayar' . Str::random(8) . '@contoh.test';

        $this->pendaftaran($a, $email, $status);

        $this->actingAs($this->akun($email))
            ->get(route('account.rekaman.tonton', $a->token))
            ->assertForbidden();
    }

    /** @return array<string, array{string}> */
    public static function statusBelumLunas(): array
    {
        return [
            'menunggu pembayaran' => ['pending'],
            'dibatalkan' => ['cancel'],
            'kedaluwarsa' => ['expired'],
        ];
    }

    #[Test]
    public function peserta_rombongan_juga_boleh_menonton(): void
    {
        $a = $this->angkatan();
        $p = $this->pendaftaran($a, 'pemesan' . Str::random(8) . '@contoh.test');

        $emailRekan = 'rekan' . Str::random(8) . '@contoh.test';
        $p->pesertaLain()->create(['urutan' => 1, 'nama' => 'Rekan Satu', 'email' => $emailRekan]);

        /*
         * Rekan rombongan membayar lewat pemesannya dan TIDAK pernah muncul
         * di kolom email pendaftaran. Menyaring hanya dari kolom itu membuat
         * seluruh rombongan — selain pemesannya — ditolak menonton sesuatu
         * yang sudah mereka bayar.
         */
        $this->actingAs($this->akun($emailRekan))
            ->get(route('account.rekaman.tonton', $a->token))
            ->assertOk();
    }

    #[Test]
    public function peserta_layanan_lain_dengan_email_sama_tidak_ikut_masuk(): void
    {
        $a = $this->angkatan();
        $p = $this->pendaftaran($a, 'pemesan' . Str::random(8) . '@contoh.test');

        $emailLuar = 'luar' . Str::random(8) . '@contoh.test';

        /*
         * Tabel pesertanya DIPAKAI BERSAMA semua layanan. Tanpa saringan
         * `layanan`, peserta rombongan Scopus Camp yang emailnya kebetulan
         * sama ikut membuka rekaman webinar yang tidak pernah ia bayar.
         */
        \DB::table('pendaftaran_peserta')->insert([
            'id' => (string) Str::uuid(),
            'layanan' => 'scopus_camp',
            'pendaftaran_id' => $p->id,
            'urutan' => 9,
            'nama' => 'Peserta Camp',
            'email' => $emailLuar,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->akun($emailLuar))
            ->get(route('account.rekaman.tonton', $a->token))
            ->assertForbidden();
    }

    #[Test]
    public function angkatan_tanpa_rekaman_menjawab_404_bukan_pemutar_kosong(): void
    {
        $a = $this->angkatan(['rekaman_url' => null]);
        $email = 'belumada' . Str::random(8) . '@contoh.test';
        $this->pendaftaran($a, $email);

        $this->actingAs($this->akun($email))
            ->get(route('account.rekaman.tonton', $a->token))
            ->assertNotFound();
    }

    #[Test]
    public function daftar_hanya_memuat_angkatan_yang_dibayar_akun_ini(): void
    {
        $milik = $this->angkatan(['nama' => 'Webinar Milik Saya']);
        $lain = $this->angkatan(['nama' => 'Webinar Orang Lain']);

        $email = 'daftar' . Str::random(8) . '@contoh.test';
        $this->pendaftaran($milik, $email);
        $this->pendaftaran($lain, 'oranglain' . Str::random(6) . '@contoh.test');

        $r = $this->actingAs($this->akun($email))->get(route('account.rekaman.index'));

        $r->assertOk();
        $r->assertSee('Webinar Milik Saya');
        $r->assertDontSee('Webinar Orang Lain');
    }

    #[Test]
    public function yang_belum_ada_rekamannya_tetap_terlihat_di_daftar(): void
    {
        $a = $this->angkatan(['nama' => 'Webinar Tanpa Rekaman', 'rekaman_url' => null]);
        $email = 'nunggu' . Str::random(8) . '@contoh.test';
        $this->pendaftaran($a, $email);

        $r = $this->actingAs($this->akun($email))->get(route('account.rekaman.index'));

        /*
         * Peserta yang webinarnya baru selesai kemarin akan melihat halaman
         * KOSONG kalau barisnya disembunyikan — dan halaman kosong tidak bisa
         * ia bedakan dari "akun saya salah" atau "saya tidak terdaftar".
         */
        $r->assertOk();
        $r->assertSee('Webinar Tanpa Rekaman');
        $r->assertSee('Belum tersedia');
    }

    #[Test]
    public function tamu_tidak_bisa_membuka_apa_pun(): void
    {
        $a = $this->angkatan();

        $this->get(route('account.rekaman.index'))->assertRedirect();
        $this->get(route('account.rekaman.tonton', $a->token))->assertRedirect();
    }
}
