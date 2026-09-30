<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mengubah varian layanan dari objek JSON jadi larik berurut.
 *
 * MySQL mengurutkan ulang kunci objek JSON-nya sendiri — terukur, {zulu, alfa,
 * bravo_panjang} terbaca kembali sebagai {alfa, zulu, bravo_panjang}, yaitu
 * menurut panjang lalu abjad. Urutan yang diketik admin hilang tanpa jejak.
 *
 * Larik dipertahankan apa adanya, jadi tiap varian jadi satu butir {kode, nama}.
 * Modelnya tetap mengenali bentuk objek yang lama, tetapi menyimpan dua bentuk
 * di satu kolom adalah jenis kerapian yang cepat menagih bunganya.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('layanan')->whereNotNull('varian')->get() as $baris) {
            $isi = json_decode($baris->varian, true) ?: [];

            if ($isi === [] || array_is_list($isi)) {
                continue;
            }

            $larik = [];

            foreach ($isi as $kode => $nama) {
                $larik[] = ['kode' => $kode, 'nama' => $nama];
            }

            DB::table('layanan')->where('id', $baris->id)->update(['varian' => json_encode($larik)]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('layanan')->whereNotNull('varian')->get() as $baris) {
            $isi = json_decode($baris->varian, true) ?: [];

            if (! array_is_list($isi)) {
                continue;
            }

            $peta = [];

            foreach ($isi as $butir) {
                $peta[$butir['kode']] = $butir['nama'];
            }

            DB::table('layanan')->where('id', $baris->id)->update(['varian' => json_encode($peta)]);
        }
    }
};
