<?php

namespace Tests\Feature\Akun;

use App\AktivitasMasuk;
use App\User;
use Dompdf\Dompdf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pemilik akun boleh menyimpan riwayat keamanannya sendiri — dan HANYA
 * miliknya sendiri.
 *
 * Berkasnya PDF, bukan CSV maupun xlsx: yang dibuka orang harus langsung
 * terlihat sebagai keluaran resmi MIS (berlogo, berkepala, bernomor halaman)
 * dan tidak berubah bentuk tergantung program pembukanya.
 */
class EksporRiwayatTest extends TestCase
{
    use DatabaseTransactions;

    private function buatPengguna(): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Ekspor',
            'username' => 'uji_ekspor_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'karyawan',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    /** HTML yang diserahkan ke Dompdf — isinya diperiksa di sini. */
    private function halamannya(User $pengguna): string
    {
        return view('account.profil.riwayat-keamanan-pdf', [
            'pengguna' => $pengguna,
            'baris' => AktivitasMasuk::where('user_id', $pengguna->getKey())->latest('id')->get(),
        ])->render();
    }

    /**
     * Isi teks PDF-nya, sudah dibongkar dari aliran terkompresi.
     *
     * Dompdf menulis teks per huruf dengan sandi dua bita (\x00H\x00a…), jadi
     * bita nolnya dibuang dulu supaya bisa dicocokkan seperti teks biasa.
     */
    private function teksPdf(string $pdf): string
    {
        $isi = '';

        foreach (preg_split('/stream\r?\n/', $pdf) as $bagian) {
            $mentah = @gzuncompress(explode('endstream', $bagian)[0]);

            if ($mentah !== false) {
                $isi .= str_replace("\x00", '', $mentah);
            }
        }

        return $isi;
    }

    private function jadikanPdf(string $html): string
    {
        $dompdf = new Dompdf();
        $pengaturan = $dompdf->getOptions();
        $pengaturan->setIsPhpEnabled(true);
        $pengaturan->setIsRemoteEnabled(false);
        $dompdf->setOptions($pengaturan);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    #[Test]
    public function logonya_disisipkan_sebagai_data_uri_bukan_ditautkan(): void
    {
        // Dompdf tidak mengambil berkas dari luar selama isRemoteEnabled mati,
        // jadi logo yang ditautkan lewat asset() akan hilang tanpa pesan galat.
        $halaman = $this->halamannya($this->buatPengguna());

        $this->assertStringContainsString('src="data:image/png;base64,', $halaman);
        $this->assertStringNotContainsString('assets/img/logo-email.png"', $halaman);
    }

    #[Test]
    public function kepala_berkas_menyebut_pemilik_dan_waktu_unduh(): void
    {
        $pengguna = $this->buatPengguna();

        $halaman = $this->halamannya($pengguna);

        $this->assertStringContainsString('Riwayat Keamanan Akun', $halaman);
        $this->assertStringContainsString($pengguna->username, $halaman);
        $this->assertStringContainsString(now()->format('d/m/Y'), $halaman);
    }

    #[Test]
    public function baris_gagal_diberi_lencana_yang_berbeda(): void
    {
        // Warnanya yang membuat baris gagal langsung terlihat saat berkasnya
        // dibuka, dan itu alasan utama orang mengunduhnya.
        $pengguna = $this->buatPengguna();
        AktivitasMasuk::catat($pengguna, $pengguna->username, false, 'kata sandi salah');
        AktivitasMasuk::catat($pengguna, $pengguna->username, true, 'masuk dengan PIN');

        $halaman = $this->halamannya($pengguna);

        $this->assertStringContainsString('lencana-gagal', $halaman);
        $this->assertStringContainsString('lencana-berhasil', $halaman);
    }

    #[Test]
    public function tabelnya_punya_kepala_kolom_yang_lengkap(): void
    {
        $pengguna = $this->buatPengguna();
        AktivitasMasuk::catat($pengguna, $pengguna->username, true, 'masuk');

        $halaman = $this->halamannya($pengguna);

        foreach (['Waktu', 'Hasil', 'Keterangan', 'Alamat IP', 'Perangkat'] as $kolom) {
            $this->assertStringContainsString('>' . $kolom . '<', $halaman);
        }
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

        $halaman = $this->halamannya($pengguna);

        $this->assertStringContainsString('Edge di Windows', $halaman);
        $this->assertStringNotContainsString('AppleWebKit', $halaman);
    }

    #[Test]
    public function riwayat_orang_lain_tidak_ikut_terbawa(): void
    {
        $saya = $this->buatPengguna();
        $orangLain = $this->buatPengguna();

        AktivitasMasuk::catat($saya, $saya->username, true, 'punya saya');
        AktivitasMasuk::catat($orangLain, $orangLain->username, true, 'punya orang lain');

        $halaman = $this->halamannya($saya);

        $this->assertStringContainsString('punya saya', $halaman);
        $this->assertStringNotContainsString('punya orang lain', $halaman);
    }

    #[Test]
    public function tiap_halaman_bernomor_dan_jumlahnya_benar(): void
    {
        // Penomorannya digambar skrip Dompdf, bukan ditulis Blade: lebar
        // pengukurnya pernah salah sehingga tulisannya terlempar ke kiri dan
        // menimpa kalimat kaki. Karena itu diuji dari isi PDF-nya langsung.
        $pengguna = $this->buatPengguna();

        for ($i = 0; $i < 80; $i++) {
            AktivitasMasuk::catat($pengguna, $pengguna->username, $i % 4 !== 0, 'baris ke-' . $i);
        }

        $teks = $this->teksPdf($this->jadikanPdf($this->halamannya($pengguna)));

        preg_match_all('/Halaman (\d+) dari (\d+)/', $teks, $cocok);

        $this->assertNotEmpty($cocok[0], 'PDF-nya harus memuat nomor halaman.');

        $jumlah = (int) $cocok[2][0];

        $this->assertGreaterThan(1, $jumlah, '80 baris harus memenuhi lebih dari satu halaman.');
        $this->assertSame(range(1, $jumlah), array_map('intval', $cocok[1]));
    }

    #[Test]
    public function unduhannya_berupa_pdf_dan_tamu_ditolak(): void
    {
        $pengguna = $this->buatPengguna();

        $jawaban = $this->actingAs($pengguna)->get(route('account.profil.ekspor.riwayat'));

        $jawaban->assertOk();
        $this->assertStringContainsString(
            '.pdf',
            (string) $jawaban->headers->get('Content-Disposition')
        );
        $this->assertStringStartsWith('%PDF', $jawaban->getContent());

        auth()->logout();
        $this->get(route('account.profil.ekspor.riwayat'))->assertRedirect();
    }
}
