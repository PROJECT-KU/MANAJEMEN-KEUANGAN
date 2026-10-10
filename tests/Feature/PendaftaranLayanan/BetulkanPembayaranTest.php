<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PembayaranPendaftaran;
use App\PendaftaranJejak;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Catatan pembayaran bisa DIBETULKAN, bukan cuma dihapus lalu dicatat ulang.
 *
 * Penghapusan meninggalkan jejak "Catatan pembayaran Rp X dihapus", yang
 * terbaca seperti pembatalan — dan yang membaca riwayatnya setengah tahun
 * kemudian tidak bisa membedakan koreksi salah ketik dari uang yang
 * benar-benar ditarik kembali.
 */
class BetulkanPembayaranTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function nominalnya_bisa_dibetulkan(): void
    {
        [$orang, $b, $bayar] = $this->dengan(2000000);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.pembayaran.ubah', $bayar->getKey()),
            ['nominal' => '2.500.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect();

        $this->assertSame(2500000, (int) $bayar->fresh()->nominal);
    }

    /**
     * Jejaknya menyebut KOREKSI, bukan penghapusan. Inilah bedanya dengan
     * hapus-lalu-catat-ulang, dan satu-satunya alasan fitur ini ada.
     */
    #[Test]
    public function jejaknya_menyebut_koreksi_bukan_pembatalan(): void
    {
        [$orang, $b, $bayar] = $this->dengan(2000000);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.pembayaran.ubah', $bayar->getKey()),
            ['nominal' => '2.500.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect();

        $jejak = PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'ubah-bayar')->first();

        $this->assertNotNull($jejak, 'Pembetulannya tidak tercatat di jejak.');
        $this->assertStringContainsString('dibetulkan jadi Rp 2.500.000', $jejak->kalimat);
        $this->assertStringContainsString('Rp 2.000.000', $jejak->kalimat,
            'Angka lamanya tidak disebut, jadi tidak ada yang tahu apa yang berubah.');

        $this->assertSame(0, PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'hapus-bayar')->count(),
            'Pembetulan tercatat sebagai penghapusan — persis yang mau dihindari.');
    }

    /**
     * Membetulkan tanggal atau catatan TIDAK menulis jejak uang: jejak
     * "Rp 2.000.000 -> Rp 2.000.000" cuma memanjangkan riwayat tanpa
     * memberi tahu apa pun.
     */
    #[Test]
    public function membetulkan_tanggal_saja_tidak_menulis_jejak_uang(): void
    {
        [$orang, $b, $bayar] = $this->dengan(2000000);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.pembayaran.ubah', $bayar->getKey()),
            ['nominal' => '2.000.000', 'tanggal' => now()->subDays(3)->toDateString(),
                'catatan' => 'transfer BCA']
        )->assertRedirect();

        $segar = $bayar->fresh();

        $this->assertSame('transfer BCA', $segar->catatan);
        $this->assertSame(now()->subDays(3)->toDateString(), $segar->tanggal->toDateString());

        $this->assertSame(0, PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'ubah-bayar')->count());
    }

    /**
     * Bukti lamanya TETAP kalau isiannya dikosongkan. Membuang bukti
     * transfer lewat borang yang niatnya membetulkan angka adalah
     * kehilangan yang tidak disengaja siapa pun.
     */
    #[Test]
    public function bukti_lamanya_tidak_hilang_saat_tidak_diganti(): void
    {
        [$orang, $b, $bayar] = $this->dengan(2000000);

        $bayar->forceFill(['bukti' => 'bukti-lama.webp'])->save();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.pembayaran.ubah', $bayar->getKey()),
            ['nominal' => '2.100.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect();

        $this->assertSame('bukti-lama.webp', $bayar->fresh()->bukti);
    }

    #[Test]
    public function tanggal_di_masa_depan_ditolak(): void
    {
        [$orang, $b, $bayar] = $this->dengan(2000000);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.pembayaran.ubah', $bayar->getKey()),
            ['nominal' => '2.100.000', 'tanggal' => now()->addDay()->toDateString()]
        )->assertSessionHasErrors('tanggal');

        $this->assertSame(2000000, (int) $bayar->fresh()->nominal);
    }

    #[Test]
    public function borangnya_ada_di_layar_dan_tertutup_dulu(): void
    {
        [$orang, $b, $bayar] = $this->dengan(2000000);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('rin-betul-' . $bayar->getKey(), $isi,
            'Borang pembetulnya tidak tergambar.');

        $this->assertMatchesRegularExpression(
            '/<form[^>]*class="rin-betul"[^>]*hidden/s', $isi,
            'Borangnya terbuka sejak awal; tiga termin berarti tiga layar penuh.'
        );
    }

    #[Test]
    public function pelanggan_tidak_boleh_membetulkan(): void
    {
        [$orang, $b, $bayar] = $this->dengan(2000000);

        $pelanggan = User::create([
            'full_name' => 'Pelanggan Uji', 'username' => 'plg_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $pelanggan->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        $this->actingAs($pelanggan)->put(
            route('account.pendaftaran-layanan.pembayaran.ubah', $bayar->getKey()),
            ['nominal' => '9.000.000', 'tanggal' => now()->toDateString()]
        );

        $this->assertSame(2000000, (int) $bayar->fresh()->nominal,
            'Pelanggan bisa mengubah angka pembayaran.');
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp, 2: PembayaranPendaftaran} */
    private function dengan(int $nominal): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'btl_' . $t,
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

        // Rombongan: termin memang hanya untuk pesanan beberapa orang.
        $b = PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '4',
            'total_pembayaran' => '8000000', 'status' => 'diproses',
        ]);

        $bayar = PembayaranPendaftaran::create([
            'jenis' => PembayaranPendaftaran::PENDAFTARAN,
            'induk_id' => (string) $b->getKey(),
            'layanan' => 'scopus_camp',
            'urutan' => 1, 'nominal' => $nominal, 'tanggal' => now()->toDateString(),
        ]);

        return [$u, $b, $bayar];
    }
}
