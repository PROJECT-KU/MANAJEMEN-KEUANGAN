<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penjagaan borang pendaftaran Scopus Camp.
 *
 * Semuanya lahir dari satu pemeriksaan 23 Okt 2026 yang menemukan borang ini
 * menerima apa saja. Angka-angka di komentar di bawah adalah hasil pengukuran
 * ke borang yang sungguhan, bukan perkiraan.
 */
class PenjagaanBorangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-JAGA');
        config()->set('services.doku.secret_key', 'SK-UJI-JAGA');

        Mail::fake();

        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => [
                'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/UJI'],
                'order' => ['invoice_number' => 'CAMP-UJI'],
            ],
        ], 200)]);
    }

    private function angkatan(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Jaga ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'group_wa' => 'https://chat.whatsapp.com/ujijaga',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ], $ganti));
    }

    /** @return array<string, mixed> */
    private function isian(CategoriesScopusCamp $a, array $ganti = []): array
    {
        return array_merge([
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Uji',
            'email' => 'uji' . Str::random(6) . '@contoh.test',
            'telp' => '081234567890',
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ], $ganti);
    }

    private function jumlahBaris(CategoriesScopusCamp $a): int
    {
        return PendaftaranScopusCamp::where('kategori_id', $a->getKey())->count();
    }

    // ------------------------------------------------------------ validasi

    #[Test]
    public function borang_kosong_tidak_pernah_jadi_pendaftaran(): void
    {
        /*
         * Terukur sebelum ada validasi: borang KOSONG SAMA SEKALI diterima,
         * tersimpan dengan nama, email, dan telepon NULL bertiga, dan
         * tagihan DOKU sungguhan terbit atasnya senilai Rp 4.501.027.
         * Yang menjaga cuma atribut `required` di peramban.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), ['kategori_id' => $a->getKey()])
            ->assertSessionHasErrors(['nama', 'email', 'telp', 'affiliasi']);

        $this->assertSame(0, $this->jumlahBaris($a),
            'Borang kosong tetap tersimpan sebagai pendaftaran.');
    }

    #[Test]
    public function email_dan_nomor_yang_tidak_masuk_akal_ditolak(): void
    {
        /*
         * Terukur: email='BUKAN-EMAIL' dan telp='abc' tersimpan apa adanya,
         * dan tagihannya tetap terbit. Panitia memegang pendaftaran yang
         * tidak bisa dihubungi dengan cara apa pun, dan surat buktinya tidak
         * pernah sampai ke siapa pun.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'),
            $this->isian($a, ['email' => 'BUKAN-EMAIL', 'telp' => 'abc']))
            ->assertSessionHasErrors(['email', 'telp']);

        $this->assertSame(0, $this->jumlahBaris($a));
    }

    #[Test]
    public function tanpa_mencentang_ketentuan_tidak_bisa_mendaftar(): void
    {
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, ['setuju' => null]))
            ->assertSessionHasErrors(['setuju']);

        $this->assertSame(0, $this->jumlahBaris($a));
    }

    #[Test]
    public function status_lunas_tetap_tidak_bisa_dikirim_lewat_borang(): void
    {
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'),
            $this->isian($a, ['status' => 'Pendaftaran Diterima']));

        $this->assertSame('diproses',
            PendaftaranScopusCamp::where('kategori_id', $a->getKey())->first()->status);
    }

    #[Test]
    public function catatan_panitia_tidak_bisa_diisi_dari_borang(): void
    {
        /*
         * `note` adalah kolom catatan PANITIA — dibaca dan disunting di layar
         * Pendaftar Layanan. Sampai 23 Okt 2026 isinya bisa ditentukan siapa
         * pun yang mengirim borang ini, sebanyak apa pun, padahal borangnya
         * sendiri memang tidak pernah punya medan itu.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'),
            $this->isian($a, ['note' => 'SUDAH LUNAS, tolong diterima']));

        $this->assertNull(
            PendaftaranScopusCamp::where('kategori_id', $a->getKey())->first()->note,
            'Catatan panitia bisa diisi dari borang umum.'
        );
    }

    // ------------------------------------------------- angkatan tertutup

    /** @return array<string, array{array<string, mixed>}> */
    public static function angkatanTertutup(): array
    {
        return [
            'masih draft' => [['status' => 'non active']],
            'tanggalnya sudah lewat' => [['mulai' => '2020-01-01', 'selesai' => '2020-01-03']],
            'kursinya habis' => [['sisa_kuota' => 0]],
        ];
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('angkatanTertutup')]
    public function borang_angkatan_tertutup_tidak_digambar(array $ganti): void
    {
        /*
         * Terukur sebelum ada penjagaan ini: ketiganya menjawab 200, dan
         * kiriman ke angkatan draft yang tanggalnya SUDAH LEWAT diterima
         * lengkap dengan tagihan DOKU yang benar-benar terbit.
         *
         * Halaman daftarnya memang menyembunyikan ketiganya — tetapi alamat
         * borangnya tetap bisa dibuka dari tautan lama, surat, atau penanda
         * buku.
         */
        $a = $this->angkatan($ganti);

        $this->get(route('public.scopuscamp.formpendaftaran', ['id' => $a->getKey()]))
            ->assertRedirect(route('public.scopuscamp.index'))
            ->assertSessionHas('error');
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('angkatanTertutup')]
    public function kiriman_ke_angkatan_tertutup_ditolak(array $ganti): void
    {
        $a = $this->angkatan($ganti);

        $this->post(route('public.scopuscamp.store'), $this->isian($a))
            ->assertRedirect(route('public.scopuscamp.index'));

        $this->assertSame(0, $this->jumlahBaris($a),
            'Pendaftaran ke angkatan yang sudah tertutup tetap tersimpan.');
    }

    // ------------------------------------------------------------- kursi

    #[Test]
    public function kursi_yang_dibatalkan_kembali_bisa_dijual(): void
    {
        /*
         * Kuotanya dulu dihitung dari SELURUH baris, termasuk yang sudah
         * dibatalkan. Terukur di basis data 23 Okt 2026: 23 kursi tertahan
         * di 5 angkatan — Scopus Camp Yogyakarta #199 terbaca "tinggal 4
         * kursi" padahal 15 dari 16 yang terpakai milik pendaftaran yang
         * sudah batal. Kursi yang tidak terjual karena sistemnya salah
         * hitung, tanpa satu galat pun yang menunjukkannya.
         */
        $a = $this->angkatan(['total_kuota' => 10, 'sisa_kuota' => 10]);

        PendaftaranScopusCamp::create([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-BATAL-' . Str::random(6),
            'kategori_id' => $a->getKey(),
            'nama' => 'Sudah Batal', 'email' => 'batal' . Str::random(4) . '@contoh.test',
            'telp' => '6281200000000', 'jumlah_pendaftar' => 7,
            'total_pembayaran' => '1', 'status' => 'Pendaftaran Dibatalkan',
        ]);

        $this->assertSame(10,
            \App\Http\Controllers\Publict\PublicScopusCampController::hitungUlangSisaKursi($a->refresh()),
            'Kursi pendaftaran yang sudah dibatalkan masih dihitung terpakai.');
    }

    #[Test]
    public function kursi_yang_masih_hidup_tetap_dihitung_terpakai(): void
    {
        // Sisi sebaliknya. Tanpa ini, "kursi batal dikembalikan" bisa dibuat
        // hijau dengan mengembalikan SEMUA kursi.
        $a = $this->angkatan(['total_kuota' => 10, 'sisa_kuota' => 10]);

        PendaftaranScopusCamp::create([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-HIDUP-' . Str::random(6),
            'kategori_id' => $a->getKey(),
            'nama' => 'Masih Hidup', 'email' => 'hidup' . Str::random(4) . '@contoh.test',
            'telp' => '6281200000001', 'jumlah_pendaftar' => 3,
            'total_pembayaran' => '1', 'status' => 'diproses',
        ]);

        $this->assertSame(7,
            \App\Http\Controllers\Publict\PublicScopusCampController::hitungUlangSisaKursi($a->refresh()));
    }

    #[Test]
    public function tidak_bisa_memesan_lebih_banyak_daripada_kursi_yang_ada(): void
    {
        $a = $this->angkatan(['total_kuota' => 3, 'sisa_kuota' => 3]);

        $this->post(route('public.scopuscamp.store'),
            $this->isian($a, ['jumlah_pendaftar' => 5]))
            ->assertRedirect();

        $this->assertSame(0, $this->jumlahBaris($a),
            'Pendaftaran melebihi sisa kursi tetap tersimpan.');
    }

    #[Test]
    public function rombongan_tersimpan_sebagai_satu_pendaftaran(): void
    {
        /*
         * `jumlah_pendaftar` dulu isian TERSEMBUNYI bernilai 1 yang tidak
         * bisa diubah siapa pun, padahal peladen dan rumus harganya sudah
         * mendukung lebih dari satu. Satu kantor yang mengirim tiga orang
         * harus mengisi borangnya tiga kali, dengan tiga pembayaran terpisah
         * yang harus dicocokkan panitia satu per satu.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'jumlah_pendaftar' => 3,
            // Nama peserta lainnya kini WAJIB sebanyak yang dipesan dikurangi
            // satu; aturannya sendiri diuji di bawah.
            'peserta' => [
                ['nama' => 'Peserta Dua'],
                ['nama' => 'Peserta Tiga'],
            ],
        ]));

        $p = PendaftaranScopusCamp::where('kategori_id', $a->getKey())->firstOrFail();

        $this->assertSame(3, (int) $p->jumlah_pendaftar);

        $this->assertSame(3 * (int) $a->harga_setelah_promo + (int) $p->kode_unik,
            (int) $p->total_pembayaran,
            'Totalnya tidak dikalikan jumlah pesertanya.');

        $this->assertSame(17, (int) $a->refresh()->sisa_kuota,
            'Kursinya tidak dipotong sebanyak pesertanya.');
    }

    // ------------------------------------------------ peserta rombongan

    #[Test]
    public function nama_peserta_lain_ikut_tersimpan(): void
    {
        /*
         * Tanpa ini, mendaftarkan tiga orang menagih biaya tiga orang lalu
         * menyimpan SATU nama. Panitia memegang daftar hadir berisi satu,
         * dua sertifikat tidak bisa diterbitkan atas nama siapa pun, dan
         * sisanya harus dikejar lewat WhatsApp satu per satu.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'nama' => 'Ketua Rombongan',
            'jumlah_pendaftar' => 3,
            // Nomornya SENGAJA berbeda dari nomor ketua di isian(): nomor
            // kembar memang ditolak, dan aturannya diuji tersendiri di bawah.
            'peserta' => [
                ['nama' => 'Peserta Dua', 'telp' => '0857-1111-2222'],
                ['nama' => 'Peserta Tiga', 'telp' => ''],
            ],
        ]));

        $p = PendaftaranScopusCamp::where('kategori_id', $a->getKey())->firstOrFail();
        $lain = \App\PendaftaranPeserta::milik('scopus_camp', (string) $p->getKey())
            ->terurut()->get();

        $this->assertCount(2, $lain, 'Nama peserta rombongannya tidak tersimpan.');

        $this->assertSame('Peserta Dua', $lain[0]->nama);
        $this->assertSame('Peserta Tiga', $lain[1]->nama);

        /*
         * Nomornya DINORMALKAN ke bentuk 62…, sama seperti nomor pendaftar
         * utamanya dan sama seperti ketujuh baris yang sudah ditulis jalur
         * panitia. Dua bentuk di satu kolom membuat tiap pembacanya harus
         * memeriksa keduanya.
         */
        $this->assertSame('6285711112222', $lain[0]->telp);

        // Kosong disimpan NULL, bukan untaian kosong.
        $this->assertNull($lain[1]->telp);

        /*
         * Urutannya mulai dari SATU. Urutan 0 dipegang pendaftar utamanya,
         * yang tersimpan di baris pendaftarannya sendiri — perjanjian yang
         * sudah dipakai jalur panitia saat menyunting daftar peserta, dan
         * ketujuh baris yang sudah ada di basis data bernomor 1 sampai 7.
         */
        $this->assertSame(1, (int) $lain[0]->urutan);
        $this->assertSame(2, (int) $lain[1]->urutan);

        // Pendaftar utamanya tetap jadi peserta pertama di daftar gabungannya.
        $semua = $p->semuaPeserta();
        $this->assertCount(3, $semua);
        $this->assertSame('Ketua Rombongan', $semua[0]['nama']);
        $this->assertTrue($semua[0]['utama']);
    }

    #[Test]
    public function nomor_peserta_yang_tidak_masuk_akal_ditolak(): void
    {
        /*
         * Nomor sepotong seperti "0812" tersimpan apa adanya dan baru
         * ketahuan saat panitia menekannya dan tidak ada yang menjawab.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'jumlah_pendaftar' => 2,
            'peserta' => [['nama' => 'Peserta Dua', 'telp' => '0812']],
        ]))->assertSessionHasErrors(['peserta']);

        $this->assertSame(0, $this->jumlahBaris($a));
    }

    #[Test]
    public function nomor_peserta_tidak_boleh_kembar_walau_beda_tanda_hubung(): void
    {
        /*
         * "0812-3456-7890" dan "081234567890" adalah nomor yang SAMA, tetapi
         * sebagai untaian keduanya berbeda — jadi memeriksa kembarnya pada
         * teks mentah akan meloloskan dua peserta bernomor sama yang cuma
         * beda tanda hubung, dan satu di antaranya tidak akan pernah
         * dihubungi.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'telp' => '081234567890',
            'jumlah_pendaftar' => 2,
            'peserta' => [['nama' => 'Peserta Dua', 'telp' => '0812-3456-7890']],
        ]))->assertSessionHasErrors(['peserta']);

        $this->assertSame(0, $this->jumlahBaris($a));
    }

    #[Test]
    public function borang_meminta_nomor_peserta_bukan_emailnya(): void
    {
        /*
         * Keputusan pemiliknya 23 Okt 2026, dan itu juga yang sudah dipakai
         * jalur panitia: kotak daftar pesertanya berbentuk "Budi Santoso,
         * 081234567890", dan ketujuh baris peserta yang ada di basis data
         * punya `telp` terisi dengan `email` NULL bertujuh. Meminta email di
         * borang umum berarti mengisi kolom yang tidak dibaca siapa pun.
         */
        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran',
            ['id' => $this->angkatan()->getKey()]))->assertOk()->getContent();

        $this->assertStringNotContainsString("peserta[' + i + '][email]", $isi,
            'Borangnya masih meminta email peserta.');

        $this->assertStringContainsString("'telp', 'Nomor WhatsApp'", $isi,
            'Baris pesertanya tidak lagi meminta nomor WhatsApp.');
    }

    #[Test]
    public function pesertanya_disimpan_untuk_layanan_yang_benar(): void
    {
        /*
         * Tabelnya DIPAKAI BERSAMA kelima layanan dan dibedakan kolom
         * `layanan`. Salah mengisinya membuat nama peserta Scopus Camp muncul
         * di rincian pendaftaran layanan lain — dan hilang dari rinciannya
         * sendiri.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'jumlah_pendaftar' => 2,
            'peserta' => [['nama' => 'Peserta Dua']],
        ]));

        $p = PendaftaranScopusCamp::where('kategori_id', $a->getKey())->firstOrFail();

        $this->assertSame(1, \App\PendaftaranPeserta::milik('scopus_camp', (string) $p->getKey())->count());
        $this->assertSame(0, \App\PendaftaranPeserta::milik('webinar_eksklusif', (string) $p->getKey())->count());
    }

    #[Test]
    public function jumlah_nama_harus_sama_dengan_jumlah_peserta(): void
    {
        /*
         * Diperiksa di PELADEN, bukan hanya di peramban: borang yang dikirim
         * tanpa JavaScript lolos begitu saja, dan yang tersimpan pendaftaran
         * tiga orang dengan satu nama.
         */
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'jumlah_pendaftar' => 3,
            'peserta' => [['nama' => 'Cuma Satu Nama']],
        ]))->assertSessionHasErrors(['peserta']);

        $this->assertSame(0, $this->jumlahBaris($a));
    }

    #[Test]
    public function nama_peserta_kosong_ditolak(): void
    {
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'jumlah_pendaftar' => 2,
            'peserta' => [['nama' => '', 'telp' => '0812-3456-7890']],
        ]))->assertSessionHasErrors();

        $this->assertSame(0, $this->jumlahBaris($a));
    }

    #[Test]
    public function pendaftaran_satu_orang_tidak_menuntut_nama_tambahan(): void
    {
        // Sisi sebaliknya: pemeriksaan jumlah nama tidak boleh menghalangi
        // pendaftaran satu orang, yaitu hampir semuanya.
        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $this->jumlahBaris($a));
    }

    #[Test]
    public function borang_menyediakan_tempat_mengisi_nama_peserta_lain(): void
    {
        /*
         * Isian jumlah peserta tanpa tempat mengisi namanya adalah fitur
         * setengah jadi: orangnya memesan lima kursi, membayar lima, lalu
         * tidak pernah diminta menyebut siapa saja yang datang.
         */
        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran',
            ['id' => $this->angkatan()->getKey()]))->assertOk()->getContent();

        $this->assertStringContainsString('id="kartuPesertaLain"', $isi,
            'Borangnya tidak punya bagian untuk nama peserta lainnya.');

        $this->assertStringContainsString('daftarPesertaLain', $isi);

        $this->assertMatchesRegularExpression("/peserta\[' \+ i \+ '\]\[' \+ kunci \+ '\]/", $isi,
            'Barisnya tidak lagi dirakit dengan nama medan peserta[i][kunci]; '
            . 'yang terkirim ke peladen tidak akan terbaca sebagai daftar peserta.');
    }

    // -------------------------------------------------------- ganda

    #[Test]
    public function data_yang_sama_tidak_jadi_dua_pendaftaran(): void
    {
        /*
         * Orang menekan tombolnya dua kali karena mengira yang pertama
         * gagal — halaman ini memang diam beberapa detik saat tagihannya
         * dibuat. Tanpa penjagaan ini ia mendapat DUA tagihan untuk satu
         * kursi, lalu sebagian membayar keduanya.
         */
        \Illuminate\Support\Facades\Mail::fake();

        $a = $this->angkatan();
        $isian = $this->isian($a);

        $this->post(route('public.scopuscamp.store'), $isian);

        $jawab = $this->post(route('public.scopuscamp.store'), $isian);

        $this->assertSame(1, $this->jumlahBaris($a),
            'Kiriman kedua membuat pendaftaran kedua.');

        /*
         * DAN TAUTANNYA TIDAK DIBUKA DI LAYAR.
         *
         * Cabang ini dulu mengantar pengirimnya langsung ke halaman status
         * pendaftaran yang sudah ada. Yang dicocokkan cuma angkatan + nama +
         * email — dan ketiganya bukan rahasia; nama dan email justru hal yang
         * paling mudah diketahui rekan sekantor atau siapa pun yang memegang
         * daftar peserta.
         *
         * Yang ia dapatkan dengan menebaknya: nomor telepon, afiliasi, daftar
         * peserta rombongan, faktur PDF, dan tombol membuat tagihan.
         */
        $alamat = (string) $jawab->headers->get('location');

        $this->assertStringNotContainsString('/Scopus-Camp/Status/', $alamat,
            'Kiriman kedua diantar ke halaman status pendaftaran yang sudah ada; '
            . 'siapa pun yang tahu nama dan email orang lain bisa membukanya.');

        // Jalan kembalinya tetap ada — lewat kotak masuk pemiliknya.
        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\TautanPendaftaranScopusCampMail::class, 1
        );
    }

    #[Test]
    public function yang_sudah_dibatalkan_boleh_mendaftar_lagi(): void
    {
        // Sisi sebaliknya: penjagaan ganda tidak boleh mengunci orang yang
        // pendaftarannya memang sudah dibatalkan panitia.
        $a = $this->angkatan();
        $isian = $this->isian($a);

        $this->post(route('public.scopuscamp.store'), $isian);

        PendaftaranScopusCamp::where('kategori_id', $a->getKey())
            ->update(['status' => 'Pendaftaran Dibatalkan']);

        $this->post(route('public.scopuscamp.store'), $isian);

        $this->assertSame(2, $this->jumlahBaris($a),
            'Yang pendaftarannya sudah dibatalkan tidak bisa mendaftar lagi.');
    }

    // ------------------------------------------------------- kedaluwarsa

    #[Test]
    public function perintah_kedaluwarsa_melepas_kursi_yang_tidak_jadi_dibayar(): void
    {
        /*
         * `kedaluwarsa_pada` sudah ditulis sejak jalur DOKU dipasang, tetapi
         * sampai 23 Okt 2026 tidak ada satu pun yang membacanya — Webinar
         * Eksklusif punya sapuannya sejak lama, Scopus Camp tidak. Jadi
         * pendaftaran yang ditinggalkan di tengah jalan menahan kursinya
         * selamanya.
         */
        $a = $this->angkatan(['total_kuota' => 10, 'sisa_kuota' => 8]);

        $p = PendaftaranScopusCamp::create([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-LEWAT-' . Str::random(6),
            'kategori_id' => $a->getKey(),
            'nama' => 'Tidak Jadi Bayar', 'email' => 'lewat' . Str::random(4) . '@contoh.test',
            'telp' => '6281200000002', 'jumlah_pendaftar' => 2,
            'total_pembayaran' => '1', 'status' => 'diproses',
            'cara_bayar' => 'doku', 'bayar_status' => 'menunggu',
            'kedaluwarsa_pada' => now()->subHour(),
        ]);

        $this->artisan('scopus-camp:kedaluwarsakan')->assertSuccessful();

        $p->refresh();

        $this->assertSame('Pendaftaran Dibatalkan', $p->status);
        $this->assertSame('expired', $p->bayar_status);
        $this->assertSame(10, (int) $a->refresh()->sisa_kuota,
            'Kursinya tidak kembali sesudah tagihannya mati.');
    }

    #[Test]
    public function perintah_kedaluwarsa_tidak_menyentuh_yang_sudah_dibayar(): void
    {
        $a = $this->angkatan();

        $p = PendaftaranScopusCamp::create([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-LUNAS-' . Str::random(6),
            'kategori_id' => $a->getKey(),
            'nama' => 'Sudah Bayar', 'email' => 'lunas' . Str::random(4) . '@contoh.test',
            'telp' => '6281200000003', 'jumlah_pendaftar' => 1,
            'total_pembayaran' => '1', 'status' => 'Pendaftaran Diterima',
            'cara_bayar' => 'doku', 'bayar_pada' => now()->subHours(2),
            'kedaluwarsa_pada' => now()->subHour(),
        ]);

        $this->artisan('scopus-camp:kedaluwarsakan')->assertSuccessful();

        $this->assertSame('Pendaftaran Diterima', $p->refresh()->status,
            'Pendaftaran yang sudah dibayar ikut dibatalkan sapuan kedaluwarsa.');
    }

    #[Test]
    public function perintah_kedaluwarsa_terjadwal(): void
    {
        /*
         * Perintah yang tidak terjadwal tidak pernah jalan, dan tidak ada
         * satu pun layar yang menunjukkannya — kursinya cuma tidak pernah
         * kembali.
         */
        /*
         * Dicari di `description`, BUKAN di `command`.
         *
         * Penjadwal di proyek ini memakai $schedule->call(), bukan
         * ->command(): proc_open dimatikan di peladen produksi, jadi tugas
         * yang menyalakan proses anak gagal sebelum mengerjakan apa pun — dan
         * gagalnya tidak terlihat. Pada CallbackEvent, `command` selalu null
         * dan nama perintahnya ada di `description`.
         */
        $terjadwal = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->contains(fn ($e) => str_contains(
                ($e->description ?? '') . ' ' . ($e->command ?? ''),
                'scopus-camp:kedaluwarsakan'
            ));

        $this->assertTrue($terjadwal,
            'Perintah pelepas kursi Scopus Camp tidak terdaftar di penjadwal.');
    }
}
