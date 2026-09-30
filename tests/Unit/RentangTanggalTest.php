<?php

namespace Tests\Unit;

use App\Support\RentangTanggal;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RentangTanggalTest extends TestCase
{
    #[Test]
    #[DataProvider('rentang')]
    public function menulis_rentang_seperti_orang_menulisnya(?string $mulai, ?string $selesai, string $harusnya): void
    {
        $this->assertSame($harusnya, RentangTanggal::tulis(
            $mulai ? Carbon::parse($mulai) : null,
            $selesai ? Carbon::parse($selesai) : null
        ));
    }

    public static function rentang(): array
    {
        return [
            'sehari' => ['2026-04-15', null, '15 April 2026'],
            'mulai sama dengan selesai' => ['2026-04-15', '2026-04-15', '15 April 2026'],
            'sebulan' => ['2026-04-15', '2026-04-29', '15 – 29 April 2026'],
            'beda bulan' => ['2026-10-30', '2026-11-01', '30 Oktober – 1 November 2026'],
            'beda tahun' => ['2026-12-30', '2027-01-01', '30 Desember 2026 – 1 Januari 2027'],
            'tanpa tanggal' => [null, null, ''],
            'tanpa mulai' => [null, '2026-04-29', ''],
        ];
    }

    #[Test]
    public function bulannya_berbahasa_indonesia_walau_locale_aplikasi_inggris(): void
    {
        /*
         * Locale aplikasinya sengaja dipaksa Inggris di sini, bukan dibaca dari
         * config: di produksi APP_LOCALE=en, sementara lingkungan uji memakai
         * id. Dibaca dari config, ujinya lulus di sini tapi tidak membuktikan
         * apa-apa tentang keadaan yang sebenarnya.
         *
         * Kalau locale('id') hilang dari kodenya, keluarannya jadi "October"
         * dan deskripsi yang dirakit setengah Inggris.
         */
        $semula = config('app.locale');
        config(['app.locale' => 'en']);
        \Carbon\Carbon::setLocale('en');

        try {
            $this->assertSame('30 Oktober – 1 November 2026', RentangTanggal::tulis(
                Carbon::parse('2026-10-30'), Carbon::parse('2026-11-01')
            ));
        } finally {
            config(['app.locale' => $semula]);
            \Carbon\Carbon::setLocale($semula);
        }
    }

    #[Test]
    public function tidak_ada_angka_nol_di_depan_tanggal(): void
    {
        // "01 November" bukan cara orang menulisnya; pengumumannya memakai
        // "1 November".
        $this->assertSame('1 – 3 November 2026', RentangTanggal::tulis(
            Carbon::parse('2026-11-01'), Carbon::parse('2026-11-03')
        ));
    }

    // ----------------------------------------------------- bentuk pendek

    #[Test]
    public function bentuk_pendek_menyingkat_nama_bulan(): void
    {
        // Dipakai kolom tanggal di daftar bertabel, yang sempit.
        $this->assertSame('30 Okt – 1 Nov 2026', RentangTanggal::tulis(
            Carbon::parse('2026-10-30'), Carbon::parse('2026-11-01'), true
        ));
    }

    #[Test]
    public function bentuk_pendek_tetap_mengikuti_aturan_pengulangan(): void
    {
        // Aturan hematnya sama persis, cuma nama bulannya yang disingkat.
        $this->assertSame('1 – 3 Nov 2026', RentangTanggal::tulis(
            Carbon::parse('2026-11-01'), Carbon::parse('2026-11-03'), true
        ));

        $this->assertSame('15 Apr 2026', RentangTanggal::tulis(
            Carbon::parse('2026-04-15'), null, true
        ));

        $this->assertSame('30 Des 2026 – 1 Jan 2027', RentangTanggal::tulis(
            Carbon::parse('2026-12-30'), Carbon::parse('2027-01-01'), true
        ));
    }

    #[Test]
    public function bentuk_panjang_tetap_bawaan(): void
    {
        // Pengumuman yang dirakit memakai nama bulan lengkap; kalau bawaannya
        // ikut berubah jadi singkatan, seluruh deskripsi angkatan ikut berubah
        // tanpa ada yang meminta.
        $this->assertSame('30 Oktober – 1 November 2026', RentangTanggal::tulis(
            Carbon::parse('2026-10-30'), Carbon::parse('2026-11-01')
        ));
    }
}
