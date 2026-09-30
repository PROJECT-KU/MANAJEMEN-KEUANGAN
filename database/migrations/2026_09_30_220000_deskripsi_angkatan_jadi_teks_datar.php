<?php

use App\Support\TeksDariHtml;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Membersihkan deskripsi angkatan yang isinya HTML.
 *
 * Empat angkatan Bibliometrik menyimpan hasil tempel dari ChatGPT — lengkap
 * dengan atribut data-start, style font Word, dan satu ikon SVG. Halaman publik
 * menampilkan deskripsi dengan {{ }}, jadi yang terbaca pengunjung adalah
 * tag-nya, bukan tulisannya: '<h2 data-start="151">📚 Pelatihan…</h2>'.
 *
 * Diukur sebelum menulis: 2.900–4.283 huruf jadi 1.178–1.283 huruf, dan
 * perbandingan kata per kata tidak menemukan satu pun kata yang hilang.
 */
return new class extends Migration
{
    /** Tempat naskah aslinya dititipkan sebelum ditimpa. */
    private const TITIPAN = 'app/deskripsi-angkatan-html-asli.json';

    public function up(): void
    {
        $asli = [];
        $diubah = 0;

        foreach (DB::table('kategori_layanan')->whereNotNull('desc')->get(['id', 'desc']) as $baris) {
            $bersih = TeksDariHtml::ubah($baris->desc);

            if ($bersih === $baris->desc) {
                continue;
            }

            $asli[$baris->id] = $baris->desc;

            DB::table('kategori_layanan')->where('id', $baris->id)->update(['desc' => $bersih]);
            $diubah++;
        }

        /*
         * Naskah aslinya dititipkan ke berkas, bukan dibuang. Perbandingan kata
         * sudah bersih, tetapi pengubahannya satu arah — tidak ada cara merakit
         * ulang HTML dari teks datar — jadi kalau ada yang ternyata keliru,
         * yang hilang harus masih bisa dibaca dari peladen.
         */
        if ($asli !== []) {
            $berkas = storage_path(self::TITIPAN);

            if (! is_dir(dirname($berkas))) {
                mkdir(dirname($berkas), 0755, true);
            }

            file_put_contents($berkas, json_encode($asli, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        echo "  deskripsi dibersihkan: {$diubah} angkatan\n";
    }

    public function down(): void
    {
        $berkas = storage_path(self::TITIPAN);

        if (! is_file($berkas)) {
            return;
        }

        foreach ((array) json_decode(file_get_contents($berkas), true) as $id => $desc) {
            DB::table('kategori_layanan')->where('id', $id)->update(['desc' => $desc]);
        }
    }
};
