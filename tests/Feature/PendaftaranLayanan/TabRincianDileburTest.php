<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Actions\Pendaftaran\UbahDataPendaftaran;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\TabPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tab yang isinya kurang dari tiga isian dilebur ke tab identitas.
 *
 * Terukur 7 Okt 2026, empat dari lima layanan punya tab setipis itu: Scopus
 * Camp dan Webinar Eksklusif (tab Jadwal, 1 isian), Clinik Scopus (2), dan
 * Scopus Kafe (tab Pembayaran, 1). Satu tab untuk satu isian menambah satu
 * klik dan satu tempat yang harus diingat, tanpa menghemat gulungan apa pun.
 */
class TabRincianDileburTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function tiap_tab_yang_berdiri_sendiri_memang_punya_cukup_isian(): void
    {
        $tipis = [];

        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            $punya = array_keys(UbahDataPendaftaran::medan($layanan));
            $dilebur = TabPendaftaran::dilebur($layanan);

            foreach (TabPendaftaran::MEDAN as $tab => $daftar) {
                if ($tab === TabPendaftaran::UTAMA || in_array($tab, $dilebur, true)) {
                    continue;
                }

                $jumlah = count(array_intersect($punya, $daftar));

                if ($jumlah > 0 && $jumlah < TabPendaftaran::BATAS_BERDIRI_SENDIRI) {
                    $tipis[] = $layanan . ' / ' . $tab . ' (' . $jumlah . ' isian)';
                }
            }
        }

        $this->assertSame([], $tipis,
            "Tab berikut berdiri sendiri padahal isinya terlalu sedikit:\n- "
            . implode("\n- ", $tipis) . "\n");
    }

    #[Test]
    public function tab_yang_dilebur_tidak_pernah_jadi_tujuan_sesudah_simpan(): void
    {
        /*
         * Inti dari satu-sumber-kebenarannya. Pengendali memulangkan kunci tab
         * yang harus dibuka sesudah Simpan; kalau ia memulangkan tab yang
         * sudah dilebur, kuncinya tidak ada di deret tab dan halamannya
         * diam-diam kembali ke Ringkasan — panitia kehilangan tempat yang
         * baru saja ia sunting, tanpa satu pun pesan.
         */
        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            $dilebur = TabPendaftaran::dilebur($layanan);

            foreach (array_keys(UbahDataPendaftaran::medan($layanan)) as $kolom) {
                $tuju = TabPendaftaran::tabDariKiriman($layanan, [$kolom]);

                $this->assertNotContains($tuju, $dilebur,
                    "Menyimpan {$kolom} ({$layanan}) membuka tab {$tuju} yang sudah dilebur.");
            }
        }
    }

    #[Test]
    public function angkatan_mendarat_di_tab_identitas_bukan_pembayaran(): void
    {
        /*
         * Isian Angkatan pernah duduk di tab Pembayaran, dan justru itu yang
         * dibuang: panitia yang hendak memindahkan peserta tidak mencarinya di
         * layar pembayaran. Peleburan ini TIDAK boleh mengembalikannya ke
         * sana — ia dilebur ke tab identitas.
         */
        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            if (! array_key_exists('kategori_id', UbahDataPendaftaran::medan($layanan))) {
                continue;
            }

            $this->assertSame(TabPendaftaran::UTAMA,
                TabPendaftaran::tabMedan($layanan, 'kategori_id'),
                "Angkatan {$layanan} tidak mendarat di tab identitas.");
        }
    }

    #[Test]
    public function tab_scopus_kafe_yang_memang_penuh_tetap_berdiri_sendiri(): void
    {
        /*
         * Alasan tab ini ada sejak awal: Scopus Kafe punya 22 isian jadwal.
         * Peleburan yang menyapu semuanya akan mengembalikan borang panjang
         * yang harus digulung jauh — persis yang dulu diperbaiki tab.
         */
        $this->assertNotContains('sesi', TabPendaftaran::dilebur('scopus_kafe'),
            'Tab Jadwal Scopus Kafe ikut dilebur; 22 isiannya kembali menumpuk jadi satu borang.');

        $this->assertContains('bayar', TabPendaftaran::dilebur('scopus_kafe'),
            'Tab Pembayaran Scopus Kafe yang isinya satu isian tidak ikut dilebur.');

        foreach (['scopus_camp', 'bibliometrik'] as $layanan) {
            $this->assertNotContains('bayar', TabPendaftaran::dilebur($layanan),
                "Tab Pembayaran {$layanan} ikut dilebur padahal isinya enam isian.");
        }
    }

    #[Test]
    public function nama_tab_mengikuti_isinya(): void
    {
        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            $this->assertSame(
                TabPendaftaran::adaYangDilebur($layanan) ? 'Data pendaftaran' : 'Identitas',
                TabPendaftaran::namaTabUtama($layanan)
            );
        }
    }

    #[Test]
    public function membetulkan_nama_tidak_ikut_ditolak_angkatan_penuh(): void
    {
        /*
         * Akibat peleburan yang paling mudah terlewat: Angkatan dan Identitas
         * kini SATU BORANG, jadi satu tekan Simpan mengirim keduanya. Kalau
         * kiriman angkatan yang nilainya TIDAK berubah tetap diperlakukan
         * sebagai perpindahan, membetulkan satu huruf di nama akan ditolak
         * dengan alasan "kuotanya penuh" — dan seluruh suntingannya mundur,
         * sebab penyimpanannya satu transaksi.
         *
         * DUA hal yang melindunginya di sesuaikanKuota(), dan keduanya perlu:
         *
         *   1. keluar lebih awal saat angkatan DAN jumlahnya sama persis;
         *   2. kursi MILIKNYA SENDIRI dihitung sebagai tersedia saat
         *      angkatannya tidak berpindah.
         *
         * Dibuktikan dengan memasang kembali KEDUANYA sekaligus: membuang
         * salah satu saja tetap hijau, sebab yang lain masih menahan. Pesan
         * yang muncul saat keduanya dibuang persis yang akan dibaca panitia —
         * "Jumlah pendaftarnya melebihi sisa kuota angkatan itu — tersisa 0
         * kursi" — padahal yang ia ubah cuma ejaan nama.
         */
        $this->refreshApplication();

        $angkatan = \App\KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Penuh ' . \Illuminate\Support\Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            // Penuh: nol kursi tersisa.
            'total_kuota' => '2', 'sisa_kuota' => '0', 'status' => 'active',
        ]);

        $baris = \App\PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . \Illuminate\Support\Str::random(6),
            'kategori_id' => $angkatan->id, 'nama' => 'Nama Salah Ketik',
            'email' => \Illuminate\Support\Str::random(8) . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '2',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        $hasil = (new \App\Actions\Pendaftaran\UbahDataPendaftaran)->jalankan(
            'scopus_camp', (string) $baris->getKey(), [
                // Persis yang dikirim borang gabungan: angkatan IKUT, tanpa berubah.
                'kategori_id' => (string) $angkatan->id,
                'jumlah_pendaftar' => '2',
                'nama' => 'Nama Sudah Dibetulkan',
            ]
        );

        $this->assertTrue($hasil['berhasil'], $hasil['pesan']);
        $this->assertSame('Nama Sudah Dibetulkan', $baris->fresh()->nama);
        $this->assertSame(0, (int) $angkatan->fresh()->sisa_kuota,
            'Kuotanya bergeser padahal angkatannya tidak berpindah.');
    }

    #[Test]
    public function daftar_medannya_tidak_tertinggal_di_pengendali(): void
    {
        /*
         * Daftar medan per tab dulu tinggal di PendaftaranLayananController.
         * Dua salinan pasti berselisih: yang satu dipakai menggambar tab, yang
         * lain dipakai memilih tab mana yang dibuka sesudah Simpan.
         */
        $sumber = file_get_contents(
            base_path('app/Http/Controllers/account/PendaftaranLayananController.php')
        );

        $this->assertStringNotContainsString("'jumlah_pendaftar', 'ppn', 'kode_unik'", $sumber,
            'Daftar medan per tab kembali ditulis di pengendali; sekarang ada dua salinan.');

        $this->assertStringContainsString('TabPendaftaran::tabDariKiriman', $sumber,
            'Pengendali tidak lagi memakai aturan bersama.');
    }
}
