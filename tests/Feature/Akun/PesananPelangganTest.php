<?php

namespace Tests\Feature\Akun;

use App\Support\PesananPelanggan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penghubung pesanan pelanggan.
 *
 * Tiap layanan mencatat pemesannya dengan caranya sendiri — ada yang lewat
 * customer_id, ada yang cuma lewat alamat email. Yang dijaga di sini: kedua
 * cara itu ketemu, dan baris yang cocok lewat KEDUANYA tidak terhitung dua
 * kali.
 */
class PesananPelangganTest extends TestCase
{
    use DatabaseTransactions;

    private ?User $netral = null;

    /** Baris induk clinikscopus yang dipinjam untuk memenuhi kunci asing. */
    private function indukClinik(): string
    {
        $id = DB::table('clinikscopus')->value('id');

        if (! $id) {
            $this->markTestSkipped('Tidak ada baris clinikscopus untuk dipinjam sebagai induk.');
        }

        return (string) $id;
    }

    /** Akun pengisi kolom wajib yang tidak sedang diuji. */
    private function netral(): User
    {
        return $this->netral ??= $this->pelanggan('netral_' . uniqid() . '@contoh.test');
    }

    private function pelanggan(string $email): User
    {
        $u = User::create([
            'full_name' => 'Uji Pesanan',
            'username' => 'uji_pes_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('RahasiaUji2026'),
            'peran' => 'user',
            'level' => 'karyawan',
        ]);

        $u->forceFill(['status' => 'active'])->save();

        return $u->refresh();
    }

    /*
     * id-nya char(36) tanpa auto_increment — kunci utamanya UUID, jadi harus
     * diisi sendiri. customer_id juga NOT NULL, sehingga "tidak tertaut"
     * diwakili 0, bukan null.
     */
    private function pesananClinik(array $isi): void
    {
        DB::table('clinikscopus_pemesanan')->insert(array_merge([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            // Empat kolom ini NOT NULL tanpa nilai bawaan; yang tidak diuji
            // di sini diisi angka netral supaya barisnya sah.
            // clinikscopus_id menunjuk baris induk yang harus benar-benar ada.
            // Dipinjam dari data yang sudah ada — hanya dibaca, tidak diubah —
            // daripada merakit seluruh rantai induknya untuk satu uji.
            'clinikscopus_id' => $this->indukClinik(),
            // trainer_id dan customer_id punya kunci asing ke users, jadi
            // keduanya harus menunjuk akun yang benar-benar ada. Akun netral
            // dipakai untuk "bukan pelanggan yang sedang diuji".
            'trainer_id' => $this->netral()->getKey(),
            'customer_id' => $this->netral()->getKey(),
            'email_pemesan' => 'bukan-siapa-siapa@contoh.test',
            'kode_booking' => 'UJI-' . uniqid(),
            'total_pembayaran' => 100000,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ], $isi));
    }

    #[Test]
    public function pesanan_ketemu_lewat_customer_id(): void
    {
        $p = $this->pelanggan(uniqid() . '@contoh.test');
        $this->pesananClinik(['customer_id' => $p->getKey(), 'email_pemesan' => 'lain@contoh.test']);

        $this->assertCount(1, PesananPelanggan::untuk($p));
        $this->assertSame(1, PesananPelanggan::ringkas(collect([$p]))[$p->id]['jumlah']);
    }

    #[Test]
    public function pesanan_ketemu_lewat_alamat_email(): void
    {
        $email = uniqid() . '@contoh.test';
        $p = $this->pelanggan($email);
        // Huruf besar sengaja: pencocokannya harus tidak peduli besar-kecil.
        $this->pesananClinik(['email_pemesan' => mb_strtoupper($email)]);

        $this->assertCount(1, PesananPelanggan::untuk($p));
    }

    #[Test]
    public function cocok_lewat_dua_cara_tidak_terhitung_dua_kali(): void
    {
        // Inti berkas ini. Satu baris yang customer_id-nya benar DAN email
        // pemesannya sama pernah terhitung dua kali, sehingga jumlah pesanan
        // di daftar tampil dobel.
        $email = uniqid() . '@contoh.test';
        $p = $this->pelanggan($email);
        $this->pesananClinik(['customer_id' => $p->getKey(), 'email_pemesan' => $email]);

        $this->assertCount(1, PesananPelanggan::untuk($p));
        $this->assertSame(1, PesananPelanggan::ringkas(collect([$p]))[$p->id]['jumlah']);
    }

    #[Test]
    public function pesanan_orang_lain_tidak_ikut(): void
    {
        $saya = $this->pelanggan(uniqid() . '@contoh.test');
        $lain = $this->pelanggan(uniqid() . '@contoh.test');
        $this->pesananClinik(['customer_id' => $lain->getKey(), 'email_pemesan' => $lain->email]);

        $this->assertCount(0, PesananPelanggan::untuk($saya));
        $this->assertArrayNotHasKey($saya->id, PesananPelanggan::ringkas(collect([$saya])));
    }

    #[Test]
    public function pelanggan_tanpa_email_tidak_mewarisi_pesanan_orang_lain(): void
    {
        // Tanpa penjagaan ini, LOWER('') = LOWER('') membuat setiap akun
        // beremail kosong mengambil pesanan akun beremail kosong lainnya.
        $kosong = $this->pelanggan(uniqid() . '@contoh.test');
        $kosong->forceFill(['email' => ''])->save();
        $this->pesananClinik(['email_pemesan' => '']);

        $this->assertArrayNotHasKey($kosong->id, PesananPelanggan::ringkas(collect([$kosong->refresh()])));
    }

    #[Test]
    public function ringkas_memuat_tanggal_pesanan_terakhir(): void
    {
        $email = uniqid() . '@contoh.test';
        $p = $this->pelanggan($email);
        $this->pesananClinik(['customer_id' => $p->getKey(), 'created_at' => now()->subYear()]);
        $this->pesananClinik(['customer_id' => $p->getKey(), 'created_at' => now()->subDay()]);

        $r = PesananPelanggan::ringkas(collect([$p]))[$p->id];

        $this->assertSame(2, $r['jumlah']);
        $this->assertTrue($r['terakhir']->isSameDay(now()->subDay()));
    }
}
