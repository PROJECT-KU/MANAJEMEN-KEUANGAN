<?php

namespace App\Http\Controllers\Publict;

use App\ClinikScopusBiayaPersesi;
use App\GaleriLayanan;
use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Support\RentangTanggal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * Data Webinar Eksklusif untuk halaman landing di subdomain tersendiri.
 *
 * Halaman itu statis dan sudah terpasang di subdomainnya sendiri; sebelum ini
 * tanggal, topik, pemateri, flyer, dan harganya diketik langsung ke dalam
 * berkas HTML-nya. Mengganti sesi berarti menyunting berkas lalu mengunggah
 * ulang, dan seringnya ada satu tempat yang terlewat — tanggal di judul sudah
 * diganti, tanggal di tombol WhatsApp belum.
 *
 * Dengan endpoint ini, admin cukup mengubah angkatannya di MIS.
 *
 * ---------------------------------------------------------------------------
 * Kenapa ini boleh ada padahal routes/api.php sengaja dikosongkan
 * ---------------------------------------------------------------------------
 *
 * Yang dicabut pada 27 September 2026 adalah endpoint BERSESI — masuk, daftar,
 * saldo, debit, kredit — yang melewati seluruh penjagaan halaman masuk.
 *
 * Yang di sini beda jenisnya:
 *
 * - HANYA membaca. Tidak ada satu pun jalur yang menulis.
 * - Tanpa autentikasi, dan memang tidak boleh punya: pembacanya halaman
 *   statis di peramban pengunjung, jadi kunci apa pun yang ditaruh di sana
 *   sama saja dengan terbuka.
 * - Yang dikeluarkan HANYA angkatan berstatus `active` — persis yang sudah
 *   terpampang di halaman publik rumahscopus.org. Tidak ada satu pun data
 *   peserta, harga pokok, atau catatan internal.
 *
 * Karena itu daftar kolomnya ditulis satu per satu di bawah, bukan dengan
 * toArray() atau only(): menambah kolom pada tabel angkatan tidak boleh
 * diam-diam ikut menerbitkannya ke internet.
 */
class ApiWebinarEksklusifController extends Controller
{
    private const KODE = 'webinar_eksklusif';

    /** Berapa lama jawabannya boleh ditembolok peramban dan CDN. */
    private const DETIK_TEMBOLOK = 60;

    /**
     * Sesi yang SEDANG dibuka — satu, yang paling dekat tanggalnya.
     *
     * Halaman landing-nya memang memajang satu sesi, jadi yang dikirim satu.
     * Sesi lain yang juga aktif ikut diringkas di `berikutnya` supaya halaman
     * itu bisa menyebut "ada juga tanggal lain" tanpa permintaan kedua.
     */
    public function sekarang(): JsonResponse
    {
        $sesi = $this->kueriAktif()->first();

        if ($sesi === null) {
            /*
             * 200, bukan 404. Yang bertanya halaman landing, dan "belum ada
             * sesi terjadwal" adalah jawaban yang sah — bukan kesalahan. Dengan
             * 404, fetch() di sana harus membedakan dua jenis kegagalan, dan
             * yang paling mungkin terjadi adalah halamannya menampilkan pesan
             * galat merah padahal panitianya memang belum menjadwalkan.
             */
            return $this->jawab([
                'ada' => false,
                'pesan' => 'Belum ada sesi yang dijadwalkan. Pantau terus, ya.',
                'sesi' => null,
                'berikutnya' => [],
            ]);
        }

        $lain = $this->kueriAktif()->whereKeyNot($sesi->getKey())->limit(4)->get();

        return $this->jawab([
            'ada' => true,
            'pesan' => null,
            'sesi' => $this->lengkap($sesi),
            'berikutnya' => $lain->map(fn ($a) => $this->ringkas($a))->values(),
        ]);
    }

    /** Semua sesi yang sedang dibuka, diringkas. */
    public function daftar(): JsonResponse
    {
        $sesi = $this->kueriAktif()->get();

        return $this->jawab([
            'jumlah' => $sesi->count(),
            'sesi' => $sesi->map(fn ($a) => $this->ringkas($a))->values(),
        ]);
    }

