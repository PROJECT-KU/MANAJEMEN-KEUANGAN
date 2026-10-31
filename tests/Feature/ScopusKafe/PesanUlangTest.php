<?php

namespace Tests\Feature\ScopusKafe;

use App\PendaftaranScopusKafe;
use App\Services\Doku;
use App\Support\HargaKafe;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Siapa yang boleh memesan lagi di tanggal yang sama.
 *
 * Aturannya satu dengan Scopus Camp, keputusan pemiliknya 31 Okt 2026: yang
 * mengunci hanya tagihan yang belum dibayar DAN belum lewat 60 menit.
 *
 * Di Kafe keadaannya sempat lebih buruk daripada di Camp. Penjagaan
 * pemesanan ganda melepaskan satu status saja — 'pembayaran ditolak' —
 * sementara penyapu kedaluwarsa tidak pernah menyentuh kolom `status`. Jadi
 * tidak ada satu pun keadaan yang membuka kuncinya kembali: terukur, memesan
 * satu tanggal lalu tidak membayar mengunci tanggal itu SELAMANYA bagi orang
 * yang sama, dan yang sudah membayar pun tidak bisa memesan lagi.
 */
class PesanUlangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('services.doku.client_id', 'BRN-UJI-ULANG');
        config()->set('services.doku.secret_key', 'SK-UJI-ULANG');

        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => ['payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/U']],
        ], 200)]);
    }

    private function sesi(): object
    {
        $id = (string) Str::uuid();

        DB::table('meme')->insert([
            'id' => $id,
            'token' => Str::random(30),
            'sesi' => 'sesi uji ulang',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '13:00:00',
            'kuota' => '25',
            'biaya' => '1.000.000',
            'lokasi' => 'Rumah Scopus Pusat',
            'status' => 'publish',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('meme')->where('id', $id)->first();
    }

    private function jumatBerikutnya(): Carbon
    {
        $hari = now()->startOfDay();

        while (! HargaKafe::hariBuka($hari)) {
            $hari = $hari->addDay();
        }

        return $hari;
    }

    /** Orang yang sama persis — tanggal, nama, dan email itulah yang dicocokkan. */
    private function isian(object $sesi): array
    {
        return [
            'nama' => 'Pemesan Yang Sama',
            'email' => 'pemesanujiulang@contoh.test',
            'telp' => '0895421735441',
            'tanggal_pemesanan' => $this->jumatBerikutnya()->toDateString(),
            'sesi' => [$sesi->id],
            'setuju' => '1',
        ];
    }

    private function jumlahBaris(): int
    {
        return PendaftaranScopusKafe::whereDate(
            'tanggal_pemesanan', $this->jumatBerikutnya()->toDateString()
        )->where('email', 'pemesanujiulang@contoh.test')->count();
    }

    private function pesanSekali(object $sesi): PendaftaranScopusKafe
    {
        $this->post(route('public.scopuskafe.store'), $this->isian($sesi));

        return PendaftaranScopusKafe::where('email', 'pemesanujiulang@contoh.test')
            ->latest('created_at')->firstOrFail();
    }

    #[Test]
    public function tagihan_yang_masih_hidup_tetap_mengunci(): void
    {
        /*
         * Satu-satunya yang boleh mengunci: tombol ditekan dua kali karena
         * halaman ini diam beberapa detik saat tagihan dibuat.
         */
        $s = $this->sesi();
        $this->pesanSekali($s);

        $this->post(route('public.scopuskafe.store'), $this->isian($s));

        $this->assertSame(1, $this->jumlahBaris(),
            'Tagihan yang belum dibayar dan belum lewat tenggat tidak mengunci: '
            . 'satu orang memegang dua tagihan untuk satu tempat.');
    }

    #[Test]
    public function sesudah_lunas_boleh_memesan_lagi(): void
    {
        $s = $this->sesi();
        $lama = $this->pesanSekali($s);

        $lama->forceFill([
            'status' => 'pembayaran diterima',
            'bayar_pada' => now(),
            'bayar_status' => 'SUCCESS',
        ])->save();

        $this->post(route('public.scopuskafe.store'), $this->isian($s));

        $this->assertSame(2, $this->jumlahBaris(),
            'Yang sudah lunas masih terkunci; ia tidak punya tagihan terbuka '
            . 'satu pun, jadi tidak ada yang bisa tertagih dua kali.');
    }

    #[Test]
    public function sesudah_tenggat_enam_puluh_menit_lewat_boleh_memesan_lagi(): void
    {
        /*
         * Inilah yang dulu mengunci selamanya: tenggatnya lewat, tagihannya
         * mati di gerbang, tetapi `status` tetap 'menunggu verifikasi' dan
         * penjagaan lama hanya melepaskan 'pembayaran ditolak'.
         */
        $s = $this->sesi();
        $lama = $this->pesanSekali($s);

        $lama->forceFill([
            'kedaluwarsa_pada' => now()->subMinutes(Doku::MENIT_KEDALUWARSA + 1),
        ])->save();

        $this->post(route('public.scopuskafe.store'), $this->isian($s));

        $this->assertSame(2, $this->jumlahBaris(),
            'Tagihan yang sudah lewat tenggat masih mengunci tanggalnya.');
    }

    #[Test]
    public function orang_lain_di_tanggal_yang_sama_tidak_pernah_terkunci(): void
    {
        $s = $this->sesi();
        $this->pesanSekali($s);

        $this->post(route('public.scopuskafe.store'), array_merge($this->isian($s), [
            'nama' => 'Pemesan Lain Sekali',
            'email' => 'pemesanlainujiulang@contoh.test',
        ]));

        $this->assertSame(1, PendaftaranScopusKafe::whereDate(
            'tanggal_pemesanan', $this->jumatBerikutnya()->toDateString()
        )->where('email', 'pemesanlainujiulang@contoh.test')->count(),
            'Pemesan lain ikut tercegat oleh tagihan milik orang pertama.');
    }
}
