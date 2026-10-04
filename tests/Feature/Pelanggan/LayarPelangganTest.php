<?php

namespace Tests\Feature\Pelanggan;

use App\AktivitasMasuk;
use App\Mail\VerifikasiEmailMail;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar Data Pelanggan: hak, penghapusan, pengurutan, dan unduhan.
 *
 * Yang dijaga di sini terutama dua hal yang dulunya membuat orang menemui
 * galat SESUDAH terlanjur bertindak: borang yang tampak bisa diisi padahal
 * kirimannya ditolak, dan tombol hapus yang berujung galat 500.
 */
class LayarPelangganTest extends TestCase
{
    use DatabaseTransactions;

    private function akun(string $peran, array $tambahan = []): User
    {
        $u = User::create(array_merge([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_lp_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ], $tambahan));

        $u->forceFill(array_merge([
            'status' => 'active',
            'email_verified_at' => now(),
            'peran' => $peran,
        ], $tambahan))->save();

        return $u->refresh();
    }

    // ------------------------------------------------------------------ hak

    #[Test]
    public function karyawan_boleh_melihat_tetapi_tidak_disuguhi_tombol_simpan(): void
    {
        /*
         * Karyawan memang boleh membuka halaman ini. Yang dulu keliru:
         * borangnya tampil utuh dan kelihatan bisa diisi, padahal
         * PenggunaController menolak kirimannya dengan 403 — pemberitahuan
         * yang datang sesudah orang mengetik.
         */
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $halaman = $this->actingAs($karyawan)->get(route('account.customer.edit', $pelanggan->uuid));

        $halaman->assertOk();
        $halaman->assertSee('Hanya administrator yang boleh mengubah data pelanggan.');

        /*
         * Diperiksa dari TOMBOLNYA, bukan dari tulisannya.
         *
         * assertDontSee('Simpan kontak') pernah dipakai di sini dan menangkap
         * hal yang salah: label itu juga tertulis di dalam komentar CSS pada
         * blok <style> halaman ini, dan komentar CSS ikut terkirim ke peramban.
         * Yang sebenarnya dijaga adalah tidak ada satu pun tombol kirim.
         */
        $isi = $halaman->getContent();

        $this->assertStringNotContainsString('type="submit"', $isi, 'Karyawan tidak boleh punya tombol kirim apa pun.');

        // Isiannya dimatikan, bukan sekadar tombolnya disembunyikan: tanpa itu
        // isian masih bisa diisi dan dikirim lewat Enter.
        $this->assertStringContainsString('disabled', $isi);
    }

    #[Test]
    public function administrator_tetap_mendapat_tombol_simpannya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $isi = $this->actingAs($admin)
            ->get(route('account.customer.edit', $pelanggan->uuid))
            ->assertOk()
            ->assertDontSee('Hanya administrator yang boleh mengubah data pelanggan.')
            ->getContent();

