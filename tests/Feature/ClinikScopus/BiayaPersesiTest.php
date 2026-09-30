<?php

namespace Tests\Feature\ClinikScopus;

use App\ClinikScopusBiayaPersesi;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tarif seluruh layanan jasa.
 *
 * Tabelnya menyimpan RIWAYAT tarif, bukan daftar pilihan: satu tarif berlaku
 * untuk satu pasangan layanan+varian pada satu waktu, dan baris lama
 * dipertahankan karena pesanan yang sudah terjadi memakai harga saat itu.
 *
 * Pemeriksaan selalu DISARINGI ke pasangannya, bukan ke seluruh tabel: basis
 * data ujinya basis data nyata yang sudah berisi tarif kelima layanan, jadi
 * hitungan tanpa saringan akan terpengaruh baris yang bukan urusan ujinya.
 */
class BiayaPersesiTest extends TestCase
{
    use DatabaseTransactions;

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_tar_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    private function tarif(
        int $nilai,
        ?int $ppn = null,
        string $status = ClinikScopusBiayaPersesi::NONAKTIF,
        string $layanan = 'clinik_scopus',
        ?string $varian = null,
        ?array $fasilitas = null
    ): ClinikScopusBiayaPersesi {
        return ClinikScopusBiayaPersesi::create([
            'layanan' => $layanan,
            'varian' => $varian,
            'biaya_persesi' => $nilai,
            'ppn' => $ppn,
            'fasilitas' => $fasilitas,
            'status' => $status,
        ]);
    }

    // ------------------------------------------------- hanya satu yang berlaku

    #[Test]
    public function memberlakukan_satu_tarif_menonaktifkan_yang_lain(): void
    {
        $lama = $this->tarif(100000, 11, ClinikScopusBiayaPersesi::AKTIF);
        $baru = $this->tarif(150000, 11);

        $baru->jadikanBerlaku();

        $this->assertSame(ClinikScopusBiayaPersesi::AKTIF, $baru->refresh()->status);
        $this->assertSame(ClinikScopusBiayaPersesi::NONAKTIF, $lama->refresh()->status);
        $this->assertSame(1, ClinikScopusBiayaPersesi::untuk('clinik_scopus')->aktif()->count());
    }

    #[Test]
    public function tarif_layanan_lain_tidak_ikut_dimatikan(): void
    {
        /*
         * Inti pemisahan per layanan. Dulu penonaktifannya menyapu SELURUH
         * tabel, jadi menyetel harga Scopus Camp akan membuat Clinik Scopus
         * kehilangan tarif berlaku — dan borang pemesanannya menampilkan harga
         * kosong tanpa ada yang tahu sebabnya.
         */
        $clinik = $this->tarif(125000, 11, ClinikScopusBiayaPersesi::AKTIF);
        $camp = $this->tarif(4500000, null, ClinikScopusBiayaPersesi::NONAKTIF, 'scopus_camp', 'jawa');

        $camp->jadikanBerlaku();

        $this->assertSame(ClinikScopusBiayaPersesi::AKTIF, $clinik->refresh()->status);
        $this->assertTrue(ClinikScopusBiayaPersesi::berlaku('scopus_camp', 'jawa')->is($camp));
    }

    #[Test]
    public function varian_lain_dari_layanan_yang_sama_tidak_ikut_dimatikan(): void
    {
        // Pulau Jawa dan luar Jawa dua harga yang berjalan berdampingan, bukan
        // dua versi dari satu harga.
        $jawa = $this->tarif(4500000, null, ClinikScopusBiayaPersesi::AKTIF, 'scopus_camp', 'jawa');
        $luar = $this->tarif(5500000, null, ClinikScopusBiayaPersesi::NONAKTIF, 'scopus_camp', 'luar_jawa');

        $luar->jadikanBerlaku();

        $this->assertSame(ClinikScopusBiayaPersesi::AKTIF, $jawa->refresh()->status);
        $this->assertSame(4500000, (int) ClinikScopusBiayaPersesi::berlaku('scopus_camp', 'jawa')->biaya_persesi);
        $this->assertSame(5500000, (int) ClinikScopusBiayaPersesi::berlaku('scopus_camp', 'luar_jawa')->biaya_persesi);
    }

