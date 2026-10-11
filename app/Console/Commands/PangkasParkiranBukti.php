<?php

namespace App\Console\Commands;

use App\Actions\Pendaftaran\BuangCatatanPenghapusan;
use App\Actions\Pendaftaran\HapusPendaftaran;
use App\PendaftaranDihapus;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Console\Command;

/**
 * Membuang bukti bayar yang menunggu di parkiran penghapusan.
 *
 * Sejak penghapusan bisa dikembalikan, bukti bayarnya tidak lagi dibuang
 * melainkan dipindahkan ke subfolder 'terhapus/'. Tanpa perintah ini
 * berkas itu duduk di sana SELAMANYA — folder yang dilayani web, berisi
 * bukti transfer pendaftaran yang dihapus bertahun-tahun lalu.
 *
 * Dua sasaran, dan keduanya perlu:
 *
 *   1. Parkiran yang sudah melewati masa simpan. Sesudah sekian lama,
 *      penghapusannya memang tidak akan dikembalikan lagi — dan menyimpan
 *      buktinya cuma menunda keputusan yang tidak pernah diambil.
 *   2. Berkas YATIM: ada di parkiran tetapi tidak ada catatan yang
 *      menunjuknya. Terukur dua berkas menumpuk begitu hanya dari uji yang
 *      dijalankan berulang; di peladen, penghapusan arsip dengan tangan
 *      meninggalkan yang sama.
 */
class PangkasParkiranBukti extends Command
{
    protected $signature = 'pendaftaran:pangkas-parkiran
                            {--hari=90 : Masa simpan bukti di parkiran, dalam hari}
                            {--kering : Hanya laporkan, jangan buang apa pun}';

    protected $description = 'Buang bukti bayar parkiran yang sudah lewat masa simpan atau yatim';

    public function handle(): int
    {
        $hari = (int) $this->option('hari');

        if ($hari < 1) {
            $this->error('Masa simpan minimal 1 hari.');

            return self::FAILURE;
        }

        $kering = (bool) $this->option('kering');
        $batas = now()->subDays($hari);

        $lewat = $this->pangkasYangLewat($batas, $kering);
        $yatim = $this->pangkasYangYatim($kering);

        $this->info(sprintf(
            '%s%d bukti lewat %s hari%s, %d berkas yatim.',
            $kering ? '[kering] ' : '',
            $lewat, $hari,
            $kering ? '' : ' dibuang',
            $yatim
        ));

        return self::SUCCESS;
    }

    /**
     * Parkiran milik catatan yang sudah melewati masa simpan.
     *
     * Yang SUDAH dipulihkan tidak ikut: buktinya memang sudah dipindahkan
     * kembali ke tempatnya saat dipulihkan, dan bukti_parkir-nya dikosongkan.
     */
    private function pangkasYangLewat($batas, bool $kering): int
    {
        $kueri = PendaftaranDihapus::query()
            ->masihTerhapus()
            ->whereNotNull('bukti_parkir')
            ->where('created_at', '<', $batas);

        if ($kering) {
            return (int) $kueri->count();
        }

        $jumlah = 0;
        $aksi = new BuangCatatanPenghapusan;

        /*
         * Catatannya TETAP tinggal; yang dibuang cuma berkasnya. Arsipnya
         * yang menjawab "ke mana perginya pendaftaran itu", dan pertanyaan
         * itu tidak kedaluwarsa bersama berkas buktinya.
         */
        foreach ($kueri->cursor() as $arsip) {
            $aksi->buangParkiran($arsip);
            $jumlah++;
        }

        return $jumlah;
    }

    /**
     * Berkas di parkiran yang tidak ditunjuk catatan mana pun.
     *
     * Dicocokkan dengan SELURUH nilai bukti_parkir, bukan hanya yang masih
     * terhapus: catatan yang sudah dipulihkan nilainya memang null, jadi
     * membatasinya akan menyatakan berkas yang sah sebagai yatim.
     */
    private function pangkasYangYatim(bool $kering): int
    {
        $dipakai = PendaftaranDihapus::query()
            ->whereNotNull('bukti_parkir')
            ->pluck('bukti_parkir')
            ->map(fn ($n) => basename((string) $n))
            ->flip();

        $jumlah = 0;

        foreach (Pendaftaran::katalog() as $layanan => $abaikan) {
            $folder = Pendaftaran::sumber($layanan)['bukti_folder'] ?? null;

            if ($folder === null) {
                continue;
            }

            $parkiran = public_path($folder . '/' . HapusPendaftaran::FOLDER_PARKIR);

            if (! is_dir($parkiran)) {
                continue;
            }

            foreach (glob($parkiran . '/*') as $berkas) {
                if (! is_file($berkas) || $dipakai->has(basename($berkas))) {
                    continue;
                }

                $jumlah++;

                if (! $kering) {
                    @unlink($berkas);
                }
            }
        }

        return $jumlah;
    }
}
