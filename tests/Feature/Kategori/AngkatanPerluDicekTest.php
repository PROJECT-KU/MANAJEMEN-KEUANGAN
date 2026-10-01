<?php

namespace Tests\Feature\Kategori;

use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Layanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tiga keadaan yang perlu ditindaklanjuti, dan hapus massal.
 *
 * Ketiganya ditemukan dengan mengukur data yang ada, bukan dibayangkan:
 * 24 draf yang tanggalnya lewat, 12 angkatan yang tidak menemukan tarif
 * induknya, dan sejumlah angkatan yang berkas sampulnya tidak ada di cakram.
 * Sebelum ini tak satu pun kelihatan dari layar mana pun.
 */
class AngkatanPerluDicekTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    private function akun(string $peran = User::PERAN_ADMINISTRATOR): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . Str::random(4),
            'username' => 'uji_perlu_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    private function angkatan(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'scopus_camp', 'varian' => 'jawa',
            'token' => Str::random(30), 'nama' => 'Angkatan Uji ' . Str::random(4),
            'nama_ke' => '7', 'mulai' => now()->addMonth(),
            'selesai' => now()->addMonth()->addDays(2), 'lokasi' => 'Yogyakarta',
            'total_kuota' => '20', 'sisa_kuota' => '20', 'status' => 'draft',
        ], $lain));
    }

    // ------------------------------------------------- draf kadaluwarsa

    #[Test]
    public function draf_yang_tanggalnya_lewat_ditandai(): void
    {
        $lewat = $this->angkatan(['mulai' => now()->subMonths(2), 'selesai' => now()->subMonths(2)->addDay()]);
        $akan = $this->angkatan(['mulai' => now()->addMonth(), 'selesai' => now()->addMonth()->addDay()]);

        $this->assertTrue($lewat->draf_kadaluwarsa);
        $this->assertFalse($akan->draf_kadaluwarsa);
    }

    #[Test]
    public function yang_aktif_bukan_draf_kadaluwarsa(): void
    {
        /*
         * Lencana "Lewat" yang sudah ada memang khusus angkatan AKTIF — ia
         * peringatan bahwa sesuatu masih terpajang padahal sudah selesai.
         * Dua lencana untuk satu baris cuma membingungkan.
         */
        $aktif = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->subMonths(2), 'selesai' => now()->subMonths(2)->addDay(),
        ]);

        $this->assertFalse($aktif->draf_kadaluwarsa);
        $this->assertTrue($aktif->sudah_lewat);
    }

    #[Test]
    public function draf_tanpa_tanggal_selesai_dinilai_dari_tanggal_mulainya(): void
    {
        $this->assertTrue(
            $this->angkatan(['mulai' => now()->subWeek(), 'selesai' => null])->draf_kadaluwarsa
        );
    }

    // ---------------------------------------------------- tanpa tarif

    #[Test]
    public function angkatan_tanpa_tarif_induk_ditandai_dan_bisa_disaring(): void
    {
        // Varian yang tidak punya tarif berlaku.
        $yatim = $this->angkatan(['varian' => null]);

        $this->assertTrue($yatim->tarif_hilang);
        $this->assertTrue(
            KategoriLayanan::perlu('tanpa-tarif')->where('id', $yatim->id)->exists()
        );

        $punya = $this->angkatan(['varian' => 'jawa']);
        $this->assertFalse($punya->tarif_hilang);
        $this->assertFalse(
            KategoriLayanan::perlu('tanpa-tarif')->where('id', $punya->id)->exists()
        );
    }

    // --------------------------------------------------- sampul hilang

    #[Test]
    public function sampul_yang_berkasnya_tidak_ada_ditandai(): void
    {
        $hilang = $this->angkatan(['gambar' => 'ScopusCamp/tidak-pernah-ada-' . Str::random(8) . '.jpg']);
        $kosong = $this->angkatan(['gambar' => null]);

        $this->assertTrue($hilang->sampul_hilang);

        // Yang memang belum punya sampul BUKAN "hilang": itu keadaan biasa,
        // dan halaman rincian sudah menjelaskannya sendiri.
        $this->assertFalse($kosong->sampul_hilang);
    }

    #[Test]
    public function jalur_sampul_diperiksa_sekali_per_berkas(): void
    {
        /*
         * Empat puluh satu angkatan Yogyakarta menunjuk satu berkas yang sama.
         * Diperiksa per baris, itu empat puluh satu stat() untuk jawaban yang
         * sama persis.
         */
        $jalur = 'ScopusCamp/berbagi-' . Str::random(8) . '.jpg';

        $this->angkatan(['gambar' => $jalur]);
        $this->angkatan(['gambar' => $jalur]);
        $this->angkatan(['gambar' => $jalur]);

        $hilang = KategoriLayanan::jalurSampulHilang();

        $this->assertSame(1, count(array_keys($hilang, $jalur)));
    }

    // ------------------------------------------------------ saringan

    #[Test]
    public function saringan_perlu_dipakai_daftar_dan_unduhan(): void
    {
        $this->actingAs($this->akun());

        $lewat = $this->angkatan([
            'nama' => 'ZZ Draf Kadaluwarsa Uji',
            'mulai' => now()->subMonths(3), 'selesai' => now()->subMonths(3)->addDay(),
        ]);

        $this->get(route('account.kategori-layanan.index', ['perlu' => 'draf-lewat']))
            ->assertOk()
            ->assertSee('ZZ Draf Kadaluwarsa Uji');

        // Saringan karangan diabaikan, bukan meledak.
        $this->get(route('account.kategori-layanan.index', ['perlu' => 'karangan']))
            ->assertOk();
    }

    #[Test]
    public function hitungan_perlu_tidak_ikut_tersaring_dirinya_sendiri(): void
    {
        /*
         * Kalau hitungannya ikut menyaring, menekan "Draf kadaluwarsa" membuat
         * angka dua keadaan lain jadi 0 — dan jalan kembalinya hilang dari
         * layar.
         */
        $this->actingAs($this->akun());
        $this->angkatan(['mulai' => now()->subMonths(3), 'selesai' => now()->subMonths(3)->addDay()]);
        $this->angkatan(['varian' => null]);

        $isi = $this->get(route('account.kategori-layanan.index', ['perlu' => 'draf-lewat']))
            ->assertOk()->getContent();

        // Menu saringannya menyebut jumlah tiap keadaan; yang bukan sedang
        // dipilih tidak boleh jadi nol.
        $this->assertMatchesRegularExpression('/Tidak menemukan tarif induk \((?!0\))\d+\)/', $isi);
    }

    #[Test]
    public function hitungan_ubin_menghitung_baris_unik(): void
    {
        /*
         * Satu angkatan bisa kena dua keadaan sekaligus. Dijumlahkan,
         * ubinnya terukur menulis 47 dari 60 baris padahal baris uniknya 34 —
         * angka yang lebih besar daripada kenyataan membuat orang menganggap
         * seluruh daftarnya rusak.
         */
        $ganda = $this->angkatan([
            'varian' => null,                                   // tanpa tarif
            'mulai' => now()->subMonths(3),                     // draf kadaluwarsa
            'selesai' => now()->subMonths(3)->addDay(),
        ]);

        $this->assertCount(2, $ganda->perlu_dicek);

        $satuan = KategoriLayanan::perlu('draf-lewat')->where('id', $ganda->id)->count()
            + KategoriLayanan::perlu('tanpa-tarif')->where('id', $ganda->id)->count();

        $this->assertSame(2, $satuan, 'dihitung dua kali kalau ketiganya dijumlahkan');
        $this->assertSame(1, KategoriLayanan::perluApaPun()->where('id', $ganda->id)->count());
    }

    #[Test]
    public function yang_sehat_tidak_masuk_hitungan(): void
    {
        $sehat = $this->angkatan(['mulai' => now()->addMonth(), 'selesai' => now()->addMonth()->addDay()]);

        $this->assertSame([], $sehat->perlu_dicek);
        $this->assertFalse(KategoriLayanan::perluApaPun()->where('id', $sehat->id)->exists());
    }

    // --------------------------------------------------- hapus massal

    #[Test]
    public function hapus_massal_melewati_yang_sudah_punya_pendaftar(): void
    {
        $this->actingAs($this->akun());

        $bersih = $this->angkatan();
        $berpendaftar = $this->angkatan();

        DB::table('scopus_camp_pendaftaran')->insert([
            'id' => (string) Str::uuid(),
            'kategori_id' => $berpendaftar->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->postJson(route('account.kategori-layanan.massal-hapus'), [
            'id' => [$bersih->id, $berpendaftar->id],
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertNull(KategoriLayanan::find($bersih->id));

        // Dilewati, BUKAN membatalkan seluruh tindakan: satu angkatan
        // berpendaftar di tengah pilihan tidak boleh menggagalkan sisanya.
        $this->assertNotNull(KategoriLayanan::find($berpendaftar->id));
    }

    #[Test]
    public function hapus_massal_bilang_berapa_yang_dilewati(): void
    {
        $this->actingAs($this->akun());
        $a = $this->angkatan();

        DB::table('scopus_camp_pendaftaran')->insert([
            'id' => (string) Str::uuid(), 'kategori_id' => $a->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->postJson(route('account.kategori-layanan.massal-hapus'), ['id' => [$a->id]])
            ->assertOk()
            ->assertJson(['success' => false])
            ->assertJsonPath('message', 'Tidak ada yang bisa dihapus; semuanya sudah punya pendaftar.');
    }

    #[Test]
    public function orang_luar_tidak_bisa_hapus_massal(): void
    {
        $a = $this->angkatan();
        $this->actingAs($this->akun(User::PERAN_PELANGGAN));

        $this->postJson(route('account.kategori-layanan.massal-hapus'), ['id' => [$a->id]])
            ->assertStatus(403);

        $this->assertNotNull(KategoriLayanan::find($a->id));
    }

    #[Test]
    public function hapus_massal_dibatasi_dua_ratus(): void
    {
        $this->actingAs($this->akun());

        $this->postJson(route('account.kategori-layanan.massal-hapus'), [
            'id' => array_map(fn () => (string) Str::uuid(), range(1, 201)),
        ])->assertStatus(422)->assertJsonPath('errors.id.0', 'Maksimal 200 angkatan sekali hapus.');
    }

    // ------------------------------------------------------- gandakan

    #[Test]
    public function hasil_gandakan_memperingatkan_tanggalnya(): void
    {
        $this->actingAs($this->akun());
        $asal = $this->angkatan();

        $tujuan = $this->post(route('account.kategori-layanan.gandakan', $asal))
            ->assertRedirect()->headers->get('Location');

        $this->assertStringContainsString('digandakan=1', $tujuan);

        $this->get($tujuan)->assertOk()->assertSee('tanggal hari ini', false);
    }

    #[Test]
    public function borang_biasa_tidak_memperingatkan_apa_apa(): void
    {
        $this->actingAs($this->akun());
        $a = $this->angkatan();

        $this->get(route('account.kategori-layanan.edit', $a))
            ->assertOk()
            ->assertDontSee('tanggal hari ini', false);
    }
}
