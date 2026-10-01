<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Layanan baru: Sharing Session (webinar eksklusif berbayar).
 *
 * Sebelum ini sesi sharing dijalankan lewat satu halaman HTML statis di
 * subdomain tersendiri — tanggal, topik, pemateri, flyer, dan harganya semua
 * diketik langsung ke dalam berkasnya, dan mendaftar berarti mengirim pesan
 * WhatsApp. Tidak ada satu pun pesertanya yang tercatat di sistem.
 *
 * Yang ditambahkan di sini:
 *
 * 1. Enam kolom baru pada `kategori_layanan` untuk acara DARING. Semuanya
 *    boleh NULL dan diabaikan layanan yang tidak memakainya, persis seperti
 *    `lokasi` yang sudah lebih dulu begitu untuk Scopus Camp.
 *
 * 2. Layanan `sharing_session` di katalog, dengan tarifnya Rp 129.000.
 *
 * 3. Tabel `sharing_session_pendaftaran`, bentuknya mengikuti
 *    `scopus_camp_pendaftaran` supaya satu layar pendaftar dan satu
 *    penghitung peserta bisa melayani keduanya.
 *
 * Yang GRATIS dipensiunkan: mulai sekarang semua sesi berbayar, jadi tidak
 * ada varian "gratis" yang dibuat.
 */
