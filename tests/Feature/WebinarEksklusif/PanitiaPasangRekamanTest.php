<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Panitia memasang tautan rekaman pada satu angkatan.
 *
 * Tiap angkatan punya rekamannya SENDIRI — itu yang membuat tautannya
 * disimpan di baris angkatan, bukan di pendaftaran. Menyimpannya per
 * pendaftaran berarti menyalin tautan yang sama puluhan kali, lalu salah
 * satunya tertinggal saat tautannya diganti.
 */
class PanitiaPasangRekamanTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function panitia(): User
    {
        $u = User::create([
            'full_name' => 'Panitia Uji',
            'username' => 'pan_' . Str::random(10),
            'email' => 'pan' . Str::random(8) . '@contoh.test',
            'password' => bcrypt('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        return $u;
    }

    private function angkatan(array $ganti = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'token' => Str::random(40),
            'layanan' => 'webinar_eksklusif',
            'nama' => 'Webinar Uji ' . Str::upper(Str::random(5)),
            'mulai' => now()->subWeek()->toDateString(),
            'status' => 'active',
        ], $ganti));
    }

    /** @return array<string, mixed> */
    private function isian(KategoriLayanan $a, array $ganti = []): array
    {
        return array_merge([
            'layanan' => $a->layanan,
            'nama' => $a->nama,
            'mulai' => $a->mulai,
            'status' => $a->status,
        ], $ganti);
    }

    #[Test]
    public function tautan_rekaman_tersimpan_beserta_waktu_pemasangannya(): void
    {
        $a = $this->angkatan();

        $this->actingAs($this->panitia())
            ->post(route('account.kategori-layanan.update', $a), $this->isian($a, [
                'rekaman_url' => 'https://youtu.be/dQw4w9WgXcQ?si=AbCdEfGh',
            ]))
            ->assertRedirect();

        $a->refresh();

        $this->assertSame('https://youtu.be/dQw4w9WgXcQ?si=AbCdEfGh', $a->rekaman_url,
            'Tautannya disimpan APA ADANYA; yang dinormalkan hanya saat '
            . 'disematkan, supaya panitia melihat kembali yang ia tempel.');
        $this->assertNotNull($a->rekaman_dipasang_pada);
    }

    #[Test]
    public function tiap_angkatan_punya_rekamannya_sendiri(): void
    {
        $a = $this->angkatan(['nama' => 'Webinar Angkatan A']);
        $b = $this->angkatan(['nama' => 'Webinar Angkatan B']);
        $panitia = $this->panitia();

        $this->actingAs($panitia)->post(route('account.kategori-layanan.update', $a),
            $this->isian($a, ['rekaman_url' => 'https://youtu.be/AAAAAAAAAAA']));
        $this->actingAs($panitia)->post(route('account.kategori-layanan.update', $b),
            $this->isian($b, ['rekaman_url' => 'https://youtu.be/BBBBBBBBBBB']));

        $this->assertSame('https://youtu.be/AAAAAAAAAAA', $a->fresh()->rekaman_url);
        $this->assertSame('https://youtu.be/BBBBBBBBBBB', $b->fresh()->rekaman_url);
    }

    #[Test]
    public function tautan_yang_bukan_youtube_ditolak_dengan_alasan(): void
    {
        $a = $this->angkatan();

        /*
         * Ditolak di borang, bukan disimpan lalu gagal diam-diam di layar
         * peserta. Tautan apa pun yang lolos aturan 'url' akan tersimpan
         * rapi lalu menghasilkan pemutar KOSONG — tanpa galat, tanpa baris
         * log, dan satu-satunya yang melihatnya orang yang sudah membayar.
         */
        $this->actingAs($this->panitia())
            ->post(route('account.kategori-layanan.update', $a), $this->isian($a, [
                'rekaman_url' => 'https://drive.google.com/file/d/abc/view',
            ]))
            ->assertSessionHasErrors('rekaman_url');

        $this->assertNull($a->fresh()->rekaman_url);
    }

    #[Test]
    public function tautan_youtube_tanpa_video_juga_ditolak(): void
    {
        $a = $this->angkatan();

        // Inangnya benar, videonya tidak ada — ini yang paling mudah lolos
        // kalau validasinya cuma memeriksa kata "youtube".
        $this->actingAs($this->panitia())
            ->post(route('account.kategori-layanan.update', $a), $this->isian($a, [
                'rekaman_url' => 'https://www.youtube.com/@rumahscopus',
            ]))
            ->assertSessionHasErrors('rekaman_url');
    }

    #[Test]
    public function tautan_boleh_dikosongkan_lagi(): void
    {
        $a = $this->angkatan(['rekaman_url' => 'https://youtu.be/dQw4w9WgXcQ']);
        $a->forceFill(['rekaman_dipasang_pada' => now()->subDay()])->save();

        $this->actingAs($this->panitia())
            ->post(route('account.kategori-layanan.update', $a), $this->isian($a, [
                'rekaman_url' => '',
            ]))
            ->assertRedirect();

        $a->refresh();

        // Salah tempel harus bisa dibatalkan, dan penanda waktunya ikut
        // dibersihkan supaya tidak mengaku ada rekaman yang sudah dipasang.
        $this->assertNull($a->rekaman_url);
        $this->assertNull($a->rekaman_dipasang_pada);
    }

    #[Test]
    public function menyimpan_hal_lain_tidak_menggeser_waktu_pemasangan(): void
    {
        $a = $this->angkatan(['rekaman_url' => 'https://youtu.be/dQw4w9WgXcQ']);
        $a->forceFill(['rekaman_dipasang_pada' => now()->subMonth()])->save();
        $sebelum = $a->fresh()->rekaman_dipasang_pada;

        $this->actingAs($this->panitia())
            ->post(route('account.kategori-layanan.update', $a), $this->isian($a, [
                'rekaman_url' => $a->rekaman_url,
                'lokasi' => 'Daring',
            ]));

        /*
         * Tanggalnya dipakai untuk tahu angkatan mana yang sudah diurus, dan
         * nanti untuk mengabari "rekaman baru tersedia". Kalau ia bergerak
         * tiap penyimpanan, kabarnya terkirim ulang ke orang yang sudah
         * menontonnya.
         */
        $this->assertEquals(
            $sebelum->format('Y-m-d H:i:s'),
            $a->fresh()->rekaman_dipasang_pada->format('Y-m-d H:i:s')
        );
    }

    #[Test]
    public function pelanggan_tidak_bisa_memasang_rekaman(): void
    {
        $a = $this->angkatan();

        $u = User::create([
            'full_name' => 'Pelanggan', 'username' => 'pel_' . Str::random(10),
            'email' => 'pel' . Str::random(8) . '@contoh.test',
            'password' => bcrypt('RahasiaUji2026'), 'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        $this->actingAs($u)
            ->post(route('account.kategori-layanan.update', $a), $this->isian($a, [
                'rekaman_url' => 'https://youtu.be/dQw4w9WgXcQ',
            ]));

        $this->assertNull($a->fresh()->rekaman_url,
            'Hanya orang dalam yang boleh memasang rekaman.');
    }
}
