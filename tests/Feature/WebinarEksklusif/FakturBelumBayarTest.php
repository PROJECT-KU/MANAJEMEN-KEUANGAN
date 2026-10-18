<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\Mail\WebinarEksklusifPendaftaranMail;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Yang belum membayar menerima FAKTURNYA; yang sudah membayar tidak.
 *
 * Sepasang dengan kwitansi, dan bedanya bukan hanya kata: kwitansi
 * menyatakan uangnya SUDAH diterima, faktur menyatakan berapa yang HARUS
 * dibayar dan sampai kapan. Tertukar, yang satu jadi bukti palsu dan yang
 * lain jadi tagihan untuk uang yang sudah masuk.
 */
class FakturBelumBayarTest extends TestCase
{
    use DatabaseTransactions;

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Faktur ' . Str::random(5),
            'nama_ke' => (string) random_int(1000, 1999),
            'mulai' => now()->addDays(13)->toDateString(),
            'selesai' => now()->addDays(13)->toDateString(),
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
            'id_transaksi' => 'WE-FAKTUR-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Faktur',
            'email' => 'faktur' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'kode_unik' => '1082',
            'total_pembayaran' => '130082',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    private function dokumen(WebinarEksklusifPendaftaran $p, string $jenis = 'faktur'): string
    {
        return view('public.webinar_eksklusif.dokumen_pdf', [
            'jenis' => $jenis,
            'pendaftaran' => $p,
            'sesi' => KategoriLayanan::find($p->kategori_id),
            'terbilang' => 'seratus tiga puluh ribu delapan puluh dua rupiah',
        ])->render();
    }

    private function surat(WebinarEksklusifPendaftaran $p): WebinarEksklusifPendaftaranMail
    {
        $s = new WebinarEksklusifPendaftaranMail($p, KategoriLayanan::find($p->kategori_id));
        $s->build();

        return $s;
    }

    #[Test]
    public function yang_belum_bayar_menerima_faktur(): void
    {
        $p = $this->pendaftaran();

        $lampiran = $this->surat($p)->rawAttachments;

        $this->assertCount(1, $lampiran, 'Suratnya terkirim tanpa faktur.');

        $this->assertStringStartsWith('%PDF', $lampiran[0]['data'],
            'Lampirannya bukan PDF yang sah.');

        $this->assertSame(
            str_replace('WE-', 'INV-', (string) $p->id_transaksi) . '.pdf',
            $lampiran[0]['name'],
            'Nama berkasnya tidak menurunkan nomor pendaftarannya; dua faktur '
            . 'di folder unduhan yang sama akan bertabrakan.'
        );
    }

    #[Test]
    public function yang_sudah_bayar_tidak_ditagih_lagi(): void
    {
        /*
         * Surat ini dikirim ULANG lewat tombol "kirim ulang bukti
         * pendaftaran", dan tombol itu tetap ada sesudah pembayarannya
         * masuk. Dilampirkan tanpa syarat, yang sudah membayar menerima
         * tagihan untuk uang yang sudah ia bayar — dan sebagian akan
         * membayarnya lagi.
         */
        $p = $this->pendaftaran([
            'status' => 'paid',
            'bayar_pada' => now(),
        ]);

        $this->assertCount(0, $this->surat($p)->rawAttachments,
            'Yang sudah lunas menerima faktur; itu tagihan untuk uang yang '
            . 'sudah masuk.');
    }

    #[Test]
    public function fakturnya_menyebut_jumlah_batas_waktu_dan_status(): void
    {
        $p = $this->pendaftaran();

        // Isinya diperiksa dari TAMPILANNYA: teks di dalam PDF tersimpan
        // tersandi dan tidak bisa dicari apa adanya.
        $html = view('public.webinar_eksklusif.dokumen_pdf', [
            'jenis' => 'faktur',
            'pendaftaran' => $p,
            'sesi' => KategoriLayanan::find($p->kategori_id),
            'terbilang' => 'seratus tiga puluh ribu delapan puluh dua rupiah',
        ])->render();

        $this->assertStringContainsString('FAKTUR', $html);
        $this->assertStringContainsString('130.082', $html);
        $this->assertStringContainsString('Belum Dibayar', $html);
        $this->assertStringContainsString('Batas pembayaran', $html);

        /*
         * SISA TAGIHANNYA sama dengan totalnya, dan "Sudah dibayar" nol.
         *
         * Ini yang membedakan faktur dari kwitansi — bukan judulnya.
         * Judulnya bisa benar sementara angkanya menyatakan uangnya sudah
         * masuk, dan dokumen seperti itu adalah bukti palsu.
         */
        $this->assertStringContainsString('Sisa tagihan', $html);
        $this->assertMatchesRegularExpression(
            '/Sudah dibayar<\/td>\s*<td[^>]*>\s*0\s*</s',
            $html,
            'Faktur menyatakan sebagian sudah dibayar padahal belum.'
        );

        // Kode uniknya WAJIB disebut: yang membayar harus mengirim angka
        // yang persis, dan selisih tiga digit membuat pembayarannya tidak
        // cocok dengan tagihan mana pun.
        $this->assertStringContainsString('1.082', $html);

        $this->assertStringNotContainsString('KWITANSI', $html,
            'Faktur memakai kata kwitansi; itu menyatakan uangnya sudah '
            . 'diterima padahal belum.');
    }

