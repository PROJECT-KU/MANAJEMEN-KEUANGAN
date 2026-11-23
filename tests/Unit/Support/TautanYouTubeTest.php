<?php

namespace Tests\Unit\Support;

use App\Support\TautanYouTube;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pembacaan tautan YouTube.
 *
 * Yang dijaga di sini BUKAN kerapian, melainkan satu kegagalan yang diam:
 * tautan yang tidak terbaca tetap menghasilkan <iframe> yang terpasang rapi
 * dan berukuran benar, isinya saja yang kosong. Tidak ada galat PHP, tidak
 * ada baris log — yang melihatnya hanya peserta yang sudah membayar, sesudah
 * webinarnya lewat.
 *
 * Bentuk-bentuk di bawah bukan karangan: itu yang dipulangkan YouTube sendiri
 * lewat bilah alamat, tombol Bagikan, tombol Sematkan, tayangan langsung,
 * dan peramban ponsel.
 */
class TautanYouTubeTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function tautanSah(): array
    {
        return [
            'watch biasa' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'watch tanpa www' => ['https://youtube.com/watch?v=dQw4w9WgXcQ'],
            'watch ponsel' => ['https://m.youtube.com/watch?v=dQw4w9WgXcQ'],
            'tombol bagikan' => ['https://youtu.be/dQw4w9WgXcQ'],
            'tombol bagikan berekor' => ['https://youtu.be/dQw4w9WgXcQ?si=AbCdEfGhIjKl'],
            'tombol sematkan' => ['https://www.youtube.com/embed/dQw4w9WgXcQ'],
            'tayangan langsung' => ['https://www.youtube.com/live/dQw4w9WgXcQ'],
            'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ'],
            'nocookie' => ['https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
            'ikut daftar putar' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&list=PLabc&index=2'],
            'v lebih dulu di kueri' => ['https://www.youtube.com/watch?app=desktop&v=dQw4w9WgXcQ'],
            'berawalan http' => ['http://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'berspasi di ujung' => ['  https://youtu.be/dQw4w9WgXcQ  '],
            'ditempel dengan waktu mulai' => ['https://youtu.be/dQw4w9WgXcQ?t=42'],
        ];
    }

    #[Test]
    #[DataProvider('tautanSah')]
    public function id_terbaca_dari_tiap_bentuk_yang_dipulangkan_youtube(string $tautan): void
    {
        $this->assertSame('dQw4w9WgXcQ', TautanYouTube::id($tautan),
            'Bentuk ini dipulangkan YouTube sendiri; tidak terbacanya berarti '
            . 'pemutar kosong tanpa satu pun galat.');
    }

    #[Test]
    public function tautan_tanpa_skema_tetap_terbaca(): void
    {
        /*
         * parse_url('youtu.be/ID') membaca seluruhnya sebagai JALUR, bukan
         * sebagai inang — host-nya null. Admin yang menempelkan tautan tanpa
         * "https://" bukan hal langka, dan tanpa penanganan ini tautannya
         * ditolak borang dengan alasan yang terdengar seperti tautannya salah.
         */
        $this->assertSame('dQw4w9WgXcQ', TautanYouTube::id('youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', TautanYouTube::id('www.youtube.com/watch?v=dQw4w9WgXcQ'));
    }

    /** @return array<string, array{?string}> */
    public static function tautanTidakSah(): array
    {
        return [
            'kosong' => [''],
            'spasi saja' => ['   '],
            'null' => [null],
            'bukan tautan' => ['dQw4w9WgXcQ'],
            'inang lain' => ['https://vimeo.com/123456789'],
            'inang menipu' => ['https://youtube.com.jahat.test/watch?v=dQw4w9WgXcQ'],
            'youtube tanpa video' => ['https://www.youtube.com/'],
            'kanal, bukan video' => ['https://www.youtube.com/@rumahscopus'],
            'id kependekan' => ['https://youtu.be/dQw4w9Wg'],
            'id kepanjangan' => ['https://youtu.be/dQw4w9WgXcQExtra'],
            'id berhuruf terlarang' => ['https://www.youtube.com/watch?v=dQw4w9Wg!cQ'],
        ];
    }

    #[Test]
    #[DataProvider('tautanTidakSah')]
    public function yang_bukan_tautan_video_ditolak(?string $tautan): void
    {
        $this->assertNull(TautanYouTube::id($tautan));
        $this->assertFalse(TautanYouTube::sah($tautan));
    }

    #[Test]
    public function inang_yang_menyamar_tidak_diterima(): void
    {
        /*
         * "youtube.com.jahat.test" MEMUAT "youtube.com". Memeriksa inang
         * dengan str_contains() akan menerimanya, dan halamannya akan
         * menyematkan iframe dari peladen orang lain ke dalam halaman kita
         * yang sudah login.
         */
        foreach ([
            'https://youtube.com.jahat.test/watch?v=dQw4w9WgXcQ',
            'https://jahat.test/youtube.com/watch?v=dQw4w9WgXcQ',
            'https://notyoutube.com/watch?v=dQw4w9WgXcQ',
        ] as $tautan) {
            $this->assertNull(TautanYouTube::id($tautan), $tautan);
        }
    }

    #[Test]
    public function yang_disematkan_selalu_bentuk_embed_bukan_tautan_mentah(): void
    {
        /*
         * Lima dari enam bentuk tautan YouTube TIDAK bisa dipasang langsung
         * di <iframe src>. Yang keluar dari sini selalu /embed/, apa pun
         * bentuk yang ditempel admin.
         */
        foreach (array_map(fn ($b) => $b[0], self::tautanSah()) as $tautan) {
            $semat = TautanYouTube::sematkan($tautan);

            $this->assertNotNull($semat, $tautan);
            $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $semat);
            $this->assertStringNotContainsString('watch?v=', (string) $semat);
            $this->assertStringNotContainsString('youtu.be', (string) $semat);
        }
    }

    #[Test]
    public function ekor_tautan_tidak_ikut_tersemat(): void
    {
        /*
         * &list= membawa seluruh daftar putar ikut terbuka, dan ?si= adalah
         * penanda bagikan milik YouTube yang tidak perlu diteruskan. Yang
         * dipakai cuma id-nya.
         */
        $semat = (string) TautanYouTube::sematkan(
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&list=PLrahasia&si=AbCdEfGh'
        );

        $this->assertStringNotContainsString('list=', $semat);
        $this->assertStringNotContainsString('si=', $semat);
    }

    #[Test]
    public function gambar_mini_dan_tautan_tonton_ikut_terbaca(): void
    {
        $tautan = 'https://youtu.be/dQw4w9WgXcQ?si=AbCdEfGh';

        $this->assertSame('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
            TautanYouTube::gambarMini($tautan));
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            TautanYouTube::tonton($tautan));
        $this->assertNull(TautanYouTube::gambarMini('https://vimeo.com/1'));
    }
}
