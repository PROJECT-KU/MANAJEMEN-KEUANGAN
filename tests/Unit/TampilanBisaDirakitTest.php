<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Menjaga dua kesalahan Blade yang gejalanya tidak menyebut sebabnya.
 *
 * Keduanya saya buat sendiri pada 1 Okt 2026 saat menambah layar Galeri
 * Layanan, dan keduanya baru ketahuan ketika halamannya dibuka di peramban —
 * galat 500 yang jejaknya menunjuk middleware, bukan tampilannya.
 *
 * Keduanya lahir dari kebiasaan proyek LAIN yang terbawa ke sini:
 *
 *   - `<x-wajib />`  — penanda medan wajib di POS-UMKM. Proyek ini tidak punya
 *     satu pun komponen Blade; penandanya di sini berupa <span> berkelas.
 *   - `@extends('livewire.layout.templateindex')` — kerangka layar admin
 *     Phoenix. Di sini kerangkanya `layouts.account`.
 *
 * Memeriksanya murni dari teks berkas, tanpa merender: cepat, dan menangkap
 * seluruh tampilan sekaligus — termasuk yang rutenya belum pernah dibuka
 * siapa pun.
 */
class TampilanBisaDirakitTest extends TestCase
{
    /**
     * Tidak satu pun direktif Blade lolos ke keluaran sebagai teks biasa.
     *
     * Blade TIDAK mengompilasi direktif yang didahului huruf atau angka:
     * polanya menuntut batas bukan-kata sebelum `@`. Jadi
     * `...bukti bayarnya@if ($berangkatan), dan...` terkirim ke peramban apa
     * adanya — lengkap dengan tanda kurung dan nama variabelnya — dan karena
     * yang pertama gagal, @endif serta @if sesudahnya ikut gagal berentet.
     *
     * Terukur di halaman rincian pendaftaran: peringatan hapusnya terbaca
     * "Barisnya hilang permanen beserta berkas bukti bayarnya@if
     * ($berangkatan), dan kursinya dikembalikan ke kuota angkatan@endif@if
     * ($layanan === 'clinik_scopus')...". Tidak ada galat, tidak ada entri
     * log; halamannya tetap 200.
     *
     * Diperiksa dari hasil KOMPILASI, bukan dari pola di sumbernya. Yang
     * menentukan bukan bagaimana markahnya ditulis melainkan apa yang
     * benar-benar dikeluarkan Blade — dan itu membuat direktif di dalam
     * komentar `{{-- --}}` tidak ikut tertuduh, sebab komentarnya memang
     * dibuang saat kompilasi.
     */
    #[Test]
    public function tidak_ada_direktif_blade_yang_lolos_jadi_teks(): void
    {
        /*
         * @media, @keyframes, dan @import adalah CSS, dan @click milik
         * Alpine — ketiganya memang harus utuh di keluaran, jadi tidak
         * didaftarkan.
         */

        /*
         * Penutup dan percabangan: SELALU dikompilasi apa adanya, tanpa
         * tanda kurung. Ketemu di keluaran berarti pasti bocor.
         */
        $telanjang = [
            'else', 'endif', 'endunless', 'endisset', 'endempty',
            'endforeach', 'endforelse', 'endfor', 'endwhile', 'endswitch',
            'endphp', 'endsection', 'endpush', 'empty', 'break', 'continue',
        ];

        /*
         * Yang membawa tanda kurung. Pembedaan ini bukan kerewelan:
         * `@include centered;` di dalam <style> adalah mixin Sass, dan Blade
         * memang TIDAK mengompilasi @include tanpa tanda kurung — terukur 18
         * kali di errors/500.blade.php. Menuduhnya bocor berarti uji yang
         * menyuruh orang merusak CSS yang tidak bersalah.
         */
        $berkurung = [
            'if', 'elseif', 'unless', 'isset', 'foreach', 'forelse', 'for',
            'while', 'switch', 'case', 'php', 'checked', 'selected',
            'disabled', 'json', 'include', 'each', 'section', 'push',
        ];

        $pola = '/@(?:(?:' . implode('|', $telanjang) . ')\b'
            . '|(?:' . implode('|', $berkurung) . ')\s*\()/';
        $temuan = [];

        foreach ($this->berkasBlade() as $jalur => $isi) {
            /*
             * `@@if` adalah cara SAH menulis "@if" sebagai teks, dan
             * kompilasinya memang menghasilkan `@if`. Tidak ada satu pun di
             * proyek ini (terukur nol), jadi kalau suatu saat ada, uji ini
             * harus diperbarui — bukan markahnya.
             */
            $this->assertStringNotContainsString(
                '@@',
                $isi,
                basename($jalur) . ' memakai @@; aturan uji ini perlu diperbarui.'
            );

            $hasil = \Illuminate\Support\Facades\Blade::compileString($isi);

            // Komentar PHP di hasil kompilasi boleh menyebut nama direktif —
            // yang dicari adalah yang tergambar, bukan yang tertulis di
            // komentar yang tidak pernah dikeluarkan.
            $hasil = preg_replace('#/\*.*?\*/|//[^\n]*#s', '', $hasil);

            if (preg_match_all($pola, (string) $hasil, $m)) {
                $temuan[] = str_replace(resource_path('views') . '/', '', $jalur)
                    . ': ' . implode(', ', array_unique($m[0]));
            }
        }

        $this->assertSame(
            [],
            $temuan,
            "Direktif Blade ini tergambar sebagai teks biasa di halaman.\n"
                . "Sebabnya hampir selalu direktif yang menempel ke huruf sebelumnya\n"
                . "(\"bayarnya@if\"); rakit kalimatnya di blok @php lalu cetak hasilnya.\n"
                . implode("\n", $temuan)
        );
    }