    #[Test]
    public function tarif_tanpa_varian_tidak_terbaca_sebagai_varian_apa_pun(): void
    {
        /*
         * Layanan tanpa varian menyimpan NULL. whereNull dan where('varian','')
         * dua hal berbeda di MySQL, jadi kalau pencariannya tidak membedakan
         * keduanya, tarif Scopus Kafe kadang ketemu kadang tidak.
         */
        $kafe = $this->tarif(1000000, null, ClinikScopusBiayaPersesi::AKTIF, 'scopus_kafe');

        $this->assertTrue(ClinikScopusBiayaPersesi::berlaku('scopus_kafe')->is($kafe));
        $this->assertNull(ClinikScopusBiayaPersesi::berlaku('scopus_kafe', 'jawa'));
    }

    #[Test]
    public function berlaku_mengembalikan_yang_paling_belakangan_disetel(): void
    {
        /*
         * Jaring pengaman. Kalau entah bagaimana ada dua yang aktif — data
         * lama, impor, atau tulisan langsung ke basis data — yang dipakai
         * harus yang paling belakangan disetel, bukan sembarang seperti
         * first() tanpa urutan.
         */
        ClinikScopusBiayaPersesi::untuk('clinik_scopus')
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        $awal = $this->tarif(100000, 11, ClinikScopusBiayaPersesi::AKTIF);
        $awal->forceFill(['updated_at' => now()->subYear()])->save();

        $akhir = $this->tarif(175000, 11, ClinikScopusBiayaPersesi::AKTIF);

        $this->assertTrue(ClinikScopusBiayaPersesi::berlaku()->is($akhir));
    }

    // ------------------------------------------------------------- menyetel

