<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranJejak;
use App\PendaftaranPengembalian;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pengembalian dana punya ANGKANYA.
 *
 * Status "Dana dikembalikan" sudah ada sejak lama, tetapi nominalnya tidak
 * pernah tersimpan di mana pun — jadi pendaftaran yang sudah direfund tetap
 * terhitung penuh di ubin "uang masuk dari yang lunas", dan laporan kas
 * menyebut uang yang sudah tidak ada.
 */
class PengembalianDanaTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function panitia_bisa_mencatat_pengembalian(): void
    {
        [$orang, $baris] = $this->pendaftaran(5500000);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pengembalian', ['scopus_camp', $baris->getKey()]),
            ['nominal' => '1.500.000', 'tanggal' => now()->toDateString(),
             'cara' => 'transfer', 'catatan' => 'transfer balik ke rekening pendaftar']
        )->assertRedirect()->assertSessionHasNoErrors();

        $satu = PendaftaranPengembalian::milik('scopus_camp', (string) $baris->getKey())->firstOrFail();

        // Pemisah ribuannya dibuang, seperti seluruh nominal di layar ini.
        $this->assertSame(1500000, (int) $satu->nominal);
        $this->assertSame('transfer', $satu->cara);
        $this->assertSame('Rp 1.500.000', $satu->nominal_tulis);
    }

    #[Test]
    public function boleh_dikembalikan_bertahap(): void
    {
        /*
         * Refund sebagian lalu sebagian lagi memang terjadi. Satu baris per
         * pendaftaran akan menimpa catatan pertama tanpa jejak — itu sebabnya
         * pengembaliannya tinggal di tabelnya sendiri.
         */
        [$orang, $baris] = $this->pendaftaran(5500000);

        foreach (['2.000.000', '1.000.000'] as $nominal) {
            $this->actingAs($orang)->post(
                route('account.pendaftaran-layanan.pengembalian', ['scopus_camp', $baris->getKey()]),
                ['nominal' => $nominal, 'tanggal' => now()->toDateString()]
            )->assertRedirect()->assertSessionHasNoErrors();
        }

        $this->assertSame(2,
            PendaftaranPengembalian::milik('scopus_camp', (string) $baris->getKey())->count());

        $this->assertSame(3000000,
            PendaftaranPengembalian::totalMilik('scopus_camp', (string) $baris->getKey()));
    }

    #[Test]
    public function tidak_boleh_melebihi_yang_dibayar(): void
    {
        /*
         * Refund yang lebih besar daripada tagihannya membuat ringkasan uang
         * masuk MINUS, dan tidak ada satu pun layar yang tahu harus berbuat
         * apa dengan angka itu.
         */
        [$orang, $baris] = $this->pendaftaran(1000000);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pengembalian', ['scopus_camp', $baris->getKey()]),
            ['nominal' => '900.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect();

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pengembalian', ['scopus_camp', $baris->getKey()]),
            ['nominal' => '900.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect()->assertSessionHas('error');

        $this->assertSame(900000,
            PendaftaranPengembalian::totalMilik('scopus_camp', (string) $baris->getKey()),
            'Pengembalian yang melebihi tagihannya tetap tersimpan.');
    }

    #[Test]
    public function tanggal_di_masa_depan_ditolak(): void
    {
        [$orang, $baris] = $this->pendaftaran(5500000);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pengembalian', ['scopus_camp', $baris->getKey()]),
            ['nominal' => '100.000', 'tanggal' => now()->addDay()->toDateString()]
        )->assertSessionHasErrors('tanggal');

        $this->assertSame(0,
            PendaftaranPengembalian::milik('scopus_camp', (string) $baris->getKey())->count());
    }

    #[Test]
    public function ubin_uang_masuk_dikurangi_refundnya(): void
    {
        /*
         * Inti seluruh pekerjaannya. Sebelum ini ubinnya tidak pernah
         * dikurangi sepeser pun oleh refund.
         */
        [$orang, $baris] = $this->pendaftaran(5500000, 'Pendaftaran Diterima');

        $sebelum = $this->angkaUangMasuk($orang);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pengembalian', ['scopus_camp', $baris->getKey()]),
            ['nominal' => '1.500.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect();

        $sesudah = $this->angkaUangMasuk($orang);

        $this->assertSame($sebelum - 1500000, $sesudah,
            'Ubin uang masuk tidak dikurangi pengembaliannya.');
    }

    #[Test]
    public function jumlah_yang_dikembalikan_disebut_terpisah_di_layar(): void
    {
        /*
         * Angka yang menyusut tanpa keterangan membuat panitia mengira ada
         * pembayaran yang hilang; dengan barisnya, ia tahu persis apa yang
         * terjadi.
         */
        [$orang, $baris] = $this->pendaftaran(5500000, 'Pendaftaran Diterima');

        PendaftaranPengembalian::create([
            'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $baris->getKey(),
            'nominal' => 750000, 'tanggal' => now()->toDateString(),
        ]);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index'))->assertOk()->getContent();

        $this->assertStringContainsString('sudah dikurangi Rp 750.000 yang dikembalikan', $isi);
    }

    #[Test]
    public function borangnya_muncul_saat_statusnya_refund(): void
    {
        /*
         * Di kartu RINGKASAN, bukan di tab "Termin & DP": tab itu hanya ada
         * untuk pesanan rombongan dan lembaga, sedangkan refund bisa terjadi
         * pada pendaftaran satu orang juga — dan itu justru yang paling
         * sering.
         */
        [$orang, $biasa] = $this->pendaftaran(5500000, 'diproses');
        [, $direfund] = $this->pendaftaran(5500000, 'Pendaftaran Refund');

        $alamat = fn ($b) => route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]);

        /*
         * Dicari TAG BORANGNYA, bukan nama kelasnya di mana saja.
         *
         * Percobaan pertama mencari 'rin-refund-borang' di seluruh halaman dan
         * langsung merah pada markah yang justru benar: blok gaya halaman ini
         * memuat pemilih `.rin-refund-borang`, dan blok itu tercetak di SETIAP
         * pendaftaran. Pemindai yang tertangkap lembar gayanya sendiri tidak
         * menjaga apa pun.
         */
        $borang = '/<form[^>]*class="rin-refund-borang"/';

        $this->assertSame(0, preg_match($borang,
            $this->actingAs($orang)->get($alamat($biasa))->assertOk()->getContent()),
            'Borang uang keluar terpampang di pendaftaran yang tidak membutuhkannya.');

        $this->assertSame(1, preg_match($borang,
            $this->actingAs($orang)->get($alamat($direfund))->assertOk()->getContent()),
            'Borang pengembalian tidak muncul padahal statusnya refund.');
    }

    #[Test]
    public function pencatatan_dan_penghapusannya_meninggalkan_jejak(): void
    {
        [$orang, $baris] = $this->pendaftaran(5500000);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pengembalian', ['scopus_camp', $baris->getKey()]),
            ['nominal' => '500.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect();

        $satu = PendaftaranPengembalian::milik('scopus_camp', (string) $baris->getKey())->firstOrFail();

        $this->actingAs($orang)->delete(
            route('account.pendaftaran-layanan.pengembalian.hapus', $satu->id)
        )->assertRedirect();

        $jejak = PendaftaranJejak::milik('scopus_camp', (string) $baris->getKey())
            ->whereIn('aksi', ['refund', 'hapus-refund'])->terurut()->get();

        $this->assertSame(['refund', 'hapus-refund'], $jejak->pluck('aksi')->all());
        $this->assertStringContainsString('Rp 500.000', $jejak[0]->kalimat);
        $this->assertStringContainsString('dihapus', $jejak[1]->kalimat);

        $this->assertSame(0,
            PendaftaranPengembalian::milik('scopus_camp', (string) $baris->getKey())->count());
    }

    #[Test]
    public function pelanggan_tidak_boleh_mencatat_pengembalian(): void
    {
        [, $baris] = $this->pendaftaran(5500000);

        $this->actingAs($this->akun(User::PERAN_PELANGGAN))->post(
            route('account.pendaftaran-layanan.pengembalian', ['scopus_camp', $baris->getKey()]),
            ['nominal' => '100.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect(route('account.dashboard.index'));

        $this->assertSame(0,
            PendaftaranPengembalian::milik('scopus_camp', (string) $baris->getKey())->count());
    }

    // ------------------------------------------------------------- pembantu

    private function angkaUangMasuk(User $orang): int
    {
        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index'))->assertOk()->getContent();

        $ada = preg_match('/<strong>Rp ([\d.]+)<\/strong>\s*<span>uang masuk/s', $isi, $c);
        $this->assertSame(1, $ada, 'Ubin uang masuk tidak tergambar.');

        return (int) str_replace('.', '', $c[1]);
    }

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Rina Panitia', 'username' => 'rfd_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function pendaftaran(int $total, string $status = 'diproses'): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $baris = PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => (string) $total, 'status' => $status,
        ]);

        return [$this->akun(User::PERAN_ADMINISTRATOR), $baris];
    }
}
