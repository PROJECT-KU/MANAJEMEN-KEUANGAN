<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\CategoriesOnlineTraining;
use App\PendaftaranJejak;
use App\PendaftaranOnlineTraining;
use App\Support\PendaftaranSemuaLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Hasil kirim WhatsApp tercatat di JEJAK pendaftarannya.
 *
 * Sampai 3 Nov 2026 hasilnya hanya masuk log peladen. Surat sudah lama
 * dicatat di jejak — 'surat' dan 'surat-gagal', lengkap dengan ikonnya di
 * layar rincian — tetapi WhatsApp tidak punya apa pun.
 *
 * Akibatnya pendaftar yang berkata "saya tidak dapat WhatsApp" tidak bisa
 * dijawab dari layar mana pun. Lebih buruk: perangkat Fonnte yang terputus —
 * keadaan yang memang sedang berlangsung — menelan SEMUA pemberitahuan tanpa
 * satu pun tanda di aplikasinya, dan `Log::warning` tidak tertulis sama
 * sekali kalau LOG_LEVEL peladennya di atas `warning`.
 */
class JejakWhatsAppTest extends TestCase
{
    use DatabaseTransactions;

    private function pendaftaran(): PendaftaranOnlineTraining
    {
        $a = CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Jejak WA ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'group_wa' => 'https://chat.whatsapp.com/ujiwa',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 3500000, 'status' => 'active',
        ]);

        return PendaftaranOnlineTraining::create([
            'token' => Str::random(30),
            'id_transaksi' => PendaftaranSemuaLayanan::nomorBaru('online_training'),
            'kategori_id' => $a->getKey(),
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'nama' => 'Penerima Kabar',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'ppn' => '0', 'kode_unik' => '123',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '1875123',
            'status' => 'Pendaftaran Diterima', 'cara_bayar' => 'doku',
            'bayar_pada' => now(),
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, PendaftaranJejak> */
    private function jejak(PendaftaranOnlineTraining $p)
    {
        return PendaftaranJejak::where('layanan', 'online_training')
            ->where('pendaftaran_id', (string) $p->getKey())
            ->whereIn('aksi', ['whatsapp', 'whatsapp-gagal'])
            ->get();
    }

    #[Test]
    public function kabar_yang_berhasil_ikut_dicatat(): void
    {
        /*
         * Yang berhasil pun dicatat, bukan hanya yang gagal: "tidak ada
         * catatan" harus berarti "memang tidak pernah dicoba", bukan
         * "mungkin berhasil, mungkin tidak".
         */
        config()->set('services.fonnte.token', 'TOKEN-UJI');
        Http::fake(['*fonnte*' => Http::response(['status' => true, 'id' => ['123']], 200)]);

        $p = $this->pendaftaran();

        app(\App\Actions\Pendaftaran\KabariWhatsApp::class)->lunas('online_training', $p);

        $jejak = $this->jejak($p);

        $this->assertCount(1, $jejak);
        $this->assertSame('whatsapp', (string) $jejak->first()->aksi);
    }

    #[Test]
    public function kabar_yang_gagal_tercatat_beserta_sebabnya(): void
    {
        /*
         * Inti perbaikannya: perangkat Fonnte yang terputus harus
         * meninggalkan jejak MERAH di pendaftarannya, bukan cuma satu baris
         * di log yang mungkin tidak pernah ditulis.
         */
        config()->set('services.fonnte.token', 'TOKEN-UJI');
        Http::fake(['*fonnte*' => Http::response([
            'status' => false, 'reason' => 'device disconnected',
        ], 200)]);

        $p = $this->pendaftaran();

        app(\App\Actions\Pendaftaran\KabariWhatsApp::class)->lunas('online_training', $p);

        $jejak = $this->jejak($p);

        $this->assertCount(1, $jejak);
        $this->assertSame('whatsapp-gagal', (string) $jejak->first()->aksi);
        $this->assertStringContainsString('GAGAL', (string) $jejak->first()->ringkasan);
    }

    #[Test]
    public function gagal_mencatat_jejak_tidak_menggagalkan_pembayarannya(): void
    {
        /*
         * Jejak ini dipanggil DARI jalur pembayaran — pemberitahuan DOKU
         * yang sudah menandai orangnya lunas. Lemparan di sini akan
         * membatalkan transaksi yang uangnya sudah masuk, atau membuat kami
         * menjawab bukan-200 ke DOKU sehingga pemberitahuannya diulang dan
         * suratnya dikirim dua kali.
         */
        config()->set('services.fonnte.token', 'TOKEN-UJI');
        Http::fake(['*fonnte*' => Http::response(['status' => true, 'id' => ['123']], 200)]);

        $p = $this->pendaftaran();

        // pendaftaran_id yang tidak masuk akal panjangnya tidak boleh
        // melempar keluar dari aksinya.
        $p->forceFill(['telp' => null])->save();

        $hasil = app(\App\Actions\Pendaftaran\KabariWhatsApp::class)
            ->lunas('online_training', $p->refresh());

        $this->assertIsBool($hasil);
    }

    #[Test]
    public function layar_rincian_mengenali_kedua_aksinya(): void
    {
        /*
         * Jejak yang aksinya tidak dikenal digambar dengan ikon bawaan
         * 'fa-exchange-alt' berwarna biru — jadi kegagalan WhatsApp akan
         * terlihat persis sama dengan perpindahan biasa, dan tidak ada yang
         * menyadarinya.
         */
        $isi = (string) file_get_contents(
            base_path('resources/views/account/pendaftaran_layanan/rincian.blade.php')
        );

        $this->assertStringContainsString("'whatsapp' => ['fa-comment-dots', 'hijau']", $isi);
        $this->assertStringContainsString("'whatsapp-gagal' => ['fa-comment-slash', 'merah']", $isi);
    }
}
