<?php

namespace Tests\Feature\Kategori;

use App\AngkatanJejak;
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
 * Putaran audit ketiga layar Angkatan Layanan.
 *
 * Yang dijaga di sini semuanya ditemukan dengan MENGUKUR data yang ada, bukan
 * dibayangkan: peserta yang terhitung per baris padahal satu baris boleh
 * berisi rombongan, tujuh pasang nomor angkatan kembar, dan tidak adanya
 * apa pun yang menutup angkatan yang tanggalnya sudah lewat.
 */
class AngkatanAuditTigaTest extends TestCase
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
            'username' => 'uji_audit3_' . Str::random(8),
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
            'nama_ke' => (string) random_int(8000, 8999), 'mulai' => now()->addMonth(),
            'selesai' => now()->addMonth()->addDays(2), 'lokasi' => 'Kota Uji ' . Str::random(5),
            'total_kuota' => '20', 'sisa_kuota' => '20', 'status' => 'draft',
        ], $lain));
    }

    // ------------------------------------------------- peserta per orang

    #[Test]
    public function satu_pendaftaran_rombongan_dihitung_sebanyak_orangnya(): void
    {
        $a = $this->angkatan(['total_kuota' => '30', 'sisa_kuota' => '25']);

        DB::table('scopus_camp_pendaftaran')->insert([
            // Kuncinya char(36) tanpa nilai bawaan, jadi id harus diisi
            // sendiri; tanpa itu MySQL menolak dengan "Field 'id' doesn't
            // have a default value".
            'id' => (string) Str::uuid(),
            'kategori_id' => $a->getKey(),
            'jumlah_pendaftar' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        KategoriLayanan::lupakanPendaftar();

        $this->assertSame(5, $a->fresh()->jumlah_pendaftar, 'Lima orang, bukan satu baris.');
        $this->assertSame(1, $a->fresh()->jumlah_pendaftaran);
    }

    #[Test]
    public function kuota_tidak_boleh_disetel_di_bawah_jumlah_orang(): void
    {
        /*
         * Inilah akibat terburuk dari menghitung baris: penjaga kuota memakai
         * angka yang sama, jadi angkatan berisi 25 orang yang barisnya cuma 5
         * masih boleh diberi kuota 10.
         */
        $a = $this->angkatan(['total_kuota' => '30', 'sisa_kuota' => '25']);

        DB::table('scopus_camp_pendaftaran')->insert([
            // Kuncinya char(36) tanpa nilai bawaan, jadi id harus diisi
            // sendiri; tanpa itu MySQL menolak dengan "Field 'id' doesn't
            // have a default value".
            'id' => (string) Str::uuid(),
            'kategori_id' => $a->getKey(),
            'jumlah_pendaftar' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        KategoriLayanan::lupakanPendaftar();

        $this->actingAs($this->akun())
            ->post(route('account.kategori-layanan.update', $a), [
                'layanan' => $a->layanan, 'varian' => $a->varian,
                'nama' => $a->nama, 'nama_ke' => $a->nama_ke,
                'mulai' => $a->mulai, 'selesai' => $a->selesai, 'lokasi' => $a->lokasi,
                'total_kuota' => 10, 'sisa_kuota' => 0, 'status' => 'draft',
            ])
            ->assertSessionHasErrors('total_kuota');
    }

    #[Test]
    public function sisa_kuota_tidak_boleh_melebihi_yang_tersedia_setelah_peserta(): void
    {
        $a = $this->angkatan(['total_kuota' => '20', 'sisa_kuota' => '20']);

        DB::table('scopus_camp_pendaftaran')->insert([
            // Kuncinya char(36) tanpa nilai bawaan, jadi id harus diisi
            // sendiri; tanpa itu MySQL menolak dengan "Field 'id' doesn't
            // have a default value".
            'id' => (string) Str::uuid(),
            'kategori_id' => $a->getKey(),
            'jumlah_pendaftar' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        KategoriLayanan::lupakanPendaftar();

        // 20 total, 15 sudah terdaftar: sisanya paling banyak 5.
        $this->actingAs($this->akun())
            ->post(route('account.kategori-layanan.update', $a), [
                'layanan' => $a->layanan, 'varian' => $a->varian,
                'nama' => $a->nama, 'nama_ke' => $a->nama_ke,
                'mulai' => $a->mulai, 'selesai' => $a->selesai, 'lokasi' => $a->lokasi,
                'total_kuota' => 20, 'sisa_kuota' => 12, 'status' => 'draft',
            ])
            ->assertSessionHasErrors('sisa_kuota');
    }

    #[Test]
    public function layanan_tanpa_tabel_pendaftaran_dinyatakan_terang_terangan(): void
    {
        // Scopus Cafe dan Clinik Scopus boleh punya angkatan, tetapi sisa
        // kuotanya tidak akan pernah berkurang sendiri. "20 dari 20" di layar
        // terbaca seperti belum ada yang daftar, padahal memang tidak ada
        // tempat menyimpan pendaftarnya.
        $camp = $this->angkatan();

        $this->assertFalse($camp->belumPunyaPendaftaran());
        $this->assertArrayHasKey('scopus_camp', KategoriLayanan::TABEL_PENDAFTARAN);
        $this->assertArrayNotHasKey('scopus_cafe', KategoriLayanan::TABEL_PENDAFTARAN);

        $cafe = new KategoriLayanan(['layanan' => 'scopus_cafe']);
        $this->assertTrue($cafe->belumPunyaPendaftaran());
        $this->assertNull($cafe->sisa_kuota_seharusnya);
    }

    // ------------------------------------------------------ nomor kembar

    #[Test]
    public function nomor_angkatan_kembar_ditolak_saat_disimpan(): void
    {
        $lokasi = 'Kota Uji ' . Str::random(6);
        $this->angkatan(['nama_ke' => '501', 'lokasi' => $lokasi]);
        $lain = $this->angkatan(['nama_ke' => '502', 'lokasi' => $lokasi]);

        $this->actingAs($this->akun())
            ->post(route('account.kategori-layanan.update', $lain), [
                'layanan' => $lain->layanan, 'varian' => $lain->varian,
                'nama' => $lain->nama, 'nama_ke' => '501',
                'mulai' => $lain->mulai, 'selesai' => $lain->selesai, 'lokasi' => $lokasi,
                'total_kuota' => 20, 'sisa_kuota' => 20, 'status' => 'draft',
            ])
            ->assertSessionHasErrors('nama_ke');
    }

    #[Test]
    public function nomor_kembar_yang_terlanjur_ada_tetap_boleh_disunting(): void
    {
        /*
         * Tujuh pasang kembar sudah terlanjur ada. Memeriksa semuanya — bukan
         * hanya nomor yang BERUBAH — membuat ketujuh belas angkatan itu tidak
         * bisa disunting sama sekali, termasuk untuk memperbaiki nomornya.
         */
        $lokasi = 'Kota Uji ' . Str::random(6);
        $satu = $this->angkatan(['nama_ke' => '601', 'lokasi' => $lokasi]);
        $dua = $this->angkatan(['nama_ke' => '601', 'lokasi' => $lokasi]);

        $this->actingAs($this->akun())
            ->post(route('account.kategori-layanan.update', $dua), [
                'layanan' => $dua->layanan, 'varian' => $dua->varian,
                'nama' => 'Nama baru', 'nama_ke' => '601',
                'mulai' => $dua->mulai, 'selesai' => $dua->selesai, 'lokasi' => $lokasi,
                'total_kuota' => 20, 'sisa_kuota' => 20, 'status' => 'draft',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Nama baru', $dua->fresh()->nama);
        $this->assertTrue($satu->fresh()->nomor_ganda, 'Keduanya tetap ditandai perlu dicek.');
    }

    // ------------------------------------------- penutup otomatis & jejak

    #[Test]
    public function perintah_menutup_angkatan_yang_tanggalnya_lewat(): void
    {
        $lewat = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->subMonth(),
            'selesai' => now()->subMonth()->addDays(2),
        ]);

        $belum = $this->angkatan(['status' => 'active']);

        $this->artisan('angkatan:tutup-lewat')->assertSuccessful();

        $this->assertSame('non active', $lewat->fresh()->status);
        $this->assertSame('active', $belum->fresh()->status, 'Yang belum lewat tidak boleh disentuh.');
    }

    #[Test]
    public function angkatan_ditutup_pada_HARI_MULAINYA_bukan_menunggu_selesai(): void
    {
        /*
         * Inti perubahan 5 Okt 2026, dan uji lama TIDAK bisa membuktikannya:
         * ia memakai angkatan sebulan lalu, yang sudah lewat menurut patokan
         * lama MAUPUN baru. Hijau di kedua aturan berarti tidak membuktikan
         * apa-apa tentang perbedaannya.
         *
         * Yang dibedakan di sini angkatan yang SEDANG BERJALAN: mulai
         * kemarin, selesai minggu depan. Dengan patokan lama ia tetap
         * terpajang dan tetap menerima pendaftar sampai hari terakhir — orang
         * membayar untuk acara yang sudah separuh jalan.
         */
        $sedangBerjalan = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addWeek()->toDateString(),
        ]);

        $mulaiHariIni = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->toDateString(),
            'selesai' => now()->addDays(2)->toDateString(),
        ]);

        $besok = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->addDay()->toDateString(),
            'selesai' => now()->addDays(3)->toDateString(),
        ]);

        $this->artisan('angkatan:tutup-lewat')->assertSuccessful();

        $this->assertSame('non active', $sedangBerjalan->fresh()->status,
            'Angkatan yang sudah berjalan masih menerima pendaftar.');

        $this->assertSame('non active', $mulaiHariIni->fresh()->status,
            'Angkatan yang mulai HARI INI seharusnya sudah ditutup.');

        $this->assertSame('active', $besok->fresh()->status,
            'Angkatan yang baru mulai besok tidak boleh ikut ditutup.');
    }

    #[Test]
    public function lencana_di_layar_sepakat_dengan_perintah_penutupnya(): void
    {
        /*
         * Aturannya dipakai EMPAT tempat: lencana di daftar, dua saringan
         * "perlu dicek", dan perintah penutup harian. Sebelumnya masing-masing
         * menuliskannya sendiri.
         *
         * Kalau menyimpang, tidak ada yang terlihat rusak: perintahnya menutup
         * angkatan yang menurut layar admin baik-baik saja, atau sebaliknya
         * layar menyalahkan angkatan yang tidak akan pernah ditutup.
         */
        $sedangBerjalan = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addWeek()->toDateString(),
        ]);

        $besok = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->addDay()->toDateString(),
            'selesai' => now()->addDays(3)->toDateString(),
        ]);

        // Lencana (accessor PHP)
        $this->assertTrue($sedangBerjalan->sudah_lewat);
        $this->assertFalse($besok->sudah_lewat);

        // Saringan (kueri) — harus menunjuk angkatan yang SAMA
        $lewatKueri = KategoriLayanan::query()->perlu('aktif-lewat')->pluck('id')->all();

        $this->assertContains($sedangBerjalan->getKey(), $lewatKueri,
            'Saringan tidak menemukan angkatan yang lencananya menyalahkan.');

        $this->assertNotContains($besok->getKey(), $lewatKueri,
            'Saringan menyalahkan angkatan yang lencananya menyatakan baik-baik saja.');
    }

    #[Test]
    public function draf_tetap_memakai_patokan_tanggal_selesai(): void
    {
        /*
         * Yang berubah hanya angkatan AKTIF. Draf tidak terpajang di mana pun
         * dan tidak menerima pendaftar, jadi menutupnya lebih awal tidak
         * menyelamatkan apa pun — yang perlu diingatkan cuma bahwa ia
         * tertinggal, dan itu baru benar sesudah acaranya habis.
         */
        $drafBerjalan = $this->angkatan([
            'status' => 'draft',
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addWeek()->toDateString(),
        ]);

        $drafHabis = $this->angkatan([
            'status' => 'draft',
            'mulai' => now()->subMonth()->toDateString(),
            'selesai' => now()->subMonth()->addDays(2)->toDateString(),
        ]);

        $basi = KategoriLayanan::query()->perlu('draf-lewat')->pluck('id')->all();

        $this->assertNotContains($drafBerjalan->getKey(), $basi,
            'Draf yang acaranya belum selesai belum basi.');

        $this->assertContains($drafHabis->getKey(), $basi,
            'Draf yang acaranya sudah habis seharusnya ditandai basi.');

        $this->artisan('angkatan:tutup-lewat')->assertSuccessful();

        $this->assertSame('draft', $drafBerjalan->fresh()->status,
            'Perintah penutup tidak boleh menyentuh draf.');
    }

    #[Test]
    public function jalan_kering_tidak_mengubah_apa_pun(): void
    {
        $lewat = $this->angkatan([
            'status' => 'active',
            'mulai' => now()->subMonth(),
            'selesai' => now()->subMonth()->addDays(2),
        ]);

        $this->artisan('angkatan:tutup-lewat', ['--kering' => true])->assertSuccessful();

        $this->assertSame('active', $lewat->fresh()->status);
    }

    #[Test]
    public function perubahan_meninggalkan_jejak_beserta_penulisnya(): void
    {
        $admin = $this->akun();
        $a = $this->angkatan(['status' => 'draft']);

        $this->assertSame('dibuat', AngkatanJejak::where('kategori_id', $a->getKey())
            ->latest('created_at')->first()?->aksi);

        $this->actingAs($admin);
        $a->status = 'active';
        $a->save();

        $jejak = AngkatanJejak::where('kategori_id', $a->getKey())
            ->where('aksi', 'diubah')->latest('created_at')->first();

        $this->assertNotNull($jejak);
        $this->assertStringContainsString('status: draft → active', $jejak->ringkasan);
        $this->assertSame($admin->full_name, $jejak->oleh_nama);
    }

    #[Test]
    public function menyimpan_tanpa_perubahan_tidak_menambah_jejak(): void
    {
        $a = $this->angkatan();
        $sebelum = AngkatanJejak::where('kategori_id', $a->getKey())->count();

        $a->save();

        $this->assertSame($sebelum, AngkatanJejak::where('kategori_id', $a->getKey())->count());
    }

    // ----------------------------------------------------- hapus & pulih

    #[Test]
    public function angkatan_yang_dihapus_bisa_dikembalikan_dengan_id_yang_sama(): void
    {
        $admin = $this->akun();
        $a = $this->angkatan(['nama' => 'Angkatan Yang Hilang']);
        $id = $a->getKey();

        $jawab = $this->actingAs($admin)
            ->deleteJson(route('account.kategori-layanan.destroy', $a));

        $jawab->assertOk();
        $this->assertNull(KategoriLayanan::find($id));
        $this->assertNotNull($jawab->json('pulihkan'), 'Jalan kembalinya harus ikut dikirim.');

        $jejak = AngkatanJejak::where('kategori_id', $id)->where('aksi', 'dihapus')->first();
        $this->assertNotNull($jejak);

        $this->actingAs($admin)
            ->post(route('account.kategori-layanan.pulihkan', $jejak))
            ->assertRedirect();

        $pulih = KategoriLayanan::find($id);

        // Id yang SAMA: pendaftaran lama menunjuk id itu, dan memulihkannya
        // dengan id baru berarti pesertanya tetap kehilangan angkatannya.
        $this->assertNotNull($pulih);
        $this->assertSame('Angkatan Yang Hilang', $pulih->nama);
    }

    #[Test]
    public function hapus_massal_tetap_meninggalkan_jejak_tiap_barisnya(): void
    {
        /*
         * Penghapusan massal lewat satu query delete TIDAK membangkitkan kait
         * model, jadi dua ratus angkatan bisa hilang tanpa satu pun jejak.
         */
        $admin = $this->akun();
        $satu = $this->angkatan();
        $dua = $this->angkatan();

        $this->actingAs($admin)->postJson(route('account.kategori-layanan.massal-hapus'), [
            'id' => [$satu->getKey(), $dua->getKey()],
        ])->assertOk();

        foreach ([$satu, $dua] as $a) {
            $this->assertNull(KategoriLayanan::find($a->getKey()));
            $this->assertSame(1, AngkatanJejak::where('kategori_id', $a->getKey())
                ->where('aksi', 'dihapus')->count());
        }
    }

    #[Test]
    public function tidak_memulihkan_kalau_angkatannya_sudah_ada_lagi(): void
    {
        $admin = $this->akun();
        $a = $this->angkatan();
        $id = $a->getKey();

        $this->actingAs($admin)->deleteJson(route('account.kategori-layanan.destroy', $a));

        $jejak = AngkatanJejak::where('kategori_id', $id)->where('aksi', 'dihapus')->first();

        $this->actingAs($admin)->post(route('account.kategori-layanan.pulihkan', $jejak));

        // Menekan "Urungkan" dua kali tidak boleh melempar galat kunci ganda.
        $this->actingAs($admin)
            ->post(route('account.kategori-layanan.pulihkan', $jejak))
            ->assertSessionHas('error');

        $this->assertSame(1, KategoriLayanan::whereKey($id)->count());
    }

    // ---------------------------------------------------- saringan waktu

    #[Test]
    public function saringan_periode_mempersempit_daftarnya(): void
    {
        $bulanDepan = $this->angkatan([
            'mulai' => now()->addMonthNoOverflow()->startOfMonth()->addDays(3),
            'selesai' => now()->addMonthNoOverflow()->startOfMonth()->addDays(5),
        ]);

        $tahunDepan = $this->angkatan([
            'mulai' => now()->addYear(),
            'selesai' => now()->addYear()->addDays(2),
        ]);

        $ada = KategoriLayanan::query()->periode('bulan-depan')->pluck('id')->all();

        $this->assertContains($bulanDepan->getKey(), $ada);
        $this->assertNotContains($tahunDepan->getKey(), $ada);
    }

    #[Test]
    public function periode_yang_tidak_dikenal_diabaikan(): void
    {
        // Nilainya datang dari alamat; yang asing tidak boleh menyaring
        // apa pun maupun melempar galat.
        $semua = KategoriLayanan::count();

        $this->assertSame($semua, KategoriLayanan::query()->periode('apa-saja')->count());
        $this->assertSame($semua, KategoriLayanan::query()->periode(null)->count());
    }

    #[Test]
    public function unduhan_memakai_daftar_kolom_pengurut_yang_sama_dengan_layar(): void
    {
        /*
         * Keduanya pernah punya daftarnya masing-masing, dan daftar unduhan
         * ketinggalan dua kolom — mengurutkan menurut Biaya lalu menekan Unduh
         * menghasilkan berkas yang urutannya diam-diam kembali ke tanggal.
         */
        $kelas = new \ReflectionClass(\App\Http\Controllers\account\KategoriLayananController::class);
        $daftar = $kelas->getConstant('BOLEH_URUT');

        foreach (['mulai', 'nama', 'sisa_kuota', 'status', 'layanan', 'biaya'] as $kolom) {
            $this->assertArrayHasKey($kolom, $daftar);
        }

        $isi = file_get_contents($kelas->getFileName());

        $this->assertSame(0, substr_count($isi, "\$bolehUrut = ["),
            'Daftar kolom pengurut tidak boleh ditulis ulang di dalam metode.');
    }
}
