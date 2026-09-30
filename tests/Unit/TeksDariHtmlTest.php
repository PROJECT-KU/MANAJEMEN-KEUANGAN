<?php

namespace Tests\Unit;

use App\Support\TeksDariHtml;
use Tests\TestCase;

/**
 * Penjaga pengubah HTML jadi teks datar.
 *
 * Yang dijaga di sini bukan kerapiannya, melainkan dua hal yang pernah salah:
 * teks datar yang ikut dirapikan padahal tidak perlu, dan akhir baris CRLF yang
 * membuat <br> lolos sehingga satu daftar pecah jadi baris kosong satu-satu.
 */
class TeksDariHtmlTest extends TestCase
{
    public function test_teks_datar_dikembalikan_apa_adanya(): void
    {
        $teks = "Judul\n\n\n\nMasih ada  dua spasi & baris kosong tiga.";

        $this->assertSame($teks, TeksDariHtml::ubah($teks));
    }

    public function test_kosong_dan_null_tidak_melempar(): void
    {
        $this->assertSame('', TeksDariHtml::ubah(null));
        $this->assertSame('   ', TeksDariHtml::ubah('   '));
    }

    public function test_br_bercrlf_jadi_satu_baris_bukan_dua(): void
    {
        $hasil = TeksDariHtml::ubah("<p>Satu<br data-start=\"7\">\r\nDua<br>\nTiga</p>");

        $this->assertSame("Satu\nDua\nTiga", $hasil);
    }

    public function test_paragraf_terpisah_baris_kosong(): void
    {
        $this->assertSame("Satu\n\nDua", TeksDariHtml::ubah('<p>Satu</p><p>Dua</p>'));
    }

    public function test_daftar_bernomor_mengikuti_format_perakit(): void
    {
        $hasil = TeksDariHtml::ubah('<ol><li><p><strong>1 Oktober</strong> – Pengenalan</p></li><li>Data Mining</li></ol>');

        $this->assertSame("1. 1 Oktober – Pengenalan\n2. Data Mining", $hasil);
    }

    public function test_daftar_bertitik_pakai_bulatan(): void
    {
        $this->assertSame("• Satu\n• Dua", TeksDariHtml::ubah('<ul><li>Satu</li><li>Dua</li></ul>'));
    }

    public function test_svg_dan_gaya_dibuang_beserta_isinya(): void
    {
        $hasil = TeksDariHtml::ubah(
            '<p>Tautan<span><svg width="20" viewBox="0 0 20 20"><path d="M5 5h10v10H5z"/></svg></span></p>'
        );

        $this->assertSame('Tautan', $hasil);
    }

    public function test_alamat_tautan_ikut_ditulis_kalau_beda_dari_tulisannya(): void
    {
        $this->assertSame(
            'daftar di sini (https://rumahscopus.com/daftar)',
            TeksDariHtml::ubah('<a href="https://rumahscopus.com/daftar">daftar di sini</a>')
        );
    }

    public function test_alamat_tidak_ditulis_dua_kali(): void
    {
        $this->assertSame(
            'www.rumahscopus.com',
            TeksDariHtml::ubah('<a href="https://www.rumahscopus.com/">www.rumahscopus.com</a>')
        );
    }

    public function test_entitas_disandikan_sekali_saja(): void
    {
        // "&amp;lt;" berarti admin memang ingin MENULIS "&lt;". Disandikan dua
        // kali, ia jadi "<" lalu hilang saat strip_tags.
        $this->assertSame('a &lt; b &amp; c', TeksDariHtml::ubah('<p>a &amp;lt; b &amp;amp; c</p>'));
    }

    public function test_spasi_takputus_tidak_menyisakan_baris_kosong(): void
    {
        $this->assertSame("Satu\n\nDua", TeksDariHtml::ubah("<p>Satu</p><p>\xC2\xA0</p><p>Dua</p>"));
    }

    public function test_spasi_rangkap_dirapatkan(): void
    {
        $this->assertSame(
            'Rp 999.000 👉 Rp 699.000',
            TeksDariHtml::ubah('<p>Rp <del>999.000</del> 👉 <strong>Rp 699.000</strong></p>')
        );
    }
}