return new class extends Migration
{
    private const KODE = 'sharing_session';

    public function up(): void
    {
        Schema::table('kategori_layanan', function (Blueprint $t) {
            // Jam acara. Tanggalnya sudah ada di `mulai`/`selesai`, tetapi
            // webinar dua jam tidak bisa diterangkan tanggal saja — "21 Juni"
            // tanpa "09.30 - 11.30 WIB" membuat peserta menebak.
            $t->time('jam_mulai')->nullable()->after('selesai');
            $t->time('jam_selesai')->nullable()->after('jam_mulai');

            // Tempat acara daring: Zoom, Google Meet, dan seterusnya. Isian
            // bebas, bukan daftar tertutup — menambah satu nama tidak boleh
            // berarti mengubah kode lalu deploy.
            $t->string('platform')->nullable()->after('lokasi');

            // Pemateri. Namanya yang paling sering jadi alasan orang
            // mendaftar, jadi ia harus bisa diganti tanpa menyentuh berkas.
            $t->string('pemateri')->nullable()->after('platform');
            $t->string('pemateri_jabatan')->nullable()->after('pemateri');
            $t->string('pemateri_foto')->nullable()->after('pemateri_jabatan');
        });

        $this->tambahLayanan();
        $this->tambahTarif();

        Schema::create('sharing_session_pendaftaran', function (Blueprint $t) {
            /*
             * Bentuknya SENGAJA sama dengan scopus_camp_pendaftaran, sampai ke
             * nama kolomnya. Penghitung peserta, penjaga hapus angkatan, dan
             * layar daftar pendaftar semuanya bekerja per nama kolom; bentuk
             * yang berbeda berarti tiga tempat itu harus bercabang.
             */
            $t->uuid('id')->primary();
            $t->string('token', 60)->nullable()->unique();
            $t->string('id_transaksi')->nullable()->index();

            $t->uuid('kategori_id')->nullable()->index();
            $t->foreign('kategori_id')->references('id')->on('kategori_layanan');

            $t->string('nama');
            $t->string('email');
            $t->string('telp', 30);
            $t->string('affiliasi')->nullable();

            // Berapa ORANG dalam satu pendaftaran. Satu baris boleh membawa
            // rombongan, dan kuota dihitung per orang — bukan per baris.
            $t->unsignedInteger('jumlah_pendaftar')->default(1);

            $t->string('ppn')->nullable();
            $t->string('kode_unik')->nullable();
            $t->string('kode_diskon')->nullable();
            $t->string('nominal_diskon')->nullable();
            $t->string('total_pembayaran')->nullable();

            // Bukti bayar untuk transfer manual; kosong kalau bayarnya lewat
            // gerbang pembayaran.
            $t->string('gambar')->nullable();

            /*
             * Jejak pembayaran daring. Disimpan di sini, bukan di tabel
             * tersendiri: satu pendaftaran punya tepat satu pembayaran, dan
             * tabel terpisah berarti gabungan di setiap layar yang menampilkan
             * daftarnya.
             */
            $t->string('cara_bayar', 20)->default('transfer');
            $t->string('bayar_rujukan')->nullable()->index();
            $t->string('bayar_status', 20)->nullable();
            $t->timestamp('bayar_pada')->nullable();
            $t->timestamp('kedaluwarsa_pada')->nullable();

            $t->string('status', 20)->default('pending')->index();
            $t->text('note')->nullable();
            $t->timestamps();
        });
    }

    private function tambahLayanan(): void
    {
        if (DB::table('layanan')->where('kode', self::KODE)->exists()) {
            return;
        }

        DB::table('layanan')->insert([
            'id' => (string) Str::uuid(),
            'kode' => self::KODE,
            'nama' => 'Sharing Session',
            'satuan' => 'peserta',
            'ikon' => 'fa-microphone',
            'warna' => 'mis-jingga',
            // Tanpa varian: semua sesi bentuknya sama, yang berganti cuma
            // topik dan pematerinya.
            'varian' => json_encode([]),
            'urutan' => (int) DB::table('layanan')->max('urutan') + 1,
            'aktif' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function tambahTarif(): void
    {
        $tabel = 'clinikscopus_biaya_persesi';

        if (DB::table($tabel)->where('layanan', self::KODE)->exists()) {
            return;
        }

        DB::table($tabel)->insert([
            'id' => (string) Str::uuid(),
            'layanan' => self::KODE,
            'varian' => null,
            'biaya_persesi' => 129000,
            'ppn' => 0,
            'fasilitas' => json_encode([
                'E-sertifikat resmi atas nama peserta',
                'Materi dan rekaman sesi',
                'Tanya jawab langsung dengan pemateri',
                'Grup diskusi peserta',
            ]),
            'kegiatan' => json_encode([
                'Pemaparan materi oleh pemateri',
                'Studi kasus dan praktik langsung',
                'Sesi tanya jawab',
            ]),
            // Teks biasa, BUKAN JSON: {kontak} dicetak apa adanya oleh
            // PerakitDeskripsi, dan tarif layanan lain sudah menyimpannya
            // begitu. Diisi JSON, yang terbaca pengunjung adalah tanda kurung
            // kurawal dan nama kuncinya.
            'kontak' => "📞 Kumala: 0889-8356-7819\n📞 Rizal: 0856-6964-7204",
            /*
             * Cetakan deskripsi memakai penanda yang sama dengan layanan lain
             * supaya PerakitDeskripsi tidak perlu tahu layanan ini ada.
             */
            'template_deskripsi' => implode("\n", [
                '🎙 {nama}',
                '',
                '🗓 {tanggal}',
                '⏰ {jam}',
                '💻 {platform}',
                '',
                '🎤 Pemateri: {pemateri}',
                '',
                '🔹 Yang dibahas',
                '{kegiatan}',
                '',
                '🔹 Yang didapat peserta',
                '{fasilitas}',
                '',
                '💵 {harga} per peserta',
                '🎟 Sisa kuota: {sisa_kuota} peserta',
                '',
                '📞 Info & pendaftaran:',
                '{kontak}',
            ]),
            'status' => 'active',
            'berlaku_mulai' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sharing_session_pendaftaran');

        DB::table('clinikscopus_biaya_persesi')->where('layanan', self::KODE)->delete();

        // Layanan hanya dibuang kalau belum ada angkatan yang menunjuknya;
        // membuangnya saat sudah terpakai akan membuat angkatan itu tidak
        // menemukan katalognya dan layar daftar galat.
        if (! DB::table('kategori_layanan')->where('layanan', self::KODE)->exists()) {
            DB::table('layanan')->where('kode', self::KODE)->delete();
        }

        Schema::table('kategori_layanan', function (Blueprint $t) {
            $t->dropColumn([
                'jam_mulai', 'jam_selesai', 'platform',
                'pemateri', 'pemateri_jabatan', 'pemateri_foto',
            ]);
        });
    }
};
