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
        $this->assertMatchesRegularExpression('/Tanpa tarif induk \((?!0\))\d+\)/', $isi);
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

    // ------------------------------------------- aktif tapi sudah lewat

    #[Test]
    public function aktif_yang_tanggalnya_lewat_ikut_perlu_dicek(): void
    {
        /*
         * Yang PALING mendesak justru ini: angkatannya masih terpajang di
         * halaman publik padahal acaranya sudah selesai. Versi pertama ubin
         * "Perlu dicek" melewatkannya.
         */
        $a = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->subMonths(2), 'selesai' => now()->subMonths(2)->addDay(),
        ]);

        $this->assertContains('masih aktif padahal tanggalnya sudah lewat — nonaktifkan kalau acaranya memang selesai', $a->perlu_dicek);
        $this->assertTrue(KategoriLayanan::perlu('aktif-lewat')->where('id', $a->id)->exists());
        $this->assertTrue(KategoriLayanan::perluApaPun()->where('id', $a->id)->exists());
    }

    #[Test]
    public function yang_sudah_punya_lencana_sendiri_tidak_dilencanai_dua_kali(): void
    {
        // Lencana "Lewat" sudah ada dan bisa ditekan untuk menonaktifkan;
        // menambahkan "Perlu dicek" di sebelahnya berarti dua peringatan untuk
        // satu hal yang sama.
        $a = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->subMonths(2), 'selesai' => now()->subMonths(2)->addDay(),
        ]);

        $this->assertNotEmpty($a->perlu_dicek);
        $this->assertSame([], $a->perlu_dicek_lain);
    }

    // ------------------------------------------------ unduhan & cetakan

    #[Test]
    public function ringkasan_cetakan_menyebut_saringan_perlu(): void
    {
        /*
         * Tanpa ini, berkas yang dicetak sambil menyaring draf kadaluwarsa
         * tetap berkepala "Seluruh angkatan, tanpa saringan." — pernyataan
         * yang salah di dokumen yang mungkin diarsipkan orang.
         */
        // Diperiksa lewat perakit kalimatnya, bukan isi berkas PDF-nya:
        // keluarannya terkompresi, jadi mencari kata di dalamnya menguji
        // pemampatan dompdf, bukan kalimat yang kita tulis.
        $perakit = new \ReflectionMethod(
            \App\Http\Controllers\account\KategoriLayananController::class,
            'ringkasanSaringan'
        );
        $perakit->setAccessible(true);

        $pengendali = app(\App\Http\Controllers\account\KategoriLayananController::class);

        $tanpa = $perakit->invoke($pengendali, \Illuminate\Http\Request::create('/'));
        $dengan = $perakit->invoke($pengendali,
            \Illuminate\Http\Request::create('/', 'GET', ['perlu' => 'draf-lewat']));

        $this->assertStringContainsString('tanpa saringan', $tanpa);
        $this->assertStringNotContainsString('tanpa saringan', $dengan);
        $this->assertStringContainsString('draf kadaluwarsa', $dengan);
    }

    #[Test]
    public function unduhan_excel_membawa_kolom_perlu_dicek(): void
    {
        $kepala = (new \App\Exports\AngkatanLayananExport(collect()))->headings();

        $this->assertContains('Perlu dicek', $kepala,
            'daftar yang diunduh untuk ditindaklanjuti kehilangan keterangan yang membuatnya perlu ditindaklanjuti');
    }

    // ------------------------------------------------------ pengurutan

    #[Test]
    public function biaya_diurutkan_sebagai_bilangan_bukan_huruf(): void
    {
        $this->actingAs($this->akun());

        // Sebagai huruf, "999000" berdiri di atas "5500000".
        $mahal = $this->angkatan(['nama' => 'ZZ Mahal Uji', 'biaya' => '5500000']);
        $murah = $this->angkatan(['nama' => 'ZZ Murah Uji', 'biaya' => '999000']);

        $urutan = KategoriLayanan::whereIn('id', [$mahal->id, $murah->id])
            ->orderByRaw('CAST(biaya AS UNSIGNED) desc')->pluck('nama')->all();

        $this->assertSame(['ZZ Mahal Uji', 'ZZ Murah Uji'], $urutan);

        $this->get(route('account.kategori-layanan.index', ['urut' => 'biaya', 'arah' => 'turun']))
            ->assertOk();
    }

    #[Test]
    public function kolom_urut_karangan_tetap_ditolak(): void
    {
        $this->actingAs($this->akun());

        // Nilainya datang dari alamat; nama kolom sembarang tidak boleh sampai
        // ke orderBy apa adanya.
        $this->get(route('account.kategori-layanan.index', ['urut' => 'password']))->assertOk();
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
    #[Test]
    public function hitungan_kueri_dan_hitungan_php_tidak_boleh_berbeda(): void
    {
        /*
         * Pertanyaan yang sama dijawab DUA tempat: ubin "Perlu dicek"
         * menghitungnya lewat kueri (scopePerluApaPun), rincian per baris
         * lewat accessor PHP (perlu_dicek). Keduanya pernah menyimpang tanpa
         * ada yang terlihat rusak.
         *
         * Yang menyimpang pemeriksaan sampul: kueri cuma melihat
         * public/<folder>/<berkas> — jalur lama — sementara accessor memakai
         * AlamatGambar yang juga tahu cakram "unggahan". Sejak unggahan pindah
         * ke sana, terukur 58 dari 59 angkatan dinyatakan kehilangan sampul
         * padahal yang benar 9, dan ubinnya menulis 59 dari 59.
         *
         * Tanda yang menyala di SETIAP baris sama saja dengan tidak ada tanda.
         * Dan gambarnya tetap tampil di layar — yang menggambarnya memakai
         * accessor — jadi tidak ada satu pun gejala yang bisa dilihat.
         */
        $lewatKueri = KategoriLayanan::query()->perluApaPun()->count();

        $lewatPhp = KategoriLayanan::all()
            ->filter(fn (KategoriLayanan $a) => $a->perlu_dicek !== [])
            ->count();

        $this->assertSame($lewatPhp, $lewatKueri, sprintf(
            'Ubin "Perlu dicek" menghitung %d angkatan, rincian per barisnya %d. '
            . 'Dua jawaban berbeda untuk pertanyaan yang sama.',
            $lewatKueri,
            $lewatPhp
        ));
    }

    #[Test]
    public function tiap_alasan_menyebut_apa_yang_harus_dikerjakan(): void
    {
        /*
         * Yang memakai layar ini bukan orang teknis. "Perlu dicek" saja
         * membuat mereka tahu ada yang salah tetapi tidak tahu harus berbuat
         * apa — dan yang tidak tahu harus berbuat apa akhirnya tidak berbuat
         * apa-apa.
         *
         * Dijaga PEMISAHNYA, bukan kata kerjanya: tiap alasan wajib memuat
         * tanda pisah yang memisahkan "apa yang salah" dari "apa yang
         * dikerjakan". Menjaga daftar kata kerja akan merah tiap kali
         * kalimatnya diperhalus.
         */
        $a = $this->angkatan([
            'status' => 'draft',
            'mulai' => now()->subDays(10)->toDateString(),
            'selesai' => now()->subDays(9)->toDateString(),
        ]);

        $this->assertNotEmpty($a->perlu_dicek, 'prasyarat ujinya: angkatan ini harus punya alasan');

        foreach ($a->perlu_dicek as $alasan) {
            $this->assertStringContainsString(' — ', $alasan, sprintf(
                'Alasan "%s" cuma menyebut apa yang salah, tanpa apa yang harus dikerjakan.',
                $alasan
            ));
        }
    }

    #[Test]
    public function alasannya_dirinci_di_halaman_ubah_bukan_di_daftar(): void
    {
        /*
         * Alasannya dulu cuma ada di atribut title di daftar. Tooltip menuntut
         * orangnya tahu harus mengarahkan tetikus lalu menunggu, dan di layar
         * sentuh ia TIDAK PERNAH muncul — jadi bagi sebagian pemakai
         * informasinya memang tidak ada.
         *
         * Dicoba merincinya di daftar, dan itu salah tempat: baris yang kena
         * dua hal tumbuh dari 71px jadi 234px, dan daftar yang gunanya
         * dipindai sekilas berubah jadi bacaan.
         *
         * Tempatnya di halaman ubah — di sana orangnya sudah datang untuk
         * membetulkan, dan semua tombol yang disebut alasannya ada di layar
         * yang sama. Di daftar cukup lencana berisi jumlahnya.
         */
        $a = $this->angkatan([
            'status' => 'draft',
            'mulai' => now()->subDays(10)->toDateString(),
            'selesai' => now()->subDays(9)->toDateString(),
        ]);

        $this->assertNotEmpty($a->perlu_dicek, 'prasyarat ujinya: angkatan ini harus punya alasan');

        $this->actingAs($this->akun());

        $isi = $this->get(route('account.kategori-layanan.edit', $a))
            ->assertOk()
            ->getContent();

        /*
         * Dicocokkan sebagai ATRIBUT class yang utuh, bukan potongan teks.
         * Percobaan pertama mencari "brg-perlu" saja, dan itu tetap hijau saat
         * pembungkusnya diganti nama — sebab "brg-perlu-judul" di dalamnya
         * masih memuat potongan yang sama.
         */
        $this->assertMatchesRegularExpression('/class="brg-perlu"/', $isi,
            'Panel rincian "perlu dicek" hilang dari halaman ubah.');

        foreach ($a->perlu_dicek as $sebab) {
            $this->assertStringContainsString(e(ucfirst($sebab)), $isi,
                'Alasan "' . $sebab . '" tidak tergambar di halaman ubah.');
        }

        /*
         * Dan di DAFTAR alasannya tidak dirinci — cukup lencana. Dijaga dari
         * markahnya, sebab yang diperiksa ketiadaan sesuatu.
         */
        $markah = file_get_contents(
            resource_path('views/account/kategori_layanan/index.blade.php')
        );

        $this->assertSame(0, preg_match('/title="Perlu dicek:/', $markah),
            'Alasannya kembali disembunyikan di tooltip; di layar sentuh ia tidak akan terbaca.');

        $this->assertSame(0, preg_match('/class="ang-alasan"/', $markah),
            'Alasannya dirinci lagi di daftar; barisnya akan membengkak seperti semula.');
    }

}
