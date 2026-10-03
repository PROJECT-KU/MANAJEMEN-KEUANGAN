<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\AnalisisBibliometrik;
use App\ClinikScopusPemesanan;
use App\ClinikScopusTestimoni;
use App\KategoriLayanan;
use App\Mail\AnalisisBibliometrikUpdateDiterimaMail;
use App\Mail\ScopusCampUpdateDiterimaMail;
use App\Mail\ScopusCampUpdateResheduleMail;
use App\Mail\UpdatePublicPendaftaranScopusKafeMail;
use App\PendaftaranScopusCamp;
use App\PendaftaranScopusKafe;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tindakan di layar Pendaftar Layanan: rincian, ubah data, ubah status, hapus.
 *
 * Seluruhnya dipindahkan dari lima layar pendaftaran per layanan yang dibuang.
 * Yang dijaga di sini terutama hal-hal yang TIDAK terlihat saat hilang:
 * email pemberitahuan yang berhenti terkirim, kuota yang tidak dikembalikan,
 * berkas bukti yang tertinggal di cakram, dan testimoni yang jadi yatim.
 */
class TindakanPendaftaranTest extends TestCase
{
    use DatabaseTransactions;

    /** @var array<int, string> berkas yang dibuat uji ini di cakram */
    private array $berkasUji = [];

    protected function setUp(): void
    {
        parent::setUp();

        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();
    }

    protected function tearDown(): void
    {
        /*
         * DatabaseTransactions TIDAK melindungi cakram.
         *
         * Uji di bawah membuat berkas bukti palsu untuk membuktikan
         * penghapusannya membuang berkasnya; kalau ujinya gagal di tengah,
         * berkas itu tertinggal di public/. Dibuang di sini, bukan di akhir
         * ujinya.
         */
        foreach ($this->berkasUji as $berkas) {
            if (is_file($berkas)) {
                @unlink($berkas);
            }
        }

        parent::tearDown();
    }

