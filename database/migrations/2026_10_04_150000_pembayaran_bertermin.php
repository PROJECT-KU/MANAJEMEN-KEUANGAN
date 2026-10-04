<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pembayaran bertermin — DP, cicilan, pelunasan.
 *
 * Sebelum ini uang hanya punya dua keadaan: "Menunggu bayar" atau "Lunas".
 * Tidak ada di antaranya. Akibatnya lembaga yang sudah mentransfer DP 30%
 * tercatat SAMA PERSIS dengan lembaga yang belum bayar sepeser pun — 30 baris
 * "Menunggu bayar" — dan satu-satunya jejak DP-nya adalah catatan panitia,
 * teks bebas yang tidak dijumlahkan di mana pun.
 *
 * Satu baris per uang yang masuk, bukan satu kolom "sudah dibayar". Lembaga
 * mencicil dua sampai tiga kali, dan satu kolom berarti termin ketiga menimpa
 * yang kedua — lalu sisa tagihannya salah tanpa ada yang bisa menelusurinya.
 *
 * DUA JENIS INDUK, sebab rombongan punya dua bentuk yang berbeda sama sekali:
 *   - 'lembaga'     induknya satu baris pemesanan_lembaga, yang sendirinya
 *                   mengikat beberapa pendaftaran di beberapa angkatan;
 *   - 'pendaftaran' induknya SATU baris pendaftaran berisi banyak kursi —
 *                   rombongan yang dibayar perorangan, seperti si A yang
 *                   mengajak enam temannya.
 * Keduanya tidak bisa disatukan jadi satu kunci: yang satu menunjuk pesanan,
 * yang lain menunjuk baris di salah satu dari lima tabel pendaftaran.
 *
 * Tanpa foreign key, dan itu disengaja — alasannya sama dengan
 * pendaftaran_peserta: sasarannya bergantung kolom `jenis`, dan MySQL tidak
 * bisa menyatakan kendala seperti itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembayaran_pendaftaran')) {
            return;
        }

        Schema::create('pembayaran_pendaftaran', function (Blueprint $t) {
            $t->char('id', 36)->primary();

            $t->string('jenis', 12);
            $t->char('induk_id', 36);
            // Hanya terisi untuk jenis 'pendaftaran'; menunjuk tabel mana di
            // antara kelimanya yang memuat barisnya.
            $t->string('layanan', 40)->nullable();

            /*
             * Urutan termin, dipakai menamai "DP", "Termin 2", "Pelunasan" —
             * dan BUKAN created_at: dua termin bisa dicatat di hari yang sama
             * saat panitia menyusulkan catatan lama, dan kwitansi yang
             * nomornya berubah-ubah tiap dibuka tidak bisa dipegang.
             */
            $t->unsignedSmallInteger('urutan')->default(1);

            // Rupiah penuh, bilangan bulat. Tagihan terbesar yang mungkin
            // jauh di bawah batas unsigned int, dan desimal rupiah tidak ada.
            $t->unsignedInteger('nominal');

            // Tanggal uang MASUK, bukan tanggal dicatat: panitia rutin
            // menyusulkan catatan beberapa hari kemudian, dan rekonsiliasi
            // dengan mutasi rekening memakai tanggal mutasinya.
            $t->date('tanggal');

            $t->string('cara_bayar', 20)->nullable();
            $t->string('bukti', 255)->nullable();
            $t->string('catatan', 255)->nullable();

            // Nama, bukan id: sama seperti `dibuat_oleh` di pemesanan_lembaga,
            // supaya jejaknya tetap terbaca walau akunnya kelak dihapus.
            $t->string('dicatat_oleh', 255)->nullable();

            $t->timestamps();

            // Satu-satunya cara barisnya dicari: "berapa yang sudah masuk
            // untuk induk ini".
            $t->index(['jenis', 'induk_id'], 'bayar_induk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pendaftaran');
    }
};
