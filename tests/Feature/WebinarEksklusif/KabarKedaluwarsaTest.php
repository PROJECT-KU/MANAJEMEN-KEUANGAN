<?php

namespace Tests\Feature\WebinarEksklusif;

use App\Actions\WebinarEksklusif\KabariKursiDilepas;
use App\KategoriLayanan;
use App\Mail\WebinarEksklusifKedaluwarsaMail;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kursi tidak boleh dilepas diam-diam.
 *
 * Dulu batas bayarnya lewat, kuotanya kembali, dan orangnya tidak pernah
 * diberi tahu apa pun. Yang ia punya cuma surat "pendaftaran diterima" yang
 * sudah tidak benar lagi — dan ia baru tahu berminggu-minggu kemudian, saat
 * menanyakan sesi yang kursinya sudah diberikan ke orang lain.
 */
class KabarKedaluwarsaTest extends TestCase
{
    use DatabaseTransactions;

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Kabar ' . Str::random(5),
            'nama_ke' => (string) random_int(5000, 5999),
            'mulai' => now()->addDays(12)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '19',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    /** @param array<string, mixed> $ubah */
    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-KABAR-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Kabar',
            'email' => 'kabar' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129000',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->subMinutes(5),
        ], $ubah));
    }

    #[Test]
    public function kursi_yang_dilepas_dikabarkan_ke_orangnya(): void
    {
        Mail::fake();

        $p = $this->pendaftaran();

        WebinarEksklusifPendaftaran::lepaskanYangKedaluwarsa();

        Mail::assertSent(
            WebinarEksklusifKedaluwarsaMail::class,
            fn ($surat) => $surat->hasTo($p->email)
        );

        $this->assertNotNull(
            $p->refresh()->kedaluwarsa_dikabari_pada,
            'Penanda "sudah dikabari" tidak tertulis, jadi sapuan berikutnya '
            . 'akan mengirim surat yang sama lagi.'
        );
    }

    #[Test]
    public function suratnya_hanya_sekali_walau_dipicu_berkali_kali(): void
    {
        /*
         * Tiga jalur bisa mengedaluwarsakan satu baris: sapuan terjadwal,
         * halaman sesi yang dibuka orang lain, dan pemberitahuan DOKU.
         * Ketiganya bisa mengenai baris yang sama dalam hitungan detik.
         */
        Mail::fake();

        $p = $this->pendaftaran();

        WebinarEksklusifPendaftaran::lepaskanYangKedaluwarsa();

        $aksi = app(KabariKursiDilepas::class);
        $aksi->jalankan($p->refresh());
        $aksi->jalankan($p->refresh());

        Mail::assertSent(WebinarEksklusifKedaluwarsaMail::class, 1);
    }

    #[Test]
    public function yang_dibatalkan_tidak_menerima_surat_batas_waktu(): void
    {
        /*
         * 'cancel' datang dari keputusan — orangnya sendiri atau panitia —
         * dan kalimat "batas waktunya lewat" akan salah di situ.
         */
        Mail::fake();

        $p = $this->pendaftaran(['status' => 'cancel']);

        $terkirim = app(KabariKursiDilepas::class)->jalankan($p);

        $this->assertFalse($terkirim);
        Mail::assertNothingSent();
    }

    #[Test]
    public function suratnya_berlogo_rumah_scopus_bukan_mis(): void
    {
        /*
         * Dua logo hidup berdampingan di proyek ini. logo-email.png adalah
         * logo MIS — "Management Integration System" — yaitu nama sistem
         * INTERNALNYA. Pesertanya tidak pernah mendengar nama itu, dan surat
         * bermerek asing dari pengirim tak dikenal adalah surat yang dihapus
         * tanpa dibuka.
         */
        $p = $this->pendaftaran(['status' => 'expired']);
        $sesi = KategoriLayanan::find($p->kategori_id);

        $html = (new WebinarEksklusifKedaluwarsaMail($p, $sesi))->render();

        $this->assertStringNotContainsString(
            'logo-email.png',
            $html,
            'Surat ke pelanggan memakai logo MIS; yang menerimanya tidak '
            . 'mengenali pengirimnya.'
        );

        $this->assertStringContainsString('logo-rsc-email.png', $html);
    }

    #[Test]
    public function pengirimnya_rumah_scopus_bukan_laravel(): void
    {
        /*
         * APP_NAME di .env masih "Laravel". Mailable yang tidak menyebut
         * from()-nya sendiri akan memakai itu, dan suratnya sampai dengan
         * pengirim bernama "Laravel" — terlihat seperti penipuan.
         */
        $p = $this->pendaftaran(['status' => 'expired']);

        $surat = new WebinarEksklusifKedaluwarsaMail($p, KategoriLayanan::find($p->kategori_id));
        $surat->build();

        $this->assertSame(config('mail.from.address'), $surat->from[0]['address'] ?? null);
        $this->assertSame(config('mail.from.name'), $surat->from[0]['name'] ?? null);
    }

    #[Test]
    public function pengiriman_yang_gagal_boleh_dicoba_lagi(): void
    {
        /*
         * Penandanya direbut SEBELUM mengirim, supaya dua proses tidak
         * sama-sama lolos. Tapi kalau pengirimannya gagal dan penandanya
         * dibiarkan tertulis, orang ini TIDAK AKAN PERNAH dikabari —
         * sapuan berikutnya melihat penandanya terisi dan melewatinya.
         * Padahal justru tidak adanya kabar yang sedang diperbaiki.
         */
        $p = $this->pendaftaran(['status' => 'expired']);

        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP mati'));

        $terkirim = app(KabariKursiDilepas::class)->jalankan($p);

        $this->assertFalse($terkirim);
        $this->assertNull(
            $p->refresh()->kedaluwarsa_dikabari_pada,
            'Penandanya tetap tertulis walau suratnya gagal terbang; orang '
            . 'ini tidak akan pernah dikabari.'
        );
    }
}
