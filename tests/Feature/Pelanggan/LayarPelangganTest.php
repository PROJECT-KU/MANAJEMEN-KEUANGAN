<?php

namespace Tests\Feature\Pelanggan;

use App\AktivitasMasuk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $halaman->assertDontSee('Simpan perubahan');
        $halaman->assertDontSee('Simpan kontak');
        $halaman->assertDontSee('Simpan foto');

        // Isiannya dimatikan, bukan sekadar tombolnya disembunyikan: tanpa itu
        // isian masih bisa diisi dan dikirim lewat Enter.
        $this->assertStringContainsString('disabled', $halaman->getContent());
    }

    #[Test]
    public function administrator_tetap_mendapat_tombol_simpannya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($admin)
            ->get(route('account.customer.edit', $pelanggan->uuid))
            ->assertOk()
            ->assertSee('Simpan perubahan')
            ->assertSee('Simpan kontak')
            ->assertDontSee('Hanya administrator yang boleh mengubah data pelanggan.');
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
}