    /** @return array<string, string> jalur => isi */
    private function berkasBlade(): array
    {
        $keluar = [];

        $jalan = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($jalan as $berkas) {
            if ($berkas->isFile() && str_ends_with($berkas->getFilename(), '.blade.php')) {
                $keluar[$berkas->getPathname()] = file_get_contents($berkas->getPathname());
            }
        }

        return $keluar;
    }

    private function pendek(string $jalur): string
    {
        return str_replace(resource_path('views') . '/', '', $jalur);
    }

    #[Test]
    public function tidak_ada_tampilan_yang_memakai_komponen_blade_yang_tidak_ada(): void
    {
        /*
         * Proyek ini memang tidak punya resources/views/components sama sekali.
         * Kalau suatu saat dibuat, uji ini tinggal memeriksa keberadaan
         * berkasnya — bukan menolak komponen secara mutlak.
         */
        $folder = resource_path('views/components');

        $hilang = [];

        foreach ($this->berkasBlade() as $jalur => $isi) {
            /*
             * Yang dicari penanda komponen SUNGGUHAN: <x-nama ...>. Tanda baca
             * di belakang namanya dibatasi supaya "<x-" yang kebetulan ditulis
             * di dalam kalimat tidak ikut terjaring.
             */
            if (! preg_match_all('/<x-([a-z0-9][a-z0-9._-]*)[\s\/>]/i', $isi, $cocok)) {
                continue;
            }

            foreach (array_unique($cocok[1]) as $nama) {
                $berkas = $folder . '/' . str_replace('.', '/', $nama) . '.blade.php';

                if (! is_file($berkas)) {
                    $hilang[] = $nama . ' (' . $this->pendek($jalur) . ')';
                }
            }
        }

        $this->assertSame([], array_unique($hilang),
            'komponen Blade dipakai tetapi berkasnya tidak ada: ' . implode(', ', array_unique($hilang)));
    }

    #[Test]
    public function tidak_ada_tampilan_yang_mewarisi_kerangka_yang_tidak_ada(): void
    {
        $hilang = [];

        foreach ($this->berkasBlade() as $jalur => $isi) {
            if (! preg_match_all("/@(?:extends|include)\(\s*'([a-zA-Z0-9._-]+)'/", $isi, $cocok)) {
                continue;
            }

            foreach (array_unique($cocok[1]) as $nama) {
                $berkas = resource_path('views/' . str_replace('.', '/', $nama) . '.blade.php');

                if (! is_file($berkas)) {
                    $hilang[] = $nama . ' (' . $this->pendek($jalur) . ')';
                }
            }
        }

        $this->assertSame([], array_unique($hilang),
            'kerangka/partial dirujuk tetapi berkasnya tidak ada: ' . implode(', ', array_unique($hilang)));
    }

    #[Test]
    public function layar_admin_memakai_kerangka_milik_proyek_ini(): void
    {
        /*
         * Kerangka layar admin di sini `layouts.account`. Yang terbawa dari
         * Phoenix — `livewire.layout.templateindex` — tidak ada di proyek ini,
         * dan memakainya membuat halamannya galat 500.
         */
        $salah = [];

        foreach ($this->berkasBlade() as $jalur => $isi) {
            if (! str_starts_with($this->pendek($jalur), 'account/')) {
                continue;
            }

            // Hanya berkas HALAMAN yang diperiksa; partial memang tidak
            // mewarisi kerangka apa pun.
            if (! str_contains($isi, '@section(')) {
                continue;
            }

            if (str_contains($isi, 'livewire.layout.')) {
                $salah[] = $this->pendek($jalur);
            }
        }

        $this->assertSame([], $salah,
            'layar admin memakai kerangka milik proyek lain: ' . implode(', ', $salah));
    }
}
