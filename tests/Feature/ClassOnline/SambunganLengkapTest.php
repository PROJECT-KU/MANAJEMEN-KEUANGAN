<?php

namespace Tests\Feature\ClassOnline;

use App\CategoriesOnlineTraining;
use App\PendaftaranOnlineTraining;
use App\Support\IsiOtomatisDariNomor;
use App\Support\PendaftaranSemuaLayanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Sambungan Class Online ke lapisan-lapisan BERSAMA.
 *
 * Audit 2 Nov 2026 menemukan enam tempat yang mencacah layanan satu per satu
 * dan tidak ikut menyebut layanan ini. Tidak satu pun melempar galat: tiap
 * daftar memakai `?? null`, `?? []`, atau `default =>`, jadi layanan yang
 * terlewat hanya DIAM. Gejalanya muncul berminggu-minggu kemudian sebagai
 * "kok tidak ada", bukan sebagai halaman galat.
 *
 * Berkas ini menjaga keenamnya sekaligus, dan menjaganya DENGAN
 * MEMBANDINGKAN terhadap Scopus Camp — bukan dengan menuliskan ulang
 * daftarnya. Layanan ini memang salinan Camp; yang dimiliki Camp pada jalur
 * bersama harus dimiliki layanan ini juga, dan pembandingnya ikut berubah
 * sendiri kalau Camp bertambah.
 */
class SambunganLengkapTest extends TestCase
{
    use DatabaseTransactions;

