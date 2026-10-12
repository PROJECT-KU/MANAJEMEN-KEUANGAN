<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\PendaftaranDihapus;
use App\Support\PotretPendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Potret penghapusan DIBACAKAN, bukan dicetak mentah.
 *
 * Isinya tersimpan apa adanya dari toArray() — 22 kolom berisi token, UUID,
 * dan created_at. Dicetak mentah ke layar, yang terpampang JSON di kotak
 * hitam: format untuk pengembang, sementara yang membukanya panitia yang
 * sedang menelusuri ke mana perginya satu pendaftaran.
 *
 * Datanya sendiri tidak boleh berubah sedikit pun: PulihkanPendaftaran
 * membaca potret yang sama untuk menyusun ulang barisnya, dan tidak ada
 * salinan lain.
 */
class PotretTerbacaTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Kolomnya dibaca lewat PETA KOLOM tiap layanan, bukan nama kolom
     * Scopus Camp yang dipukul rata.
     *
     * Clinik Scopus menyimpan nama penanya di `nama_pemesan` dan teleponnya
     * di `telp_pemesan`. Dibaca dengan nama kolom Scopus Camp, kedua
     * keterangan itu hilang diam-diam dari panelnya — tanpa galat, cuma
     * baris yang tidak pernah muncul.
     */
    #[Test]
    public function kolomnya_dibaca_lewat_peta_tiap_layanan(): void
    {
        $arsip = $this->arsip('clinik_scopus', [
            'nama_pemesan' => 'Bu Sulastri',
            'telp_pemesan' => '081220177011',
            'afiliasi_pemesan' => 'RSUD Kota Mataram',
            'kode_unik' => '85',
            'cara_bayar' => 'transfer',
        ]);

        $pokok = PotretPendaftaran::pokok($arsip);

        $this->assertArrayHasKey('Telepon', $pokok,
            'Telepon tidak terbaca; petanya dipukul rata dengan nama kolom layanan lain.');
        $this->assertSame('081220177011', $pokok['Telepon']);
        $this->assertSame('RSUD Kota Mataram', $pokok['Afiliasi']);
        $this->assertSame('85', $pokok['Kode unik']);
        $this->assertSame('Transfer bank', $pokok['Cara bayar']);
    }

    /** Tanggal tanpa jam tidak dicetak berjam 00:00. */
    #[Test]
    public function tanggal_tanpa_jam_tidak_berjam_nol(): void
    {
        $arsip = $this->arsip('scopus_camp', ['nama' => 'Uji'], [
            'pembayaran' => [
                ['urutan' => 1, 'nominal' => 2000000, 'tanggal' => '2026-08-20'],
            ],
            'jejak' => [
                ['aksi' => 'status', 'kalimat' => 'Status diubah', 'waktu' => '2026-09-02 10:14:00'],
            ],
        ]);

        $termin = PotretPendaftaran::pembayaran($arsip);

        $this->assertStringContainsString('20 Agt 2026', $termin[0]['label']);
        $this->assertStringNotContainsString('00:00', $termin[0]['label'],
            'Tanggal termin dicetak berjam tengah malam, padahal jamnya memang '
            . 'tidak pernah ada.');

        // Yang MEMANG berjam tetap menyebut jamnya.
        $this->assertStringContainsString('10:14', PotretPendaftaran::jejak($arsip)[0]['waktu']);
    }

    /** Jejaknya terbaru di atas. */
    #[Test]
    public function jejaknya_terbaru_di_atas(): void
    {
        $arsip = $this->arsip('scopus_camp', ['nama' => 'Uji'], [
            'jejak' => [
                ['aksi' => 'a', 'kalimat' => 'Yang lama', 'waktu' => '2026-08-01 09:00:00'],
                ['aksi' => 'b', 'kalimat' => 'Yang baru', 'waktu' => '2026-09-01 09:00:00'],
            ],
        ]);

        $this->assertSame('Yang baru', PotretPendaftaran::jejak($arsip)[0]['kalimat']);
    }

    /**
     * Di LAYARNYA: ringkasannya yang tampil lebih dulu, JSON mentahnya satu
     * lapis lebih dalam.
     */
    #[Test]
    public function layarnya_menampilkan_ringkasan_bukan_json_mentah(): void
    {
        $admin = $this->admin();
        $arsip = $this->arsip('scopus_camp', ['nama' => 'Uji Potret', 'kode_unik' => '77']);

        $isi = $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['cari' => $arsip->nomor]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Lihat rincian yang tersimpan', $isi);
        $this->assertStringContainsString('Kode unik', $isi,
            'Panelnya tidak membacakan keterangannya.');

        /*
         * JSON mentahnya TETAP ADA — yang membutuhkannya pengembang saat
         * pemulihan bermasalah — tetapi di balik pembuka kedua, bukan
         * sebagai isi utama.
         */
        $this->assertStringContainsString('Data mentahnya', $isi);
        $this->assertStringContainsString('thp-mentah', $isi);
    }

    /**
     * Sel keterangannya DIBATASI lebarnya, tidak melar mengikuti panel.
     *
     * Dengan 1fr, tiga lajur di layar lebar membuat tiap sel selebar 357px
     * sementara isinya cuma sekitar 150px — label dan nilainya jadi
     * pulau-pulau yang terpisah lautan kosong, dan mata harus melompat jauh
     * untuk membaca satu pasangan.
     *
     * Dijaga dari sumbernya: uji PHP tidak menjalankan CSS, dan 1fr yang
     * kembali tidak akan membuat apa pun merah selain mata yang membacanya.
     */
    #[Test]
    public function sel_keterangan_tidak_melar_mengikuti_lebar_panel(): void
    {
        $isi = file_get_contents(resource_path(
            'views/account/pendaftaran_layanan/terhapus.blade.php'
        ));

        $this->assertSame(1, preg_match(
            '/\.thp-pokok\s*\{[^}]*grid-template-columns:\s*repeat\(auto-fill,\s*minmax\(\d+px,\s*(\d+px|1fr)\)\)/s',
            $isi, $cocok
        ), 'Kisi keterangannya tidak ditemukan.');

        $this->assertNotSame('1fr', $cocok[1],
            'Sel keterangannya memakai 1fr, jadi ia melar memenuhi lebar panel '
            . 'berapa pun — label dan nilainya terpisah lautan kosong.');
    }

    // ------------------------------------------------------------- pembantu

    private function admin(): User
    {
        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Admin Uji ' . $t, 'username' => 'ptr_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        return $u;
    }

    private function arsip(string $layanan, array $baris, array $tambahan = []): PendaftaranDihapus
    {
        $t = Str::random(8);

        return PendaftaranDihapus::create(array_merge([
            'layanan' => $layanan,
            'pendaftaran_id' => (string) Str::uuid(),
            'nomor' => 'UJI-' . $t,
            'nama' => $baris['nama'] ?? ($baris['nama_pemesan'] ?? 'Tanpa nama'),
            'email' => 'uji@contoh.test',
            'status' => 'diproses',
            'total' => 5500000,
            'uang_terhapus' => 0,
            'jumlah_pembayaran' => 0,
            'jumlah_jejak' => 0,
            'potret' => array_merge(['pendaftaran' => $baris], $tambahan),
            'oleh_nama' => 'Panitia Uji',
        ], []));
    }
}
