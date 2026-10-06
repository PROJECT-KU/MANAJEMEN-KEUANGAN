<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Dua cacat tata letak yang dilaporkan dari iPhone, 5 Okt 2026.
 *
 * Keduanya sejenis: ukuran yang seharusnya mengikuti wadahnya justru
 * ditentukan isinya, lalu isinya meluber keluar wadah itu.
 */
class TataLetakProfilPonselTest extends TestCase
{
    #[Test]
    public function bingkai_foto_punya_jalur_grid_yang_pasti(): void
    {
        /*
         * Bingkainya 92px, tetapi JALUR grid di dalamnya berukuran auto — dan
         * tinggi persen pada item grid diukur terhadap jalurnya, bukan
         * terhadap bingkainya. Jalur auto ukurannya ditentukan isinya,
         * sementara isinya (height: 100%) menunggu jalurnya: saling menunggu,
         * jadi peramban menyerah dan memperlakukan keduanya sebagai auto.
         *
         * Foto TEGAK lalu memakai tinggi aslinya. Terukur di layar 393px:
         * 84x175 di dalam bingkai 92x92 — meluber 87px ke bawah, dan bingkai
         * ungunya tertutup di sisi itu sehingga terlihat terpotong.
         * Sesudah diperbaiki: 84x84, masuk 4px di dalam bingkainya.
         *
         * minmax(0, 1fr), bukan 1fr polos: 1fr berdasar auto, dan dasar itulah
         * yang tadi membuatnya melar.
         */
        $gaya = $this->tanpaKomentar(
            file_get_contents(resource_path('views/account/profil/gaya.blade.php'))
        );

        $ada = preg_match('/\.prof-foto-bingkai\s*\{(?<isi>[^}]*)\}/', $gaya, $cocok);

        $this->assertSame(1, $ada, 'Aturan bingkai foto hilang.');

        $this->assertMatchesRegularExpression('/grid-template:\s*minmax\(0,/', $cocok['isi'],
            'Jalur grid bingkai foto tidak lagi dipatok; foto tegak akan meluber keluar bingkainya.');

        $this->assertStringContainsString('height: 100%', $gaya,
            'prasyarat: fotonya memang disetel setinggi bingkainya');
    }

    #[Test]
    public function isian_tidak_boleh_melebihi_selnya(): void
    {
        /*
         * .mis-isian sudah min-width: 0, tetapi itu melonggarkan SELNYA —
         * bukan kotak isian di dalamnya. Isian adalah item grid juga, dan item
         * grid bawaannya min-width: auto, yaitu selebar isi terkecilnya.
         *
         * Untuk <input type="date"> isi terkecil itu ditentukan peramban, dan
         * di iOS Safari jauh lebih lebar daripada di Chrome — itu sebabnya ia
         * meluber keluar kartu di iPhone sementara di komputer tampak
         * baik-baik saja.
         *
         * CATATAN JUJUR: cacat ini TIDAK bisa direproduksi di Chrome, bahkan
         * dengan isian yang sengaja dibuat berlebar-minimum besar — Chrome
         * membiarkannya menyusut. Jadi yang dijaga di sini aturannya, bukan
         * hasil pengukurannya. Bukti lapangannya laporan dari iPhone.
         */
        $css = file_get_contents(public_path('assets/css/mis-ui.css'));

        $ada = preg_match(
            '/\.mis-isian\s*>\s*input[^{]*\{(?<isi>[^}]*)\}/s',
            $css,
            $cocok
        );

        $this->assertSame(1, $ada, 'Aturan pembatas lebar isian hilang dari mis-ui.css.');

        $this->assertMatchesRegularExpression('/min-width:\s*0/', $cocok['isi'],
            'Isian tidak lagi dilonggarkan min-width-nya; di iOS ia akan meluber keluar kartunya.');

        $this->assertMatchesRegularExpression('/max-width:\s*100%/', $cocok['isi'],
            'Isian tidak lagi dibatasi selebar selnya.');
    }

    /** Membuang komentar, menyisakan kode. */
    private function tanpaKomentar(string $isi): string
    {
        $isi = preg_replace('#\{\{--.*?--\}\}#s', '', $isi);

        return (string) preg_replace('#/\*.*?\*/#s', '', (string) $isi);
    }
}
