<?php

namespace App\Console\Commands;

use App\Galeri;
use App\Services\Gambar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Memindahkan berkas galeri ke folder layanannya, dan menyapu yang yatim.
 *
 * Foto galeri sempat tersimpan rata di `galeri/` — satu tumpukan berisi berkas
 * berkode acak yang tidak bisa ditelusuri lewat Finder. Perintah ini
 * memindahkannya ke `galeri/<layanan>/`, atau `galeri/bersama/` untuk yang
 * dipakai lebih dari satu layanan.
 *
 * MEMINDAHKAN BERKAS MENGUBAH ALAMATNYA. Karena itu ini perintah tersendiri
 * yang dijalankan sengaja, bukan sesuatu yang terjadi diam-diam tiap kali
 * daftar layanan sebuah foto disunting: alamat yang sudah beredar di halaman
 * publik dan sudah ditembolok peramban akan mati.
 *
 * Yang yatim — berkas di folder galeri yang tidak ditunjuk baris mana pun —
 * dilaporkan, dan baru dihapus kalau diminta dengan --buang-yatim.
 */
class RapikanGaleri extends Command
{
    protected $signature = 'galeri:rapikan
                            {--kering : Hanya melaporkan, tidak mengubah apa pun}
                            {--buang-yatim : Hapus juga berkas yang tidak ditunjuk baris mana pun}';

    protected $description = 'Merapikan berkas galeri ke folder per layanan';

    public function handle(Gambar $gambar): int
    {
        $kering = (bool) $this->option('kering');
        $cakram = Storage::disk(Gambar::CAKRAM);

        $pindah = 0;
        $tetap = 0;

        foreach (Galeri::all() as $g) {
            $sekarang = trim((string) $g->berkas);

            if ($sekarang === '' || ! $cakram->exists($sekarang)) {
                $this->warn(sprintf('  dilewati: %s — berkasnya tidak ada', $sekarang ?: '(kosong)'));

                continue;
            }

            $tujuan = $g->folderSeharusnya() . '/' . basename($sekarang);

            if ($tujuan === $sekarang) {
                $tetap++;

                continue;
            }

            $this->line(sprintf('  %s  ->  %s', $sekarang, $tujuan));

            if ($kering) {
                $pindah++;

                continue;
            }

            /*
             * Berkasnya dipindah LEBIH DULU, barisnya menyusul. Terbalik, satu
             * kegagalan di tengah meninggalkan baris yang menunjuk tempat yang
             * belum ada isinya — dan halaman publik kehilangan fotonya.
             */
            $cakram->move($sekarang, $tujuan);
            $g->forceFill(['berkas' => $tujuan])->saveQuietly();

            $pindah++;
        }

        $this->newLine();
        $this->info(sprintf('%d berkas %sdipindah, %d sudah di tempatnya.',
            $pindah, $kering ? 'AKAN ' : '', $tetap));

        $this->sapuYatim($cakram, $kering);

        if ($kering) {
            $this->comment('Jalan kering: tidak ada yang diubah.');
        }

        return self::SUCCESS;
    }

    /**
     * Berkas di folder galeri yang tidak ditunjuk baris mana pun.
     *
     * Dilaporkan, bukan langsung dihapus: berkas yatim bisa saja sisa
     * percobaan, tetapi bisa juga foto yang barisnya terhapus karena kekeliruan
     * — dan yang kedua tidak bisa dikembalikan.
     */
    private function sapuYatim($cakram, bool $kering): void
    {
        $dipakai = Galeri::pluck('berkas')->filter()->all();
        $yatim = [];

        foreach ($cakram->allFiles(Galeri::FOLDER) as $berkas) {
            // .DS_Store dan sejenisnya bukan foto; dibiarkan saja.
            if (! preg_match('/\.(webp|jpe?g|png)$/i', $berkas)) {
                continue;
            }

            if (! in_array($berkas, $dipakai, true)) {
                $yatim[] = $berkas;
            }
        }

        if ($yatim === []) {
            return;
        }

        $this->newLine();
        $this->warn(count($yatim) . ' berkas tidak ditunjuk baris mana pun:');

        foreach ($yatim as $b) {
            $this->line('  ' . $b);
        }

        if (! $this->option('buang-yatim')) {
            $this->comment('Jalankan dengan --buang-yatim kalau memang mau dihapus.');

            return;
        }

        if ($kering) {
            $this->comment('Jalan kering: yang yatim AKAN dihapus.');

            return;
        }

        foreach ($yatim as $b) {
            $cakram->delete($b);
        }

        $this->info(count($yatim) . ' berkas yatim dihapus.');
    }
}
