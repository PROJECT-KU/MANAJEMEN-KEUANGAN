<?php

namespace Tests\Feature;

use App\ArtikelKomentar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Komentar blog: pintu tulis publik satu-satunya di situs ini.
 *
 * Sampai 22 Okt 2026 pintu itu terbuka tanpa kunci: nol validasi, rutenya
 * tanpa batas laju, komentar langsung tampil, dan TIDAK ADA satu pun layar
 * untuk menghapusnya kembali — rute komentar yang ada di seluruh aplikasi
 * cuma POST publiknya. Terukur, satu-satunya komentar di basis data
 * berjudul "Call Center Agoda Indonesia".
 */
class KomentarBlogTest extends TestCase
{
    use DatabaseTransactions;

    private function artikel(): object
    {
        $a = DB::table('artikel')->where('status', 'publish')->first();

        $this->assertNotNull($a, 'Tidak ada tulisan terbit untuk diuji.');

        return $a;
    }

    private function orangDalam(): User
    {
        $u = User::all()->first(fn ($x) => $x->adalahOrangDalam());

        if ($u === null) {
            $this->markTestSkipped('Tidak ada akun orang dalam untuk diuji.');
        }

        return $u;
    }

    private function kiriman(array $ganti = []): array
    {
        return array_merge([
            'artikel_id' => $this->artikel()->id,
            'nama' => 'Uji Pengirim',
            'email' => 'uji@contoh.id',
            'komentar' => 'Ini komentar uji yang panjangnya wajar.',
        ], $ganti);
    }

    #[Test]
    public function komentar_baru_ditahan_dulu_dan_belum_tampil(): void
    {
        /*
         * Tabelnya dulu tidak punya kolom status sama sekali: apa pun yang
         * terkirim langsung terbaca orang di halaman tulisannya.
         */
        $artikel = $this->artikel();
        $kalimat = 'Komentar uji ' . Str::random(8);

        $this->post(route('blog.store.komentar'), $this->kiriman(['komentar' => $kalimat]))
            ->assertRedirect();

        $baris = ArtikelKomentar::where('komentar', $kalimat)->first();

        $this->assertNotNull($baris, 'Komentar yang sah tidak tersimpan.');
        $this->assertSame('tertahan', $baris->status,
            'Komentar baru langsung berstatus "' . $baris->status . '"; ia tampil ke publik '
            . 'sebelum ada yang melihatnya.');

        $this->get(route('blog.topic.blog-single', ['id' => $artikel->id]))
            ->assertOk()
            ->assertDontSee($kalimat, false);
    }

    #[Test]
    public function kiriman_yang_tidak_lengkap_ditolak(): void
    {
        /*
         * Nol validasi sebelumnya: nama kosong, email ngawur, dan komentar
         * sepanjang berapa pun semuanya masuk apa adanya.
         */
        $sebelum = ArtikelKomentar::count();

        $this->post(route('blog.store.komentar'), $this->kiriman([
            'nama' => '',
            'email' => 'bukan-email',
            'komentar' => '',
        ]))->assertSessionHasErrors(['nama', 'email', 'komentar']);

        $this->assertSame($sebelum, ArtikelKomentar::count(),
            'Kiriman yang tidak lolos validasi tetap tersimpan.');
    }

    #[Test]
    public function komentar_untuk_tulisan_yang_tidak_ada_ditolak(): void
    {
        $sebelum = ArtikelKomentar::count();

        $this->post(route('blog.store.komentar'), $this->kiriman(['artikel_id' => 99999999]))
            ->assertSessionHasErrors('artikel_id');

        $this->assertSame($sebelum, ArtikelKomentar::count());
    }

    #[Test]
    public function perangkap_lebah_membuang_kiriman_bot_tanpa_memberi_tahu(): void
    {
        /*
         * Dijawab dengan pesan sukses yang SAMA PERSIS, bukan penolakan: bot
         * yang diberi tahu ia tertangkap akan dicoba lagi dengan cara lain.
         */
        $kalimat = 'Spam uji ' . Str::random(8);

        $this->post(route('blog.store.komentar'), $this->kiriman([
            'komentar' => $kalimat,
            'alamat_kantor' => 'diisi bot',
        ]))->assertRedirect()->assertSessionHas('success');

        $this->assertSame(0, ArtikelKomentar::where('komentar', $kalimat)->count(),
            'Kiriman yang mengisi perangkap lebah tetap tersimpan.');
    }

    #[Test]
    public function pemilik_komentar_diambil_dari_sesi_bukan_dari_borang(): void
    {
        /*
         * `user_id` dulu diambil dari $request->input('user_id'), dan
         * borangnya selalu mengirim $artikel->user_id — id PENULIS artikelnya.
         * Jadi tiap komentar tamu tersimpan atas nama penulis tulisan itu,
         * dan siapa pun bisa menggantinya sebelum mengirim.
         */
        $kalimat = 'Komentar tamu ' . Str::random(8);
        $korban = $this->orangDalam();

        $this->post(route('blog.store.komentar'), $this->kiriman([
            'komentar' => $kalimat,
            'user_id' => $korban->id,
        ]))->assertRedirect();

        $baris = ArtikelKomentar::where('komentar', $kalimat)->first();

        $this->assertNotNull($baris);
        $this->assertNull($baris->user_id,
            'Komentar tamu tercatat atas nama pengguna id ' . $baris->user_id
            . ' yang dikirim dari borang.');
    }