    // ------------------------------------------------------------- kueri

    private function kueriAktif()
    {
        return KategoriLayanan::query()
            ->where('layanan', self::KODE)
            ->where('status', 'active')
            /*
             * Yang sudah LEWAT tidak ikut, walau statusnya masih aktif.
             *
             * Ada perintah terjadwal yang menutupnya tiap pagi, tetapi
             * mengandalkan itu berarti sesi yang selesai kemarin sore masih
             * terpampang sampai 01:10 — dan yang membacanya halaman iklan.
             */
            ->whereRaw('coalesce(selesai, mulai) >= ?', [Carbon::today()->toDateString()])
            ->orderBy('mulai');
    }

    // ---------------------------------------------------------- penyusun

    /**
     * Semua yang dibutuhkan halaman landing untuk satu sesi.
     *
     * @return array<string, mixed>
     */
    private function lengkap(KategoriLayanan $a): array
    {
        $tarif = $a->tarif();

        return array_merge($this->ringkas($a), [
            'pemateri' => [
                'nama' => $a->pemateri,
                'jabatan' => $a->pemateri_jabatan,
                // null, bukan jalur yang salah: halaman landing memakai
                // gambar cadangannya sendiri kalau ini kosong.
                'foto' => $a->alamat_pemateri,
            ],

            'deskripsi' => $a->desc,

            'kegiatan' => $tarif?->daftar_kegiatan ?? [],
            'fasilitas' => $tarif?->daftar_fasilitas ?? [],
            'kontak' => $this->kontak($tarif),

            /*
             * Galeri. Yang dikirim hanya alamat dan keterangannya — tidak ada
             * id, tanggal unggah, atau siapa yang mengunggah. Daftar kolomnya
             * ditulis satu per satu dengan alasan yang sama seperti sesi di
             * atas: menambah kolom pada tabel galeri tidak boleh diam-diam
             * ikut menerbitkannya ke internet.
             */
            'galeri' => GaleriLayanan::untukAngkatan($a)->get()
                ->map(fn ($g) => ['gambar' => $g->alamat, 'keterangan' => $g->keterangan_tampil])
                // Yang berkasnya sudah tidak ada di cakram dibuang di sini,
                // bukan dikirim sebagai null — halaman landing tidak perlu
                // tahu bahwa ada yang hilang.
                ->filter(fn ($g) => $g['gambar'] !== null)
                ->values(),

            'kuota' => [
                'total' => $a->total_kuota === null ? null : (int) $a->total_kuota,
                'sisa' => $a->sisa_kuota === null ? null : (int) $a->sisa_kuota,
                'habis' => (bool) $a->kuota_habis,
                /*
                 * Kalimat jadi, bukan cuma angka. Halaman landing-nya menulis
                 * ini apa adanya, dan merakit "tinggal 7 kursi lagi" di
                 * JavaScript berarti aturan bahasa yang sama ditulis dua kali
                 * di dua tempat yang tidak saling tahu.
                 */
                'kalimat' => $this->kalimatKuota($a),
            ],
        ]);
    }

    /**
     * Yang cukup untuk satu kartu sesi.
     *
     * @return array<string, mixed>
     */
    private function ringkas(KategoriLayanan $a): array
    {
        $mulai = $a->mulai ? Carbon::parse($a->mulai) : null;
        $selesai = $a->selesai ? Carbon::parse($a->selesai) : null;

        $harga = (int) $a->biaya;
        $promo = (int) $a->total_biaya > 0 && (int) $a->total_biaya !== $harga
            ? (int) $a->total_biaya
            : null;

        return [
            'id' => $a->getKey(),
            'judul' => $a->nama,
            'nomor' => $a->nama_ke,

            'tanggal' => RentangTanggal::tulis($mulai, $selesai),
            'tanggal_mulai' => $mulai?->toDateString(),
            'tanggal_selesai' => $selesai?->toDateString(),

            'jam' => $a->jam,
            'platform' => $a->platform,

            /*
             * Waktu mulai lengkap beserta zona, untuk hitung mundur di halaman
             * landing. Dirakit di sini, bukan di sana: menggabung tanggal dan
             * jam di JavaScript tanpa zona waktu membuat hitungannya meleset
             * tujuh jam bagi pengunjung yang perangkatnya bukan WIB.
             */
            'mulai_pada' => $mulai && $a->jam_mulai
                ? $mulai->copy()
                    ->setTimeFromTimeString(Carbon::parse($a->jam_mulai)->format('H:i:s'))
                    ->toIso8601String()
                : null,

            'flyer' => $a->alamat_sampul,

            'harga' => $harga ?: null,
            'harga_tulis' => $harga ? 'Rp ' . number_format($harga, 0, ',', '.') : null,
            'harga_promo' => $promo,
            'harga_promo_tulis' => $promo ? 'Rp ' . number_format($promo, 0, ',', '.') : null,

            // Alamat borang pendaftaran di aplikasi; halaman landing cukup
            // memasangnya ke tombolnya tanpa tahu bagaimana ia dirakit.
            'daftar_url' => route('public.webinareksklusif.daftar', [$a->getKey(), $a->token]),
        ];
    }

