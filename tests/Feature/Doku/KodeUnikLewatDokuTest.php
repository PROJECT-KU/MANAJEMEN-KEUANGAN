<?php

namespace Tests\Feature\Doku;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kode unik tetap dipakai walau pembayarannya lewat DOKU.
 *
 * Keputusan pemiliknya, 17 Okt 2026. Sebelumnya kode uniknya dinolkan saat
 * gerbang pembayaran aktif — di sana pencocokannya sudah memakai nomor
 * rujukan, dan angka recehnya tidak menambah apa pun.
 *
 * Konsekuensinya diterima: nominal yang dibayar berakhir ganjil. Yang TIDAK
 * boleh terjadi adalah kode uniknya masuk ke total yang tersimpan tetapi
 * tidak ikut dikirim ke DOKU — rinciannya jadi tidak berjumlah sama dengan
 * totalnya, dan DOKU menolak seluruh tagihannya dengan "AMOUNT NOT MATCH".
 */
class KodeUnikLewatDokuTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');
        config()->set('services.doku.produksi', false);

        Http::fake(['*' => Http::response(
            ['response' => ['payment' => ['url' => 'https://bayar.test/x']]], 200
        )]);
    }

    private function sesi(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Kode Unik ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30',
            'jam_selesai' => '11:30',
            'platform' => 'Zoom',
            'pemateri' => 'Pemateri Uji',
            'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'biaya' => '129000',
            'status' => 'active',
        ], $lain));
    }

    /**
     * Namanya WAJIB berbeda tiap kali.
     *
     * Pendaftaran dengan nama yang sama di satu sesi dianggap orang yang
     * sama dan dikembalikan ke pendaftaran lamanya — itu memang perilaku
     * yang benar, tetapi membuat uji yang memakai nama tetap diam-diam cuma
     * menguji SATU baris berulang kali.
     */
    private function daftarkan(KategoriLayanan $sesi): WebinarEksklusifPendaftaran
    {
        /*
         * Baris baru dikenali dari SELISIH kumpulan id, bukan dari
         * latest('id').
         *
         * Kunci utamanya UUID, jadi latest('id') mengurutkan menurut abjad
         * UUID — sama sekali bukan urutan pembuatan — dan memulangkan baris
         * sembarang. Terukur: empat pendaftaran dengan nominal yang semuanya
         * berbeda tetap terbaca kembar, sebab baris yang sama terambil dua
         * kali.
         */
        $sebelumnya = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)
            ->pluck('id')->all();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id,
            'nama' => 'Pendaftar Uji ' . Str::random(6),
            'email' => 'uji' . Str::random(5) . '@contoh.test',
            'telp' => '0812-3456-' . random_int(1000, 9999),
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'peserta' => [],
            'setuju' => '1',
        ])->assertRedirect();

        $baru = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)
            ->whereNotIn('id', $sebelumnya)
            ->get();

        $this->assertCount(1, $baru,
            'Pendaftarannya tidak bertambah tepat satu — kirimannya ditolak, '
            . 'atau dianggap orang yang sama.');

        return $baru->first();
    }

    /** Badan permintaan yang BENAR-BENAR dikirim ke DOKU. */
    private function terkirim(): array
    {
        $kiriman = null;

        Http::recorded(function ($permintaan) use (&$kiriman) {
            $kiriman = json_decode($permintaan->body(), true);
        });

        $this->assertNotNull($kiriman, 'Tidak ada tagihan yang dikirim ke DOKU.');

        return $kiriman;
    }

    #[Test]
    public function kode_unik_tetap_dihitung_saat_doku_aktif(): void
    {
        $p = $this->daftarkan($this->sesi());

        $this->assertGreaterThan(0, (int) $p->kode_unik,
            'Kode uniknya dinolkan padahal DOKU aktif; itu perilaku lama.');

        $this->assertSame(129000 + (int) $p->kode_unik, (int) $p->total_pembayaran,
            'Kode uniknya tidak ikut masuk ke total yang ditagihkan.');
    }

    #[Test]
    public function kode_unik_ikut_jadi_rincian_tagihan_doku(): void
    {
        /*
         * INI yang paling mudah terlewat.
         *
         * Kode unik yang masuk ke total tetapi tidak ikut dikirim sebagai
         * rincian membuat jumlah rinciannya meleset, dan DOKU menolak SELURUH
         * tagihannya. Pendaftarnya cuma melihat "pembayaran daring gagal
         * dibuat" tanpa tahu sebabnya.
         */
        $p = $this->daftarkan($this->sesi());

        $isi = $this->terkirim();
        $baris = $isi['order']['line_items'];

        $this->assertSame(
            (int) $isi['order']['amount'],
            array_sum(array_map(fn ($b) => $b['price'] * $b['quantity'], $baris)),
            'Rincian tagihannya tidak berjumlah sama dengan amount; DOKU akan '
            . 'menolak dengan "AMOUNT NOT MATCH".'
        );

        $this->assertSame((int) $p->total_pembayaran, (int) $isi['order']['amount'],
            'Yang ditagihkan DOKU berbeda dari total yang tersimpan.');

        $kodeUnikBaris = collect($baris)->firstWhere('name', 'Kode unik pembayaran');

        $this->assertNotNull($kodeUnikBaris,
            'Kode uniknya tidak muncul sebagai rincian; yang membayar hanya '
            . 'melihat total yang tidak bulat tanpa penjelasan.');

        $this->assertSame((int) $p->kode_unik, $kodeUnikBaris['price']);
    }

    #[Test]
    public function totalnya_tidak_pernah_kembar_di_satu_sesi(): void
    {
        /*
         * Inti gunanya kode unik, dan tetap berlaku lewat DOKU: dua
         * pendaftaran di sesi yang sama tidak boleh berakhir dengan nominal
         * yang sama persis.
         */
        $sesi = $this->sesi();

        $total = [];

        for ($i = 0; $i < 4; $i++) {
            $total[] = (int) $this->daftarkan($sesi)->total_pembayaran;
        }

        $this->assertSame($total, array_values(array_unique($total)),
            'Ada dua pendaftaran dengan nominal yang sama persis di satu sesi.');
    }
}
