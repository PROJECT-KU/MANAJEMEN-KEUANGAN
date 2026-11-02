<?php

namespace Tests\Feature\ClassOnline;

use App\CategoriesOnlineTraining;
use App\PendaftaranOnlineTraining;
use App\Support\PendaftaranSemuaLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Jalur pendaftaran Class Online, dari daftar angkatan sampai lunas.
 *
 * Layanan ini SALINAN bentuk Scopus Camp, dan justru itu sebabnya ia perlu
 * ujinya sendiri. Yang salah pada salinan bukan logikanya — logikanya memang
 * sudah terbukti di sana — melainkan sambungannya: nama rute yang tertinggal
 * menunjuk layanan lama, kelas model yang tidak menyatakan kunci untaiannya,
 * butir katalog yang menyebut kelas surat yang tidak ada. Ketiganya sudah
 * benar-benar terjadi saat layanan ini dirakit, dan tidak satu pun terlihat
 * dari membaca kodenya.
 *
 * Jadi yang dijaga berkas ini SAMBUNGANNYA: tiap jalur ditembus sungguhan
 * lewat HTTP, bukan dipanggil sebagai method.
 */
class AlurLengkapTest extends TestCase
{
    use DatabaseTransactions;

    private const KLIEN = 'BRN-UJI-CO';
    private const RAHASIA = 'SK-UJI-CO';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', self::KLIEN);
        config()->set('services.doku.secret_key', self::RAHASIA);

        Mail::fake();

