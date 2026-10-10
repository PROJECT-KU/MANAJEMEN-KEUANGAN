<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PesanWaPendaftaran;
use App\Support\TautanWa;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tombol WhatsApp: endpointnya, dan isinya.
 *
 * Dua hal yang dijaga, dan keduanya gagal tanpa terlihat:
 *
 * 1. Lewat wa.me, emoji dan sebagian tanda baca sampai dalam keadaan rusak
 *    di WhatsApp Web maupun Desktop. Pengirimnya tidak pernah tahu — di
 *    layarnya sendiri teksnya benar.
 * 2. Pesan yang kosong memaksa panitia mengetik ulang nominal dan kode
 *    uniknya. Kode unik yang salah ketik membuat transfernya tidak bisa
 *    dicocokkan sama sekali, dan pendaftarnya tetap tercatat belum bayar
 *    meski uangnya sudah masuk.
 */
class TautanWaPendaftaranTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function endpointnya_api_whatsapp_bukan_wame(): void
    {
        [$orang, $b] = $this->dengan();

        foreach ([
            route('account.pendaftaran-layanan.index', ['cari' => $b->id_transaksi]),
            route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]),
        ] as $alamat) {
            $isi = $this->actingAs($orang)->get($alamat)->assertOk()->getContent();

            $this->assertStringContainsString('api.whatsapp.com/send?phone=62811', $isi,
                'Tautan WhatsApp tidak memakai endpoint api.whatsapp.com di ' . $alamat);

            $this->assertStringNotContainsString('wa.me/', $isi,
                'Masih ada tautan wa.me di ' . $alamat
                . '; emoji di pesannya akan sampai dalam keadaan rusak.');
        }
    }

    #[Test]
    public function pesannya_sudah_terisi_bukan_percakapan_kosong(): void
    {
        [$orang, $b] = $this->dengan();

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#api\.whatsapp\.com/send\?phone=\d+&amp;text=\S#', $isi,
            'Tautannya membuka percakapan KOSONG; panitia harus mengetik ulang '
            . 'nominal dan kode unik yang sudah tergambar di layar yang sama.');
    }

    /**
     * Spasi harus jadi %20, bukan '+'.
     *
     * urlencode() memakai '+', dan WhatsApp menampilkannya apa adanya —
     * pesan yang terkirim penuh tanda tambah di antara tiap kata. Itulah
     * sebabnya rawurlencode, dan beda keduanya cuma satu huruf di nama
     * fungsinya.
     */
    #[Test]
    public function teksnya_disandikan_mentah(): void
    {
        $tautan = TautanWa::kirim('0811-2233-4455', "Halo Budi 👋\nsatu dua");

        $this->assertStringContainsString('&text=', $tautan);
        $this->assertStringNotContainsString('+', $tautan, 'Spasinya jadi tanda tambah.');
        $this->assertStringContainsString('%F0%9F%91%8B', $tautan, 'Emojinya tidak tersandikan.');
        $this->assertStringContainsString('%0A', $tautan, 'Ganti barisnya hilang.');
    }

    #[Test]
    public function nominal_dan_kode_unik_ikut_di_pesannya(): void
    {
        $pesan = PesanWaPendaftaran::untuk((object) [
            'nama_orang' => 'Budi Santoso', 'nomor' => 'camp-001', 'total' => 5500750,
            'kode_unik' => 750, 'status' => 'diproses', 'layanan' => 'scopus_camp',
        ]);

        $this->assertStringContainsString('Budi Santoso', $pesan);
        $this->assertStringContainsString('CAMP-001', $pesan, 'Nomor pendaftarannya tidak disebut.');
        $this->assertStringContainsString('Rp 5.500.750', $pesan);
        $this->assertStringContainsString('persis', $pesan,
            'Peringatan jangan dibulatkan hilang, padahal justru kode unik '
            . 'yang paling sering salah ketik.');
    }

    /**
     * Nominal bulat TIDAK diberi peringatan pembulatan: tidak ada yang bisa
     * dibulatkan, dan kalimatnya cuma membingungkan.
     */
    #[Test]
    public function tanpa_kode_unik_tidak_ada_peringatan_pembulatan(): void
    {
        $pesan = PesanWaPendaftaran::untuk((object) [
            'nama_orang' => 'Budi', 'nomor' => 'camp-002', 'total' => 5500000,
            'kode_unik' => 0, 'status' => 'diproses', 'layanan' => 'scopus_camp',
        ]);

        $this->assertStringContainsString('Rp 5.500.000', $pesan);
        $this->assertStringNotContainsString('persis', $pesan);
    }

    #[Test]
    public function yang_sudah_lunas_tidak_ditagih_lewat_wa(): void
    {
        $lunas = Pendaftaran::statusLunas('scopus_camp');

        $pesan = PesanWaPendaftaran::untuk((object) [
            'nama_orang' => 'Budi', 'nomor' => 'camp-003', 'total' => 5500750,
            'kode_unik' => 750, 'status' => $lunas, 'layanan' => 'scopus_camp',
        ]);

        $this->assertStringNotContainsString('belum kami terima', $pesan,
            'Pendaftar yang sudah lunas ditagih lewat WhatsApp.');
        $this->assertStringContainsString('sudah kami terima', $pesan);
    }

    /**
     * Nomor di bawah sembilan angka bukan nomor telepon melainkan sisa
     * isian. Tautan ke sana membuka percakapan dengan nomor yang tidak ada,
     * dan panitia mengira pesannya terkirim.
     */
    #[Test]
    public function nomor_yang_tidak_masuk_akal_tidak_jadi_tautan(): void
    {
        $this->assertSame('', TautanWa::kirim('0812', 'halo'));
        $this->assertSame('', TautanWa::kirim('', 'halo'));
        $this->assertSame('', TautanWa::kirim(null, 'halo'));
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function dengan(): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'wa_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $b = PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Budi ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-2233-4455', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500750', 'kode_unik' => '750', 'status' => 'diproses',
        ]);

        return [$u, $b];
    }
}
