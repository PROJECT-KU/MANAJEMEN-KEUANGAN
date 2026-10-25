<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use App\Mail\ScopusCampMail;
use App\Mail\TautanPendaftaranScopusCampMail;
use App\PendaftaranScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Jalur dari pembayaran yang GAGAL sampai orangnya bisa membayar lagi.
 *
 * Sampai 25 Okt 2026 halaman status Scopus Camp hanya mengenal tiga keadaan —
 * lunas, dibatalkan, menunggu — dan tidak pernah membaca `bayar_status` sama
 * sekali. Terukur: pendaftaran yang pembayarannya gagal digambar sebagai
 * "Tinggal satu langkah lagi" lengkap dengan tombol bayar yang mengantar ke
 * tagihan mati, dan pollernya menanyai keadaan itu selamanya sebab 'selesai'
 * pun tidak pernah jadi benar.
 *
 * Yang dijaga di sini hal-hal yang kalau rusak TIDAK mengeluarkan galat
 * apa pun:
 *
 *   - orang yang gagal bayar disuruh menekan tagihan yang sudah mati;
 *   - tagihan baru memakai nomor yang sama, lalu DOKU menolaknya dengan
 *     "INVOICE ALREADY USED" dan pendaftaran itu tidak bisa dibayar lagi
 *     selamanya;
 *   - pembayaran tagihan kedua tidak menemukan barisnya, jadi uang yang
 *     sudah masuk tidak pernah tercatat;
 *   - kwitansi terunduh sebelum uangnya masuk;
 *   - borang "kehilangan tautan" membocorkan alamat email siapa yang
 *     terdaftar di sini.
 */
class BayarUlangDanBerkasTest extends TestCase
{
    use DatabaseTransactions;

    private const KLIEN = 'BRN-UJI-ULANG';