        Http::fake([
            '*/checkout/v1/payment' => Http::response([
                'response' => [
                    'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/UJI-CO'],
                    'order' => ['invoice_number' => 'CO-UJI'],
                ],
            ], 200),

            // Fonnte: dipalsukan supaya ujinya tidak benar-benar mengirim
            // WhatsApp ke nomor siapa pun.
            '*api.fonnte.com/*' => Http::response(['status' => true], 200),
        ]);
    }

    /**
     * Untaian acak BERHURUF KECIL SAJA.
     *
     * Str::random() memulangkan huruf besar juga, dan alamat email yang
     * memuatnya membuat ujinya goyah tanpa sebab yang kelihatan: borangnya
     * menyimpan alamat yang sudah dikecilkan — mb_strtolower() — sementara
     * surat buktinya dikirim ke alamat yang DIKETIK. Jadi hasTo() pada
     * alamat tersimpan tidak cocok, dan ujinya menuduh suratnya tidak pernah
     * berangkat padahal berangkat. Terukur: merah pada satu jalan, hijau
     * pada jalan berikutnya, kode yang sama persis.
     */
    private static function acakKecil(int $panjang): string
    {
        return Str::lower(Str::random($panjang * 2));
    }

    private function angkatan(array $ganti = []): CategoriesOnlineTraining
    {
        return CategoriesOnlineTraining::create(array_merge([
            'token' => Str::random(30),
            'nama' => 'UJI Class Online ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'lokasi' => 'Zoom',
            'group_wa' => 'https://chat.whatsapp.com/ujico',
            'total_kuota' => 20,
            'sisa_kuota' => 20,
            'biaya' => 3500000,
            'status' => 'active',
        ], $ganti));
    }

    /** @return array<string, mixed> */
    private function isian(CategoriesOnlineTraining $a, array $ganti = []): array
    {
        return array_merge([
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Uji Daring',
            'email' => 'uji' . self::acakKecil(6) . '@contoh.test',
            'telp' => '0812' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ], $ganti);
    }

    /**
     * Baris yang BARU muncul sesudah satu kiriman borang.
     *
     * Bukan latest('created_at'): dua pendaftaran yang dibuat dalam detik
     * yang sama membuat urutannya tidak ditentukan, dan ujinya lalu menuduh
     * kode yang benar. Terukur di Scopus Camp — hijau sendirian, merah di
     * rangkaian penuh.
     */
    private function daftar(CategoriesOnlineTraining $a, array $ganti = []): PendaftaranOnlineTraining
    {
        $sebelum = PendaftaranOnlineTraining::where('kategori_id', $a->getKey())
            ->pluck('id')->map(fn ($k) => (string) $k)->all();

        $this->post(route('public.onlinetraining.store'), $this->isian($a, $ganti));

        $baris = PendaftaranOnlineTraining::where('kategori_id', $a->getKey())
            ->whereNotIn('id', $sebelum !== [] ? $sebelum : ['-'])
            ->first();

        $this->assertNotNull($baris, 'Borangnya tidak menghasilkan baris pendaftaran.');

        return $baris;
    }

    // ------------------------------------------------------------- halaman

    #[Test]
    public function daftar_angkatan_memajang_angkatan_yang_terbuka(): void
    {
        $a = $this->angkatan();

        $this->get(route('public.onlinetraining.index'))
            ->assertOk()
            ->assertSee($a->nama)

            // Kalimatnya kelas DARING, bukan salinan "Kelas tatap muka · 3
            // hari" milik Scopus Camp. Itu yang paling mudah tertinggal pada
            // halaman yang disalin, dan yang membacanya datang ke tempat yang
            // tidak ada.
            ->assertSee('Daring · 8 sesi')
            ->assertDontSee('Kelas tatap muka');
    }

    #[Test]
    public function kartu_fasilitas_tidak_digambar_kalau_kosong(): void
    {
        /*
         * Terukur di layar 1440px pada layanan yang tarifnya belum diisi:
         * kotak putih selebar 866px dan setinggi 174px, berbingkai, TANPA
         * SATU HURUF PUN, berdiri di sebelah kartu "Sudah pernah mendaftar?".
         * Halaman ini dirombak justru untuk menghilangkan ruang kosong, dan
         * ini ruang kosong yang paling besar di seluruh halamannya.
         *
         * Sebabnya: fasilitas tidak disimpan di angkatan melainkan diambil
         * dari TARIF induknya, jadi layanan yang tarifnya belum diisi selalu
         * memulangkan daftar kosong. Pengendalinya sudah menyaring daftarnya;
         * yang tidak ikut tersaring KARTUNYA.
         *
         * Tidak ada galat, dan tidak ada uji yang jatuh — halamannya tetap
         * 200. Yang menemukannya potret layar.
         */
        $a = $this->angkatan();

        /*
         * KEADAANNYA DIPASTIKAN, tidak diandaikan.
         *
         * Versi pertama uji ini cuma membuat angkatan lalu mengira
         * fasilitasnya kosong — dan ia hijau hanya selama tarif layanan ini
         * memang belum diisi. Begitu pemiliknya mengisinya lewat layar Tarif
         * Layanan (terukur 2 Nov 2026), uji ini merah tanpa satu baris kode
         * pun berubah. Uji yang bergantung pada keadaan basis data menuduh
         * kode yang tidak bersalah.
         */
        if ($a->daftar_fasilitas !== []) {
            $this->markTestSkipped('Tarif layanan ini sedang berisi fasilitas, jadi kartunya memang digambar.');
        }

        $isi = (string) $this->get(route('public.onlinetraining.index'))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('class="kmp-panel"', $isi);

        // Dan lajurnya ikut menyempit: tanpa ini kartu lacak duduk di lajur
        // pertama dan menyisakan 420px kosong di kanannya.
        $this->assertStringContainsString('kmp-bawah-tunggal', $isi);
    }

    #[Test]
    public function angkatan_yang_sudah_berjalan_tidak_dipajang(): void
    {
        $a = $this->angkatan(['mulai' => now()->subDay()->toDateString()]);

        $this->get(route('public.onlinetraining.index'))
            ->assertOk()
            ->assertDontSee($a->nama);
    }

    #[Test]
    public function halaman_rincian_dan_borangnya_terbuka(): void
    {
        $a = $this->angkatan();

        $this->get(route('public.onlinetraining.Selengkapnya', $a->getKey()))
            ->assertOk()
            ->assertSee($a->nama)

            // "Media kelas", bukan "Lokasi": kelas ini tidak punya tempat
            // yang didatangi, dan "Zoom" di sebelah label "Lokasi" membuat
            // orang mencari alamat.
            ->assertSee('Media kelas')
            ->assertSee('Zoom');

        $this->get(route('public.onlinetraining.formpendaftaran', $a->getKey()))
            ->assertOk()
            ->assertSee('Media kelas');
    }

    #[Test]
    public function halamannya_tidak_pernah_menjanjikan_transfer(): void
    {
        /*
         * Pembayarannya SELURUHNYA lewat gerbang — jalur unggah bukti
         * transfer dicabut 26 Okt 2026, dan layanan ini lahir sesudahnya.
         * Tabelnya bahkan tidak punya kolom `gambar`.
         *
         * Kalimat "lalu transfer" sempat terbawa ke halaman rincian bersama
         * salinannya dari Scopus Camp — terukur di potret layar, tepat di
         * bawah tombol yang justru mengantar ke halaman DOKU. Yang
         * membacanya menunggu nomor rekening yang tidak akan pernah datang,
         * lalu menghubungi panitia menanyakannya.
         */
        $a = $this->angkatan();

        foreach ([
            route('public.onlinetraining.index'),
            route('public.onlinetraining.Selengkapnya', $a->getKey()),
            route('public.onlinetraining.formpendaftaran', $a->getKey()),
        ] as $alamat) {
            $isi = (string) $this->get($alamat)->assertOk()->getContent();

            // Dicari pada ISI halaman saja. Komentar Blade tidak ikut
            // terkirim ke peramban, jadi yang tertangkap di sini memang
            // kalimat yang dibaca orang.
            $this->assertStringNotContainsString('lalu transfer', $isi, $alamat);
            $this->assertStringNotContainsString('nomor rekening', mb_strtolower($isi), $alamat);
        }
    }

    #[Test]
    public function borang_angkatan_tertutup_dipantulkan(): void
    {
        foreach ([
            ['status' => 'non active'],
            ['mulai' => now()->subDay()->toDateString()],
            ['total_kuota' => 5, 'sisa_kuota' => 0],
        ] as $ganti) {
            $a = $this->angkatan($ganti);

            $this->get(route('public.onlinetraining.formpendaftaran', $a->getKey()))
                ->assertRedirect(route('public.onlinetraining.index'));
        }
    }

    // ------------------------------------------------------------ mendaftar

    #[Test]
    public function borang_terkirim_membuat_tagihan_dan_mengantar_ke_doku(): void
    {
        $a = $this->angkatan();

        $jawab = $this->post(route('public.onlinetraining.store'), $this->isian($a));

        $jawab->assertRedirect('https://staging.doku.com/checkout-link-v2/UJI-CO');

        $p = PendaftaranOnlineTraining::where('kategori_id', $a->getKey())->first();

        $this->assertNotNull($p);
        $this->assertSame('doku', $p->cara_bayar);
        $this->assertSame('menunggu', $p->bayar_status);
        $this->assertNotNull($p->bayar_url);
        $this->assertNotNull($p->kedaluwarsa_pada);

        // Nomornya berawalan CO, bukan CAMP: awalan yang tertinggal dari
        // layanan sumber membuat dua layanan berbagi ruang nomor, dan
        // pemberitahuan DOKU mencari barisnya lewat nomor itu.
        $this->assertStringStartsWith('CO', (string) $p->id_transaksi);

        // Statusnya diputuskan peladen, bukan diterima dari borang.
        $this->assertSame(
            PendaftaranSemuaLayanan::statusAwal('online_training'),
            (string) $p->status
        );
    }

    #[Test]
    public function kursinya_benar_benar_dipotong(): void
    {
        $a = $this->angkatan(['total_kuota' => 10, 'sisa_kuota' => 10]);

        $this->daftar($a, ['jumlah_pendaftar' => 3, 'peserta' => [
            ['nama' => 'Peserta Dua', 'telp' => '081200000002'],
            ['nama' => 'Peserta Tiga', 'telp' => '081200000003'],
        ]]);

        $this->assertSame(7, (int) $a->refresh()->sisa_kuota);
    }

    #[Test]
    public function total_tagihannya_dihitung_peladen_bukan_diterima_dari_borang(): void
    {
        /*
         * Terukur di Scopus Camp 31 Okt 2026: `nominal_diskon` diambil mentah
         * dari kiriman borang, jadi kursi Rp 5.500.000 tersimpan dengan
         * tagihan Rp 2.039 — dan tagihan DOKU-nya benar-benar terbit atas
         * angka itu. Layanan ini memakai PotonganKode yang sama; ujinya di
         * sini memastikan sambungannya memang lewat ke sana.
         */
        $a = $this->angkatan(['biaya' => 3500000]);

        /*
         * Harga acuannya DIBACA, bukan ditulis ulang di sini.
         *
         * Sejak 2 Nov 2026 layanan ini membaca harganya dari Tarif Layanan,
         * sama seperti Class Offline — jadi `biaya` di angkatan bukan lagi
         * angka yang ditagihkan. Ditulis 3.500.000 di sini, uji ini merah
         * tiap kali pemiliknya mengubah tarifnya, dan merahnya menunjuk
         * kodenya padahal yang berubah tarifnya.
         */
        $dasar = \App\Support\PromoAngkatan::hargaDasar($a);

        $p = $this->daftar($a, [
            'kode_diskon' => 'KODE-KARANGAN-' . Str::random(5),
            'nominal_diskon' => (string) max(1, $dasar - 1000),
            'total_pembayaran' => '1000',
            'ppn' => '0',
        ]);

        $this->assertNull($p->nominal_diskon);
        $this->assertGreaterThanOrEqual($dasar, (int) $p->total_pembayaran);
    }

    #[Test]
    public function surat_bukti_dan_kabar_whatsapp_berangkat(): void
    {
        $a = $this->angkatan();

        $p = $this->daftar($a);

        Mail::assertSent(\App\Mail\OnlineTrainingMail::class, function ($surat) use ($p) {
            return $surat->hasTo($p->email);
        });

        // Butir katalognya ADA. Tanpa itu KabariWhatsApp melewatinya diam-diam
        // dan pendaftar tidak pernah dikabari ke mana pun.
        $this->assertTrue(
            \App\Actions\Pendaftaran\KabariWhatsApp::melayani('online_training')
        );
    }

    // ---------------------------------------------------------------- promo

    #[Test]
    public function promo_tanpa_kode_mencoret_harga_awalnya(): void
    {
        $a = $this->angkatan(['biaya' => 3500000]);

        // Harganya dari Tarif Layanan sejak 2 Nov 2026; dibaca, bukan
        // ditulis ulang. Lihat alasannya di uji total tagihan.
        $dasar = \App\Support\PromoAngkatan::hargaDasar($a);

        /*
         * TANPA `kode`: itu yang membuatnya promo otomatis, dan hanya promo
         * otomatis yang mencoret harga di halaman publik. Sasarannya angkatan
         * yang dicentang satu-satu, bukan nama layanannya.
         */
        $promo = \App\PromoLayanan::create([
            'nama' => 'UJI Promo Daring ' . Str::random(5),
            'tipe' => 'persen',
            'persen' => 10,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(20)->toDateString(),
            'aktif' => true,
        ]);

        $promo->angkatan()->attach($a->getKey());
        \App\Support\PromoAngkatan::lupakan();

        $this->get(route('public.onlinetraining.Selengkapnya', $a->getKey()))
            ->assertOk()

            // Harga promonya yang dipajang, harga awalnya dicoret. Itu tipe
            // promo pertama dari ketiga tipe yang disebut pemiliknya.
            ->assertSee(number_format((int) round($dasar * 0.9), 0, ',', '.'))
            ->assertSee(number_format($dasar, 0, ',', '.'));

        $promo->angkatan()->detach();
        $promo->forceDelete();
        \App\Support\PromoAngkatan::lupakan();
    }

    #[Test]
    public function harganya_dibaca_dari_tarif_layanan_bukan_dari_kolom_angkatan(): void
    {
        /*
         * KEPUTUSAN UANG, 2 Nov 2026.
         *
         * Layanan ini membaca harganya dari Tarif Layanan, sama seperti Class
         * Offline — bukan dari kolom `biaya`/`total_biaya` yang diketik ulang
         * tiap angkatan baru dibuat. Alasannya yang sama: terukur pada
         * angkatan Scopus Camp ke-202, layar Tarif Layanan menyebut
         * Rp 5.500.000 sementara halaman umum memajang Rp 4.950.000, dan
         * tidak ada satu pun layar yang menjelaskan dari mana selisihnya.
         *
         * Uji ini SENGAJA menyetel kolom angkatannya ke angka yang jelas
         * berbeda dari tarifnya. Kalau 'online_training' dicabut dari daftar
         * di PromoAngkatan::hargaDasar(), angka yang dipajang berbalik ke
         * kolom itu dan uji ini merah — yang memang harus, sebab yang berubah
         * nominal yang ditagihkan kepada orang.
         */
        $tarif = \App\ClinikScopusBiayaPersesi::berlaku('online_training', null);

        if ($tarif === null) {
            $this->markTestSkipped('Tarif Layanan untuk Class Online belum disetel.');
        }

        $dariTarif = (int) preg_replace('/\D+/', '', (string) $tarif->biaya_persesi);

        if ($dariTarif <= 0) {
            $this->markTestSkipped('Tarif Layanan untuk Class Online belum berisi nominal.');
        }

        // Angka yang SENGAJA berbeda dari tarifnya.
        $a = $this->angkatan(['biaya' => $dariTarif + 777000, 'total_biaya' => $dariTarif + 777000]);

        $this->assertSame($dariTarif, \App\Support\PromoAngkatan::hargaDasar($a));

        // Dan yang DITAGIH, bukan cuma yang dihitung.
        $p = $this->daftar($a);

        $this->assertGreaterThanOrEqual($dariTarif, (int) $p->total_pembayaran);
        $this->assertLessThan($dariTarif + 777000, (int) $p->total_pembayaran);
    }

    #[Test]
    public function kode_milik_angkatan_memotong_lewat_jalur_yang_sama(): void
    {
        $kode = 'UJICO' . Str::random(5);

        $a = $this->angkatan([
            'biaya' => 3500000,
            'kode_diskon' => $kode,
            'tipe_diskon' => 'nominal',
            'nominal_diskon' => 500000,
        ]);

        // Pratinjaunya — tombol "Cek" di borang.
        $pratinjau = $this->getJson(route('public.onlinetraining.cekkodediskon', [
            'id' => $a->getKey(), 'kode_diskon' => $kode, 'jumlah' => 1,
        ]));

        $pratinjau->assertOk()->assertJsonPath('status', 'success');

        $dipratinjau = (int) $pratinjau->json('casted_nominal_diskon');

        // Dan yang menagih. Keduanya WAJIB sepakat: terukur di Scopus Camp,
        // tombol "Cek" menolak kode yang justru DIPOTONG peladen — layar dan
        // tagihan berselisih, dan yang membacanya menyerah sebelum tahu
        // potongannya ada.
        $p = $this->daftar($a, ['kode_diskon' => $kode]);

        $this->assertSame($dipratinjau, (int) $p->nominal_diskon);
        $this->assertSame($kode, (string) $p->kode_diskon);
    }

    // --------------------------------------------------------- halaman status

    #[Test]
    public function halaman_statusnya_terbuka_dan_katalog_menunjuk_rute_yang_ada(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->get(route('public.onlinetraining.status', $p->getKey()))
            ->assertOk()
            ->assertSee($p->id_transaksi);

        /*
         * Katalognya menunjuk rute yang BENAR-BENAR ADA.
         *
         * Sempat diisi nama rute yang belum dibuat, dan layar rincian panitia
         * 500 seketika — terukur begitu layanan ini didaftarkan. Nilainya
         * boleh null; yang tidak boleh nama yang tidak terdaftar.
         */
        $rute = PendaftaranSemuaLayanan::sumber('online_training')['rute_status'] ?? null;

        $this->assertNotNull($rute);
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has($rute),
            'rute_status katalog menunjuk rute yang tidak terdaftar: ' . $rute
        );
    }

    #[Test]
    public function keadaan_ringkasnya_tidak_pernah_memulangkan_data_diri(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $jawab = $this->getJson(route('public.onlinetraining.keadaan', $p->getKey()));

        $jawab->assertOk()
            ->assertJsonPath('ada', true)
            ->assertJsonPath('lunas', false);

        // Nama, email, nominal: tidak satu pun boleh ikut. Alamat ini tidak
        // memakai sesi, jadi siapa pun yang memegang tautannya bisa
        // memanggilnya.
        $this->assertSame(
            ['ada', 'lunas', 'selesai'],
            array_keys($jawab->json())
        );
    }

    #[Test]
    public function model_menemukan_barisnya_lewat_kunci_untaian(): void
    {
        /*
         * $keyType = 'string' DAN $incrementing = false, keduanya.
         *
         * Tanpa keduanya Eloquent memakai bawaannya — 'int' + incrementing —
         * dan getCasts() lalu menyisipkan `id => int` sendiri. Kuncinya
         * tercor jadi angka: terukur, getKey() pada baris ber-UUID
         * memulangkan 0, dan layar rincian panitia 404 untuk baris yang jelas
         * ada.
         *
         * KEDUANYA disebut terpisah di bawah, bukan cuma akibatnya.
         * Pencorannya hanya terjadi kalau keduanya salah sekaligus — jadi
         * memeriksa akibatnya saja membuat uji ini HIJAU sementara separuh
         * pernyataannya sudah hilang, dan separuh yang tinggal itu akan
         * dibuang orang berikutnya karena "tidak ada yang rusak kalau
         * dihapus". Terukur: dengan satu dari dua dimutasi, uji ini tetap
         * hijau.
         */
        $m = new PendaftaranOnlineTraining;

        $this->assertSame('string', $m->getKeyType());
        $this->assertFalse($m->getIncrementing());

        // Dan akibatnya, pada baris yang sungguhan.
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $ketemu = PendaftaranOnlineTraining::find((string) $p->getKey());

        $this->assertNotNull($ketemu);
        $this->assertSame((string) $p->getKey(), (string) $ketemu->getKey());
        $this->assertNotSame('0', (string) $ketemu->getKey());
    }

    // ------------------------------------------------------- pemberitahuan

    /** @param array<string, mixed> $isi */
    private function kirimPemberitahuan(array $isi, bool $benar = true)
    {
        $jalur = '/Class-Online/pemberitahuan/doku';
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
            hash_hmac('sha256', $bahan, $benar ? self::RAHASIA : 'SALAH', true)
        );

        return $this->call('POST', $jalur, [], [], [], [
            'HTTP_Client-Id' => self::KLIEN,
            'HTTP_Request-Id' => $requestId,
            'HTTP_Request-Timestamp' => $waktu,
            'HTTP_Signature' => $tanda,
            'CONTENT_TYPE' => 'application/json',
        ], $json);
    }

    /** @return array<string, mixed> */
    private function kiriman(PendaftaranOnlineTraining $p, string $status = 'SUCCESS'): array
    {
        return [
            'order' => ['invoice_number' => $p->bayar_nomor ?: $p->id_transaksi],
            'transaction' => ['status' => $status, 'date' => now()->toIso8601String()],
            'service' => ['id' => 'VIRTUAL_ACCOUNT'],
            'virtual_account_info' => ['virtual_account_number' => '8888801234567890'],
            'acquirer' => ['id' => 'MANDIRI'],
        ];
    }

    #[Test]
    public function pemberitahuan_bertanda_tangan_salah_tidak_melunaskan_apa_pun(): void
    {
        /*
         * Alamat ini dikecualikan dari CSRF, jadi tanda tangannya satu-satunya
         * penjaga. Tanpa diperiksa, siapa pun yang tahu alamatnya bisa
         * menandai pendaftaran mana pun lunas dengan satu POST.
         */
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->kirimPemberitahuan($this->kiriman($p), false)->assertStatus(401);

        $this->assertNotSame('Pendaftaran Diterima', (string) $p->refresh()->status);
        $this->assertNull($p->bayar_pada);
    }

    #[Test]
    public function pemberitahuan_sah_melunaskan_dan_mengirim_kwitansinya(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->kirimPemberitahuan($this->kiriman($p))->assertOk();

        $p->refresh();

        $this->assertSame('Pendaftaran Diterima', (string) $p->status);
        $this->assertNotNull($p->bayar_pada);

        // Kanal yang BENAR-BENAR dipakai, bukan sekadar "DOKU".
        $this->assertNotEmpty($p->bayar_kanal);

        /*
         * Suratnya lewat KirimSuratStatus, yang memilih kelasnya dari
         * katalog. Katalog menyebut OnlineTrainingUpdateDiterimaMail; kelas
         * itu harus benar-benar ada, kalau tidak jalur ini melempar di dalam
         * pemberitahuan yang sudah dibayar orangnya.
         */
        Mail::assertSent(\App\Mail\OnlineTrainingUpdateDiterimaMail::class);
    }

    #[Test]
    public function pemberitahuan_yang_sama_dua_kali_hanya_melunaskan_sekali(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->kirimPemberitahuan($this->kiriman($p))->assertOk();

        $pertama = $p->refresh()->bayar_pada;

        Mail::fake();

        // DOKU boleh mengirim pemberitahuan yang sama lebih dari sekali, dan
        // dua kali proses berarti dua kwitansi ke orang yang sama.
        $this->kirimPemberitahuan($this->kiriman($p))->assertOk();

        Mail::assertNothingSent();
        $this->assertEquals($pertama, $p->refresh()->bayar_pada);
    }

    #[Test]
    public function pembayaran_gagal_tidak_membatalkan_pendaftarannya(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->kirimPemberitahuan($this->kiriman($p, 'FAILED'))->assertOk();

        $p->refresh();

        // Statusnya TIDAK diturunkan: 'Pendaftaran Dibatalkan' keputusan
        // panitia, bukan keputusan gerbang, dan ia menyeret surat pembatalan
        // ke pendaftarnya.
        $this->assertSame(
            PendaftaranSemuaLayanan::statusAwal('online_training'),
            (string) $p->status
        );
        $this->assertSame('failed', (string) $p->bayar_status);
    }

    // ----------------------------------------------------- jalur pemulihan

    #[Test]
    public function tagihan_bisa_dibuat_ulang_dan_nomornya_tidak_dipakai_dua_kali(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $nomorPertama = (string) $p->bayar_nomor;

        $this->post(route('public.onlinetraining.bayarulang', $p->getKey()))
            ->assertRedirect('https://staging.doku.com/checkout-link-v2/UJI-CO');

        $p->refresh();

        // DOKU menolak nomor yang sudah pernah dipakai dengan "INVOICE
        // ALREADY USED", jadi percobaan kedua memakai -R2.
        $this->assertNotSame($nomorPertama, (string) $p->bayar_nomor);
        $this->assertStringEndsWith('-R2', (string) $p->bayar_nomor);

        // Tenggatnya disetel ulang; dibiarkan, tombol bayar disembunyikan
        // untuk tagihan yang justru baru hidup.
        $this->assertTrue($p->kedaluwarsa_pada->isFuture());
    }

    #[Test]
    public function yang_sudah_lunas_tidak_bisa_membuat_tagihan_lagi(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->kirimPemberitahuan($this->kiriman($p))->assertOk();

        $this->post(route('public.onlinetraining.bayarulang', $p->getKey()))
            ->assertRedirect(route('public.onlinetraining.status', $p->getKey()));

        // Tidak ada tagihan kedua: uangnya sudah masuk, dan tagihan kedua
        // membuat orangnya membayar dua kali.
        $this->assertSame('Pendaftaran Diterima', (string) $p->refresh()->status);
    }

    #[Test]
    public function faktur_bisa_diunduh_dan_kwitansi_hanya_setelah_lunas(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->get(route('public.onlinetraining.dokumen', [
            'id' => $p->getKey(), 'jenis' => 'faktur',
        ]))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        // Kwitansi menyatakan uangnya sudah diterima; diberikan sebelum itu,
        // ia surat palsu yang kami tanda tangani sendiri.
        $this->get(route('public.onlinetraining.dokumen', [
            'id' => $p->getKey(), 'jenis' => 'kwitansi',
        ]))->assertRedirect(route('public.onlinetraining.status', $p->getKey()));

        $this->kirimPemberitahuan($this->kiriman($p))->assertOk();

        $this->get(route('public.onlinetraining.dokumen', [
            'id' => $p->getKey(), 'jenis' => 'kwitansi',
        ]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function tautan_pendaftaran_dikirim_ke_email_bukan_ditampilkan(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $jawab = $this->post(route('public.onlinetraining.tautansaya'), [
            'email' => $p->email,
        ]);

        $jawab->assertSessionHas('success');

        // Tautannya TIDAK pernah muncul di layar: ditampilkan, siapa pun yang
        // menebak alamat email orang lain bisa membuka pendaftarannya.
        $this->assertStringNotContainsString(
            (string) $p->getKey(),
            (string) session('success')
        );

        Mail::assertSent(\App\Mail\TautanPendaftaranOnlineTrainingMail::class);
    }

    #[Test]
    public function alamat_yang_tidak_terdaftar_dijawab_sama_persis(): void
    {
        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->post(route('public.onlinetraining.tautansaya'), ['email' => $p->email]);
        $terdaftar = (string) session('success');

        $this->post(route('public.onlinetraining.tautansaya'), [
            'email' => 'tidak-ada-' . Str::random(8) . '@contoh.test',
        ]);

        // Jawaban yang berbeda mengubah borang ini jadi alat memeriksa
        // apakah satu alamat email pernah mendaftar di sini.
        $this->assertSame($terdaftar, (string) session('success'));
    }

    // ------------------------------------------------------------- tenggat

    #[Test]
    public function kursinya_dilepas_begitu_tenggatnya_lewat(): void
    {
        $a = $this->angkatan(['total_kuota' => 10, 'sisa_kuota' => 10]);
        $p = $this->daftar($a, ['jumlah_pendaftar' => 2, 'peserta' => [
            ['nama' => 'Peserta Dua', 'telp' => '081200000002'],
        ]]);

        $this->assertSame(8, (int) $a->refresh()->sisa_kuota);

        $p->forceFill(['kedaluwarsa_pada' => now()->subMinute()])->save();

        // Ditegakkan oleh halaman yang sedang dibuka orangnya, bukan hanya
        // oleh cron — cron hPanel di peladen ini belum terpasang.
        $this->get(route('public.onlinetraining.status', $p->getKey()))->assertOk();

        $this->assertSame(10, (int) $a->refresh()->sisa_kuota);
        $this->assertSame('expired', (string) $p->refresh()->bayar_status);
    }

    #[Test]
    public function perintah_sapuannya_terdaftar_dan_jalan(): void
    {
        $a = $this->angkatan(['total_kuota' => 10, 'sisa_kuota' => 10]);
        $p = $this->daftar($a);

        $p->forceFill(['kedaluwarsa_pada' => now()->subMinute()])->save();

        $this->artisan('online-training:kedaluwarsakan')->assertExitCode(0);

        $this->assertSame('expired', (string) $p->refresh()->bayar_status);
    }
}