    #[Test]
    public function capnya_tidak_pernah_menyatakan_lunas_di_faktur(): void
    {
        /*
         * Cap "LUNAS" pada dokumen yang belum dibayar adalah BUKTI PALSU —
         * dan cap justru yang paling dipercaya orang, sebab ia yang membuat
         * selembar kertas terbaca sah.
         *
         * Cetakannya dipakai bersama kwitansi dan keadaannya diserahkan
         * pemanggilnya, jadi tertukarnya bukan kemungkinan yang jauh.
         */
        $p = $this->pendaftaran();

        $html = view('public.webinar_eksklusif.dokumen_pdf', [
            'jenis' => 'faktur',
            'pendaftaran' => $p,
            'sesi' => KategoriLayanan::find($p->kategori_id),
            'terbilang' => 'seratus tiga puluh ribu delapan puluh dua rupiah',
        ])->render();

        /*
         * Dicari di ATRIBUT KELAS capnya, bukan di mana saja.
         *
         * Nama kelasnya muncul juga di blok <style> halaman yang sama — ia
         * memang harus, sebab di situlah aturannya ditulis. Dicari apa
         * adanya, "tidak memuat cap-lunas" mustahil terpenuhi dan ujinya
         * merah selamanya tanpa ada yang salah pada dokumennya.
         */
        $this->assertMatchesRegularExpression('/class="cap cap-belum"/', $html,
            'Faktur ini tidak memakai cap "belum dibayar".');

        $this->assertDoesNotMatchRegularExpression('/class="cap cap-lunas"/', $html,
            'Faktur memakai cap lunas; itu bukti palsu.');

        $this->assertStringNotContainsString('>LUNAS<', $html);
    }

    #[Test]
    public function langkah_cara_membayar_tidak_diulang_di_sini(): void
    {
        /*
         * Dokumen ini dibaca bersama emailnya, dan emailnya sudah memuat
         * tombol menuju halaman bayar. Langkah yang sama ditulis dua kali
         * membuat yang satu tertinggal saat yang lain diperbarui — dan yang
         * tertinggal pada dokumen keuangan selalu yang dicetak, sebab ia
         * sudah ada di tangan orangnya.
         */
        $p = $this->pendaftaran();

        $html = view('public.webinar_eksklusif.dokumen_pdf', [
            'jenis' => 'faktur',
            'pendaftaran' => $p,
            'sesi' => KategoriLayanan::find($p->kategori_id),
            'terbilang' => 'seratus tiga puluh ribu delapan puluh dua rupiah',
        ])->render();

        $this->assertStringNotContainsString('CARA MEMBAYAR', $html);
        $this->assertStringNotContainsString('Lanjutkan pembayaran', $html);
    }

    #[Test]
    public function barisnya_menyebut_angkatan_dan_tanggal_kegiatannya(): void
    {
        /*
         * Nama sesi saja tidak cukup pada dokumen yang DISIMPAN.
         *
         * Judul yang sama dipakai lagi di angkatan berikutnya, dan yang
         * membuka faktur lamanya berbulan-bulan kemudian tidak punya cara
         * tahu angkatan mana yang ia bayar. Bendahara yang mencocokkannya
         * pun tidak.
         */
        $sesi = $this->sesi();
        $sesi->forceFill([
            'nama' => 'Sesi Uji Angkatan',
            'nama_ke' => '7',
            'mulai' => '2026-11-20',
            'selesai' => '2026-11-20',
        ])->save();

        $p = $this->pendaftaran(['kategori_id' => $sesi->getKey()]);

        $html = $this->dokumen($p->refresh());

        $this->assertStringContainsString('Sesi Uji Angkatan #7', $html,
            'Angkatannya tidak disebut di baris rinciannya.');

        $this->assertStringContainsString('20 November 2026', $html,
            'Tanggal kegiatannya tidak disebut di baris rinciannya.');
    }