    private function angkatan(): CategoriesOnlineTraining
    {
        return CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Sambungan CO ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'lokasi' => 'Zoom',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 3500000, 'status' => 'active',
        ]);
    }

    private function pendaftaran(array $ganti = []): PendaftaranOnlineTraining
    {
        return PendaftaranOnlineTraining::create(array_merge([
            'token' => Str::random(30),
            'id_transaksi' => PendaftaranSemuaLayanan::nomorBaru('online_training'),
            'kategori_id' => $this->angkatan()->getKey(),
            'email' => 'sambung' . Str::lower(Str::random(10)) . '@contoh.test',
            'nama' => 'Peserta Sambungan',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Sambungan',
            'ppn' => '0', 'kode_unik' => '123',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '1875123',
            'status' => 'Pendaftaran Diterima', 'cara_bayar' => 'doku',
            'bayar_pada' => now(),
        ], $ganti));
    }

    // ------------------------------------------------- tagihan di gerbang

    #[Test]
    public function membatalkan_menutup_tagihannya_di_gerbang(): void
    {
        /*
         * Terukur TIDAK ADA saat layanan ini dibuka: membatalkan di layar
         * panitia tidak sampai ke DOKU sama sekali. Tagihannya hidup sampai
         * tenggatnya habis, dan pendaftar yang tabnya masih terbuka bisa
         * tetap membayar kursi yang sudah dikembalikan ke kuota — lalu
         * diberikan kepada orang lain. Uang masuk untuk kursi yang tidak ada,
         * dan tidak ada satu pun galat yang memberi tahu.
         */
        $peta = (new \ReflectionClass(\App\Actions\Pendaftaran\UbahStatusPendaftaran::class))
            ->getConstant('TIDAK_AKTIF');

        $this->assertArrayHasKey('online_training', $peta,
            'Pembatalan Class Online tidak menutup tagihan DOKU-nya.');

        // Kosakatanya harus sama dengan Camp: katalognya memang menyalinnya,
        // dan status yang tidak terdaftar di sini tidak menutup apa pun.
        $this->assertSame($peta['scopus_camp'], $peta['online_training']);
    }

    // --------------------------------------------------- isi otomatis

    #[Test]
    public function pendaftarnya_dikenali_pengisian_otomatis(): void
    {
        /*
         * Terukur tidak dikenali: orang yang nomornya HANYA pernah dipakai di
         * Class Online dijawab "tidak ketemu" di semua borang — termasuk
         * borang Class Online itu sendiri.
         */
        $p = $this->pendaftaran();

        $hasil = app(IsiOtomatisDariNomor::class)->cari($p->telp);

        $this->assertNotNull($hasil, 'Pendaftar Class Online tidak dikenali isi otomatis.');
        $this->assertSame($p->nama, $hasil['nama']);
        $this->assertSame($p->email, $hasil['email']);

        // Asal instansinya ikut: tabel ini PUNYA kolomnya, tidak seperti
        // Scopus Kafe yang memang tidak menyimpannya.
        $this->assertSame($p->affiliasi, $hasil['affiliasi']);
    }

    // ------------------------------------------------ riwayat pelanggan

    #[Test]
    public function pendaftarannya_muncul_di_riwayat_pesanan_pelanggan(): void
    {
        /*
         * Terlewat dengan sebab yang SAMA seperti Webinar Eksklusif dulu:
         * layanannya lahir sesudah daftar sumbernya dibuat. Akibatnya
         * pendaftaran seseorang tidak pernah muncul di halaman pelanggannya,
         * dan jumlah pesanannya terhitung kurang.
         */
        $tabel = collect(
            (new \ReflectionClass(\App\Support\PesananPelanggan::class))->getConstants()
        )->first(fn ($v) => is_array($v) && isset($v[0]['tabel']));

        $this->assertNotNull($tabel);
        $this->assertContains('online_training_pendaftaran', array_column($tabel, 'tabel'),
            'Riwayat Pesanan pelanggan tidak membaca tabel Class Online.');

        // Dan benar-benar terbaca, bukan cuma terdaftar.
        $p = $this->pendaftaran();

        $u = User::create([
            'full_name' => $p->nama,
            'username' => 'uji_sb_' . Str::random(8),
            'email' => $p->email,
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'telp' => $p->telp])->save();

        $pesanan = \App\Support\PesananPelanggan::untuk($u->refresh());

        $this->assertContains('Class Online', collect($pesanan)->pluck('layanan')->all());
    }

    // ------------------------------------------------------- beranda

    #[Test]
    public function kartu_jadwal_di_beranda_mengantar_ke_angkatannya(): void
    {
        /*
         * `tautanSesi()` memakai `match` dengan `default => url('/')`. Layanan
         * yang terlewat karena itu mengantar ke BERANDA — orang yang menekan
         * jadwal yang ia incar mendarat kembali di tempat ia menekannya,
         * tanpa galat apa pun.
         */
        $a = $this->angkatan();

        $m = (new \ReflectionClass(\App\Support\RingkasBeranda::class))->getMethod('tautanSesi');
        $m->setAccessible(true);

        $this->assertSame(
            route('public.onlinetraining.Selengkapnya', ['id' => $a->getKey()]),
            $m->invoke(null, $a)
        );
    }

    // -------------------------------------------- penyuntingan panitia

    #[Test]
    public function panitia_bisa_menyunting_datanya(): void
    {
        /*
         * Tanpa butirnya, medanUntuk() memulangkan daftar KOSONG — jadi salah
         * ketik nama atau email pada pendaftaran yang sudah dibayar hanya
         * bisa diperbaiki lewat basis data.
         */
        $peta = (new \ReflectionClass(\App\Actions\Pendaftaran\UbahDataPendaftaran::class))
            ->getConstant('MEDAN');

        $this->assertArrayHasKey('online_training', $peta,
            'Pendaftaran Class Online tidak bisa disunting panitia sama sekali.');

        // Tabelnya menyalin Scopus Camp kolom demi kolom, jadi medannya sama.
        $this->assertSame($peta['scopus_camp'], $peta['online_training']);
    }

    // ------------------------------------------------------- surat

    #[Test]
    public function setiap_status_bersurat_di_camp_bersurat_juga_di_sini(): void
    {
        /*
         * 'Pendaftaran Reschedule' terlewat: statusnya ADA di daftar status,
         * jadi panitia bisa memilihnya di layar rincian — tetapi tidak ada
         * kelas suratnya, jadi tidak ada satu pun kabar yang berangkat.
         * Pendaftarnya tahu jadwalnya berubah hanya kalau ia kebetulan
         * membuka halaman statusnya lagi.
         */
        $camp = PendaftaranSemuaLayanan::sumber('scopus_camp')['surat'] ?? [];
        $online = PendaftaranSemuaLayanan::sumber('online_training')['surat'] ?? [];

        $this->assertSame(array_keys($camp), array_keys($online),
            'Ada status yang bersurat di Class Offline tetapi diam di Class Online.');

        foreach ($online as $status => $kelas) {
            $this->assertTrue(class_exists($kelas),
                'Katalog menyebut kelas surat yang tidak ada: ' . $kelas . ' (' . $status . ')');
        }
    }

    // ------------------------------------------------------- akses

    #[Test]
    public function layar_angkatan_tertutup_untuk_yang_bukan_panitia(): void
    {
        /*
         * Grup rute `account/` hanya ber-middleware `web, auth,
         * terverifikasi` — TIDAK ADA pemeriksaan peran. Penjagaannya
         * diserahkan ke tiap pengendali, dan layar ini tidak punya satu pun.
         *
         * Terukur dengan akun pelanggan biasa (peran 'user'):
         *
         *     GET    .../kategori            -> 200
         *     GET    .../kategori/create     -> 200
         *     GET    .../kategori/edit/{id}  -> 200
         *     DELETE .../kategori/delete/{id} -> 200, angkatannya BENAR-BENAR HILANG
         *
         * Bilah sisi menyembunyikan menunya, tetapi alamatnya bisa ditebak
         * dan menyembunyikan tautan bukan penjagaan.
         */
        $u = User::create([
            'full_name' => 'Pelanggan Biasa',
            'username' => 'uji_pb_' . Str::random(8),
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();
        $u = $u->refresh();

        $this->assertFalse($u->adalahAdministrator());

        $a = $this->angkatan();

        foreach ([
            route('account.kategorionlinetraining.index'),
            route('account.kategorionlinetraining.create'),
            route('account.kategorionlinetraining.edit', $a->getKey()),
        ] as $alamat) {
            $this->actingAs($u)->get($alamat)->assertForbidden();
        }

        $this->actingAs($u)
            ->delete(route('account.kategorionlinetraining.destroy', $a->getKey()))
            ->assertForbidden();

        // Yang paling penting: angkatannya MASIH ADA.
        $this->assertTrue(CategoriesOnlineTraining::whereKey($a->getKey())->exists());
    }

    #[Test]
    public function layar_angkatan_camp_dan_bibliometrik_ikut_tertutup(): void
    {
        // Lubang yang sama persis, dan ditemukan lewat layanan ini. Dijaga di
        // sini supaya perbaikannya tidak hilang sendiri.
        $u = User::create([
            'full_name' => 'Pelanggan Biasa',
            'username' => 'uji_pb2_' . Str::random(8),
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        foreach ([
            'account.kategoriscopuscamp.index',
            'account.kategorianalisisbibliometrik.index',
        ] as $rute) {
            if (! \Illuminate\Support\Facades\Route::has($rute)) {
                continue;
            }

            $this->actingAs($u->refresh())->get(route($rute))->assertForbidden();
        }
    }
}