    private const RAHASIA = 'SK-UJI-ULANG';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('services.doku.client_id', self::KLIEN);
        config()->set('services.doku.secret_key', self::RAHASIA);
    }

    private function angkatan(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Ulang ' . Str::random(6),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(20)->toDateString(),
            'selesai' => now()->addDays(22)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'total_kuota' => 20, 'sisa_kuota' => 19,
            'biaya' => 5500000, 'status' => 'active',
        ], $ganti));
    }

    /** @return array{0: PendaftaranScopusCamp, 1: CategoriesScopusCamp} */
    private function pendaftaran(array $ubah = [], array $angkatan = []): array
    {
        $a = $this->angkatan($angkatan);

        $p = PendaftaranScopusCamp::create(array_merge([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-ULANG-' . Str::upper(Str::random(6)),
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Ulang',
            'email' => 'ulang' . Str::random(6) . '@contoh.test',
            'telp' => '081234567890',
            'jumlah_pendaftar' => 1,
            /*
             * Totalnya HARUS berjumlah sama dengan rinciannya — harga kali
             * peserta, dikurangi potongan, ditambah kode unik dan PPN.
             * Tidak cocok, DOKU menolak dengan "AMOUNT NOT MATCH" dan
             * tagihannya tidak pernah terbuat; ujinya lalu merah dengan
             * sebab yang tidak disebut sama sekali di pesannya.
             */
            'total_pembayaran' => '5500321',
            'kode_unik' => 321,
            'cara_bayar' => 'doku',
            'status' => 'diproses',
            'kedaluwarsa_pada' => now()->addMinutes(30),
        ], $ubah));

        return [$p, $a];
    }

    /** Jawaban DOKU saat tagihan baru dibuat. */
    private function gerbangMenerima(): void
    {
        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => [
                'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/BARU'],
                'order' => ['invoice_number' => 'CAMP-ULANG'],
            ],
        ], 200)]);
    }

    // ------------------------------------------------- keadaan halamannya

    #[Test]
    public function pembayaran_gagal_tidak_lagi_digambar_sebagai_menunggu(): void
    {
        /*
         * PENJAGA TERPENTING di berkas ini.
         *
         * Yang kartunya ditolak diantar ke tombol yang membawanya ke halaman
         * DOKU bertuliskan "INVOICE ALREADY USED" — dan orangnya menyalahkan
         * dirinya sendiri, lalu menghubungi panitia. Terukur persis begitu
         * sebelum dibetulkan: judulnya "Tinggal satu langkah lagi" dan
         * alamat tagihan matinya tercetak di halamannya.
         */
        [$p] = $this->pendaftaran([
            'bayar_status' => 'failed',
            'bayar_url' => 'https://staging.doku.com/checkout-link-v2/SUDAH-MATI',
        ]);

        $isi = $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Pembayarannya belum berhasil', $isi,
            'Pembayaran yang gagal tidak dikabari sama sekali.');

        $this->assertStringNotContainsString('Tinggal satu langkah lagi', $isi,
            'Pembayaran yang gagal masih digambar sebagai menunggu pembayaran.');

        $this->assertStringNotContainsString('SUDAH-MATI', $isi,
            'Halamannya masih menawarkan tagihan yang sudah mati; yang menekannya '
            . 'mendarat di halaman galat DOKU dan menyalahkan dirinya sendiri.');

        $this->assertStringContainsString('Buat tagihan baru', $isi,
            'Tidak ada jalan keluar; orangnya harus menghubungi panitia.');
    }

    #[Test]
    public function kegagalan_dari_gerbang_dicatat_saat_halamannya_dibuka(): void
    {
        /*
         * DITAMBAHKAN SETELAH PENJAGA LAIN TERBUKTI KOSONG.
         *
         * Uji di atas menyetel `bayar_status` langsung di data ujinya, jadi
         * ia tidak pernah melewati jalur yang MENCATAT kegagalan itu —
         * mematikan cabang pencatatnya di susulkanDariGerbang() tidak
         * memerahkan satu pun uji.
         *
         * Yang dijaga di sini jalur sungguhannya, dan ia yang paling
         * menentukan di produksi: pemberitahuan DOKU TIDAK SELALU SAMPAI —
         * ke localhost tidak pernah, dan di peladen pun bergantung pada
         * alamat yang disetel di dasbor mereka. Kalau halaman ini tidak
         * menanyakan sendiri lalu mencatat jawabannya, orang yang
         * pembayarannya gagal melihat "Tinggal satu langkah lagi" sampai
         * sapuan melepas kursinya — tanpa pernah tahu kenapa.
         */
        [$p] = $this->pendaftaran([
            'bayar_status' => 'menunggu',
            'bayar_url' => 'https://staging.doku.com/checkout-link-v2/SUDAH-MATI',
        ]);

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'FAILED'],
        ], 200)]);

        $isi = $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertSame('failed', (string) $p->refresh()->bayar_status,
            'Jawaban gagal dari gerbang dibuang; halaman ini akan terus berkata '
            . 'menunggu pembayaran sampai sapuan melepas kursinya.');

        $this->assertStringContainsString('Pembayarannya belum berhasil', $isi,
            'Halamannya tidak langsung mengabarkan kegagalan yang baru saja '
            . 'ditanyakannya sendiri.');

        $this->assertStringNotContainsString('SUDAH-MATI', $isi,
            'Tombol bayarnya masih menunjuk tagihan yang baru saja dinyatakan gagal.');
    }

    #[Test]
    public function halaman_berhenti_menanyai_keadaan_yang_tidak_akan_berubah(): void
    {
        /*
         * Tagihan mati tidak akan dibayar siapa pun, jadi menanyainya tiap
         * beberapa detik tidak pernah memulangkan jawaban yang berbeda.
         *
         * Di hosting ini tumpukan poller pernah menghabiskan kuota prosesnya
         * dan berakhir 503 — satu tab yang dibiarkan terbuka semalam
         * mengirim ribuan permintaan untuk keadaan yang sudah final.
         */
        foreach (['failed', 'expired', 'gagal-dibuat'] as $keadaanGerbang) {
            [$p] = $this->pendaftaran(['bayar_status' => $keadaanGerbang]);

            $this->get(route('public.scopuscamp.keadaan', $p->getKey()))
                ->assertOk()
                ->assertJson(['selesai' => true], true);
        }
    }

    #[Test]
    public function tagihan_kedaluwarsa_dibedakan_dari_kursi_yang_sudah_dilepas(): void
    {
        /*
         * DUA KEADAAN BERBEDA, dan menyamakannya merugikan dua arah.
         *
         * Tenggat tagihannya 60 menit; kursinya baru dilepas sapuan yang
         * berjalan tiap sepuluh menit. Di antaranya ada tenggang: tagihannya
         * mati, kursinya MASIH ditahan.
         *
         * Disuruh "Daftar lagi" di tenggang itu, orangnya mendaftar untuk
         * kedua kalinya — dua baris untuk satu orang, kuota terpotong dua
         * kali, dan panitia yang menghapus salah satunya harus menebak mana
         * yang dimaksud.
         */
        [$p] = $this->pendaftaran(['bayar_status' => 'expired']);

        $isi = $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Kursi Anda masih kami tahan', $isi,
            'Tagihan yang kedaluwarsa dikabari seolah kursinya sudah hilang.');

        $this->assertStringContainsString('Buat tagihan baru', $isi);
        $this->assertStringNotContainsString('Daftar lagi', $isi,
            'Ditawari mendaftar lagi padahal kursinya masih ditahan; hasilnya dua '
            . 'pendaftaran untuk satu orang.');

        // Sesudah sapuan melepas kursinya, yang benar justru kebalikannya.
        $p->forceFill(['status' => 'Pendaftaran Dibatalkan'])->save();

        $isi = $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Kursinya sudah kami lepas', $isi);
        $this->assertStringContainsString('Daftar lagi', $isi,
            'Yang kursinya sudah dilepas tidak diberi jalan mendaftar lagi; '
            . 'halamannya jadi jalan buntu.');
        $this->assertStringNotContainsString('Buat tagihan baru', $isi,
            'Ditawari membuat tagihan untuk kursi yang sudah diberikan ke orang lain.');
    }

    // ------------------------------------------------------- tagihan baru

    #[Test]
    public function tagihan_baru_memakai_nomor_yang_belum_pernah_dipakai(): void
    {
        /*
         * DOKU menolak nomor tagihan yang sudah pernah dipakai dengan
         * "INVOICE ALREADY USED" — terukur 24 Okt 2026 pada
         * CAMP-20261024-0001, yang sejak itu tidak bisa dibayar sama sekali.
         *
         * Nomor PENDAFTARANNYA tidak berubah: itu yang sudah ada di surat,
         * di pesan WhatsApp, dan di catatan panitia. Yang berubah hanya nomor
         * tagihannya.
         */
        [$p] = $this->pendaftaran(['bayar_status' => 'failed']);
        $nomorAsli = $p->id_transaksi;

        $this->gerbangMenerima();

        $this->post(route('public.scopuscamp.bayarulang', $p->getKey()))
            ->assertRedirect('https://staging.doku.com/checkout-link-v2/BARU');

        $p->refresh();

        $this->assertSame($nomorAsli, $p->id_transaksi,
            'Nomor pendaftarannya berubah; yang ada di surat dan di catatan panitia '
            . 'tidak cocok lagi.');

        $this->assertSame($nomorAsli . '-R2', $p->bayar_nomor,
            'Tagihan barunya memakai nomor yang sudah pernah dipakai; DOKU menolaknya.');

        Http::assertSent(function ($permintaan) use ($nomorAsli) {
            $this->assertSame($nomorAsli . '-R2',
                data_get($permintaan->data(), 'order.invoice_number'));

            return true;
        });

        // Percobaan ketiga melanjutkan hitungannya, bukan kembali ke -R2.
        $this->gerbangMenerima();
        $this->post(route('public.scopuscamp.bayarulang', $p->getKey()));

        $this->assertSame($nomorAsli . '-R3', $p->refresh()->bayar_nomor);
    }

    #[Test]
    public function pembayaran_tagihan_kedua_tetap_menemukan_barisnya(): void
    {
        /*
         * CACAT YANG PALING MAHAL kalau terlewat: uang yang sudah masuk tidak
         * tercatat sebagai pembayaran pendaftaran mana pun.
         *
         * Pemberitahuan DOKU membawa nomor TAGIHAN — berakhiran -R2 —
         * sementara pencariannya dulu hanya lewat `id_transaksi`. Barisnya
         * tidak ditemukan, kami menjawab 200 supaya DOKU tidak mengulang, dan
         * orangnya tercatat belum bayar sampai ia mengadu.
         */
        [$p] = $this->pendaftaran(['bayar_status' => 'failed']);

        $this->gerbangMenerima();
        $this->post(route('public.scopuscamp.bayarulang', $p->getKey()));

        $nomorTagihan = $p->refresh()->bayar_nomor;
        $this->assertSame($p->id_transaksi . '-R2', $nomorTagihan);

        $this->kirimPemberitahuan([
            'order' => ['invoice_number' => $nomorTagihan],
            'transaction' => ['status' => 'SUCCESS'],
        ])->assertOk();

        $this->assertSame('Pendaftaran Diterima', $p->refresh()->status,
            'Pembayaran tagihan kedua tidak menemukan barisnya; uangnya sudah masuk '
            . 'dan pendaftarnya tetap tercatat belum bayar.');
    }

    #[Test]
    public function keadaan_yang_ditanyakan_ke_gerbang_nomor_tagihan_berlakunya(): void
    {
        /*
         * Sesudah tagihannya dibuat ulang, nomor pendaftaran dan nomor
         * tagihan berbeda. Yang ditanyakan harus nomor TAGIHANNYA: menanyakan
         * nomor pendaftaran memulangkan keadaan tagihan LAMA — yang memang
         * gagal — sehingga pembayaran yang baru saja berhasil tidak pernah
         * terbaca, dan halaman statusnya tetap berkata belum lunas.
         */
        [$p] = $this->pendaftaran([
            'bayar_status' => 'failed',
            'bayar_nomor' => 'CAMP-ULANG-LAMA-R2',
        ]);

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'PENDING'],
        ], 200)]);

        $this->get(route('public.scopuscamp.status', $p->getKey()))->assertOk();

        Http::assertSent(function ($permintaan) {
            $this->assertStringContainsString('CAMP-ULANG-LAMA-R2', $permintaan->url(),
                'Yang ditanyakan nomor pendaftarannya, bukan nomor tagihan yang berlaku.');

            return true;
        });
    }

    #[Test]
    public function tagihan_baru_ditolak_untuk_yang_sudah_lunas(): void
    {
        /*
         * Uangnya sudah masuk. Tagihan kedua membuat orangnya membayar dua
         * kali, dan pengembaliannya dikerjakan dengan tangan lewat gerbang
         * yang menolak membatalkan pesanan yang sudah dibayar.
         */
        [$p] = $this->pendaftaran(['status' => 'Pendaftaran Diterima']);

        Http::fake();

        $this->post(route('public.scopuscamp.bayarulang', $p->getKey()))
            ->assertRedirect(route('public.scopuscamp.status', $p->getKey()))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    #[Test]
    public function tagihan_baru_ditolak_setelah_kursinya_dilepas(): void
    {
        /*
         * Kursinya sudah dikembalikan ke kuota dan mungkin sudah diambil
         * orang lain. Dibuatkan tagihan lagi, yang membayarnya tidak punya
         * tempat — dan uangnya sudah masuk.
         */
        [$p] = $this->pendaftaran(['status' => 'Pendaftaran Dibatalkan']);

        Http::fake();

        $this->post(route('public.scopuscamp.bayarulang', $p->getKey()))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    #[Test]
    public function tagihan_baru_ditolak_kalau_angkatannya_sudah_tutup(): void
    {
        /*
         * Tanpa ini, yang tagihannya mati setelah acaranya berjalan tetap
         * bisa membayar acara yang sudah selesai.
         */
        [$p] = $this->pendaftaran(['bayar_status' => 'failed'], [
            'mulai' => now()->subDay()->toDateString(),
        ]);

        Http::fake();

        $this->post(route('public.scopuscamp.bayarulang', $p->getKey()))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    #[Test]
    public function tenggatnya_disetel_ulang_saat_tagihannya_dibuat_ulang(): void
    {
        /*
         * Tagihan DOKU-nya berumur 60 menit sejak dibuat. Tenggat lama yang
         * dibiarkan membuat halaman status menyembunyikan tombol bayar untuk
         * tagihan yang justru baru saja hidup — dan sapuan kedaluwarsa
         * melepas kursinya beberapa menit kemudian.
         */
        [$p] = $this->pendaftaran([
            'bayar_status' => 'expired',
            'kedaluwarsa_pada' => now()->subHours(3),
        ]);

        $this->gerbangMenerima();
        $this->post(route('public.scopuscamp.bayarulang', $p->getKey()));

        $this->assertTrue($p->refresh()->kedaluwarsa_pada->isFuture(),
            'Tenggatnya masih yang lama, jadi tagihan yang baru dibuat langsung '
            . 'terbaca kedaluwarsa dan kursinya dilepas sapuan berikutnya.');
    }

    // ----------------------------------------------------------- berkasnya

    #[Test]
    public function faktur_bisa_diunduh_sebagai_pdf(): void
    {
        /*
         * Halaman statusnya dirancang SEBAGAI kwitansi, tetapi berkasnya
         * sampai sekarang hanya hidup sebagai lampiran surat — jadi yang
         * suratnya tidak sampai tidak punya faktur sama sekali, dan bagian
         * keuangan kampusnya memintanya.
         */
        [$p] = $this->pendaftaran();

        $jawab = $this->get(route('public.scopuscamp.dokumen', [$p->getKey(), 'faktur']))
            ->assertOk();

        $this->assertSame('application/pdf', $jawab->headers->get('content-type'));

        $this->assertStringContainsString('attachment',
            (string) $jawab->headers->get('content-disposition'),
            'PDF-nya dibuka di tab, bukan diunduh; di peramban ponsel tombol '
            . 'simpannya sering tidak terlihat dan orangnya mengira tidak dapat.');

        $this->assertStringStartsWith('%PDF', $jawab->getContent());
    }

    #[Test]
    public function kwitansi_hanya_setelah_uangnya_masuk(): void
    {
        /*
         * Kwitansi menyatakan uangnya SUDAH diterima. Diberikan sebelum itu,
         * ia jadi surat yang kami tanda tangani sendiri untuk sesuatu yang
         * belum terjadi — dan bisa dipakai menagih balik lembaga ini.
         */
        [$p] = $this->pendaftaran();

        $this->get(route('public.scopuscamp.dokumen', [$p->getKey(), 'kwitansi']))
            ->assertRedirect(route('public.scopuscamp.status', $p->getKey()))
            ->assertSessionHas('error');

        $p->forceFill(['status' => 'Pendaftaran Diterima'])->save();

        $this->get(route('public.scopuscamp.dokumen', [$p->getKey(), 'kwitansi']))
            ->assertOk();
    }

    #[Test]
    public function jenis_dokumen_yang_tidak_dikenal_ditolak(): void
    {
        /*
         * Jenisnya masuk dari alamat, dan LampiranDokumen memilih berkas
         * tampilan dari namanya. Diteruskan apa adanya, nilai karangan jadi
         * nama tampilan yang dicari di cakram.
         */
        [$p] = $this->pendaftaran();

        $this->get(route('public.scopuscamp.dokumen', [$p->getKey(), 'apa-saja']))
            ->assertNotFound();
    }

    // --------------------------------------------------- surat & tautannya

    #[Test]
    public function bukti_bisa_dikirim_ulang_dan_tercatat(): void
    {
        /*
         * Untuk yang emailnya masuk spam atau terhapus. Tanpa ini satu-satunya
         * jalan mendapatkan nomor dan fakturnya kembali adalah menghubungi
         * panitia.
         *
         * DICATAT di jejak: pendaftar yang berkata "saya tidak terima email"
         * hanya bisa dijawab dari catatan itu.
         */
        [$p] = $this->pendaftaran();

        $this->post(route('public.scopuscamp.kirimulang', $p->getKey()))
            ->assertRedirect(route('public.scopuscamp.status', $p->getKey()))
            ->assertSessionHas('success');

        Mail::assertSent(ScopusCampMail::class, 1);

        $this->assertSame(1, \App\PendaftaranJejak::milik('scopus_camp', (string) $p->getKey())
            ->where('aksi', 'surat')->count(),
            'Pengiriman ulangnya tidak tercatat, jadi "saya tidak terima email" '
            . 'tidak bisa dijawab dari layar mana pun.');
    }

    #[Test]
    public function alamat_yang_disebut_kembali_sebagian_ditutup(): void
    {
        /*
         * Alamat lengkap yang dicetak kembali ke layar membuat halaman ini
         * memberi tahu alamat email pendaftarnya kepada siapa pun yang
         * memegang tautannya — termasuk yang mendapatkannya dari layar orang
         * lain.
         */
        [$p] = $this->pendaftaran(['email' => 'budisantoso@contoh.test']);

        $this->post(route('public.scopuscamp.kirimulang', $p->getKey()));

        $pesan = (string) session('success');

        $this->assertStringNotContainsString('budisantoso@contoh.test', $pesan,
            'Alamat email pendaftarnya dicetak lengkap ke layar.');

        $this->assertStringContainsString('@contoh.test', $pesan,
            'Ranah alamatnya pun ditutup, jadi pengirimnya tidak bisa memastikan '
            . 'suratnya menuju alamat yang benar.');
    }

    #[Test]
    public function borang_tautan_tidak_membocorkan_siapa_yang_terdaftar(): void
    {
        /*
         * Jawaban yang BERBEDA antara alamat terdaftar dan tidak mengubah
         * borang ini jadi alat memeriksa apakah satu orang pernah mendaftar
         * di sini — dan itu keterangan yang tidak boleh diberikan kepada
         * siapa pun yang mengetik alamat orang lain.
         */
        [$p] = $this->pendaftaran();

        $this->post(route('public.scopuscamp.tautansaya'), ['email' => $p->email])
            ->assertSessionHas('success');

        $terdaftar = (string) session('success');

        $this->post(route('public.scopuscamp.tautansaya'),
            ['email' => 'belum-pernah-' . Str::random(8) . '@contoh.test'])
            ->assertSessionHas('success');

        $this->assertSame($terdaftar, (string) session('success'),
            'Jawabannya berbeda antara alamat yang terdaftar dan yang tidak; borang '
            . 'ini jadi alat memeriksa siapa yang pernah mendaftar.');

        // Suratnya hanya untuk yang memang terdaftar.
        Mail::assertSent(TautanPendaftaranScopusCampMail::class, 1);
    }

    #[Test]
    public function surat_tautannya_memuat_alamat_halaman_statusnya(): void
    {
        /*
         * Isi suratnya yang menentukan apakah jalan kembalinya benar-benar
         * ada. Surat yang terkirim tanpa tautan sama saja dengan tidak
         * mengirim apa pun — dan kegagalannya tidak terlihat di mana pun,
         * sebab pengirimannya tetap tercatat berhasil.
         */
        [$p] = $this->pendaftaran();

        $this->post(route('public.scopuscamp.tautansaya'), ['email' => $p->email]);

        Mail::assertSent(TautanPendaftaranScopusCampMail::class,
            function ($surat) use ($p) {
                $isi = $surat->render();

                $this->assertStringContainsString(
                    route('public.scopuscamp.status', $p->getKey()), $isi,
                    'Suratnya terkirim tanpa tautan halaman statusnya.');

                $this->assertStringContainsString($p->id_transaksi, $isi,
                    'Suratnya tidak menyebut nomor pendaftaran mana yang dimaksud.');

                return true;
            });
    }

    // ------------------------------------------------- halaman lainnya

    #[Test]
    public function halaman_selengkapnya_tidak_menawarkan_angkatan_yang_tutup(): void
    {
        /*
         * Terukur 25 Okt 2026 pada angkatan Scopus Camp Padang — 'non active'
         * dan tanggal mulainya sudah lewat sebulan: halamannya menjawab 200
         * dengan DUA tombol "Daftar sekarang", dan menekannya memantulkan
         * orangnya ke daftar angkatan tanpa satu kata pun penjelasan.
         *
         * Halaman ini dulu cuma menghitung sisa kursi — satu dari tiga sebab
         * tertutupnya.
         */
        foreach ([
            ['status' => 'non active'],
            ['mulai' => now()->subDay()->toDateString()],
            ['sisa_kuota' => 0],
        ] as $ganti) {
            $a = $this->angkatan($ganti);

            $isi = $this->get(route('public.scopuscamp.Selengkapnya', $a->getKey()))
                ->assertOk()->getContent();

            $this->assertStringNotContainsString('Daftar sekarang', $isi,
                'Angkatan tertutup (' . json_encode($ganti) . ') masih menawarkan '
                . 'tombol daftar yang ditolak borangnya sendiri.');
        }

        // Yang masih terbuka TIDAK ikut tertutup oleh perbaikan ini.
        $buka = $this->angkatan();

        $this->assertStringContainsString('Daftar sekarang',
            $this->get(route('public.scopuscamp.Selengkapnya', $buka->getKey()))
                ->assertOk()->getContent(),
            'Angkatan yang masih terbuka ikut ditutup.');
    }

    #[Test]
    public function pesan_galat_benar_benar_tergambar_di_halaman_daftar(): void
    {
        /*
         * Borang angkatan tertutup memantulkan orangnya ke halaman daftar
         * dengan ->with('error') — dan sampai 25 Okt 2026 halaman itu tidak
         * menggambar 'error' sama sekali. Terukur: pesannya 0 kali di halaman
         * tujuan, jadi orangnya dipantulkan tanpa penjelasan apa pun.
         */
        $a = $this->angkatan(['status' => 'non active']);

        $this->followingRedirects()
            ->get(route('public.scopuscamp.formpendaftaran', $a->getKey()))
            ->assertOk()
            ->assertSee('belum dibuka untuk pendaftaran umum');
    }

    #[Test]
    public function halaman_daftar_menampilkan_jalan_kembali(): void
    {
        /*
         * Alamat halaman status memuat UUID yang tidak bisa ditebak, jadi yang
         * menutup tabnya dan tidak menerima suratnya kehilangan
         * pendaftarannya untuk selamanya — termasuk tombol bayarnya.
         */
        $this->get(route('public.scopuscamp.index'))
            ->assertOk()
            ->assertSee('Kehilangan tautan pendaftaran Anda?', false)
            ->assertSee(route('public.scopuscamp.tautansaya'), false);
    }

    // ------------------------------------------------------------ pembantu

    private function kirimPemberitahuan(array $isi)
    {
        $jalur = '/Scopus-Camp/pemberitahuan/doku';
        $requestId = (string) Str::uuid();
        $waktu = gmdate('Y-m-d\TH:i:s\Z');
        $json = json_encode($isi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $bahan = implode("\n", [
            'Client-Id:' . self::KLIEN,
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $waktu,
            'Request-Target:' . $jalur,
            'Digest:' . base64_encode(hash('sha256', $json, true)),
        ]);

        $tanda = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $bahan, self::RAHASIA, true)
        );

        return $this->call('POST', $jalur, [], [], [], [
            'HTTP_Client-Id' => self::KLIEN,
            'HTTP_Request-Id' => $requestId,
            'HTTP_Request-Timestamp' => $waktu,
            'HTTP_Signature' => $tanda,
            'CONTENT_TYPE' => 'application/json',
        ], $json);
    }
}