    #[Test]
    public function kaki_dokumennya_memuat_kontak_dan_sosial_media(): void
    {
        /*
         * Dokumen ini BERPINDAH TANGAN: disimpan, dicetak, diteruskan ke
         * bendahara kantor, kadang dibaca berbulan-bulan kemudian oleh
         * orang yang tidak pernah membuka emailnya. Tanpa kaki ini,
         * satu-satunya cara menghubungi penerbitnya adalah mencari
         * emailnya kembali.
         */
        $p = $this->pendaftaran();

        $html = $this->dokumen($p);

        $this->assertStringContainsString('rumahscopusfoundation.com', $html);
        $this->assertStringContainsString('@rumah_scopus', $html, 'Instagram/TikTok tidak disebut.');
        $this->assertStringContainsString('@rumahscopus', $html, 'YouTube tidak disebut.');
        $this->assertStringContainsString('RumahScopusAkademi', $html, 'Facebook tidak disebut.');

        // Ketiga kolomnya ada: alamat, kontak, dan sosial. Satu kolom yang
        // hilang menyisakan pita yang timpang, bukan pita yang salah.
        foreach (['ALAMAT', 'KONTAK', 'IKUTI KAMI'] as $kolom) {
            $this->assertStringContainsString($kolom, $html, $kolom . ' tidak ada di kaki dokumennya.');
        }
    }

    #[Test]
    public function nomor_panitia_ikut_config_bukan_diketik_di_cetakan(): void
    {
        /*
         * Nomornya berubah saat orangnya berganti. Diketik langsung di
         * cetakan, ia akan tertinggal — dan pada dokumen yang sudah dicetak
         * dan dipegang orang, tertinggalnya PERMANEN.
         *
         * Disimpan format internasional tanpa plus karena wa.me memintanya;
         * yang dicetak harus bentuk yang dibaca orang Indonesia.
         */
        /*
         * Nomornya sengaja BERBEDA dari telp pendaftarannya.
         *
         * Dipakai angka yang sama, penegasan kedua di bawah ikut menemukan
         * telepon PENDAFTAR di blok "Ditagihkan kepada" — yang memang
         * tersimpan apa adanya — lalu menuduh cetakannya salah format.
         * Uji itu sempat merah persis karena itu.
         */
        config()->set('panitia.whatsapp', '6289911223344');

        $html = $this->dokumen($this->pendaftaran());

        $this->assertStringContainsString('0899-1122-3344', $html,
            'Nomor panitia di dokumen tidak mengikuti config.');

        $this->assertStringNotContainsString('6289911223344', $html,
            'Nomornya dicetak dalam format wa.me, bukan format yang dibaca orang.');
    }

    #[Test]
    public function suratnya_menyebut_lampirannya(): void
    {
        /*
         * Lampiran yang tidak disebut tidak dibuka — terutama oleh yang
         * harus menyerahkannya ke bendahara kantornya lebih dulu.
         */
        $p = $this->pendaftaran();

        $html = $this->surat($p)->render();

        $this->assertStringContainsString('Fakturnya terlampir', $html);
    }

    #[Test]
    public function kartu_faktur_tidak_menempel_ke_kartu_di_atasnya(): void
    {
        /*
         * Kartu "Langkah berikutnya" ditutup "padding: 24px 26px 0" dan
         * kartu faktur ini dulu dibuka "padding: 0 26px" — nol di KEDUA
         * sisi, jadi keduanya benar-benar bersentuhan dan terbaca sebagai
         * satu kotak yang warnanya berubah di tengah.
         *
         * Terukur lewat peramban: jaraknya 0px, kini 14px.
         *
         * Yang diperiksa di sini bantalan atasnya BUKAN NOL — bukan angka
         * persisnya, sebab 14 cuma hasil penyetelan dan boleh berubah.
         */
        $html = $this->surat($this->pendaftaran())->render();

        $this->assertMatchesRegularExpression(
            '/<td style="padding:\s*(\d+)px[^"]*"[^>]*>\s*<p[^>]*>\s*<strong[^>]*>Fakturnya terlampir/s',
            $html,
            'Kartu faktur tidak punya bantalan atas sama sekali.'
        );

        preg_match(
            '/<td style="padding:\s*(\d+)px[^"]*"[^>]*>\s*<p[^>]*>\s*<strong[^>]*>Fakturnya terlampir/s',
            $html,
            $cocok
        );

        $this->assertGreaterThan(0, (int) ($cocok[1] ?? 0),
            'Kartu faktur menempel ke kartu di atasnya; keduanya terbaca '
            . 'sebagai satu kotak yang warnanya berubah di tengah.');
    }

    #[Test]
    public function fakturnya_tidak_menyisipkan_font(): void
    {
        /*
         * Dompdf menyisipkan font yang bukan bawaan PDF, utuh, ke tiap
         * berkas. Terukur pada kwitansi: DejaVu Sans membuatnya 1.289.545
         * bita melawan 20.965 dengan Helvetica.
         */
        $p = $this->pendaftaran();

        $ukuran = strlen($this->surat($p)->rawAttachments[0]['data']);

        $this->assertLessThan(200_000, $ukuran,
            'Fakturnya ' . number_format($ukuran) . ' bita — hampir pasti ada '
            . 'font yang disisipkan utuh ke dalamnya.');
    }
}