    // ------------------------------------------------------------- pembantu

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_tp_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'peran' => $peran,
        ])->save();

        return $u->refresh();
    }

    /** Satu angkatan baru milik layanan tertentu, dengan kuota yang diketahui. */
    private function angkatan(string $layanan, int $total = 50, ?int $sisa = null): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => $layanan,
            'nama' => 'Angkatan Uji ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => (string) $total,
            'sisa_kuota' => (string) ($sisa ?? $total),
            'status' => 'active',
        ]);
    }

    private function sesiClinik(): string
    {
        $id = DB::table('clinikscopus')->value('id');
        $this->assertNotNull($id, 'Basis data uji tidak punya sesi Clinik Scopus.');

        return (string) $id;
    }

    /** Satu baris contoh per layanan, beserta angkatannya kalau ada. */
    private function buat(string $layanan, array $tambahan = []): array
    {
        $tanda = Str::random(8);

        if (in_array($layanan, ['scopus_camp', 'bibliometrik'], true)) {
            $angkatan = $this->angkatan($layanan);
            $kelas = $layanan === 'scopus_camp' ? PendaftaranScopusCamp::class : AnalisisBibliometrik::class;

            return [$kelas::create(array_merge([
                'id_transaksi' => 'T-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => 'Peserta ' . $tanda,
                'email' => $tanda . '@contoh.test',
                'telp' => '0811-0000-0001',
                'affiliasi' => 'Instansi ' . $tanda,
                'jumlah_pendaftar' => '2',
                'total_pembayaran' => '1000000',
                'status' => 'diproses',
            ], $tambahan)), $angkatan];
        }

        if ($layanan === 'webinar_eksklusif') {
            $angkatan = $this->angkatan($layanan);

            return [WebinarEksklusifPendaftaran::create(array_merge([
                'id_transaksi' => 'WE-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => 'Peserta ' . $tanda,
                'email' => $tanda . '@contoh.test',
                'telp' => '62811000002',
                'jumlah_pendaftar' => 3,
                'total_pembayaran' => '400000',
                'status' => 'pending',
            ], $tambahan)), $angkatan];
        }

        if ($layanan === 'scopus_kafe') {
            return [PendaftaranScopusKafe::create(array_merge([
                'id_pemesanan' => 'K-' . substr($tanda, 0, 6),
                'nama' => 'Pemesan ' . $tanda,
                'email' => $tanda . '@contoh.test',
                'telp' => '0811-0000-0003',
                'sesi' => 'sesi 1',
                'total_keseluruhan_pembayaran' => '250000',
                'status' => 'menunggu verifikasi',
            ], $tambahan)), null];
        }

        $orang = $this->akun(User::PERAN_KARYAWAN);

        return [ClinikScopusPemesanan::create(array_merge([
            'clinikscopus_id' => $this->sesiClinik(),
            'trainer_id' => $orang->id,
            'customer_id' => $orang->id,
            'id_transaksi' => 'C-' . $tanda,
            'kode_booking' => 'BOOK-' . $tanda,
            'nama_pemesan' => 'Pemesan ' . $tanda,
            'email_pemesan' => $tanda . '@contoh.test',
            'telp_pemesan' => '0811-0000-0004',
            'sesi' => 'Sesi 1',
            'jam_sesi' => '09.00 - 10.00 WIB',
            'total_pembayaran' => 99000,
            'status' => 'pending',
        ], $tambahan)), null];
    }

    // -------------------------------------------------------------- rincian

    #[Test]
    public function rincian_terbuka_untuk_kelima_layanan(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            [$baris] = $this->buat($layanan);

            $halaman = $this->actingAs($orang)
                ->get(route('account.pendaftaran-layanan.rincian', [$layanan, $baris->getKey()]));

            $halaman->assertOk();
            $halaman->assertSee('Rincian Pendaftaran');
            // Nama orangnya memang tampil, jadi halamannya benar-benar
            // membaca barisnya dan bukan cuma kerangkanya.
            $halaman->assertSee($baris->nama ?? $baris->nama_pemesan);

            $this->flushSession();
        }
    }

    #[Test]
    public function layanan_yang_tidak_dikenali_dan_id_asing_jadi_404(): void
    {
        /*
         * {layanan} datang dari alamat halaman dan menentukan MODEL mana yang
         * dipanggil. Tanpa pencocokan ke katalog tertutup, nilai mentah dari
         * alamat bisa menunjuk kelas apa pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['layanan-karangan', 'abc']))
            ->assertNotFound();

        $this->flushSession();

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', (string) Str::uuid()]))
            ->assertNotFound();
    }

    #[Test]
    public function pelanggan_tidak_boleh_menyentuh_satu_pun_tindakannya(): void
    {
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        [$baris, $angkatan] = $this->buat('scopus_camp');
        $kunci = [$baris->getKey()];
        $tujuan = route('account.dashboard.index');

        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', ...$kunci]))
            ->assertRedirect($tujuan);
        $this->flushSession();

        $this->actingAs($pelanggan)
            ->put(route('account.pendaftaran-layanan.ubah', ['scopus_camp', ...$kunci]), [
                'nama' => 'Diubah Paksa', 'email' => 'x@contoh.test', 'telp' => '0811',
                'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 1,
            ])
            ->assertRedirect($tujuan);
        $this->flushSession();

        $this->actingAs($pelanggan)
            ->post(route('account.pendaftaran-layanan.status', ['scopus_camp', ...$kunci]), [
                'status' => 'Pendaftaran Diterima',
            ])
            ->assertRedirect($tujuan);
        $this->flushSession();

        $this->actingAs($pelanggan)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', ...$kunci]))
            ->assertRedirect($tujuan);

        // Dan datanya memang tidak tersentuh.
        $baris->refresh();
        $this->assertSame('diproses', $baris->status);
        $this->assertStringStartsWith('Peserta ', $baris->nama);
    }

    // ---------------------------------------------------------- ubah status

    #[Test]
    public function status_diterima_mengirim_email_ke_pendaftarnya(): void
    {
        /*
         * Inilah yang paling mudah hilang saat menyatukan layar: mengubah
         * status di layar lama MENGIRIM EMAIL. Dihilangkan, pelanggan berhenti
         * diberi tahu dan tidak ada galat apa pun yang memberitahukannya.
         */
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.status', ['scopus_camp', $camp->getKey()]), [
                'status' => 'Pendaftaran Diterima',
            ])
            ->assertRedirect();

        Mail::assertSent(ScopusCampUpdateDiterimaMail::class, 1);
        $this->assertSame('Pendaftaran Diterima', $camp->refresh()->status);
    }

    #[Test]
    public function tiap_layanan_mengirim_surat_yang_memang_miliknya(): void
    {
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $harapan = [
            ['scopus_camp', 'Pendaftaran Reschedule', ScopusCampUpdateResheduleMail::class],
            ['bibliometrik', 'Pendaftaran Diterima', AnalisisBibliometrikUpdateDiterimaMail::class],
            ['scopus_kafe', 'pembayaran diterima', UpdatePublicPendaftaranScopusKafeMail::class],
        ];

        foreach ($harapan as [$layanan, $status, $surat]) {
            [$baris] = $this->buat($layanan);

            $this->actingAs($orang)
                ->post(route('account.pendaftaran-layanan.status', [$layanan, $baris->getKey()]), [
                    'status' => $status,
                ])
                ->assertRedirect();

            Mail::assertSent($surat, 1);

            $this->flushSession();
        }
    }

    #[Test]
    public function webinar_dan_clinik_tidak_mengirim_surat_apa_pun(): void
    {
        // Bukan kelalaian: pemberitahuan lunas webinar sudah dikirim jalur
        // pendaftarannya sendiri, dan Clinik Scopus memang tidak pernah
        // mengirim surat dari layar panitia.
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        foreach ([['webinar_eksklusif', 'paid'], ['clinik_scopus', 'completed']] as [$layanan, $status]) {
            [$baris] = $this->buat($layanan);

            $this->actingAs($orang)
                ->post(route('account.pendaftaran-layanan.status', [$layanan, $baris->getKey()]), [
                    'status' => $status,
                ])
                ->assertRedirect();

            $this->assertSame($status, $baris->refresh()->status);
            $this->flushSession();
        }

        Mail::assertNothingSent();
    }

    #[Test]
    public function status_yang_tidak_berlaku_untuk_layanannya_ditolak(): void
    {
        // Kosakata statusnya berbeda di tiap layanan; 'pembayaran diterima'
        // milik Scopus Kafe tidak berarti apa pun di Scopus Camp, dan
        // menyimpannya membuat barisnya jatuh ke keadaan yang salah.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.status', ['scopus_camp', $camp->getKey()]), [
                'status' => 'pembayaran diterima',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('diproses', $camp->refresh()->status);
    }

    #[Test]
    public function kursi_webinar_dikembalikan_lalu_diambil_lagi(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$webinar, $angkatan] = $this->buat('webinar_eksklusif');

        // Kursinya sudah dipotong saat mendaftar; di uji ini barisnya dibuat
        // langsung, jadi sisanya disetel dulu seperti sesudah pendaftaran.
        $angkatan->forceFill(['sisa_kuota' => (string) (50 - 3)])->save();

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $webinar->getKey()]),
            ['status' => 'cancel']
        )->assertRedirect();

        $this->assertSame(50, (int) $angkatan->refresh()->sisa_kuota,
            'Dibatalkan, ketiga kursinya harus kembali.');

        $this->flushSession();

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $webinar->getKey()]),
            ['status' => 'paid']
        )->assertRedirect();

        $this->assertSame(47, (int) $angkatan->refresh()->sisa_kuota,
            'Diaktifkan kembali, ketiga kursinya harus diambil lagi.');
    }

    #[Test]
    public function status_scopus_camp_tidak_menggeser_kuota(): void
    {
        /*
         * Disengaja, dan dijaga supaya tidak "diperbaiki" tanpa sadar:
         * keempat layanan selain Webinar memang tidak pernah memindahkan
         * kuota saat statusnya berubah. Menyeragamkannya akan menggeser angka
         * sisa_kuota pada 48 angkatan yang sudah ada — perubahan yang tidak
         * diminta dan tidak bisa dibedakan dari kekeliruan nanti. Kuotanya
         * tetap berpindah saat barisnya DIHAPUS.
         */
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');
        $sisaAwal = (int) $angkatan->sisa_kuota;

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.status', ['scopus_camp', $camp->getKey()]),
            ['status' => 'Pendaftaran Dibatalkan']
        )->assertRedirect();

        $this->assertSame($sisaAwal, (int) $angkatan->refresh()->sisa_kuota);
    }

    // ------------------------------------------------------------ ubah data

    #[Test]
    public function data_diri_bisa_dibetulkan(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => 'Nama Yang Sudah Dibetulkan',
                'email' => 'betul@contoh.test',
                'telp' => '0812-3456-7890',
                'affiliasi' => 'Universitas Contoh',
                'kategori_id' => $angkatan->id,
                'jumlah_pendaftar' => 2,
                'note' => 'Dibetulkan lewat uji.',
            ]
        )->assertRedirect(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]));

        $camp->refresh();
        $this->assertSame('Nama Yang Sudah Dibetulkan', $camp->nama);
        $this->assertSame('betul@contoh.test', $camp->email);
        $this->assertSame('Universitas Contoh', $camp->affiliasi);
    }

    #[Test]
    public function status_tidak_ikut_berubah_saat_data_disunting(): void
    {
        // Memindahkan status mengirim email dan menggeser kuota; keduanya
        // tidak boleh terjadi hanya karena seseorang membetulkan ejaan nama.
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => 'Ejaan Dibetulkan',
                'email' => $camp->email,
                'telp' => $camp->telp,
                'kategori_id' => $angkatan->id,
                'jumlah_pendaftar' => 2,
                // Dikirim sengaja, dan HARUS diabaikan.
                'status' => 'Pendaftaran Diterima',
            ]
        )->assertRedirect();

        $this->assertSame('diproses', $camp->refresh()->status);
        Mail::assertNothingSent();
    }

    #[Test]
    public function email_yang_bentuknya_tidak_sah_ditolak(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');
        $emailAsli = $camp->email;

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => 'Tetap', 'email' => 'bukan-email', 'telp' => '0811',
                'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 1,
            ]
        )->assertSessionHasErrors('email');

        $this->assertSame($emailAsli, $camp->refresh()->email);
    }

    #[Test]
    public function pindah_angkatan_mengembalikan_kuota_lama_dan_memotong_yang_baru(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $lama] = $this->buat('scopus_camp');
        $baru = $this->angkatan('scopus_camp', 30, 30);

        // Seperti sesudah pendaftaran: dua kursi sudah terpakai di angkatan lama.
        $lama->forceFill(['sisa_kuota' => (string) (50 - 2)])->save();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => $camp->nama, 'email' => $camp->email, 'telp' => $camp->telp,
                'kategori_id' => $baru->id, 'jumlah_pendaftar' => 2,
            ]
        )->assertRedirect();

        $this->assertSame($baru->id, $camp->refresh()->kategori_id);
        $this->assertSame(50, (int) $lama->refresh()->sisa_kuota, 'Kursi di angkatan lama harus kembali.');
        $this->assertSame(28, (int) $baru->refresh()->sisa_kuota, 'Kursi di angkatan baru harus terpotong.');
    }

    #[Test]
    public function jumlah_yang_melebihi_sisa_kuota_ditolak(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        // Sisa tiga kursi, dan dua di antaranya sudah dipakai baris ini.
        $angkatan->forceFill(['total_kuota' => '50', 'sisa_kuota' => '3'])->save();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => $camp->nama, 'email' => $camp->email, 'telp' => $camp->telp,
                'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 9,
            ]
        );

        $this->assertSame(2, (int) $camp->refresh()->jumlah_pendaftar,
            'Jumlahnya tidak boleh tersimpan kalau melebihi sisa kuota.');
        $this->assertSame(3, (int) $angkatan->refresh()->sisa_kuota,
            'Kuotanya tidak boleh bergeser saat kirimannya ditolak.');
    }

    #[Test]
    public function jumlah_sama_dengan_sisa_plus_miliknya_sendiri_tetap_diterima(): void
    {
        /*
         * Jebakan yang mudah salah: sisa kuota angkatan BARU harus dihitung
         * dengan menambahkan kembali jumlah pendaftar lama bila angkatannya
         * tidak berpindah. Tanpa itu, menyunting pendaftaran berisi 2 orang
         * tanpa mengubah jumlahnya pun akan ditolak, sebab 2 dianggap
         * tambahan baru di atas sisa yang sudah dikurangi 2.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        $angkatan->forceFill(['total_kuota' => '50', 'sisa_kuota' => '1'])->save();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => $camp->nama, 'email' => $camp->email, 'telp' => $camp->telp,
                // 1 sisa + 2 miliknya sendiri = 3 kursi yang boleh dipakai.
                'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 3,
            ]
        )->assertRedirect();

        $this->assertSame(3, (int) $camp->refresh()->jumlah_pendaftar);
        $this->assertSame(0, (int) $angkatan->refresh()->sisa_kuota);
    }

    #[Test]
    public function nominal_diterima_dengan_pemisah_ribuan_apa_pun(): void
    {
        /*
         * Pengendali lama membuang TITIK untuk Scopus Camp dan KOMA untuk
         * Scopus Kafe. Jadi "4.275.028" yang diketik di layar Kafe dulu
         * tersimpan sebagai nol — tanpa galat, dan total bayarnya hilang.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        foreach (['4.275.028', '4,275,028', '4 275 028', 'Rp 4.275.028'] as $ditulis) {
            $this->actingAs($orang)->put(
                route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
                [
                    'nama' => $camp->nama, 'email' => $camp->email, 'telp' => $camp->telp,
                    'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 2,
                    'total_pembayaran' => $ditulis,
                ]
            )->assertRedirect();

            $this->assertSame(4275028, (int) $camp->refresh()->total_pembayaran,
                'Nominal "' . $ditulis . '" tidak terbaca benar.');

            $this->flushSession();
        }
    }

    // ---------------------------------------------------------------- hapus

    #[Test]
    public function hanya_administrator_yang_boleh_menghapus(): void
    {
        /*
         * Aturan terketat di antara kelima layar lama, disengaja dipakai
         * untuk semuanya: penghapusannya tidak bisa diurungkan dan belum ada
         * tong sampah. Tiga layar lama tidak menuntut apa pun.
         */
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($karyawan)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $camp->getKey()]))
            ->assertRedirect();

        $this->assertNotNull(PendaftaranScopusCamp::find($camp->getKey()),
            'Karyawan tidak boleh berhasil menghapus.');

        $this->flushSession();

        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $camp->getKey()]))
            ->assertRedirect(route('account.pendaftaran-layanan.index'));

        $this->assertNull(PendaftaranScopusCamp::find($camp->getKey()));
    }

    #[Test]
    public function hapus_mengembalikan_kuota_dan_membuang_berkas_buktinya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        // Berkas bukti palsu, bernama khas supaya tidak mungkin bertabrakan
        // dengan bukti sungguhan milik pendaftar.
        $nama = 'uji-hapus-' . Str::random(10) . '.jpg';
        $berkas = public_path('ScopusCamp/' . $nama);
        $this->berkasUji[] = $berkas;

        @mkdir(dirname($berkas), 0775, true);
        file_put_contents($berkas, 'bukan gambar sungguhan');
        $this->assertFileExists($berkas);

        [$camp, $angkatan] = $this->buat('scopus_camp', ['gambar' => 'ScopusCamp/' . $nama]);
        $angkatan->forceFill(['sisa_kuota' => (string) (50 - 2)])->save();

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $camp->getKey()]))
            ->assertRedirect();

        $this->assertNull(PendaftaranScopusCamp::find($camp->getKey()));
        $this->assertSame(50, (int) $angkatan->refresh()->sisa_kuota, 'Kuotanya harus kembali.');
        $this->assertFileDoesNotExist($berkas, 'Berkas buktinya harus ikut terbuang.');
    }

    #[Test]
    public function hapus_pemesanan_clinik_ikut_membuang_testimoninya(): void
    {
        /*
         * Testimoni menunjuk pemesanannya TANPA kunci asing, jadi basis
         * datanya tidak akan menolak maupun membersihkannya sendiri — ia akan
         * tinggal sebagai baris yatim, dan halaman publik galat saat merujuk
         * pemesanan yang sudah tidak ada.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        [$clinik] = $this->buat('clinik_scopus');

        // Empat kolom NOT NULL tanpa nilai bawaan; barisnya tidak bisa
        // dibuat tanpa keempatnya.
        $testimoni = ClinikScopusTestimoni::create([
            'clinikscopus_id' => $clinik->clinikscopus_id,
            'clinikscopus_pemesanan_id' => $clinik->getKey(),
            'trainer_id' => $clinik->trainer_id,
            'customer_id' => $clinik->customer_id,
            'rating' => 5,
            'deskripsi' => 'Testimoni uji ' . Str::random(6),
        ]);

        $this->assertNotNull(ClinikScopusTestimoni::find($testimoni->getKey()));

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['clinik_scopus', $clinik->getKey()]))
            ->assertRedirect();

        $this->assertNull(ClinikScopusPemesanan::find($clinik->getKey()));
        $this->assertNull(ClinikScopusTestimoni::find($testimoni->getKey()),
            'Testimoninya harus ikut terhapus, bukan tinggal yatim.');
    }

    // ----------------------------------------------------------- angkatan

    #[Test]
    public function nama_angkatan_tidak_mengulang_nama_layanannya(): void
    {
        /*
         * Terukur: 56 dari 59 angkatan namanya memuat nama layanannya
         * sendiri, jadi tiap baris daftar menulis hal yang sama dua kali —
         * kolom Layanan berbunyi "Scopus Camp" dan kolom Sesi "Scopus Camp
         * Jakarta". Yang ingin dibaca cuma satu kata.
         */
        $this->assertSame('Jakarta', Pendaftaran::namaRingkas('scopus_camp', 'Scopus Camp Jakarta'));
        $this->assertSame('Batch 2', Pendaftaran::namaRingkas('webinar_eksklusif', 'Webinar Eksklusif - Batch 2'));

        // Kalau sesudah dipangkas tidak tersisa apa-apa, nama penuhnya
        // dikembalikan: sel kosong lebih buruk daripada sel yang mengulang.
        $this->assertSame('Analisis Bibliometrik',
            Pendaftaran::namaRingkas('bibliometrik', 'Analisis Bibliometrik'));

        // Dipangkas dari DEPAN saja; kata yang berada di tengah bukan awalan
        // yang mubazir.
        $this->assertSame('Kelas Scopus Camp lanjutan',
            Pendaftaran::namaRingkas('scopus_camp', 'Kelas Scopus Camp lanjutan'));
    }

    #[Test]
    public function daftar_memajang_nama_ringkas_dan_menyimpan_yang_penuh(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Kota ' . $tanda,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'RINGKAS-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Ringkas ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0021',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda]));

        $halaman->assertOk();
        // Yang terbaca di kolomnya nama ringkasnya...
        $halaman->assertSee('Kota ' . $tanda);
        // ...dan nama penuhnya tetap ada, di atribut title.
        $halaman->assertSee('title="Scopus Camp Kota ' . $tanda . '"', false);
    }

    #[Test]
    public function angkatan_yang_kursinya_habis_ditandai_penuh(): void
    {
        /*
         * Tanpa penanda ini, daftar pendaftar tidak memberi tahu apakah
         * angkatannya masih bisa menerima orang — dan itu baru ketahuan saat
         * pendaftaran berikutnya ditolak.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Penuh ' . $tanda,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '10',
            'sisa_kuota' => '0',
            'status' => 'active',
        ]);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'PENUH-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Penuh ' . $tanda,
            'email' => 'p' . $tanda . '@contoh.test',
            'telp' => '0811-0000-0022',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda]))
            ->assertOk()
            ->assertSee('pdl-penuh', false)
            ->assertSee('penuh');
    }

    #[Test]
    public function nomor_angkatan_tampil_di_daftar_dan_ikut_kedua_berkas(): void
    {
        /*
         * "Batch ke berapa" — yang dipakai admin merekap.
         *
         * Nama tempat saja TIDAK menunjuk satu angkatan: terukur, Scopus Camp
         * Yogyakarta sudah angkatan ke-202 sementara Jakarta baru ke-9. Rekap
         * yang menyebut "Scopus Camp Yogyakarta" tanpa nomornya menggabungkan
         * dua ratus angkatan jadi satu baris.
         *
         * Diperiksa di KETIGA tempat sekaligus — layar, PDF, dan lembar
         * kerja — sebab yang diunduh untuk direkap justru dua yang terakhir,
         * dan nomor yang hanya ada di layar tidak menolong siapa pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Kota ' . $tanda,
            'nama_ke' => '202',
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        $p = PendaftaranScopusCamp::create([
            'id_transaksi' => 'NOMOR-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Nomor ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0031',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        // 1. Di layar daftar.
        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda]))
            ->assertOk()
            ->assertSee('#202')
            ->assertSee('Angkatan ke-202');

        $baris = Pendaftaran::kueri()->where('id', $p->getKey())->first();

        // 2. Di PDF — lewat sebutan yang dipakai templatnya.
        $this->assertSame(
            'Scopus Camp Kota ' . $tanda . ' — angkatan ke-202',
            Pendaftaran::sesiUntukBerkas($baris)
        );

        // 3. Di lembar kerja, sebagai KOLOM TERSENDIRI supaya bisa dipakai
        //    mengelompokkan — nomor yang menempel di dalam untaian nama tidak
        //    bisa.
        $ekspor = new \App\Exports\PendaftaranLayananExport(
            collect([$baris]), Pendaftaran::katalog(), []
        );

        $kepala = $ekspor->headings();
        $isi = $ekspor->array()[0];

        $kolom = array_search('Angkatan ke-', $kepala, true);

        $this->assertNotFalse($kolom, 'Lembar kerja harus punya kolom nomor angkatan.');
        $this->assertSame('202', $isi[$kolom]);
        $this->assertSame(count($kepala), count($isi), 'Jumlah kolom isi dan kepalanya harus sama.');
    }

    #[Test]
    public function pencarian_menemukan_lewat_nama_dan_nomor_angkatan(): void
    {
        /*
         * Orang mencari lewat apa yang mereka ingat, dan untuk merekap yang
         * diingat biasanya "Yogyakarta" atau "202" — bukan nomor pendaftaran
         * seseorang. Sebelum ini keduanya mengembalikan nol hasil.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Palu' . $tanda,
            'nama_ke' => '777',
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'CARIANGKATAN-' . $tanda,
            'kategori_id' => $angkatan->id,
            // Namanya sengaja TIDAK memuat kata yang dicari, supaya yang
            // terbukti memang pencocokan lewat angkatannya.
            'nama' => 'Orang Biasa Saja',
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0033',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        foreach (['Palu' . $tanda, '777'] as $kataKunci) {
            $this->actingAs($orang)
                ->get(route('account.pendaftaran-layanan.index', ['cari' => $kataKunci]))
                ->assertOk()
                ->assertSee('CARIANGKATAN-' . $tanda);

            $this->flushSession();
        }
    }

    #[Test]
    public function nomor_angkatan_dicocokkan_persis_bukan_sebagian(): void
    {
        /*
         * Dengan LIKE, mencari "7" akan menarik angkatan ke-7, ke-70, ke-77,
         * dan ke-777 sekaligus — dan rekap yang mengira dirinya satu angkatan
         * sebenarnya memuat empat.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $a777 = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Scopus Camp Tujuh' . $tanda,
            'nama_ke' => '777', 'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20', 'sisa_kuota' => '20', 'status' => 'active',
        ]);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'TIGATUJUH-' . $tanda,
            'kategori_id' => $a777->id,
            'nama' => 'Peserta Tujuh Ratus', 'email' => 't' . $tanda . '@contoh.test',
            'telp' => '0811-0000-0034', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000', 'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        // '7' TIDAK boleh menarik angkatan ke-777.
        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => '7', 'layanan' => 'scopus_camp']));

        $halaman->assertOk();
        $halaman->assertDontSee('TIGATUJUH-' . $tanda);
    }

    #[Test]
    public function angkatan_tanpa_nomor_tidak_menampilkan_apa_apa(): void
    {
        // Kolom nama_ke boleh kosong; yang tidak boleh adalah layar atau
        // berkas yang menulis "angkatan ke-" lalu berhenti.
        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Tanpa Nomor ' . Str::random(5),
            'nama_ke' => null,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        $p = PendaftaranScopusCamp::create([
            'id_transaksi' => 'TANPA-' . Str::random(6),
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Tanpa Nomor',
            'email' => Str::random(6) . '@contoh.test',
            'telp' => '0811-0000-0032',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        $baris = Pendaftaran::kueri()->where('id', $p->getKey())->first();

        $this->assertNull(Pendaftaran::nomorAngkatanBaris($baris));
        $this->assertStringNotContainsString('angkatan ke-', (string) Pendaftaran::sesiUntukBerkas($baris));
        $this->assertSame($angkatan->nama, Pendaftaran::sesiUntukBerkas($baris));
    }

    #[Test]
    public function menu_angkatan_hanya_memuat_yang_punya_pendaftar(): void
    {
        /*
         * Menawarkan angkatan kosong berarti menyediakan pilihan yang pasti
         * mengembalikan nol baris, dan orang yang menekannya menyimpulkan
         * saringannya rusak. Terukur 39 dari 59 angkatan yang punya pendaftar.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $kosong = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Sepi ' . $tanda,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        Pendaftaran::lupakan();

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index'))
            ->assertOk()
            // Angkatan tanpa satu pun pendaftar tidak ditawarkan.
            ->assertDontSee('value="' . $kosong->id . '"', false);
    }

    #[Test]
    public function menu_angkatan_benar_benar_menyaring(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $satu = $this->angkatan('scopus_camp');
        $dua = $this->angkatan('scopus_camp');

        foreach ([['DIANGKATAN', $satu], ['DILUAR', $dua]] as [$nama, $a]) {
            PendaftaranScopusCamp::create([
                'id_transaksi' => $nama . '-' . $tanda,
                'kategori_id' => $a->id,
                'nama' => $nama . ' ' . $tanda,
                'email' => strtolower($nama) . $tanda . '@contoh.test',
                'telp' => '0811-0000-0023',
                'jumlah_pendaftar' => '1',
                'total_pembayaran' => '10000',
                'status' => 'diproses',
            ]);
        }

        Pendaftaran::lupakan();

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda, 'angkatan' => $satu->id]))
            ->assertOk()
            ->assertSee('DIANGKATAN-' . $tanda)
            ->assertDontSee('DILUAR-' . $tanda);
    }

    // ----------------------------------------- mendaftarkan dari panitia

    #[Test]
    public function borang_pendaftaran_hanya_untuk_orang_dalam(): void
    {
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        $tujuan = route('account.dashboard.index');

        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.baru'))
            ->assertRedirect($tujuan);

        $this->flushSession();

        $this->actingAs($pelanggan)
            ->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp', 'nama' => 'Paksa Masuk',
                'email' => 'paksa@contoh.test', 'telp' => '0811',
            ])
            ->assertRedirect($tujuan);
    }

    #[Test]
    public function panitia_bisa_mendaftarkan_orang_dan_kuotanya_berkurang(): void
    {
        /*
         * Sebelum ini TIDAK ADA jalurnya: yang mendaftar lewat WhatsApp atau
         * datang langsung tidak bisa dimasukkan, sehingga daftar pendaftar
         * tidak pernah lengkap dan kuota angkatan tidak mencerminkan kursi
         * yang sebenarnya terpakai.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '900000'])->save();

        $jawab = $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Didaftarkan Panitia',
            'email' => 'panitia' . Str::random(6) . '@contoh.test',
            'telp' => '0812-3456-7890',
            'affiliasi' => 'Instansi Uji',
            'jumlah' => 3,
        ]);

        $baris = PendaftaranScopusCamp::where('nama', 'Didaftarkan Panitia')->first();

        $this->assertNotNull($baris, 'Barisnya harus tersimpan.');
        // Diantar ke halaman rinciannya: nomor dan kode uniknya baru dibuat
        // sistem, dan itu yang perlu dikirim panitia ke orangnya.
        $jawab->assertRedirect(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]));

        $this->assertSame('diproses', $baris->status, 'Status awalnya milik layanan itu.');
        $this->assertSame(2700000, (int) $baris->total_pembayaran, '900.000 x 3 orang.');
        $this->assertNotEmpty($baris->id_transaksi, 'Nomornya dibuat sistem.');
        $this->assertGreaterThan(0, (int) $baris->kode_unik, 'Kode uniknya dibuat sistem.');
        $this->assertStringContainsString('Didaftarkan panitia', (string) $baris->note);
        $this->assertStringContainsString('Uji administrator', (string) $baris->note);

        $this->assertSame(17, (int) $angkatan->refresh()->sisa_kuota, 'Tiga kursinya terpakai.');
    }

    #[Test]
    public function kursi_yang_tidak_cukup_ditolak_dan_kuotanya_tidak_bergeser(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 2);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Melebihi Kursi',
            'email' => 'lebih' . Str::random(6) . '@contoh.test',
            'telp' => '0812-0000-0000',
            'jumlah' => 5,
        ]);

        $this->assertNull(PendaftaranScopusCamp::where('nama', 'Melebihi Kursi')->first());
        $this->assertSame(2, (int) $angkatan->refresh()->sisa_kuota, 'Kuotanya tidak boleh bergeser.');
    }

    #[Test]
    public function angkatan_milik_layanan_lain_ditolak(): void
    {
        /*
         * Angkatan Bibliometrik dipasang ke pendaftaran Scopus Camp akan
         * membuat kuota KEDUA layanan salah tanpa ada yang menolak — dan
         * barisnya muncul di saringan layanan yang keliru.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $milikBiblio = $this->angkatan('bibliometrik', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $milikBiblio->id,
            'nama' => 'Angkatan Silang',
            'email' => 'silang' . Str::random(6) . '@contoh.test',
            'telp' => '0812-0000-0001',
            'jumlah' => 1,
        ]);

        $this->assertNull(PendaftaranScopusCamp::where('nama', 'Angkatan Silang')->first());
        $this->assertSame(20, (int) $milikBiblio->refresh()->sisa_kuota);
    }

    #[Test]
    public function potongan_khusus_mengurangi_total_dan_tercatat(): void
    {
        /*
         * Potongan KHUSUS, di atas potongan bawaan angkatannya.
         *
         * Angkatan sudah punya diskonnya sendiri dan itu sudah terhitung di
         * `total_biaya`; yang ini untuk hal yang tidak bisa diketahui
         * angkatan — peserta yang disponsori, harga mitra, atau kesepakatan
         * di tempat.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '900000'])->save();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Disponsori',
            'email' => 'sponsor' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0001',
            'jumlah' => 2,
            // Ditulis berpemisah, seperti yang diketik orang.
            'potongan' => '300.000',
            'kode_potongan' => 'SPONSOR',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Peserta Disponsori')->first();

        $this->assertNotNull($b);
        // 900.000 x 2 = 1.800.000, dipotong 300.000.
        $this->assertSame(1500000, (int) $b->total_pembayaran);
        $this->assertSame(300000, (int) $b->nominal_diskon);
        $this->assertSame('SPONSOR', $b->kode_diskon);
        // Potongan yang tidak bisa dijelaskan enam bulan kemudian sama saja
        // dengan selisih uang yang tidak ada keterangannya.
        $this->assertStringContainsString('potongan khusus Rp 300.000', (string) $b->note);
        $this->assertStringContainsString('SPONSOR', (string) $b->note);
    }

    #[Test]
    public function potongan_tidak_boleh_melebihi_tagihannya(): void
    {
        // Total negatif tidak berarti apa-apa sebagai nominal transfer, dan
        // kode uniknya akan dicari atas angka yang mustahil dicocokkan.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '500000', 'total_biaya' => '500000'])->save();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Potongan Kebablasan',
            'email' => 'lebih' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0002',
            'jumlah' => 1,
            'potongan' => '9999999',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Potongan Kebablasan')->first();

        $this->assertNotNull($b);
        $this->assertSame(0, (int) $b->total_pembayaran, 'Totalnya nol, bukan negatif.');
        $this->assertSame(500000, (int) $b->nominal_diskon, 'Potongannya dibatasi tagihannya.');
    }

    #[Test]
    public function layanan_tanpa_kolom_diskon_tidak_menyimpan_potongan(): void
    {
        /*
         * Scopus Kafe tidak punya kolom nominal_diskon — dan di sana
         * nominalnya memang diketik langsung, jadi potongannya sudah termasuk
         * di dalamnya. Menuliskannya ke sana akan melempar "Unknown column"
         * dan menggagalkan seluruh pendaftarannya.
         */
        $this->assertFalse(Pendaftaran::katalogBisaDibuat()['scopus_kafe']['bisa_potongan']);

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Kafe Dengan Potongan',
            'email' => 'kafe' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0003',
            'total' => '200000',
            'potongan' => '50000',
            'kode_potongan' => 'ABAIKAN',
        ])->assertRedirect();

        $b = PendaftaranScopusKafe::where('nama', 'Kafe Dengan Potongan')->first();

        $this->assertNotNull($b, 'Pendaftarannya tetap tersimpan, tidak gagal.');
        // Potongannya tetap mengurangi totalnya — yang tidak bisa cuma
        // mencatatnya di kolom tersendiri.
        $this->assertSame(150000, (int) $b->total_keseluruhan_pembayaran);
    }

    #[Test]
    public function layanan_tanpa_angkatan_memakai_nominal_yang_diketik(): void
    {
        // Scopus Kafe tidak berangkatan — tarifnya per sesi dan berbeda-beda,
        // jadi nominalnya memang harus diketik.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Pemesan Kafe Uji',
            'email' => 'kafe' . Str::random(6) . '@contoh.test',
            'telp' => '0813-0000-0000',
            // Ditulis berpemisah: pengendali lama membuang titik di satu
            // layanan dan koma di layanan lain, jadi "250.000" pernah
            // tersimpan sebagai nol.
            'total' => '250.000',
        ])->assertRedirect();

        $baris = PendaftaranScopusKafe::where('nama', 'Pemesan Kafe Uji')->first();

        $this->assertNotNull($baris);
        $this->assertSame(250000, (int) $baris->total_keseluruhan_pembayaran);
        $this->assertSame('menunggu verifikasi', $baris->status);
        $this->assertNotEmpty($baris->id_pemesanan);
    }

    #[Test]
    public function clinik_scopus_tidak_bisa_didaftarkan_dari_layar_ini(): void
    {
        /*
         * Disengaja, dan dijaga supaya tidak "dilengkapi" tanpa sadar:
         * pemesanan Clinik Scopus mengikat sesi, trainer, dan akun pelanggan —
         * ketiganya kolom NOT NULL yang menunjuk baris lain. Borang yang
         * menebaknya akan membuat pemesanan yang menunjuk sesi atau trainer
         * yang salah.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->assertArrayNotHasKey('clinik_scopus', Pendaftaran::katalogBisaDibuat());

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'clinik_scopus',
            'nama' => 'Clinik Paksa',
            'email' => 'clinik' . Str::random(6) . '@contoh.test',
            'telp' => '0814-0000-0000',
            'total' => '100000',
        ])->assertSessionHasErrors('layanan');

        $this->assertNull(ClinikScopusPemesanan::where('nama_pemesan', 'Clinik Paksa')->first());
    }

    #[Test]
    public function nomor_pendaftaran_mengikuti_pola_layanannya(): void
    {
        // Baris yang dibuat panitia tidak boleh bisa dibedakan dari yang
        // didaftarkan sendiri oleh orangnya — termasuk bentuk nomornya.
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{5}$/', Pendaftaran::nomorBaru('scopus_camp'));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{5}$/', Pendaftaran::nomorBaru('scopus_kafe'));
        $this->assertMatchesRegularExpression('/^WE-\d{8}-\d{4}$/', Pendaftaran::nomorBaru('webinar_eksklusif'));
        $this->assertMatchesRegularExpression('/^BOOK-\d{14}-[A-Z0-9]{5}$/', Pendaftaran::nomorBaru('clinik_scopus'));
    }

    #[Test]
    public function kode_unik_membuat_totalnya_belum_terpakai(): void
    {
        /*
         * Gunanya mencocokkan mutasi rekening: dua orang yang membayar nominal
         * yang SAMA PERSIS tidak bisa dibedakan. Jadi yang harus unik bukan
         * kodenya melainkan hasil penjumlahannya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 50, 50);
        $angkatan->forceFill(['biaya' => '500000', 'total_biaya' => '500000'])->save();

        $totalnya = [];

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp',
                'kategori_id' => $angkatan->id,
                'nama' => 'Kode Unik ' . $i,
                'email' => 'kode' . $i . Str::random(5) . '@contoh.test',
                'telp' => '0815-0000-000' . $i,
                'jumlah' => 1,
            ])->assertRedirect();

            $b = PendaftaranScopusCamp::where('nama', 'Kode Unik ' . $i)->first();
            $this->assertNotNull($b);
            $totalnya[] = (int) $b->total_pembayaran + (int) $b->kode_unik;

            $this->flushSession();
        }

        $this->assertSame(count($totalnya), count(array_unique($totalnya)),
            'Dua pendaftaran menghasilkan nominal transfer yang sama persis.');
    }

    // ------------------------------------------------ saringan tanggal

    #[Test]
    public function saringan_tanggal_memasukkan_hari_batasnya_sendiri(): void
    {
        /*
         * Perkara batas yang paling mudah salah: kolom waktunya bertimestamp,
         * jadi `<= '2026-10-03'` berarti `<= 2026-10-03 00:00:00` dan
         * MEMBUANG seluruh pendaftaran yang terjadi pada hari itu. Orangnya
         * menyaring "sampai hari ini" lalu mendapati pendaftaran hari ini
         * hilang — tanpa galat apa pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);
        $angkatan = $this->angkatan('scopus_camp');

        $sore = PendaftaranScopusCamp::create([
            'id_transaksi' => 'SORE-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Daftar Sore ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0011',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        // Jam 17.30 pada hari yang jadi batas akhirnya.
        $hari = now()->subDays(3)->startOfDay();
        $sore->forceFill(['created_at' => $hari->copy()->setTime(17, 30)])->save();

        $halaman = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.index', [
            'cari' => $tanda,
            'dari' => $hari->toDateString(),
            'sampai' => $hari->toDateString(),
        ]));

        $halaman->assertOk();
        $halaman->assertSee('SORE-' . $tanda);
    }

    #[Test]
    public function saringan_tanggal_membuang_yang_di_luar_rentang(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);
        $angkatan = $this->angkatan('scopus_camp');

        $dibuat = [];

        foreach ([['LAMA', 40], ['TENGAH', 10], ['BARU', 1]] as [$nama, $hariLalu]) {
            $b = PendaftaranScopusCamp::create([
                'id_transaksi' => $nama . '-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => $nama . ' ' . $tanda,
                'email' => strtolower($nama) . $tanda . '@contoh.test',
                'telp' => '0811-0000-0012',
                'jumlah_pendaftar' => '1',
                'total_pembayaran' => '10000',
                'status' => 'diproses',
            ]);

            $b->forceFill(['created_at' => now()->subDays($hariLalu)])->save();
            $dibuat[$nama] = $b;
        }

        $halaman = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.index', [
            'cari' => $tanda,
            'dari' => now()->subDays(20)->toDateString(),
            'sampai' => now()->subDays(5)->toDateString(),
        ]));

        $halaman->assertOk();
        $halaman->assertSee('TENGAH-' . $tanda);
        $halaman->assertDontSee('LAMA-' . $tanda);
        $halaman->assertDontSee('BARU-' . $tanda);
    }

    #[Test]
    public function tanggal_yang_tidak_masuk_akal_diabaikan_bukan_mengosongkan_daftar(): void
    {
        /*
         * "2026-13-45" diterima SQL sebagai untaian dan kuerinya mengembalikan
         * nol baris TANPA galat — jadi orangnya menyimpulkan datanya yang
         * tidak ada. Diabaikan, daftarnya tetap utuh.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', [
                'cari' => $camp->id_transaksi,
                'dari' => '2026-13-45',
                'sampai' => 'bukan tanggal',
            ]))
            ->assertOk()
            ->assertSee($camp->id_transaksi);
    }

    #[Test]
    public function berkas_unduhan_menyebut_rentang_tanggalnya(): void
    {
        // Berkas berisi pendaftaran satu bulan yang tidak menyebut bulannya
        // terbaca persis seperti daftar yang lengkap.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.pdf', [
                'dari' => '2026-09-01', 'sampai' => '2026-09-30',
            ]))
            ->assertOk();

        $html = view('account.pendaftaran_layanan.ekspor-pdf', [
            'baris' => collect(),
            'saringan' => ['Tanggal daftar' => '01 Sep 2026 sampai 30 Sep 2026'],
            'katalog' => Pendaftaran::katalog(),
        ])->render();

        $this->assertStringContainsString('01 Sep 2026 sampai 30 Sep 2026', $html);
    }

    // --------------------------------------------- menunggu terlalu lama

    #[Test]
    public function saringan_menggantung_hanya_menyisakan_yang_menunggu_lama(): void
    {
        /*
         * Empat dari lima layanan TIDAK punya kedaluwarsa sama sekali, jadi
         * pendaftaran yang transfernya tidak pernah datang menunggu selamanya
         * tanpa ada yang menengok. Terukur di basis data: 5 menunggu lebih
         * dari 7 hari, 2 di antaranya lebih dari 90 hari.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);
        $angkatan = $this->angkatan('scopus_camp');

        $buat = function (string $nama, int $hariLalu, string $status) use ($tanda, $angkatan) {
            $b = PendaftaranScopusCamp::create([
                'id_transaksi' => $nama . '-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => $nama . ' ' . $tanda,
                'email' => strtolower($nama) . $tanda . '@contoh.test',
                'telp' => '0811-0000-0013',
                'jumlah_pendaftar' => '1',
                'total_pembayaran' => '10000',
                'status' => $status,
            ]);

            $b->forceFill(['created_at' => now()->subDays($hariLalu)])->save();

            return $b;
        };

        $buat('LAMAMENUNGGU', 30, 'diproses');
        $buat('BARUMENUNGGU', 2, 'diproses');
        // Sudah lunas: lamanya tidak jadi soal, ia tidak menggantung.
        $buat('LAMALUNAS', 30, 'Pendaftaran Diterima');

        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda, 'lama' => '1']));

        $halaman->assertOk();
        $halaman->assertSee('LAMAMENUNGGU-' . $tanda);
        $halaman->assertDontSee('BARUMENUNGGU-' . $tanda);
        $halaman->assertDontSee('LAMALUNAS-' . $tanda);
    }

    // ------------------------------------------------ jejak kelima layanan

    #[Test]
    public function kelima_layanan_mencatat_jejak_perubahan_statusnya(): void
    {
        /*
         * Scopus Kafe dan Clinik Scopus dulu TIDAK punya kolom `note`, jadi
         * siapa pun yang memindahkan statusnya tidak meninggalkan jejak apa
         * pun — terukur 11 dari 187 baris tidak terlacak. Kolomnya
         * ditambahkan migrasi 2026_10_03_160000.
         *
         * Diperiksa untuk KELIMA layanan sekaligus, bukan dua yang baru:
         * layanan berikutnya yang datang tanpa kolom itu harus ketahuan di
         * sini, bukan saat ada selisih uang yang tidak bisa ditelusuri.
         */
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $sasaran = [
            'scopus_camp' => 'Pendaftaran Dibatalkan',
            'bibliometrik' => 'Pendaftaran Dibatalkan',
            'webinar_eksklusif' => 'cancel',
            'scopus_kafe' => 'pembayaran ditolak',
            'clinik_scopus' => 'canceled',
        ];

        foreach ($sasaran as $layanan => $status) {
            [$b] = $this->buat($layanan);
            $statusLama = $b->status;

            $this->actingAs($orang)->post(
                route('account.pendaftaran-layanan.status', [$layanan, $b->getKey()]),
                ['status' => $status]
            )->assertRedirect();

            $kolom = Pendaftaran::kolomCatatan($layanan);

            $this->assertNotNull($kolom, "Layanan {$layanan} tidak punya kolom catatan.");
            $this->assertStringContainsString(
                '"' . $statusLama . '" → "' . $status . '"',
                (string) $b->refresh()->{$kolom},
                "Jejak perubahan {$layanan} tidak tercatat."
            );
            $this->assertStringContainsString('Uji administrator', (string) $b->{$kolom},
                "Jejak {$layanan} tidak menyebut siapa yang mengubahnya.");

            $this->flushSession();
        }
    }

    // ------------------------------------------- layar yang dipertahankan

    #[Test]
    public function pelanggan_hanya_melihat_pesanan_clinik_miliknya(): void
    {
        /*
         * Riwayat Pemesanan Clinik Scopus DIPERTAHANKAN saat empat layar
         * pendaftaran per layanan dibuang: ia satu-satunya tempat pelanggan
         * bisa melihat pesanannya sendiri.
         *
         * Dan penyaringnya dikencangkan. Syarat lamanya `jenis ===
         * 'perorangan'`, sehingga pelanggan berjenis lain — atau kosong —
         * jatuh ke luar semua cabang dan melihat pesanan SELURUH orang.
         * Uji ini memakai pelanggan berjenis 'perusahaan', yaitu keadaan
         * yang dulu membuka celahnya.
         */
        // Kolom `jenis` NOT NULL berbawaan 'perorangan', jadi celahnya hanya
        // terbuka untuk akun yang jenisnya disetel ke nilai lain dengan
        // sengaja — 'perusahaan' adalah nilai yang sudah dikenal borangnya.
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        $pelanggan->forceFill(['jenis' => 'perusahaan'])->save();
        $pelanggan->refresh();

        $this->assertSame('perusahaan', $pelanggan->jenis,
            'Ujinya harus memakai pelanggan yang BUKAN perorangan.');

        [$punyaOrangLain] = $this->buat('clinik_scopus');

        $milikDia = ClinikScopusPemesanan::create([
            'clinikscopus_id' => $punyaOrangLain->clinikscopus_id,
            'trainer_id' => $punyaOrangLain->trainer_id,
            'customer_id' => $pelanggan->id,
            'id_transaksi' => 'MILIKDIA-' . Str::random(6),
            'kode_booking' => 'BOOK-' . Str::random(6),
            'nama_pemesan' => 'Punya Dia Sendiri',
            'email_pemesan' => 'dia@contoh.test',
            'telp_pemesan' => '0811-0000-0009',
            'sesi' => 'Sesi 1',
            'total_pembayaran' => 50000,
            'status' => 'pending',
        ]);

        $halaman = $this->actingAs($pelanggan)
            ->get(route('account.Clinik-Scopus-Riwayat-Pemesanan.index'));

        $halaman->assertOk();

        /*
         * Diperiksa dari KODE BOOKING-nya, bukan dari nama pemesannya.
         *
         * Layar itu menampilkan nama dari relasi `customer` — yaitu
         * `full_name` akunnya — bukan kolom `nama_pemesan` di barisnya. Jadi
         * memeriksa nama pemesan akan gagal walau penyaringnya benar, dan
         * ujinya menuduh kode yang tidak bersalah.
         */
        $halaman->assertSee($milikDia->kode_booking);
        $halaman->assertDontSee($punyaOrangLain->kode_booking);
    }

    #[Test]
    public function tombol_hapus_tidak_disuguhkan_ke_yang_tidak_boleh(): void
    {
        /*
         * Layar tidak boleh menyuguhkan sesuatu yang kirimannya akan ditolak,
         * dan alasannya ditulis di tempat tombol itu seharusnya berada.
         * Diperiksa dari MARKAHNYA, bukan dari tulisannya: komentar Blade
         * tidak terkirim, tetapi kalimat di dalam kartunya bisa saja memuat
         * kata yang sama.
         */
        [$camp] = $this->buat('scopus_camp');
        $alamat = route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]);

        $karyawan = $this->actingAs($this->akun(User::PERAN_KARYAWAN))->get($alamat);
        $karyawan->assertOk();
        $karyawan->assertDontSee('id="rin-tombol-hapus"', false);
        $karyawan->assertSee('Hanya administrator yang boleh menghapus');

        $this->flushSession();

        $admin = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))->get($alamat);
        $admin->assertOk();
        $admin->assertSee('id="rin-tombol-hapus"', false);
    }
}
