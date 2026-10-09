<?php

namespace Tests\Feature\PendaftaranLayanan;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kartu identitas ikut tergulir bersama kolom kanannya.
 *
 * Terukur di 1440x900, tab Ringkasan: kolom kanan 1725px, kartu identitas
 * 752px. Begitu panitia menggulir ke Jejak perubahan, kartunya sudah lewat
 * di atas layar dan menyisakan 973px lajur kosong di sebelah kiri.
 *
 * Tidak bisa diselesaikan dengan menyamakan tingginya: tinggi kolom kanan
 * berayun dari 170px (tab Hapus) sampai 1410px (tab Ringkasan) — susunan
 * tetap mana pun pasti timpang di salah satu tab.
 */
class KartuIdentitasMelekatTest extends TestCase
{
    private function gaya(): string
    {
        return file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        );
    }

    /**
     * Aturannya dicari DI DALAM kurung @media-nya, bukan di mana saja.
     *
     * `position: sticky` yang bocor ke lebar ponsel membuat kartu setinggi
     * 711px melekat menutupi layar 844px — tabnya praktis tidak bisa
     * dipakai. Penjaga yang cuma mencari untaian "sticky" di seluruh berkas
     * tidak akan menangkap itu sama sekali.
     */
    #[Test]
    public function melekatnya_hanya_di_lebar_layar_besar(): void
    {
        $blok = $this->blokMedia();

        $this->assertMatchesRegularExpression('/\.rin-identitas\s*\{[^}]*position:\s*sticky/s', $blok,
            'Kartu identitas tidak lagi melekat; kolom kirinya kembali kosong '
            . 'sepanjang 973px begitu halamannya digulir.');
    }

    /**
     * Tanpa batas tinggi, ujung bawah kartu TIDAK PERNAH bisa dilihat di
     * layar pendek: ia melekat terus, jadi menggulir tidak membawanya naik.
     * Terukur merah di 1440x700 dan 1280x800 — keduanya tinggi layar laptop
     * yang lazim.
     */
    #[Test]
    public function layar_pendek_tetap_bisa_melihat_ujung_bawahnya(): void
    {
        $blok = $this->blokMedia();

        $this->assertMatchesRegularExpression('/\.rin-identitas\s*\{[^}]*max-height:\s*calc\(/s', $blok,
            'Batas tinggi kartu melekat hilang.');

        $this->assertMatchesRegularExpression('/\.rin-identitas\s*\{[^}]*overflow:\s*auto/s', $blok,
            'Kartu dibatasi tingginya tapi tidak bisa digulir di dalam — '
            . 'isi yang terpotong jadi hilang sama sekali, bukan cuma tersembunyi.');
    }

    /**
     * `sticky` mati diam-diam kalau SALAH SATU induknya memotong luapan.
     * Tidak ada galat, tidak ada tanda apa pun — kartunya cuma berhenti
     * melekat. Sudah ditelusuri sampai <html>: tak ada yang memotong. Yang
     * dijaga di sini pembungkus terdekatnya, yang ada di berkas ini.
     */
    #[Test]
    public function pembungkusnya_tidak_memotong_luapan(): void
    {
        $this->assertSame(0, preg_match('/\.rin-kisi\s*\{[^}]*overflow:\s*(hidden|auto|scroll|clip)/s', $this->gaya()),
            'Pembungkus dua kolomnya memotong luapan; kartu identitas berhenti melekat '
            . 'tanpa menimbulkan galat apa pun.');
    }

    /**
     * Isi kurung `@media (min-width: 992px)` yang memuat .rin-identitas.
     *
     * Dipotong dengan menghitung kurung, bukan `[^}]*`: blok media berisi
     * aturan lain yang juga punya `}`, jadi pemotong sederhana berhenti di
     * kurung pertama dan membuat penjaga ini merah pada kode yang benar.
     */
    private function blokMedia(): string
    {
        $gaya = $this->gaya();
        $awal = 0;

        while (($awal = strpos($gaya, '@media (min-width: 992px)', $awal)) !== false) {
            $buka = strpos($gaya, '{', $awal);
            $dalam = 0;

            for ($i = $buka; $i < strlen($gaya); $i++) {
                if ($gaya[$i] === '{') {
                    $dalam++;
                } elseif ($gaya[$i] === '}') {
                    $dalam--;
                    if ($dalam === 0) {
                        $blok = substr($gaya, $buka, $i - $buka);

                        if (str_contains($blok, '.rin-identitas')) {
                            return $blok;
                        }

                        $awal = $i;
                        break;
                    }
                }
            }

            if ($dalam !== 0) {
                break;
            }
        }

        $this->fail('Tidak ada @media (min-width: 992px) yang mengatur .rin-identitas.');
    }
}