    /**
     * Kontak panitia dipecah jadi larik.
     *
     * Kolomnya teks bebas berisi beberapa baris "📞 Nama: nomor" — bentuk yang
     * enak dicetak di WhatsApp tetapi tidak bisa dijadikan tautan. Dipecah di
     * sini supaya halaman landing bisa membuat tombol WhatsApp per orang.
     *
     * @return array<int, array<string, string>>
     */
    private function kontak(?ClinikScopusBiayaPersesi $tarif): array
    {
        $mentah = trim((string) ($tarif?->getRawOriginal('kontak') ?? ''));

        if ($mentah === '') {
            return [];
        }

        $hasil = [];

        foreach (preg_split('/\R+/', $mentah) as $baris) {
            $baris = trim($baris);

            if ($baris === '') {
                continue;
            }

            // "📞 Kumala: 0889-8356-7819" -> nama + nomor.
            if (preg_match('/^\W*(.+?)\s*:\s*([0-9()+\-.\s]{7,})$/u', $baris, $cocok)) {
                $nomor = preg_replace('/\D+/', '', $cocok[2]);

                $hasil[] = [
                    'nama' => trim($cocok[1]),
                    'telp' => trim($cocok[2]),
                    // Nomor Indonesia yang diawali 0 diubah jadi 62 supaya
                    // tautannya bisa dipakai langsung.
                    'wa' => $nomor === '' ? null : preg_replace('/^0/', '62', $nomor),
                ];

                continue;
            }

            $hasil[] = ['nama' => $baris, 'telp' => null, 'wa' => null];
        }

        return $hasil;
    }

    /** Kalimat sisa kuota yang siap ditampilkan. */
    private function kalimatKuota(KategoriLayanan $a): string
    {
        if ($a->total_kuota === null) {
            return 'Pendaftaran dibuka';
        }

        $sisa = (int) $a->sisa_kuota;

        if ($sisa < 1) {
            return 'Kuota sudah penuh';
        }

        // Ambang 10, bukan persentase: yang membuat orang bergegas adalah
        // angka kecil yang bisa dibayangkan, dan "tinggal 18%" bukan itu.
        if ($sisa <= 10) {
            return 'Tinggal ' . $sisa . ' kursi lagi';
        }

        return 'Sisa ' . $sisa . ' kursi dari ' . (int) $a->total_kuota;
    }

    /**
     * @param  array<string, mixed>  $isi
     */
    private function jawab(array $isi): JsonResponse
    {
        return response()
            ->json($isi, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            /*
             * Ditembolok semenit. Halaman landing-nya halaman iklan — satu
             * iklan yang jalan bisa membuat ratusan orang membukanya dalam
             * semenit, dan tiap pembukaan memanggil endpoint ini.
             *
             * Semenit, bukan sejam: begitu admin mengganti flyer atau
             * menutup sesi, ia akan membuka halamannya untuk memeriksa, dan
             * menunggu sejam untuk melihat perubahan sendiri terasa rusak.
             */
            ->header('Cache-Control', 'public, max-age=' . self::DETIK_TEMBOLOK);
    }
}
