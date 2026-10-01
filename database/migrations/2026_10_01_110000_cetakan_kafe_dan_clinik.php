<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Memberi cetakan pengumuman untuk Scopus Kafe dan Clinik Scopus.
 *
 * Keduanya sudah punya tarif tetapi cetakannya kosong, jadi tombol "Rakit dari
 * cetakan" menghasilkan teks kosong dan admin harus mengetik seluruh
 * pengumumannya dari nol — persis hal yang ingin dihapus fitur ini.
 *
 * ISINYA SENGAJA HANYA FAKTA YANG MEMANG ADA DI DATA.
 *
 * Yang saya tahu dari tarifnya cuma: harganya, satuannya ("per pertemuan",
 * "per satu sesi"), dan daftar fasilitasnya. Apa sebenarnya yang dikerjakan di
 * Scopus Kafe, siapa yang cocok ikut, dan apa hasil yang dijanjikan — itu tidak
 * ada di mana pun, dan mengarangnya berarti menaruh janji palsu di naskah yang
 * dibaca calon pembeli.
 *
 * Jadi cetakan ini merakit kerangkanya saja: nama, tanggal, lokasi, harga,
 * fasilitas, dan kontak. Satu alinea penjelasan tinggal diketik pemiliknya di
 * layar Tarif Layanan, dan kerangkanya sudah menunggu di tempatnya.
 *
 * Bagian yang datanya belum ada — kegiatan dan kontak keduanya masih kosong —
 * tidak akan tercetak sama sekali berkat aturan blok di PerakitDeskripsi, dan
 * muncul sendiri begitu diisi.
 */
return new class extends Migration
{
    private const CETAKAN_KAFE = <<<'TEKS'
☕ {nama}

🗓 {tanggal}
📍 {lokasi}

🔹 Yang dikerjakan
{kegiatan}

🔹 Yang Anda dapat
{fasilitas}

💰 Biaya {harga} per pertemuan

📞 Mau daftar atau bertanya dulu? Hubungi:
{kontak}
TEKS;

    private const CETAKAN_CLINIK = <<<'TEKS'
🩺 {nama}

🗓 {tanggal}
📍 {lokasi}

🔹 Yang dibahas
{kegiatan}

🔹 Yang Anda dapat
{fasilitas}

💰 Biaya {harga} per satu sesi

📞 Mau daftar atau bertanya dulu? Hubungi:
{kontak}
TEKS;

    public function up(): void
    {
        foreach ([
            'scopus_kafe' => self::CETAKAN_KAFE,
            'clinik_scopus' => self::CETAKAN_CLINIK,
        ] as $layanan => $cetakan) {
            DB::table('clinikscopus_biaya_persesi')
                ->where('layanan', $layanan)
                // Yang sudah punya cetakan TIDAK ditimpa: kalau pemiliknya
                // sempat menulis sendiri sebelum migrasi ini jalan di peladen,
                // tulisannya yang menang.
                ->where(fn ($q) => $q->whereNull('template_deskripsi')->orWhere('template_deskripsi', ''))
                ->update(['template_deskripsi' => $cetakan]);
        }
    }

    public function down(): void
    {
        DB::table('clinikscopus_biaya_persesi')
            ->whereIn('layanan', ['scopus_kafe', 'clinik_scopus'])
            ->whereIn('template_deskripsi', [self::CETAKAN_KAFE, self::CETAKAN_CLINIK])
            ->update(['template_deskripsi' => null]);
    }
};
