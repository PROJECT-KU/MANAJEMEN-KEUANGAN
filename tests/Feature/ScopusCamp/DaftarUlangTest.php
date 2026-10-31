<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use App\Services\Doku;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Siapa yang boleh mendaftar lagi di angkatan yang sama.
 *
 * Aturannya ditetapkan pemiliknya 31 Okt 2026, sesudah satu laporan yang
 * bisa ditelusuri sampai ke datanya: nomor 0895…441 punya pendaftaran LUNAS
 * di Scopus Camp Yogyakarta ke-202, dan karena ke-202 satu-satunya angkatan
 * yang terbuka, ia tidak bisa mendaftar lagi sama sekali — borangnya malah
 * mengirimkan kembali tautan pendaftaran lamanya.
 *
 * Yang mengunci sekarang HANYA tagihan yang belum dibayar dan belum lewat
 * 60 menit. Itu memang bahaya yang mau dicegah sejak awal: dua tagihan
 * terbuka atas satu kursi, yang sebagiannya dibayar dua-duanya.
 */
class DaftarUlangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-ULANG');
        config()->set('services.doku.secret_key', 'SK-UJI-ULANG');

        Mail::fake();

        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => [
                'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/UJI'],
                'order' => ['invoice_number' => 'CAMP-UJI'],
            ],
        ], 200)]);
    }

    private function angkatan(): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Ulang ' . Str::random(6),
            'nama_ke' => (string) random_int(6000, 6999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'group_wa' => 'https://chat.whatsapp.com/ujiulang',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ]);
    }

    /** Orang yang sama persis — nama dan email itulah yang dicocokkan. */
    private function isian(CategoriesScopusCamp $a): array
    {
        return [
            'kategori_id' => $a->getKey(),
            'nama' => 'Alumni Yang Sama',
            'email' => 'alumniujiulang@contoh.test',
            'telp' => '0895421735441',
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ];
    }

    private function jumlahBaris(CategoriesScopusCamp $a): int
    {
        return PendaftaranScopusCamp::where('kategori_id', $a->getKey())->count();
    }

    /** Pendaftaran pertama, lalu dikembalikan barisnya. */
    private function daftarSekali(CategoriesScopusCamp $a): PendaftaranScopusCamp
    {
        $this->post(route('public.scopuscamp.store'), $this->isian($a));

        return PendaftaranScopusCamp::where('kategori_id', $a->getKey())
            ->latest('created_at')->firstOrFail();
    }

    #[Test]
    public function tagihan_yang_masih_hidup_tetap_mengunci(): void
    {
        /*
         * Inilah satu-satunya hal yang boleh mengunci: tombol ditekan dua
         * kali karena halaman ini diam beberapa detik saat tagihan dibuat.
         * Tanpa ini orangnya memegang dua tagihan untuk satu kursi.
         */
        $a = $this->angkatan();
        $this->daftarSekali($a);

        $this->post(route('public.scopuscamp.store'), $this->isian($a));

        $this->assertSame(1, $this->jumlahBaris($a),
            'Tagihan yang belum dibayar dan belum lewat tenggat tidak mengunci: '
            . 'satu orang memegang dua tagihan untuk satu kursi.');
    }

    #[Test]
    public function sesudah_lunas_boleh_mendaftar_lagi(): void
    {
        /*
         * Kasus yang dilaporkan. Pendaftaran yang SUDAH DIBAYAR bukan
         * tagihan terbuka, jadi ia tidak boleh menutup pintu — alumni yang
         * ingin ikut lagi berhenti di sini sampai 31 Okt 2026.
         */
        $a = $this->angkatan();
        $lama = $this->daftarSekali($a);

        $lama->forceFill([
            'status' => 'Pendaftaran Diterima',
            'bayar_pada' => now(),
            'bayar_status' => 'SUCCESS',
        ])->save();

        $this->post(route('public.scopuscamp.store'), $this->isian($a));

        $this->assertSame(2, $this->jumlahBaris($a),
            'Yang sudah lunas masih terkunci; ia tidak punya tagihan terbuka '
            . 'satu pun, jadi tidak ada yang bisa tertagih dua kali.');
    }

    #[Test]
    public function sesudah_tenggat_enam_puluh_menit_lewat_boleh_mendaftar_lagi(): void
    {
        /*
         * Tagihan DOKU hidup 60 menit. Lewat dari itu ia mati di gerbang —
         * menahan kursinya lebih lama berarti menahan kursi yang tidak
         * mungkin terbayar, dan orangnya tidak punya jalan maju apa pun.
         */
        $a = $this->angkatan();
        $lama = $this->daftarSekali($a);

        $lama->forceFill([
            'kedaluwarsa_pada' => now()->subMinutes(Doku::MENIT_KEDALUWARSA + 1),
        ])->save();

        $this->post(route('public.scopuscamp.store'), $this->isian($a));

        $this->assertSame(2, $this->jumlahBaris($a),
            'Tagihan yang sudah lewat tenggat masih mengunci kursinya.');

        $this->assertSame('Pendaftaran Dibatalkan', (string) $lama->fresh()->status,
            'Tagihan yang lewat tenggat tidak ikut dimatikan, jadi kursinya '
            . 'tetap terhitung terpakai di sisa kuota.');
    }

    #[Test]
    public function tenggat_lewat_tetap_dilepas_walau_jatah_sapuan_habis(): void
    {
        /*
         * Penyapu di halaman daftar dan halaman borang mengambil 20 baris
         * sekali jalan, diurut dari yang PALING LAMA kedaluwarsa. Jadi
         * tagihan yang baru saja lewat tenggat — milik orang yang sedang
         * berdiri di borang — justru yang paling mungkin tidak kebagian.
         * Dengan 20 baris lain yang lebih tua menunggu, sapuan umum tidak
         * akan pernah sampai ke barisnya.
         */
        $a = $this->angkatan();
        $lama = $this->daftarSekali($a);

        $lama->forceFill(['kedaluwarsa_pada' => now()->subMinutes(1)])->save();

        // 20 baris lain yang kedaluwarsanya JAUH lebih tua: persis sejatah
        // sapuan umum, jadi barisnya sendiri terdorong keluar antrean.
        for ($i = 0; $i < 20; $i++) {
            PendaftaranScopusCamp::create([
                'kategori_id' => $a->getKey(),
                'id_transaksi' => 'CAMP-UJI-LAMA-' . $i,
                'token' => Str::random(30),
                'nama' => 'Pendaftar Lama ' . $i,
                'email' => 'lama' . $i . '@contoh.test',
                'telp' => '08120000' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'affiliasi' => 'Universitas Uji',
                'jumlah_pendaftar' => 1,
                'status' => 'diproses',
                'cara_bayar' => 'doku',
                'kedaluwarsa_pada' => now()->subDays(30 + $i),
            ]);
        }

        $this->post(route('public.scopuscamp.store'), $this->isian($a));

        $this->assertSame('Pendaftaran Dibatalkan', (string) $lama->fresh()->status,
            'Barisnya sendiri tidak ikut dilepas karena kalah antre dengan '
            . 'baris yang lebih tua; orangnya tetap terkunci tanpa sebab.');
    }

    #[Test]
    public function orang_lain_di_angkatan_yang_sama_tidak_pernah_terkunci(): void
    {
        $a = $this->angkatan();
        $this->daftarSekali($a);

        $this->post(route('public.scopuscamp.store'), array_merge($this->isian($a), [
            'nama' => 'Orang Lain Sekali',
            'email' => 'oranglainujiulang@contoh.test',
        ]));

        $this->assertSame(2, $this->jumlahBaris($a),
            'Pendaftar lain ikut tercegat oleh tagihan milik orang pertama.');
    }
}