    #[Test]
    public function status_tidak_bisa_disetel_dari_kiriman_publik(): void
    {
        /*
         * `status` sengaja TIDAK ada di $fillable model; nilainya datang dari
         * bawaan kolom. Kalau suatu saat ia dimasukkan ke fillable, kiriman
         * publik bisa menerbitkan dirinya sendiri.
         */
        $kalimat = 'Terbit paksa ' . Str::random(8);

        $this->post(route('blog.store.komentar'), $this->kiriman([
            'komentar' => $kalimat,
            'status' => 'terbit',
        ]))->assertRedirect();

        $this->assertSame('tertahan',
            ArtikelKomentar::where('komentar', $kalimat)->value('status'),
            'Kiriman publik berhasil menerbitkan komentarnya sendiri.');
    }

    #[Test]
    public function rutenya_dibatasi_lajunya(): void
    {
        /*
         * Rutenya dulu polos. Satu skrip bisa mengisi tabelnya semalaman
         * tanpa satu pun yang menahannya.
         */
        $rute = collect(app('router')->getRoutes())
            ->first(fn ($r) => $r->getName() === 'blog.store.komentar');

        $this->assertNotNull($rute, 'Rute kirim komentar hilang.');

        $punyaBatas = collect($rute->gatherMiddleware())
            ->contains(fn ($m) => is_string($m) && str_starts_with($m, 'throttle:'));

        $this->assertTrue($punyaBatas,
            'Rute kirim komentar tidak punya middleware throttle.');
    }

    // ------------------------------------------------------------ moderasi

    #[Test]
    public function layar_moderasinya_benar_benar_ada_dan_terbuka(): void
    {
        /*
         * Pengendalinya sudah ditulis sejak lama tetapi berkasnya bernama
         * "...Controller.php.php" — berakhiran ganda, jadi kelasnya tidak
         * pernah bisa dimuat dan nol rute menunjuknya.
         */
        $this->assertTrue(
            class_exists(\App\Http\Controllers\account\ArtikelKomentarController::class),
            'Kelas pengendali moderasi tidak bisa dimuat.');

        $this->actingAs($this->orangDalam())
            ->get(route('account.komentar-artikel.index'))
            ->assertOk()
            ->assertSee('Komentar blog', false);
    }

    #[Test]
    public function pelanggan_biasa_tidak_bisa_membuka_moderasi(): void
    {
        /*
         * Grup rute account/ TIDAK memakai middleware peran — tiap pengendali
         * memeriksa sendiri. Yang lupa memanggilnya terbuka untuk pelanggan
         * biasa yang kebetulan menebak alamatnya.
         */
        $luar = User::all()->first(fn ($u) => ! $u->adalahOrangDalam());

        if ($luar === null) {
            $this->markTestSkipped('Tidak ada akun non-orang-dalam untuk diuji.');
        }

        $this->actingAs($luar)
            ->get(route('account.komentar-artikel.index'))
            ->assertRedirect();
    }

    #[Test]
    public function menerbitkan_komentar_membuatnya_tampil(): void
    {
        $artikel = $this->artikel();
        $kalimat = 'Layak terbit ' . Str::random(8);

        $this->post(route('blog.store.komentar'), $this->kiriman(['komentar' => $kalimat]));

        $baris = ArtikelKomentar::where('komentar', $kalimat)->firstOrFail();

        $this->actingAs($this->orangDalam())
            ->post(route('account.komentar-artikel.status', $baris->id), ['status' => 'terbit'])
            ->assertRedirect();

        $this->get(route('blog.topic.blog-single', ['id' => $artikel->id]))
            ->assertOk()
            ->assertSee($kalimat, false);
    }

    #[Test]
    public function menghapus_komentar_ikut_menghapus_balasannya(): void
    {
        /*
         * Balasan yang ditinggalkan jadi yatim: tampilan publik mencarinya
         * lewat kolom `reply` yang menunjuk komentar yang sudah tidak ada,
         * jadi ia tidak pernah tergambar lagi tetapi menumpuk selamanya.
         */
        $artikel = $this->artikel();

        $induk = ArtikelKomentar::create([
            'artikel_id' => $artikel->id, 'token' => Str::random(20),
            'nama' => 'Induk', 'email' => 'a@b.id', 'komentar' => 'Induk ' . Str::random(6),
        ]);

        $balasan = ArtikelKomentar::create([
            'artikel_id' => $artikel->id, 'token' => Str::random(20), 'reply' => $induk->id,
            'nama' => 'Balasan', 'email' => 'c@d.id', 'komentar' => 'Balasan ' . Str::random(6),
        ]);

        $this->actingAs($this->orangDalam())
            ->deleteJson(route('account.komentar-artikel.destroy', $induk->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(ArtikelKomentar::find($induk->id), 'Komentarnya tidak terhapus.');
        $this->assertNull(ArtikelKomentar::find($balasan->id), 'Balasannya tertinggal jadi yatim.');
    }
}
