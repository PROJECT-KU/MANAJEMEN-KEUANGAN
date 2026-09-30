<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cetakan deskripsi angkatan, disimpan bersama tarifnya.
 *
 * Teks pengumuman tiap angkatan nyaris sama persis. Yang berganti cuma nomor
 * angkatan, tanggal, lokasi, harga, dan kontak — dan SEMUANYA sudah jadi kolom
 * di `kategori_layanan`. Jadi deskripsinya tidak perlu diketik ulang sama
 * sekali; cukup satu cetakan per layanan, lalu bagian yang berganti diisi dari
 * kolom yang memang sudah diisi admin.
 *
 * Tempatnya di tarif, bukan tabel sendiri: cetakan tanpa harga dan fasilitas
 * di sebelahnya tidak ada artinya, dan memisahkannya cuma menambah satu tempat
 * lagi yang harus dibuka.
 *
 * `kegiatan` diperlakukan sama seperti `fasilitas` — daftar yang sama
 * berulangnya, dan sama-sama selama ini hidup di dalam teks bebas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $t) {
            $t->longText('template_deskripsi')->nullable()->after('fasilitas');
            $t->json('kegiatan')->nullable()->after('template_deskripsi');
            // Kontak panitia berganti tiap beberapa bulan (Dinar/Elsa lalu
            // Kumala/Rizal) dan tidak punya kolom di mana pun sebelum ini.
            $t->text('kontak')->nullable()->after('kegiatan');
        });

        $this->isiCetakanAwal();
    }

    private function isiCetakanAwal(): void
    {
        $cetakanCamp = <<<'TEKS'
        📘 {nama}

        🗓 {tanggal}
        🎯 Target Training: Paper Tersubmit

        Program intensif pendampingan penulisan artikel ilmiah yang dirancang khusus bagi dosen, peneliti, dan akademisi yang menargetkan submit artikel ke jurnal terindeks Scopus.
        Selama 3 hari 2 malam, peserta dibimbing secara fokus, terarah, dan praktis mulai dari pengolahan hasil riset hingga menjadi artikel ilmiah yang siap disubmit.

        🔹 Kegiatan Utama
        {kegiatan}

        🔹 Fasilitas Peserta
        {fasilitas}

        💰 Biaya Investasi: {harga}
        📍 Lokasi: {lokasi}

        📞 Info & Pendaftaran:
        {kontak}
        TEKS;

        $cetakanBiblio = <<<'TEKS'
        📣 {nama}

        🗓 {tanggal}
        ⏰ 19.30–20.45 WIB

        Masih bingung mengolah data bibliometrik dan menuangkannya ke dalam manuskrip jurnal? Kelas ini membimbing dari pengenalan tools sampai manuskrip siap submit.

        🔹 Yang akan dipelajari
        {kegiatan}

        🔹 Fasilitas Peserta
        {fasilitas}

        💰 Investasi: {harga}
        💸 Promo: {harga_promo} dengan kode {kode_promo}

        📞 Info & Pendaftaran:
        {kontak}
        TEKS;

        $kegiatanCamp = [
            'Penulisan dan penajaman hasil riset ke dalam artikel ilmiah',
            'Pencarian dan pemilihan jurnal terindeks Scopus yang relevan',
            'Penguasaan tools & aplikasi online pendukung penulisan paper',
            'Strategi penggunaan AI untuk mempercepat dan meningkatkan kualitas paper',
            'Pendampingan teknis hingga tahap submit',
        ];

        $kegiatanBiblio = [
            'Pengenalan & instalasi tools bibliometrik',
            'Data mining & visualisasi data',
            'Interpretasi hasil',
            'Penyusunan manuskrip (pendahuluan & metode)',
            'Klinik manuskrip',
        ];

        $kontak = "📞 Kumala: 0889-8356-7819\n📞 Rizal: 0856-6964-7204";

        DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', 'scopus_camp')
            ->update([
                'template_deskripsi' => $cetakanCamp,
                'kegiatan' => json_encode($kegiatanCamp),
                'kontak' => $kontak,
            ]);

        DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', 'bibliometrik')
            ->update([
                'template_deskripsi' => $cetakanBiblio,
                'kegiatan' => json_encode($kegiatanBiblio),
                'kontak' => $kontak,
            ]);
    }

    public function down(): void
    {
        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $t) {
            $t->dropColumn(['template_deskripsi', 'kegiatan', 'kontak']);
        });
    }
};