    #[Test]
    public function tarif_baru_langsung_berlaku(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $lama = $this->tarif(100000, 11, ClinikScopusBiayaPersesi::AKTIF);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'layanan' => 'clinik_scopus',
                'biaya_persesi' => '175.000',
                'ppn' => 11,
            ])
            ->assertRedirect(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $berlaku = ClinikScopusBiayaPersesi::berlaku();

        $this->assertSame(175000, (int) $berlaku->biaya_persesi);
        $this->assertFalse($berlaku->is($lama), 'Seharusnya baris BARU, bukan yang lama ditimpa.');
        $this->assertSame(ClinikScopusBiayaPersesi::NONAKTIF, $lama->refresh()->status);
    }

    #[Test]
    public function tarif_bervarian_tersimpan_pada_variannya_sendiri(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $sebelum = (int) ClinikScopusBiayaPersesi::berlaku('bibliometrik', 'online')->biaya_persesi;

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'bibliometrik',
            'varian' => 'offline',
            'biaya_persesi' => '1.200.000',
        ]);

        $this->assertSame(1200000, (int) ClinikScopusBiayaPersesi::berlaku('bibliometrik', 'offline')->biaya_persesi);
        $this->assertSame(
            $sebelum,
            (int) ClinikScopusBiayaPersesi::berlaku('bibliometrik', 'online')->biaya_persesi,
            'Menyetel harga offline tidak boleh menyentuh harga online.'
        );
    }

    #[Test]
    public function tarif_diketik_berformat_rupiah_tetap_tersimpan_sebagai_angka(): void
    {
        // Isiannya dipoles jadi "175.000" saat diketik; yang tersimpan harus
        // bilangan, bukan untaian bertitik.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'clinik_scopus',
            'biaya_persesi' => 'Rp 1.250.000',
            'ppn' => null,
        ]);

        $this->assertSame(1250000, (int) ClinikScopusBiayaPersesi::berlaku()->biaya_persesi);
    }

    #[Test]
    public function memperbaiki_tarif_yang_berlaku_tidak_menambah_baris_riwayat(): void
    {
        /*
         * Membetulkan salah ketik tidak boleh meninggalkan jejak seolah
         * harganya pernah berubah — riwayat tarif dibaca orang untuk tahu
         * kapan harga naik.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $berlaku = $this->tarif(125000, 11, ClinikScopusBiayaPersesi::AKTIF);
        $sebelum = ClinikScopusBiayaPersesi::count();

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'clinik_scopus',
            'biaya_persesi' => '125.000',
            'ppn' => 12,
            'perbaiki' => $berlaku->getKey(),
        ]);

        $this->assertSame($sebelum, ClinikScopusBiayaPersesi::count());
        $this->assertSame(12, $berlaku->refresh()->ppn_persen);
    }

    #[Test]
    public function memperbaiki_tarif_milik_layanan_lain_ditolak_dan_jadi_baris_baru(): void
    {
        /*
         * Acuan perbaikan datang dari isian tersembunyi di peramban. Kalau
         * UUID-nya ditukar ke tarif layanan lain, memperbaikinya akan menimpa
         * harga layanan yang sama sekali tidak sedang disetel.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $milikKafe = $this->tarif(1000000, null, ClinikScopusBiayaPersesi::AKTIF, 'scopus_kafe');

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'clinik_scopus',
            'biaya_persesi' => '300.000',
            'perbaiki' => $milikKafe->getKey(),
        ]);

        $this->assertSame(1000000, (int) $milikKafe->refresh()->biaya_persesi);
        $this->assertSame(300000, (int) ClinikScopusBiayaPersesi::berlaku('clinik_scopus')->biaya_persesi);
    }

    #[Test]
    public function tarif_nol_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'layanan' => 'clinik_scopus',
                'biaya_persesi' => '0',
            ])
            ->assertSessionHasErrors('biaya_persesi');
    }

    #[Test]
    public function ppn_di_atas_seratus_persen_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'layanan' => 'clinik_scopus',
                'biaya_persesi' => '100.000',
                'ppn' => 150,
            ])
            ->assertSessionHasErrors('ppn');
    }

    #[Test]
    public function layanan_yang_tidak_ada_di_katalog_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $sebelum = ClinikScopusBiayaPersesi::count();

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'layanan' => 'layanan_karangan',
                'biaya_persesi' => '100.000',
            ])
            ->assertSessionHasErrors('layanan');

        $this->assertSame($sebelum, ClinikScopusBiayaPersesi::count());
    }

    #[Test]
    public function varian_yang_bukan_milik_layanannya_ditolak(): void
    {
        // Clinik Scopus tidak punya varian; tarif bervarian di sana tidak akan
        // pernah terbaca oleh pencari tarifnya.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'layanan' => 'clinik_scopus',
                'varian' => 'jawa',
                'biaya_persesi' => '100.000',
            ])
            ->assertSessionHasErrors('varian');
    }

    #[Test]
    public function layanan_bervarian_tanpa_varian_ditolak(): void
    {
        // Sebaliknya: Scopus Camp tanpa varian jadi tarif menggantung yang
        // tidak dipakai baik Jawa maupun luar Jawa.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'layanan' => 'scopus_camp',
                'biaya_persesi' => '4.500.000',
            ])
            ->assertSessionHasErrors('varian');
    }

    // ---------------------------------------------------------- fasilitas

    #[Test]
    public function fasilitas_dipisah_per_baris_bukan_per_koma(): void
    {
        /*
         * Fasilitasnya sendiri kerap memuat koma — "Konsumsi pagi, siang, dan
         * sore" adalah SATU fasilitas. Dipisah koma, ia pecah jadi tiga dan
         * daftarnya jadi tidak masuk akal.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'scopus_kafe',
            'biaya_persesi' => '1.000.000',
            'fasilitas' => "Konsumsi pagi, siang, dan sore\nSertifikat",
        ]);

        $this->assertSame(
            ['Konsumsi pagi, siang, dan sore', 'Sertifikat'],
            ClinikScopusBiayaPersesi::berlaku('scopus_kafe')->daftar_fasilitas
        );
    }

    #[Test]
    #[DataProvider('bentukDaftarFasilitas')]
    public function awalan_daftar_dan_baris_kosong_dibersihkan(string $diketik, array $harusnya): void
    {
        // Orang mengetik daftar dengan cara masing-masing; yang tersimpan tetap
        // teks fasilitasnya saja, supaya tampilannya tidak jadi "- - Sertifikat".
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'scopus_kafe',
            'biaya_persesi' => '1.000.000',
            'fasilitas' => $diketik,
        ]);

        $this->assertSame($harusnya, ClinikScopusBiayaPersesi::berlaku('scopus_kafe')->daftar_fasilitas);
    }

    public static function bentukDaftarFasilitas(): array
    {
        return [
            'tanda hubung' => ["- Sertifikat\n- Konsumsi", ['Sertifikat', 'Konsumsi']],
            'bulatan' => ["• Sertifikat\n• Konsumsi", ['Sertifikat', 'Konsumsi']],
            'bintang' => ["* Sertifikat", ['Sertifikat']],
            'baris kosong di tengah' => ["Sertifikat\n\n\nKonsumsi", ['Sertifikat', 'Konsumsi']],
            'spasi berlebih' => ["   Sertifikat   \n  Konsumsi", ['Sertifikat', 'Konsumsi']],
        ];
    }

    #[Test]
    public function fasilitas_kosong_tersimpan_sebagai_null_bukan_larik_kosong(): void
    {
        // Larik kosong dan NULL sama-sama "tidak ada fasilitas", tapi hanya satu
        // yang perlu ada di basis data. Satu bentuk berarti satu pemeriksaan.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'scopus_kafe',
            'biaya_persesi' => '1.000.000',
            'fasilitas' => "   \n  \n",
        ]);

        $tarif = ClinikScopusBiayaPersesi::berlaku('scopus_kafe');

        $this->assertNull($tarif->fasilitas);
        $this->assertSame([], $tarif->daftar_fasilitas);
    }

    // ------------------------------------------------------------------ hak

    #[Test]
    public function seluruh_layanan_muncul_walau_tarifnya_belum_disetel(): void
    {
        /*
         * Kartu disusun dari katalog, bukan dari isi tabel. Disusun dari isi
         * tabel, layanan yang tarifnya belum pernah disetel hilang sama sekali
         * dari layar — dan tidak ada yang tahu ia terlewat.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        ClinikScopusBiayaPersesi::untuk('online_training')
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $halaman->assertOk();

        foreach (ClinikScopusBiayaPersesi::LAYANAN as $tentang) {
            $halaman->assertSee($tentang['nama'], false);
        }

        $halaman->assertSee('Belum disetel');
    }

    #[Test]
    public function karyawan_boleh_melihat_tetapi_tidak_disuguhi_borang(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $this->tarif(125000, 11, ClinikScopusBiayaPersesi::AKTIF);

        $halaman = $this->actingAs($karyawan)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $halaman->assertOk();
        $halaman->assertSee('Hanya administrator yang boleh mengubah tarif.');
        $this->assertStringNotContainsString('type="submit"', $halaman->getContent());
    }

    #[Test]
    public function karyawan_tidak_boleh_menyetel_tarif(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $sebelum = ClinikScopusBiayaPersesi::count();

        $this->actingAs($karyawan)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'clinik_scopus',
            'biaya_persesi' => '999.000',
        ]);

        $this->assertSame($sebelum, ClinikScopusBiayaPersesi::count());
    }

    // ------------------------------------------------------------ penghapusan

    #[Test]
    public function tarif_yang_sedang_berlaku_tidak_bisa_dihapus(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $berlaku = $this->tarif(125000, 11, ClinikScopusBiayaPersesi::AKTIF);

        $this->actingAs($admin)
            ->deleteJson(route('account.Clinik-Scopus-Biaya-Persesi.destroy', $berlaku))
            ->assertStatus(409)
            ->assertJson(['success' => false]);

        $this->assertNotNull(ClinikScopusBiayaPersesi::find($berlaku->getKey()));
    }

    #[Test]
    public function tarif_yang_masih_jadi_acuan_sesi_tidak_bisa_dihapus(): void
    {
        /*
         * clinikscopus.biaya_persesi_id berkunci asing ON DELETE NO ACTION,
         * jadi tanpa pemeriksaan ini MySQL menolak dengan galat 1451 yang
         * hanya menyebut nama constraint-nya.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $tarif = $this->tarif(100000, 11);

        $sesi = DB::table('clinikscopus')->first();

        if (! $sesi) {
            $this->markTestSkipped('Tidak ada baris clinikscopus untuk diuji.');
        }

        DB::table('clinikscopus')->where('id', $sesi->id)->update(['biaya_persesi_id' => $tarif->getKey()]);

        $jawab = $this->actingAs($admin)
            ->deleteJson(route('account.Clinik-Scopus-Biaya-Persesi.destroy', $tarif));

        $jawab->assertStatus(409);
        $this->assertStringContainsString('sesi', $jawab->json('message'));
        $this->assertNotNull(ClinikScopusBiayaPersesi::find($tarif->getKey()));
    }

    #[Test]
    public function tarif_lama_yang_belum_dipakai_boleh_dihapus(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $tarif = $this->tarif(90000, 11);

        $this->actingAs($admin)
            ->deleteJson(route('account.Clinik-Scopus-Biaya-Persesi.destroy', $tarif))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(ClinikScopusBiayaPersesi::find($tarif->getKey()));
    }

    #[Test]
    public function memberlakukan_lagi_tarif_lama_lewat_layarnya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $sekarang = $this->tarif(200000, 11, ClinikScopusBiayaPersesi::AKTIF);
        $lama = $this->tarif(125000, 11);

        $this->actingAs($admin)
            ->postJson(route('account.Clinik-Scopus-Biaya-Persesi.berlakukan', $lama))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue(ClinikScopusBiayaPersesi::berlaku()->is($lama->refresh()));
        $this->assertSame(ClinikScopusBiayaPersesi::NONAKTIF, $sekarang->refresh()->status);
    }

    #[Test]
    public function karyawan_tidak_boleh_memberlakukan_atau_menghapus(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $tarif = $this->tarif(90000, 11);

        $this->actingAs($karyawan)
            ->postJson(route('account.Clinik-Scopus-Biaya-Persesi.berlakukan', $tarif))
            ->assertStatus(403);

        $this->actingAs($karyawan)
            ->deleteJson(route('account.Clinik-Scopus-Biaya-Persesi.destroy', $tarif))
            ->assertStatus(403);

        $this->assertSame(ClinikScopusBiayaPersesi::NONAKTIF, $tarif->refresh()->status);
    }

    // -------------------------------------------------------- pemakai tarifnya

    #[Test]
    public function ppn_publik_datang_dari_tarif_yang_berlaku_bukan_sembarang(): void
    {
        /*
         * cekPpn() dulu memanggil first() TANPA menyaring status. Begitu
         * tarifnya pernah diganti sekali saja, PPN yang ditagihkan ke
         * pelanggan bisa datang dari harga yang sudah tidak dipakai.
         */
        ClinikScopusBiayaPersesi::untuk('clinik_scopus')
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        $usang = $this->tarif(100000, 5);
        $usang->forceFill(['created_at' => now()->subYears(2), 'updated_at' => now()->subYears(2)])->save();

        $sekarang = $this->tarif(150000, 11);
        $sekarang->jadikanBerlaku();

        $this->assertSame(11, ClinikScopusBiayaPersesi::berlaku()->ppn_persen);
    }

    #[Test]
    public function ppn_kosong_berarti_tanpa_ppn(): void
    {
        // Kolomnya varchar dan boleh NULL; pada data yang ada nilainya memang
        // NULL, yang berarti "tidak dikenakan" — bukan nol yang disetel.
        $tarif = $this->tarif(125000, null, ClinikScopusBiayaPersesi::AKTIF);

        $this->assertSame(0, $tarif->ppn_persen);
    }

    #[Test]
    public function total_dibayar_menambahkan_ppn_ke_tarifnya(): void
    {
        // Angka inilah yang dilihat pelanggan; tarif tanpa PPN-nya bukan jumlah
        // yang benar-benar ditagihkan.
        $kena = $this->tarif(1000000, 11);
        $bebas = $this->tarif(1000000, null);

        $this->assertSame(1110000, $kena->total_dibayar);
        $this->assertSame(1000000, $bebas->total_dibayar);
    }

    #[Test]
    public function nama_layanan_dan_varian_terbaca_manusia(): void
    {
        $camp = $this->tarif(5500000, null, ClinikScopusBiayaPersesi::NONAKTIF, 'scopus_camp', 'luar_jawa');

        $this->assertSame('Scopus Camp', $camp->nama_layanan);
        $this->assertSame('Luar Pulau Jawa', $camp->nama_varian);
        $this->assertSame('per peserta', $camp->satuan);
    }

    #[Test]
    public function rute_memakai_uuid_bukan_nomor_berurut(): void
    {
        // Kunci utamanya UUID, jadi alamat satu tarif tidak bisa ditebak
        // dengan menambah satu seperti nomor berurut.
        $tarif = $this->tarif(125000, 11);
        $alamat = route('account.Clinik-Scopus-Biaya-Persesi.destroy', $tarif);

        $this->assertMatchesRegularExpression(
            '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $alamat
        );
    }
}
