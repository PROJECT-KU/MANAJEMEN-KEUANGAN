<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Memindahkan promo Clinik Scopus yang lama ke tabel promo bersama.
 *
 * Tiga baris, dan ketiganya sudah `non active` dengan tanggal selesai paling
 * akhir 30 Juli 2026 — jadi yang dipindah RIWAYAT, bukan promo yang sedang
 * berjalan. Tidak ada harga yang berubah karena migrasi ini.
 *
 * Idnya DIPERTAHANKAN. Dengan id baru, baris di sini dan baris lama di
 * `clinikscopus_promo` tidak bisa lagi dikenali sebagai promo yang sama, dan
 * itu satu-satunya cara memeriksa apakah pemindahannya benar.
 *
 * Yang TIDAK ikut pindah, dan sengaja:
 *
 * - Sasarannya. Promo lama menunjuk sesi Clinik Scopus
 *   (`clinikscopus_promo_sesi`, 2 baris), sementara promo baru menunjuk
 *   ANGKATAN — dan Clinik Scopus tidak punya angkatan sama sekali, tarifnya
 *   per sesi. Jadi ketiganya masuk tanpa angkatan: terbaca di layar sebagai
 *   "belum menunjuk angkatan", yang memang keadaannya.
 * - Kode diskonnya ("703", "SalamQ1", "-"). Promo baru berlaku otomatis,
 *   tidak ada yang diketik pendaftar. Kodenya disimpan di `catatan` supaya
 *   tidak hilang dari catatan sejarah.
 *
 * Tabel lamanya TIDAK dibuang. Halaman pemesanan Clinik Scopus yang dilihat
 * umum masih membacanya langsung (PublicClinikScopusController), dan
 * membuangnya di migrasi yang sama berarti satu migrasi bisa mematikan
 * halaman publik. Ketiga barisnya non-active, jadi tidak ada promo yang
 * tampil dari sana.
 */
return new class extends Migration
{
    public function up(): void
    {
        $lama = DB::table('clinikscopus_promo')->orderBy('created_at')->get();

        foreach ($lama as $p) {
            // Sudah pernah dipindah; migrasi yang dijalankan dua kali tidak
            // boleh menggandakan riwayatnya.
            if (DB::table('promo_layanan')->where('id', $p->id)->exists()) {
                continue;
            }

            $persen = (int) $p->diskon_persentase;
            $nominal = (int) preg_replace('/\D+/', '', (string) $p->nominal_diskon);

            // Tipe lamanya "persentase"/"nominal"; yang baru "persen"/"rupiah".
            // Yang tidak dikenali jatuh ke rupiah, bukan ke persen: nominal
            // yang salah dibaca sebagai persen menjadi potongan ribuan persen.
            $persenan = $p->tipe_diskon === 'persentase' && $persen > 0;

            $kode = trim((string) $p->kode_diskon);

            DB::table('promo_layanan')->insert([
                'id' => $p->id,
                'nama' => mb_substr((string) $p->nama_promo, 0, 120),
                'tipe' => $persenan ? 'persen' : 'rupiah',
                'persen' => $persenan ? min(100, $persen) : null,
                'nominal' => $persenan ? null : $nominal,
                'batas_potongan' => null,
                'mulai' => substr((string) $p->tanggal_mulai_promo, 0, 10),
                'selesai' => substr((string) $p->tanggal_selesai_promo, 0, 10),
                'aktif' => false,
                'catatan' => 'Dipindah dari promo Clinik Scopus yang lama.'
                    . ($kode !== '' && $kode !== '-' ? ' Dulu memakai kode diskon "' . $kode . '".' : ''),
                'penginput_id' => null,
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        // Hanya yang datang dari pemindahan ini; promo yang dibuat admin
        // sesudahnya tidak boleh ikut terhapus saat migrasinya dibatalkan.
        DB::table('promo_layanan')
            ->whereIn('id', DB::table('clinikscopus_promo')->pluck('id'))
            ->delete();
    }
};