        // Tiga tombol kirim: data akun, kontak, dan unggah foto.
        $this->assertSame(3, substr_count($isi, 'type="submit"'));
    }

    // ----------------------------------------------------------- penghapusan

    #[Test]
    public function pelanggan_yang_masih_punya_jejak_layanan_ditolak_dengan_keterangan(): void
    {
        /*
         * Dulu: MySQL menolak dengan galat 1451, tidak ada yang menangkapnya,
         * layar menerima 500, dan toast-nya berbunyi "Tidak bisa menghubungi
         * peladen" — padahal peladennya terhubung dan justru bekerja benar.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $induk = DB::table('clinikscopus')->value('id');

        if (! $induk) {
            $this->markTestSkipped('Tidak ada baris clinikscopus untuk dipinjam sebagai induk.');
        }

        DB::table('clinikscopus_pemesanan')->insert([
            'id' => (string) Str::uuid(),
            'clinikscopus_id' => $induk,
            'trainer_id' => $admin->getKey(),
            'customer_id' => $pelanggan->getKey(),
            'nama_pemesan' => $pelanggan->full_name,
            'email_pemesan' => $pelanggan->email,
            'kode_booking' => 'UJI-' . Str::random(6),
            'total_pembayaran' => 100000,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $jawab = $this->actingAs($admin)
            ->deleteJson(route('account.customer.destroy', $pelanggan->uuid));

        $jawab->assertStatus(409);
        $jawab->assertJson(['success' => false]);

        // Keterangannya menyebut APA yang menghalangi dan APA yang sebaiknya
        // dilakukan — bukan sekadar "gagal".
        $pesan = $jawab->json('message');
        $this->assertStringContainsString('pesanan Clinik Scopus', $pesan);
        $this->assertStringContainsString('Nonaktifkan', $pesan);

        $this->assertNotNull(User::find($pelanggan->getKey()), 'Pelanggannya harus tetap ada.');
    }

    #[Test]
    public function pelanggan_tanpa_jejak_terhapus_beserta_riwayat_masuknya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        AktivitasMasuk::create([
            'user_id' => $pelanggan->getKey(),
            'identitas' => $pelanggan->username,
            'berhasil' => true,
            'alasan' => 'uji',
            'ip' => '127.0.0.1',
            'peramban' => 'uji',
            'perangkat' => 'uji',
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('account.customer.destroy', $pelanggan->uuid))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(User::find($pelanggan->getKey()));

        /*
         * aktivitas_masuk TIDAK punya kunci asing ke users, jadi barisnya tidak
         * ikut terhapus sendiri. Dibiarkan, ia jadi baris yatim yang menunjuk
         * akun yang sudah tidak ada — dan dialog konfirmasinya sudah terlanjur
         * berjanji "riwayatnya ikut terhapus".
         */
        $this->assertSame(0, AktivitasMasuk::where('user_id', $pelanggan->getKey())->count());
    }

    #[Test]
    public function karyawan_tidak_boleh_menghapus(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($karyawan)
            ->deleteJson(route('account.customer.destroy', $pelanggan->uuid))
            ->assertStatus(403);

        $this->assertNotNull(User::find($pelanggan->getKey()));
    }

    // --------------------------------------------------------------- tampilan

    #[Test]
    public function peran_ditulis_dalam_bahasa_indonesia(): void
    {
        // "User" adalah satu-satunya kata Inggris yang dulu muncul di layar ini.
        $this->assertSame('Pelanggan', $this->akun(User::PERAN_PELANGGAN)->peranTerbaca());
        $this->assertSame('Karyawan', $this->akun(User::PERAN_KARYAWAN)->peranTerbaca());
        $this->assertSame('Administrator', $this->akun(User::PERAN_ADMINISTRATOR)->peranTerbaca());
    }

    #[Test]
    public function daftar_menyediakan_tautan_email_dan_whatsapp(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $this->akun(User::PERAN_PELANGGAN, ['telp' => '0812-3456-7890']);

        $halaman = $this->actingAs($admin)->get(route('account.customer.index'));

        $halaman->assertOk();
        $halaman->assertSee('mailto:', false);
        // wa.me menuntut bentuk internasional tanpa tanda baca.
        $halaman->assertSee('https://wa.me/6281234567890', false);
    }

    #[Test]
    public function ubin_ringkasan_tidak_lagi_memuat_angka_kembar(): void
    {
        /*
         * "Akun aktif" dan "Email terverifikasi" selalu sama persis — terukur
         * 65 lawan 65, nol yang berbeda — sebab verifyEmail() menyetel
         * keduanya sekaligus. Satu dari empat ubin tidak membawa keterangan.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->get(route('account.customer.index'))
            ->assertOk()
            ->assertSee('Pernah memesan')
            ->assertDontSee('Email terverifikasi');
    }

    // ------------------------------------------------------- urutan & unduhan

    #[Test]
    public function daftar_bisa_diurutkan_berdasarkan_jumlah_pesanan(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $halaman = $this->actingAs($admin)
            ->get(route('account.customer.index', ['urut' => 'pesanan', 'arah' => 'turun']));

        $halaman->assertOk();

        // Yang paling banyak memesan harus berada di atas yang tidak memesan
        // sama sekali. Diperiksa dari urutan namanya di markah.
        $isi = $halaman->getContent();
        $pel = User::where('peran', User::PERAN_PELANGGAN)->get();
        $ringkas = \App\Support\PesananPelanggan::ringkas($pel);

        if ($ringkas === []) {
            $this->markTestSkipped('Belum ada pesanan yang tertaut untuk diuji urutannya.');
        }

        arsort($ringkas);
        $teratas = $pel->firstWhere('id', array_key_first($ringkas));
        $tanpa = $pel->first(fn ($p) => ! isset($ringkas[$p->id]));

        $this->assertNotNull($teratas);

        if ($tanpa) {
            $this->assertLessThan(
                strpos($isi, e($tanpa->full_name ?: $tanpa->username)) ?: PHP_INT_MAX,
                strpos($isi, e($teratas->full_name ?: $teratas->username)),
                'Yang paling banyak memesan seharusnya di atas yang belum pernah memesan.'
            );
        }
    }

    #[Test]
    public function unduhan_excel_terkirim_sebagai_lembar_kerja(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $jawab = $this->actingAs($admin)->get(route('account.customer.ekspor.excel'));

        $jawab->assertOk();
        $jawab->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        $this->assertStringContainsString(
            'data-pelanggan-',
            $jawab->headers->get('content-disposition')
        );
    }

    // -------------------------------------------------- pencarian & saringan

    public static function bentukNomorDicari(): array
    {
        return [
            'apa adanya' => ['0812-3456-7890'],
            'tanpa tanda hubung' => ['081234567890'],
            'kode negara' => ['6281234567890'],
            'kode negara bertanda tambah' => ['+6281234567890'],
            'sebagian tengah' => ['34567890'],

            /*
             * Bentuk yang ditulis WhatsApp: kode negara, spasi, lalu kelompok
             * bertanda hubung. Tanda tambahnya sengaja diuji apa adanya —
             * pada alamat URL "+" berarti SPASI, jadi kata kunci ini sampai
             * ke peladen sebagai " 62 812 3456 7890" dan tetap harus ketemu.
             */
            'gaya WhatsApp' => ['+62 812-3456-7890'],
            'berspasi seluruhnya' => ['+62 812 3456 7890'],
            'kode negara berspasi tanpa tambah' => ['62 812-3456-7890'],
        ];
    }

    #[Test]
    #[DataProvider('bentukNomorDicari')]
    public function nomor_telepon_ketemu_ditulis_bagaimanapun(string $dicari): void
    {
        /*
         * Terukur sebelum ini: 93 dari 101 nomor pelanggan bertanda hubung,
         * sementara pencariannya memakai LIKE mentah. Mencari "081234567890"
         * atas nomor tersimpan "0812-3456-7890" mengembalikan NOL hasil — dan
         * yang disalin orang dari WhatsApp memang tidak bertanda hubung.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $orang = $this->akun(User::PERAN_PELANGGAN, ['telp' => '0812-3456-7890']);

        $this->actingAs($admin)
            ->get(route('account.customer.index', ['cari' => $dicari]))
            ->assertOk()
            ->assertSee($orang->username);
    }

    #[Test]
    public function kata_kunci_tanpa_angka_tidak_mencocokkan_semua_nomor(): void
    {
        // Kalau bagian nomornya dibiarkan jadi LIKE '%%', kata kunci yang tidak
        // cocok dengan siapa pun justru mengembalikan seluruh daftar.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $orang = $this->akun(User::PERAN_PELANGGAN, ['telp' => '0812-3456-7890']);

        $this->actingAs($admin)
            ->get(route('account.customer.index', ['cari' => 'zzzqqqtidakada']))
            ->assertOk()
            ->assertDontSee($orang->username)
            ->assertSee('Tidak ada yang cocok');
    }

    #[Test]
    public function ubin_ringkasan_menyaring_daftarnya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $aktif = $this->akun(User::PERAN_PELANGGAN);
        $mati = $this->akun(User::PERAN_PELANGGAN);
        $mati->forceFill(['status' => 'non active'])->save();

        $halaman = $this->actingAs($admin)->get(route('account.customer.index', ['status' => 'aktif']));

        $halaman->assertOk()->assertSee($aktif->username)->assertDontSee($mati->username);
    }

    #[Test]
    public function saringan_pernah_memesan_hanya_memuat_yang_punya_pesanan(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanpa = $this->akun(User::PERAN_PELANGGAN, ['telp' => '0899-0000-1234']);

        $halaman = $this->actingAs($admin)->get(route('account.customer.index', ['pesanan' => 'ada']));

        $halaman->assertOk()->assertDontSee($tanpa->username);
    }

    // ------------------------------------------------------------ aksi massal

    #[Test]
    public function administrator_bisa_menonaktifkan_banyak_sekaligus(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $a = $this->akun(User::PERAN_PELANGGAN);
        $b = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($admin)
            ->postJson(route('account.customer.massal'), [
                'aksi' => 'nonaktifkan',
                'uuid' => [$a->uuid, $b->uuid],
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'jumlah' => 2]);

        $this->assertSame('non active', $a->refresh()->status);
        $this->assertSame('non active', $b->refresh()->status);
    }

    #[Test]
    public function aksi_massal_tidak_menyentuh_akun_yang_bukan_pelanggan(): void
    {
        /*
         * uuid yang dikirim datang dari peramban. Tanpa batas peran di kueri,
         * satu uuid karyawan yang diselipkan ke kiriman bisa ikut dinonaktifkan
         * dari layar yang seharusnya cuma menyentuh pelanggan.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($admin)
            ->postJson(route('account.customer.massal'), [
                'aksi' => 'nonaktifkan',
                'uuid' => [$karyawan->uuid, $pelanggan->uuid],
            ])
            ->assertOk()
            ->assertJson(['jumlah' => 1]);

        $this->assertSame('active', $karyawan->refresh()->status, 'Akun karyawan tidak boleh ikut.');
        $this->assertSame('non active', $pelanggan->refresh()->status);
    }

    #[Test]
    public function karyawan_tidak_boleh_memakai_aksi_massal(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($karyawan)
            ->postJson(route('account.customer.massal'), [
                'aksi' => 'nonaktifkan',
                'uuid' => [$pelanggan->uuid],
            ])
            ->assertStatus(403);

        $this->assertSame('active', $pelanggan->refresh()->status);
    }

    // ------------------------------------------------------ akses & penanda

    #[Test]
    public function hasil_pencarian_diumumkan_dan_arah_urutan_dinyatakan(): void
    {
        /*
         * Isi tabel ditukar diam-diam tiap ketikan. Tanpa aria-live, pembaca
         * layar tidak mengumumkan apa pun; tanpa aria-sort, ia menyebut kepala
         * kolom sebagai tautan biasa tanpa menyatakan kolom mana yang sedang
         * dipakai mengurutkan dan ke arah mana.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $halaman = $this->actingAs($admin)
            ->get(route('account.customer.index', ['urut' => 'nama', 'arah' => 'naik']));

        $halaman->assertOk();
        $halaman->assertSee('aria-live="polite"', false);
        $halaman->assertSee('aria-sort="ascending"', false);
        $halaman->assertSee('aria-sort="none"', false);
    }

    #[Test]
    public function email_yang_bentuknya_tidak_sah_ditandai(): void
    {
        // Lima alamat di data yang ada terpotong tepat di 30 huruf; surat ke
        // sana tidak akan pernah sampai, dan tidak ada apa pun yang menandainya.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $rusak = $this->akun(User::PERAN_PELANGGAN);
        $rusak->forceFill(['email' => 'terpotong@mail.unnes.a'])->save();

        $halaman = $this->actingAs($admin)
            ->get(route('account.customer.index', ['cari' => 'terpotong']));

        $halaman->assertOk();
        $halaman->assertSee('pel-email-rusak', false);
        $halaman->assertDontSee('mailto:terpotong@mail.unnes.a', false);
    }

    #[Test]
    public function unduhan_menghormati_urutan_yang_sedang_dipakai(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $jawab = $this->actingAs($admin)
            ->get(route('account.customer.ekspor.excel', ['urut' => 'nama', 'arah' => 'turun']));

        $jawab->assertOk();

        // Isi berkasnya tidak dibedah di sini; yang dijaga cuma bahwa urutannya
        // ikut diterima dan tidak membuat unduhannya galat.
        $this->assertStringContainsString('data-pelanggan-', $jawab->headers->get('content-disposition'));
    }

    // ------------------------------------- saringan ubin dianggap "sedang aktif"

    #[Test]
    public function saringan_dari_ubin_ikut_dihitung_sebagai_saringan(): void
    {
        /*
         * Empat tempat di tampilan dulu memeriksa cari/status/verifikasi saja,
         * dan dua saringan dari ubin ringkasan tidak disebut di satu pun.
         * Akibatnya tombol Reset tidak muncul dan lencana "aktif" di penyaring
         * yang terlipat tidak menyala — di ponsel, tidak ada tanda apa pun
         * bahwa daftarnya sedang disaring.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->get(route('account.customer.index', ['pesanan' => 'ada']))
            ->assertOk()
            ->assertSee('Hapus semua saringan');

        $this->actingAs($admin)
            ->get(route('account.customer.index', ['baru' => '30']))
            ->assertOk()
            ->assertSee('Hapus semua saringan');
    }

    #[Test]
    public function saringan_yang_tidak_menyisakan_siapa_pun_tidak_bilang_belum_ada_pelanggan(): void
    {
        // "Belum ada pelanggan" berarti tabelnya kosong. Saat 101 pelanggan ada
        // dan hanya saringannya yang mengecualikan semua, kalimat itu keliru.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        User::where('peran', User::PERAN_PELANGGAN)->update(['created_at' => now()->subYears(2)]);

        $halaman = $this->actingAs($admin)->get(route('account.customer.index', ['baru' => '30']));

        $halaman->assertOk()
            ->assertSee('Tidak ada yang cocok')
            ->assertDontSee('Belum ada pelanggan');
    }

    // ----------------------------------------------------- aksi massal di ponsel

    #[Test]
    public function kotak_centang_tidak_disembunyikan_di_mode_kartu(): void
    {
        /*
         * Terukur sebelum ini: 12 kotak centang ada di markah dan NOL terlihat
         * di 390px, sebab selnya diberi kelas mis-td-samar — kelas yang di mode
         * kartu berarti display:none, dan yang memang dibuat untuk kolom nomor
         * urut. Seluruh aksi massal jadi mustahil dipakai dari ponsel.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $this->akun(User::PERAN_PELANGGAN);

        $isi = $this->actingAs($admin)->get(route('account.customer.index'))->getContent();

        $this->assertStringContainsString('class="pel-centang-sel"', $isi);
        $this->assertStringNotContainsString('pel-centang-sel mis-td-samar', $isi);
    }

    #[Test]
    public function baris_aksi_massal_punya_tombol_pilih_semua(): void
    {
        // Kotak "pilih semua" ada di kepala tabel, dan kepala tabel
        // disembunyikan di mode kartu — tombol ini penggantinya di ponsel.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->get(route('account.customer.index'))
            ->assertOk()
            ->assertSee('pel-massal-semua', false)
            ->assertSee('Pilih semua');
    }

    // -------------------------------------------------- galat menunjuk tabnya

    #[Test]
    public function galat_pada_kontak_membuka_tab_kontak(): void
    {
        /*
         * Kirim email yang sudah dipakai: toast memang muncul, tetapi halamannya
         * dulu digambar ulang pada tab Data akun — sementara tanda merah beserta
         * nilai yang tadi diketik ada di tab Kontak, tidak terlihat sama sekali.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($admin)
            ->from(route('account.customer.edit', $pelanggan->uuid))
            ->post(route('account.pengguna.update.datadiri', $pelanggan->uuid), [
                'email' => $admin->email,
                'telp' => '0812-0000-0000',
            ])
            ->assertRedirect();

        // Galatnya dikirim lewat sesi, jadi baru terbaca saat halamannya dibuka.
        $isi = $this->actingAs($admin)
            ->get(route('account.customer.edit', $pelanggan->uuid))
            ->getContent();

        $this->assertStringContainsString('id="pel-panel-kontak"', $isi);
        // Panel Kontak yang terbuka, bukan Data akun.
        $this->assertMatchesRegularExpression(
            '/class="tab-pane fade show active"\s+id="pel-panel-kontak"/',
            $isi,
            'Tab Kontak seharusnya yang terbuka saat galatnya ada di sana.'
        );
    }

    #[Test]
    public function tanpa_galat_tab_pertama_yang_terbuka(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $isi = $this->actingAs($admin)
            ->get(route('account.customer.edit', $pelanggan->uuid))
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/class="tab-pane fade show active"\s+id="pel-panel-akun"/',
            $isi
        );
    }

    // -------------------------------------------- berkas unduhan jujur soal saringan

    #[Test]
    public function pdf_menyebut_saringan_dari_ubin_ringkasan(): void
    {
        /*
         * Templat PDF-nya sendiri sudah memperingatkan: "berkas berisi 12 baris
         * tidak bisa dibedakan dari daftar yang memang cuma punya 12
         * pelanggan." Sesudah dua saringan dari ubin ditambahkan tanpa
         * memperbarui ringkasannya, berkas berisi 29 dari 101 pelanggan tetap
         * mencetak "Tanpa saringan".
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $metode = new \ReflectionMethod(
            \App\Http\Controllers\account\CustomerController::class,
            'ringkasanSaringan'
        );
        $metode->setAccessible(true);

        $pengendali = new \App\Http\Controllers\account\CustomerController();

        $ringkas = $metode->invoke(
            $pengendali,
            \Illuminate\Http\Request::create('/', 'GET', ['pesanan' => 'ada']),
            '', null, null
        );

        $this->assertArrayHasKey('Pesanan', $ringkas);
        $this->assertSame('Pernah memesan', $ringkas['Pesanan']);

        $ringkas = $metode->invoke(
            $pengendali,
            \Illuminate\Http\Request::create('/', 'GET', ['baru' => '30']),
            '', null, null
        );

        $this->assertArrayHasKey('Bergabung', $ringkas);
    }

    #[Test]
    public function lembar_kerja_memuat_keterangan_saringannya(): void
    {
        // Lembar kerja justru berkas yang paling sering diteruskan ke orang
        // lain, terlepas dari layar tempat ia diunduh.
        $ekspor = new \App\Exports\PelangganExport(collect(), [], ['Pesanan' => 'Pernah memesan']);

        $this->assertNotEmpty($ekspor->registerEvents());
        $this->assertArrayHasKey(\Maatwebsite\Excel\Events\AfterSheet::class, $ekspor->registerEvents());
    }

    // --------------------------------------------------- surat untuk pelanggan

    #[Test]
    public function administrator_bisa_mengirim_ulang_tautan_verifikasi(): void
    {
        Mail::fake();
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        $pelanggan->forceFill(['email_verified_at' => null])->save();
        RateLimiter::clear('verifikasi|pelanggan|' . $pelanggan->getKey());

        $this->actingAs($admin)
            ->postJson(route('account.customer.kirim.verifikasi', $pelanggan->uuid))
            ->assertOk()
            ->assertJson(['success' => true]);

        Mail::assertSent(VerifikasiEmailMail::class, fn ($s) => $s->hasTo($pelanggan->email));
    }

    #[Test]
    public function tautan_verifikasi_tidak_dikirim_ke_alamat_yang_terpotong(): void
    {
        /*
         * Lima alamat di data yang ada terpotong tepat di 30 huruf. Mengirim ke
         * sana hanya menghasilkan surat pantulan, sementara layarnya terlanjur
         * bilang "terkirim".
         */
        Mail::fake();
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        $pelanggan->forceFill(['email_verified_at' => null, 'email' => 'terpotong@mail.unnes.a'])->save();

        $this->actingAs($admin)
            ->postJson(route('account.customer.kirim.verifikasi', $pelanggan->uuid))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        Mail::assertNothingSent();
    }

    #[Test]
    public function yang_sudah_terverifikasi_tidak_dikirimi_tautan_lagi(): void
    {
        Mail::fake();
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        RateLimiter::clear('verifikasi|pelanggan|' . $pelanggan->getKey());

        $this->actingAs($admin)
            ->postJson(route('account.customer.kirim.verifikasi', $pelanggan->uuid))
            ->assertStatus(422);

        Mail::assertNothingSent();
    }

    #[Test]
    public function karyawan_tidak_boleh_mengirim_surat_ke_pelanggan(): void
    {
        Mail::fake();
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($karyawan)
            ->postJson(route('account.customer.kirim.verifikasi', $pelanggan->uuid))
            ->assertStatus(403);

        $this->actingAs($karyawan)
            ->postJson(route('account.customer.kirim.sandi', $pelanggan->uuid))
            ->assertStatus(403);

        Mail::assertNothingSent();
    }

    #[Test]
    public function pengiriman_surat_dibatasi_lajunya(): void
    {
        // Tanpa batas, satu layar yang terbuka cukup untuk membanjiri kotak
        // masuk seseorang — dan alamat pengirim kantor yang akan dianggap
        // pengirim sampah.
        Mail::fake();
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        $pelanggan->forceFill(['email_verified_at' => null])->save();
        RateLimiter::clear('verifikasi|pelanggan|' . $pelanggan->getKey());

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($admin)
                ->postJson(route('account.customer.kirim.verifikasi', $pelanggan->uuid))
                ->assertOk();
        }

        $this->actingAs($admin)
            ->postJson(route('account.customer.kirim.verifikasi', $pelanggan->uuid))
            ->assertStatus(429);
    }

    #[Test]
    public function tautan_atur_ulang_sandi_terkirim_dan_tercatat_di_jejak(): void
    {
        Notification::fake();
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        RateLimiter::clear('atur-ulang|pelanggan|' . $pelanggan->getKey());

        $this->actingAs($admin)
            ->postJson(route('account.customer.kirim.sandi', $pelanggan->uuid))
            ->assertOk()
            ->assertJson(['success' => true]);

        // Tindakan orang dalam atas akun orang lain harus meninggalkan jejak.
        $this->assertTrue(
            AktivitasMasuk::where('user_id', $pelanggan->getKey())
                ->where('alasan', 'like', '%atur ulang kata sandi%')
                ->exists()
        );
    }

    #[Test]
    public function unduhan_excel_tidak_membawa_kolom_rahasia(): void
    {
        /*
         * FromCollection akan menumpahkan seluruh kolom tabel users apa adanya,
         * termasuk sidik kata sandi dan token ingat-saya. Kepala kolomnya
         * ditulis sendiri justru untuk itu.
         */
        $kepala = (new \App\Exports\PelangganExport(collect(), []))->headings();

        foreach (['password', 'remember_token', 'pin', 'token'] as $rahasia) {
            foreach ($kepala as $kolom) {
                $this->assertStringNotContainsStringIgnoringCase($rahasia, $kolom);
            }
        }
    }

    #[Test]
    public function tombol_unduh_pelanggan_membawa_SELURUH_saringannya(): void
    {
        /*
         * Diperiksa dari TAUTAN DI HALAMANNYA, bukan dari peladennya.
         *
         * Peladennya sudah menghormati kelima saringan sejak awal, dan kepala
         * berkasnya pun sudah menyebut semuanya. Yang tidak terjaga: apakah
         * tombol unduhnya benar-benar MENGIRIMKANNYA. Terukur sebelum
         * perbaikan, 'pesanan' dan 'baru' tidak ada di tautannya — jadi
         * menekan ubin "Pernah memesan" lalu Unduh Excel memulangkan SELURUH
         * pelanggan, tanpa galat, dan kepala berkasnya ikut tidak menyebut
         * saringan itu karena ia memang tidak pernah sampai.
         *
         * Dituntut SETIAP medan, bukan kedua nama itu saja: saringan
         * berikutnya akan jatuh ke lubang yang sama.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $saringan = [
            'cari' => 'Budi',
            'status' => 'aktif',
            'verifikasi' => 'sudah',
            'pesanan' => 'ada',
            'baru' => '30',
        ];

        $isi = $this->actingAs($admin)
            ->get(route('account.customer.index', $saringan))
            ->assertOk()
            ->getContent();

        $jalur = [
            'excel' => parse_url(route('account.customer.ekspor.excel'), PHP_URL_PATH),
            'pdf' => parse_url(route('account.customer.ekspor'), PHP_URL_PATH),
        ];

        foreach ($jalur as $bentuk => $alamat) {
            // Jalurnya dicari di mana saja di dalam hrefnya: route()
            // memulangkan alamat penuh berikut hostnya.
            $ada = preg_match(
                '#href="([^"]*' . preg_quote((string) $alamat, '#') . '[^"]*)"#',
                $isi,
                $cocok
            );

            $this->assertSame(1, $ada, 'Tombol Unduh ' . $bentuk . ' tidak ditemukan.');

            // Dibongkar jadi larik, bukan dicari sebagai untaian: tanda & di
            // markah tertulis &amp; dan urutan medannya tidak dijamin.
            parse_str((string) parse_url(html_entity_decode($cocok[1]), PHP_URL_QUERY), $medan);

            foreach ($saringan as $nama => $nilai) {
                $this->assertSame(
                    $nilai,
                    $medan[$nama] ?? null,
                    'Tombol Unduh ' . $bentuk . ' tidak membawa saringan "' . $nama
                        . '", jadi berkasnya berisi baris yang tidak terlihat di layar.'
                );
            }
        }
    }

    #[Test]
    public function mengurutkan_kolom_pelanggan_tidak_melepaskan_saringannya(): void
    {
        // Lubang yang sama lewat pintu lain: keempat kepala kolom pengurut
        // memakai daftar bawaan yang itu juga.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        /*
         * Barisnya dibuat sendiri, tidak mengandalkan data yang ada.
         *
         * Kepala kolom pengurut hanya ada kalau TABELNYA dirender; saat
         * saringannya tidak menyisakan siapa pun, layarnya menampilkan keadaan
         * kosong dan tidak ada satu pun tautan pengurut. Terukur: "pernah
         * memesan" + "bergabung 30 hari terakhir" menyisakan nol dari 101
         * pelanggan, jadi ujinya dulu gagal dengan alasan yang salah —
         * mengabarkan kepala kolomnya hilang, padahal yang hilang barisnya.
         *
         * Satu pelanggan yang bergabung hari ini, berikut satu pendaftaran
         * beremail sama supaya ia terhitung "pernah memesan" — penautannya
         * lewat nomor, lalu email, lalu nama.
         */
        $tanda = substr(preg_replace('/[^A-Za-z]/', '', Str::random(40)) ?? '', 0, 10);
        $surel = strtolower($tanda) . '@contoh.test';

        $this->akun(User::PERAN_PELANGGAN, [
            'full_name' => 'Pelanggan ' . $tanda,
            'email' => $surel,
        ]);

        \App\PendaftaranScopusCamp::create([
            'id_transaksi' => 'UJI-URUT-' . $tanda,
            'kategori_id' => \App\KategoriLayanan::where('layanan', 'scopus_camp')->value('id'),
            'nama' => 'Pelanggan ' . $tanda,
            'email' => $surel,
            'telp' => '081200000000',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => 100000,
            'cara_bayar' => 'transfer',
            'status' => 'diproses',
        ]);

        $isi = $this->actingAs($admin)
            ->get(route('account.customer.index', ['pesanan' => 'ada', 'baru' => '30']))
            ->assertOk()
            ->getContent();

        preg_match('/href="([^"]*urut=nama[^"]*)"/', $isi, $cocok);

        $this->assertNotEmpty($cocok, 'Kepala kolom Pelanggan harus berupa tautan pengurut.');

        parse_str((string) parse_url(html_entity_decode($cocok[1]), PHP_URL_QUERY), $medan);

        $this->assertSame('ada', $medan['pesanan'] ?? null,
            'Mengurutkan kolom melepaskan saringan "pernah memesan".');
        $this->assertSame('30', $medan['baru'] ?? null,
            'Mengurutkan kolom melepaskan saringan "30 hari terakhir".');
    }

    #[Test]
    public function setiap_saringan_pelanggan_ikut_ke_KEDUA_unduhan(): void
    {
        /*
         * Sama seperti di layar Pendaftar Layanan: jumlah baris di daftar, di
         * lembar kerja, dan di PDF diadu bertiga untuk SETIAP saringan.
         *
         * Layar ini punya lima saringan, dan dua di antaranya — "pernah
         * memesan" dan "30 hari terakhir" — memang pernah tidak sampai ke
         * unduhannya sama sekali. Menambal keduanya saja tidak cukup: yang
         * dijaga di sini kelimanya, supaya saringan berikutnya tidak jatuh ke
         * lubang yang sama.
         *
         * Yang dituntut KESAMAAN, bukan penyusutan: saringan yang kebetulan
         * mencakup semua pelanggan memang harus memulangkan semuanya, dan
         * uji yang menuntut penyusutan akan merah karena datanya.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $kasus = [
            'cari' => ['cari' => 'a'],
            'status aktif' => ['status' => 'aktif'],
            'status nonaktif' => ['status' => 'nonaktif'],
            'verifikasi sudah' => ['verifikasi' => 'sudah'],
            'verifikasi belum' => ['verifikasi' => 'belum'],
            'pernah memesan' => ['pesanan' => 'ada'],
            'bergabung 30 hari' => ['baru' => '30'],
            'gabungan' => ['status' => 'aktif', 'pesanan' => 'ada', 'baru' => '30'],
        ];

        foreach ($kasus as $nama => $q) {
            $diDaftar = $this->actingAs($admin)
                ->get(route('account.customer.index', $q))
                ->assertOk()
                ->viewData('pelanggan')
                ->total();

            $diExcel = $this->barisLembarKerjaPelanggan($admin, $q);
            $diPdf = $this->barisPdfPelanggan($admin, $q);

            $this->assertSame($diDaftar, $diExcel,
                'Saringan "' . $nama . '": daftar ' . $diDaftar . ' baris, lembar kerja '
                    . $diExcel . '. Berkasnya berisi pelanggan yang tidak terlihat di layar.');

            $this->assertSame($diDaftar, $diPdf,
                'Saringan "' . $nama . '": daftar ' . $diDaftar . ' baris, PDF ' . $diPdf . '.');
        }
    }

    /**
     * Jumlah baris lembar kerja pelanggan untuk satu kumpulan saringan.
     *
     * Namanya dicocokkan lewat pola, bukan nama persis: berkasnya berpenanda
     * waktu sampai DETIK, jadi nama yang dirakit ulang di dalam uji bisa
     * meleset satu detik dan ujinya merah tanpa ada yang rusak.
     */
    private function barisLembarKerjaPelanggan(User $admin, array $q): int
    {
        Excel::fake();
        Excel::matchByRegex();

        $this->actingAs($admin)
            ->get(route('account.customer.ekspor.excel', $q))
            ->assertOk();

        $jumlah = -1;

        Excel::assertDownloaded('/^data-pelanggan-\d{8}-\d{6}\.xlsx$/',
            function ($ekspor) use (&$jumlah) {
                $jumlah = count($ekspor->array());

                return true;
            });

        return $jumlah;
    }

    /** Jumlah baris PDF pelanggan, ditangkap sebelum Dompdf merakitnya. */
    private function barisPdfPelanggan(User $admin, array $q): int
    {
        $terkumpul = null;

        \Illuminate\Support\Facades\View::composer(
            'account.customer.ekspor-pdf',
            function ($view) use (&$terkumpul) {
                $terkumpul = $view->getData();
            }
        );

        $this->actingAs($admin)
            ->get(route('account.customer.ekspor', $q))
            ->assertOk();

        $this->assertNotNull($terkumpul, 'Templat PDF pelanggan tidak pernah dirakit.');

        return $terkumpul['pelanggan']->count();
    }
}
