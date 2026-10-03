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
