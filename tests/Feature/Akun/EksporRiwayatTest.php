<?php

namespace Tests\Feature\Akun;

use App\AktivitasMasuk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pemilik akun boleh menyimpan riwayat keamanannya sendiri — dan HANYA
 * miliknya sendiri.
 */
class EksporRiwayatTest extends TestCase
{
    use DatabaseTransactions;

    private function buatPengguna(string $level = 'karyawan'): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Ekspor',
            'username' => 'uji_ekspor_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => $level,
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    private function isi($jawaban): string
    {
        ob_start();
        $jawaban->sendContent();

        return ob_get_clean();
    }

    #[Test]
    public function riwayat_sendiri_bisa_diunduh_sebagai_csv(): void
    {
        $pengguna = $this->buatPengguna();
        AktivitasMasuk::catat($pengguna, $pengguna->username, true, 'masuk dengan PIN');
        AktivitasMasuk::catat($pengguna, $pengguna->username, false, 'kata sandi salah');

        $jawaban = $this->actingAs($pengguna)->get(route('account.profil.ekspor.riwayat'));
        $jawaban->assertOk();

        $this->assertStringContainsString('text/csv', $jawaban->headers->get('Content-Type'));

        $isi = $this->isi($jawaban->baseResponse);

        // fputcsv mengutip medan yang mengandung spasi, jadi "Alamat IP" berkutip.
        $this->assertStringContainsString('Waktu,Hasil,Keterangan,"Alamat IP",Perangkat', $isi);
        $this->assertStringContainsString('masuk dengan PIN', $isi);
        $this->assertStringContainsString('kata sandi salah', $isi);

        // BOM supaya Excel membaca huruf beraksen dengan benar.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $isi);
    }

    #[Test]
    public function riwayat_orang_lain_tidak_ikut_terbawa(): void
    {
        $saya = $this->buatPengguna();
        $orangLain = $this->buatPengguna();

        AktivitasMasuk::catat($saya, $saya->username, true, 'punya saya');
        AktivitasMasuk::catat($orangLain, $orangLain->username, true, 'punya orang lain');

        $isi = $this->isi($this->actingAs($saya)->get(route('account.profil.ekspor.riwayat'))->baseResponse);

        $this->assertStringContainsString('punya saya', $isi);
        $this->assertStringNotContainsString('punya orang lain', $isi);
    }

    #[Test]
    public function tamu_tidak_bisa_mengunduh_apa_pun(): void
    {
        $this->get(route('account.profil.ekspor.riwayat'))->assertRedirect();
    }

    #[Test]
    public function nama_perangkat_ditulis_terbaca_bukan_user_agent_mentah(): void
    {
        $pengguna = $this->buatPengguna();

        AktivitasMasuk::create([
            'user_id' => $pengguna->getKey(),
            'identitas' => $pengguna->username,
            'berhasil' => true,
            'alasan' => 'masuk',
            'ip' => '127.0.0.1',
            'peramban' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
            'perangkat' => null,
        ]);

        $isi = $this->isi($this->actingAs($pengguna)->get(route('account.profil.ekspor.riwayat'))->baseResponse);

        $this->assertStringContainsString('Edge di Windows', $isi);
        $this->assertStringNotContainsString('AppleWebKit', $isi);
    }
}
