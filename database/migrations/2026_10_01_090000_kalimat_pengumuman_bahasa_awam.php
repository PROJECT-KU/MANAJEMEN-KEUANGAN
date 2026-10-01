<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menulis ulang kalimat pengumuman supaya terbaca orang awam.
 *
 * Yang dibaca pelanggan selama ini penuh istilah dalam: "Target Training:
 * Paper Tersubmit", "penajaman hasil riset", "penguasaan tools & aplikasi
 * online pendukung penulisan paper", "pendampingan handling jurnal pasca
 * camp", "Seminar Kit", "E-Materi". Orang yang baru pertama mendengar Scopus
 * tidak bisa menebak apa yang sebenarnya ia dapat.
 *
 * Yang diubah tiga hal, sebab ketiganya ikut tercetak di pengumuman: kalimat
 * cetakannya, daftar kegiatan, dan daftar fasilitas.
 *
 * Isi dan janjinya TIDAK ditambah. "Target Training: Paper Tersubmit" tetap
 * jadi target, bukan jaminan — menaikkan target jadi janji di naskah yang
 * dibaca calon pembeli bukan pekerjaan yang boleh dikerjakan diam-diam.
 */
return new class extends Migration
{
    /** Tempat naskah lamanya dititipkan sebelum ditimpa. */
    private const TITIPAN = 'app/pengumuman-naskah-lama.json';

    private const CETAKAN_CAMP = <<<'TEKS'
📘 {nama}

🗓 {tanggal}
📍 {lokasi}

Punya data penelitian yang belum sempat ditulis jadi artikel? Di acara ini Anda menulisnya sampai selesai, ditemani pendamping yang sudah terbiasa menembus jurnal internasional.

Selama {durasi} Anda menulis, bukan duduk mendengarkan ceramah. Targetnya satu: begitu acara selesai, artikel Anda sudah dikirim ke jurnal bereputasi — jurnal yang terdaftar di Scopus, pangkalan data yang dipakai kampus di seluruh dunia untuk menilai mutu terbitan.

Cocok untuk dosen, peneliti, dan mahasiswa pascasarjana yang datanya sudah ada tetapi artikelnya belum jalan.

🔹 Yang dikerjakan bersama
{kegiatan}

🔹 Yang Anda dapat
{fasilitas}

💰 Biaya {harga} per orang

📞 Mau daftar atau bertanya dulu? Hubungi:
{kontak}
TEKS;

    private const CETAKAN_BIBLIO = <<<'TEKS'
📣 {nama}

🗓 {tanggal}
⏰ 19.30–20.45 WIB

Analisis bibliometrik itu cara memetakan sebuah bidang ilmu: siapa yang paling banyak dirujuk, topik apa yang sedang ramai, dan bagian mana yang belum digarap orang. Dari peta itu Anda tahu di mana penelitian Anda layak berdiri — dan artikelnya jadi lebih mudah diterima jurnal.

Di kelas ini Anda dibimbing dari nol: memasang aplikasinya, mengolah datanya, membaca hasilnya, sampai menuliskannya jadi naskah.

Cocok untuk dosen, peneliti, dan mahasiswa yang belum pernah memakai analisis bibliometrik sama sekali.

🔹 Yang dipelajari
{kegiatan}

🔹 Yang Anda dapat
{fasilitas}

💰 Biaya {harga} per orang
💸 Promo {harga_promo} dengan kode {kode_promo}

📞 Mau daftar atau bertanya dulu? Hubungi:
{kontak}
TEKS;

    private const KEGIATAN_CAMP = [
        'Mengubah data penelitian Anda jadi naskah artikel',
        'Memilih jurnal yang paling cocok dengan topik Anda',
        'Memakai aplikasi bantu menulis, mengutip, dan merapikan daftar pustaka',
        'Memakai AI untuk mempercepat menulis, tanpa melanggar aturan jurnal',
        'Didampingi sampai naskahnya benar-benar terkirim ke jurnal',
    ];

    private const KEGIATAN_BIBLIO = [
        'Mengenal dan memasang aplikasinya',
        'Mengambil data dari Scopus dan mengubahnya jadi peta',
        'Membaca arti peta itu untuk penelitian Anda',
        'Menulis bagian pendahuluan dan metode naskah',
        'Naskah Anda dibaca dan dikoreksi bersama',
    ];

    /** Kata lama => kata baru, dipakai pada daftar fasilitas tiap tarif. */
    private const FASILITAS = [
        'Konsumsi selama kegiatan' => 'Makan dan minum selama acara',
        // Kata bendanya DIPERTAHANKAN. "Menginap di tempat acara" sama
        // jelasnya, tetapi pencarian tarif mencocokkan potongan kata — dan
        // admin yang mengetik "penginapan" tidak akan menemukan apa pun.
        'Penginapan ala Rumah Scopus' => 'Penginapan di tempat acara',
        'Pendampingan handling jurnal pasca camp' => 'Pendampingan lanjutan sampai jurnal memberi jawaban',
        'Sertifikat & Seminar Kit' => 'Sertifikat dan perlengkapan peserta',
        'Cek plagiasi' => 'Pemeriksaan kemiripan naskah (cek plagiasi)',
        'E-Materi' => 'Materi digital yang bisa diunduh',
        'Mushola, kolam renang & treadmill' => 'Mushola, kolam renang, dan treadmill',
        'Materi & rekaman' => 'Materi dan rekaman kelas',
        'Materi cetak' => 'Materi cetak',
        'Konsumsi' => 'Makan dan minum selama kelas',
        'Pendampingan analisis' => 'Pendampingan saat mengolah data sendiri',
    ];

    public function up(): void
    {
        $lama = [];
        $diubah = 0;

        foreach (DB::table('clinikscopus_biaya_persesi')->get() as $baris) {
            $baru = [];

            if ($baris->layanan === 'scopus_camp') {
                $baru['template_deskripsi'] = self::CETAKAN_CAMP;
                $baru['kegiatan'] = json_encode(self::KEGIATAN_CAMP, JSON_UNESCAPED_UNICODE);
            } elseif ($baris->layanan === 'bibliometrik') {
                $baru['template_deskripsi'] = self::CETAKAN_BIBLIO;
                $baru['kegiatan'] = json_encode(self::KEGIATAN_BIBLIO, JSON_UNESCAPED_UNICODE);
            }

            $fasilitas = json_decode((string) $baris->fasilitas, true);

            if (is_array($fasilitas) && $fasilitas !== []) {
                $diganti = array_map(fn ($f) => self::FASILITAS[trim((string) $f)] ?? $f, $fasilitas);

                if ($diganti !== $fasilitas) {
                    $baru['fasilitas'] = json_encode(array_values($diganti), JSON_UNESCAPED_UNICODE);
                }
            }

            if ($baru === []) {
                continue;
            }

            $lama[$baris->id] = [
                'template_deskripsi' => $baris->template_deskripsi,
                'kegiatan' => $baris->kegiatan,
                'fasilitas' => $baris->fasilitas,
            ];

            DB::table('clinikscopus_biaya_persesi')->where('id', $baris->id)->update($baru);
            $diubah++;
        }

        /*
         * Naskah lamanya dititipkan, bukan dibuang. Kalimat pengumuman itu
         * keputusan bisnis; kalau pemiliknya lebih suka yang lama, ia harus
         * masih bisa dibaca dari peladen, bukan dicari di riwayat git.
         */
        if ($lama !== []) {
            $berkas = storage_path(self::TITIPAN);

            if (! is_dir(dirname($berkas))) {
                mkdir(dirname($berkas), 0755, true);
            }

            file_put_contents($berkas, json_encode($lama, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        echo "  naskah pengumuman ditulis ulang: {$diubah} tarif\n";
    }

    public function down(): void
    {
        $berkas = storage_path(self::TITIPAN);

        if (! is_file($berkas)) {
            return;
        }

        foreach ((array) json_decode(file_get_contents($berkas), true) as $id => $nilai) {
            DB::table('clinikscopus_biaya_persesi')->where('id', $id)->update($nilai);
        }
    }
};
