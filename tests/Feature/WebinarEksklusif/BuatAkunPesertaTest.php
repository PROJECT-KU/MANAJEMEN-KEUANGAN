<?php

namespace Tests\Feature\WebinarEksklusif;

use App\Actions\Webinar\BuatAkunPeserta;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Akun peserta webinar yang dibuatkan sistem.
 *
 * Yang dijaga di sini hal-hal yang kalau rusak TIDAK mengeluarkan galat di
 * layar siapa pun — ia terjadi di dalam pemrosesan pembayaran, jauh dari
 * mata, dan baru terasa saat peserta mencoba masuk berminggu-minggu kemudian.
 */
class BuatAkunPesertaTest extends TestCase
{
    use DatabaseTransactions;

    private function pendaftaran(array $ganti = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'token' => Str::random(30),
            'nama' => 'Pemesan Uji',
            'email' => 'pemesan' . Str::random(8) . '@contoh.test',
            'telp' => '081234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => 100000,

            /*
             * 'pending', BUKAN 'paid'.
             *
             * Model ini punya kait yang menyiapkan akun begitu barisnya
             * lunas. Membuat umpan uji dalam keadaan lunas berarti akunnya
             * sudah dibuat SEBELUM aksi yang sedang diuji dipanggil — dan
             * ujinya lalu memeriksa hasil kaitnya, bukan hasil aksinya.
             * Kaitnya diuji terpisah di AkunDikirimSaatLunasTest.
             */
            'status' => 'pending',
        ], $ganti));
    }

    /**
     * Peserta kedua dan seterusnya, LEWAT RELASINYA.
     *
     * Bukan DB::table(): pesertanya ada di tabel BERSAMA semua layanan, dan
     * relasinya yang menyetel kolom `layanan` lewat withAttributes(). Menulis
     * langsung ke tabel berarti menguji tabel yang salah — dan tabel
     * webinar_eksklusif_peserta memang masih ada, nol baris, tidak pernah
     * ditulis dari mana pun.
     */
    private function peserta(WebinarEksklusifPendaftaran $p, string $nama, ?string $email, int $urutan = 1): void
    {
        $p->pesertaLain()->create([
            'urutan' => $urutan,
            'nama' => $nama,
            'email' => $email,
        ]);
    }

    #[Test]
    public function pemesan_dapat_akun_siap_pakai(): void
    {
        $p = $this->pendaftaran(['nama' => 'Dr. Siti Rahayu']);

        $hasil = (new BuatAkunPeserta)->untuk($p);

        $this->assertCount(1, $hasil);
        $this->assertTrue($hasil[0]['baru']);

        $akun = User::where('email', $p->email)->first();
        $this->assertNotNull($akun);

        /*
         * Keempatnya diperiksa karena masing-masing menutup pintu yang
         * berbeda, dan ketiadaannya sama-sama diam: tanpa status active dan
         * email_verified_at, akun yang baru saja dikirimkan justru ditolak
         * middleware di pintu masuk.
         */
        $this->assertSame('active', $akun->status);
        $this->assertNotNull($akun->email_verified_at);
        $this->assertSame(User::PERAN_PELANGGAN, $akun->peran);
        $this->assertTrue(Hash::check(BuatAkunPeserta::SANDI_BAWAAN, $akun->password));
    }

    #[Test]
    public function akun_baru_ditandai_wajib_ganti_sandi(): void
    {
        $p = $this->pendaftaran();

        (new BuatAkunPeserta)->untuk($p);

        /*
         * Inilah yang menutup lubang sandi bersama: selama penanda ini
         * menyala, akunnya tidak bisa dipakai ke mana-mana selain halaman
         * ganti sandi. Tanpa penanda ini, nama orang lain adalah kunci akun
         * orang lain — sebab MIS masuk lewat username.
         */
        $this->assertTrue((bool) User::where('email', $p->email)->first()->wajib_ganti_sandi);
    }

    #[Test]
    public function semua_peserta_dapat_akunnya_sendiri(): void
    {
        $p = $this->pendaftaran(['nama' => 'Pemesan Kampus', 'jumlah_pendaftar' => 3]);
        $e2 = 'rekan2' . Str::random(6) . '@contoh.test';
        $e3 = 'rekan3' . Str::random(6) . '@contoh.test';
        $this->peserta($p, 'Rekan Kedua', $e2, 1);
        $this->peserta($p, 'Rekan Ketiga', $e3, 2);

        $hasil = (new BuatAkunPeserta)->untuk($p->fresh());

        $this->assertCount(3, $hasil,
            'Tiap peserta beremail harus punya akunnya sendiri; kalau tidak, '
            . 'sandi satu akun beredar di beberapa tangan.');

        foreach ([$p->email, $e2, $e3] as $email) {
            $this->assertNotNull(User::where('email', $email)->first(), $email);
        }
    }

    #[Test]
    public function peserta_tanpa_email_dilewati_bukan_dikarang(): void
    {
        $p = $this->pendaftaran(['jumlah_pendaftar' => 2]);
        $this->peserta($p, 'Rekan Tanpa Email', null, 1);

        $hasil = (new BuatAkunPeserta)->untuk($p->fresh());

        /*
         * Akun tanpa alamat tidak bisa dikirimi apa pun, tidak bisa
         * dipulihkan sandinya, dan tidak akan pernah dipakai siapa pun.
         */
        $this->assertCount(1, $hasil);

        // strtolower(): emailnya dinormalkan saat dicocokkan, supaya
        // "Budi@X.com" dan "budi@x.com" tidak jadi dua akun.
        $this->assertSame(strtolower($p->email), $hasil[0]['email']);
    }

    #[Test]
    public function email_yang_sudah_punya_akun_tidak_disetel_ulang_sandinya(): void
    {
        $email = 'lama' . Str::random(8) . '@contoh.test';

        $lama = User::create([
            'full_name' => 'Peserta Lama', 'username' => 'lama_' . Str::random(8),
            'email' => $email, 'password' => Hash::make('SandiPilihanSendiri2026'),
            'level' => 'user',
        ]);
        $lama->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        $p = $this->pendaftaran(['email' => $email, 'nama' => 'Peserta Lama']);

        $hasil = (new BuatAkunPeserta)->untuk($p);

        $lama->refresh();

        /*
         * Orang yang sama mendaftar webinar kedua. Menyetel ulang sandinya
         * jadi bawaan akan MENGUNCI dia keluar dari akunnya sendiri — sandi
         * yang ia hafal tiba-tiba tidak berlaku, tanpa ada yang
         * memberitahunya.
         */
        $this->assertTrue(Hash::check('SandiPilihanSendiri2026', $lama->password),
            'Sandi akun yang sudah ada tidak boleh disentuh.');
        $this->assertFalse((bool) $lama->wajib_ganti_sandi,
            'Akun lama tidak boleh ikut ditahan di halaman ganti sandi.');
        $this->assertFalse($hasil[0]['baru']);
        $this->assertSame(1, User::where('email', $email)->count(),
            'Tidak boleh ada akun kedua dengan email yang sama.');
    }

    #[Test]
    public function dua_peserta_bernama_sama_tetap_dapat_akun(): void
    {
        $p1 = $this->pendaftaran(['nama' => 'Ahmad Fauzi']);
        $p2 = $this->pendaftaran(['nama' => 'Ahmad Fauzi']);

        (new BuatAkunPeserta)->untuk($p1);
        (new BuatAkunPeserta)->untuk($p2);

        $a = User::where('email', $p1->email)->first();
        $b = User::where('email', $p2->email)->first();

        /*
         * username UNIK dan dipakai sebagai kunci masuk. Tanpa penomoran,
         * peserta kedua gagal dibuatkan akun dengan galat kunci ganda — di
         * tengah pemrosesan pembayaran, sesudah uangnya masuk.
         */
        $this->assertNotNull($b, 'Peserta bernama sama harus tetap dapat akun.');
        $this->assertNotSame($a->username, $b->username);
    }

    #[Test]
    public function nama_tanpa_huruf_latin_tetap_menghasilkan_nama_pengguna(): void
    {
        $p = $this->pendaftaran(['nama' => '王小明']);

        (new BuatAkunPeserta)->untuk($p);

        $akun = User::where('email', $p->email)->first();

        // Slug kosong melanggar NOT NULL, dan gejalanya muncul jauh dari
        // sebabnya.
        $this->assertNotNull($akun);
        $this->assertNotSame('', trim((string) $akun->username));
    }

    #[Test]
    public function email_yang_sama_dua_kali_dalam_satu_pendaftaran_jadi_satu_akun(): void
    {
        $p = $this->pendaftaran(['jumlah_pendaftar' => 2]);
        $this->peserta($p, 'Pemesan Lagi', $p->email, 1);

        $hasil = (new BuatAkunPeserta)->untuk($p->fresh());

        $this->assertCount(1, $hasil);
        $this->assertSame(1, User::where('email', $p->email)->count());
    }

    #[Test]
    public function aman_dijalankan_dua_kali(): void
    {
        $p = $this->pendaftaran();

        (new BuatAkunPeserta)->untuk($p);
        $sandiAwal = User::where('email', $p->email)->first()->password;

        $hasil = (new BuatAkunPeserta)->untuk($p->fresh());

        /*
         * Gerbang pembayaran boleh mengirim pemberitahuan yang sama lebih
         * dari sekali. Jalan kedua tidak boleh membuat akun kedua, dan tidak
         * boleh menyetel ulang sandi yang mungkin sudah diganti pemiliknya.
         */
        $this->assertSame(1, User::where('email', $p->email)->count());
        $this->assertSame($sandiAwal, User::where('email', $p->email)->first()->password);
        $this->assertFalse($hasil[0]['baru']);
    }
}
