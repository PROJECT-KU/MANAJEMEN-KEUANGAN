<?php

namespace App\Actions\Pendaftaran;


use App\KategoriLayanan;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\DB;

/**
 * Mengubah status satu pendaftaran, layanan apa pun.
 *
 * Dipindahkan ke sini dari lima pengendali terpisah saat layar pendaftaran
 * per layanan dibuang. Yang paling mudah hilang dalam pemindahan semacam itu
 * BUKAN tampilannya melainkan efek sampingnya:
 *
 *   Scopus Camp & Bibliometrik  status 'Pendaftaran Diterima' dan
 *                               'Pendaftaran Reschedule' MENGIRIM EMAIL
 *                               ke pendaftarnya
 *   Scopus Kafe                 status 'pembayaran diterima' mengirim email
 *   Webinar Eksklusif           kursinya diambil/dikembalikan dari kuota
 *   Clinik Scopus               tidak ada efek samping
 *
 * Dihilangkan, pelanggan berhenti diberi tahu dan tidak ada galat apa pun
 * yang memberitahukannya.
 */
class UbahStatusPendaftaran
{
    /**
     * @return array{berhasil:bool, pesan:string, surat:bool}
     */
    public function jalankan(string $layanan, string $id, string $status, ?string $olehSiapa = null): array
    {
        $sumber = Pendaftaran::sumber($layanan);

        if ($sumber === null) {
            return ['berhasil' => false, 'pesan' => 'Layanan itu tidak dikenali.', 'surat' => false];
        }

        if (! array_key_exists($status, Pendaftaran::pilihanStatus($layanan))) {
            return [
                'berhasil' => false,
                'surat' => false,
                'pesan' => 'Status itu tidak berlaku untuk ' . $sumber['nama'] . '.',
            ];
        }

        $pendaftaran = Pendaftaran::temukan($layanan, $id);

        if ($pendaftaran === null) {
            return ['berhasil' => false, 'pesan' => 'Pendaftarannya tidak ditemukan.', 'surat' => false];
        }

        $statusLama = (string) $pendaftaran->status;

        if ($statusLama === $status) {
            return [
                'berhasil' => false,
                'surat' => false,
                'pesan' => 'Statusnya memang sudah "' . Pendaftaran::pilihanStatus($layanan)[$status] . '".',
            ];
        }

        DB::transaction(function () use ($layanan, $pendaftaran, $status, $statusLama, $olehSiapa) {
            /*
             * Kuota HANYA disentuh untuk Webinar Eksklusif.
             *
             * Bukan kelalaian: keempat layanan lain memang tidak pernah
             * memindahkan kuota saat statusnya berubah, bahkan saat jadi
             * dibatalkan. Menyeragamkannya dari sini akan menggeser angka
             * `sisa_kuota` pada 48 angkatan yang sudah ada — perubahan yang
             * tidak diminta dan tidak bisa dibedakan dari kekeliruan nanti.
             * Kuotanya tetap berpindah saat barisnya DIHAPUS, seperti dulu.
             */
            if ($layanan === 'webinar_eksklusif') {
                $this->geserKuotaWebinar($pendaftaran, $statusLama, $status);
            }

            $isi = ['status' => $status];

            /*
             * Jejak SIAPA yang mengubah: kalau belakangan ada selisih uang,
             * yang bisa ditanya orangnya, bukan sistemnya.
             *
             * Ditulis ke tabel jejak tersendiri, BUKAN ke kolom catatan.
             * Dulu ia ditempelkan ke `note` — kolom yang sama yang dipakai
             * panitia menulis catatannya sendiri — dengan tiga akibat:
             * catatan panitia bercampur jejak sistem dan bisa terhapus saat
             * disunting, kolomnya memanjang tanpa batas, dan dua layanan yang
             * TIDAK punya kolom `note` (Scopus Kafe, Clinik Scopus) tidak
             * pernah terekam sama sekali.
             *
             * Jejaknya dicatat untuk kelima layanan tanpa kecuali, sebab
             * tabelnya tidak bergantung pada kolom di tabel pendaftarannya.
             */
            \App\PendaftaranJejak::create([
                'layanan' => $layanan,
                'pendaftaran_id' => (string) $pendaftaran->getKey(),
                'aksi' => 'status',
                'dari' => $statusLama,
                'ke' => $status,
                // Dibaca dari sesi, bukan diminta lewat argumen: pemanggilnya
                // sudah menyerahkan NAMANYA, dan menuntut id lagi berarti dua
                // sumber untuk satu hal. Null saat dijalankan dari CLI, dan
                // nama tetap tersimpan.
                'oleh_id' => \Illuminate\Support\Facades\Auth::id(),
                'oleh_nama' => $olehSiapa,
            ]);

            if ($layanan === 'webinar_eksklusif' && $status === 'paid') {
                $isi['bayar_status'] = 'manual';
                $isi['bayar_pada'] = now();
            }

            $pendaftaran->forceFill($isi)->save();
        });

        /*
         * Tagihannya ikut ditutup di gerbang pembayaran.
         *
         * SESUDAH transaksinya selesai, bukan di dalamnya: ini panggilan
         * jaringan ke peladen orang lain, dan menahannya di dalam transaksi
         * berarti baris angkatannya terkunci selama DOKU berpikir.
         *
         * Kegagalannya TIDAK membatalkan perpindahan status. Panitia sudah
         * memutuskan pendaftaran ini batal; menolak keputusannya karena
         * gerbang pembayaran sedang tidak menjawab hanya memindahkan masalah
         * ke orang yang paling tidak bisa memperbaikinya. Yang gagal tercatat
         * di log, dan penjagaan di penerima pemberitahuan tetap menangkap
         * uang yang telanjur masuk.
         */
        $this->tutupTagihanGerbang($layanan, $pendaftaran->refresh(), $statusLama, $status, $olehSiapa);

        /*
         * Pengirimnya PINDAH ke KirimSuratStatus.
         *
         * Tiga pemanggil sekarang: perpindahan status, tombol "kirim ulang"
         * di layar rincian, dan perintah pengingat terjadwal. Ketiganya harus
         * mengirim surat yang sama persis dan mencatatnya dengan cara yang
         * sama — dan yang terakhir itu baru ada sejak hasil kirimnya dicatat
         * di jejak, bukan cuma di log peladen.
         */
        $hasilSurat = (new KirimSuratStatus)->jalankan($layanan, $pendaftaran->refresh(), $olehSiapa);
        $surat = $hasilSurat['terkirim'];

        return [
            'berhasil' => true,
            'surat' => $surat,
            'pesan' => 'Status ' . ($pendaftaran->id_transaksi ?? $pendaftaran->id_pemesanan ?? '')
                . ' jadi "' . Pendaftaran::pilihanStatus($layanan)[$status] . '".',
        ];
    }

    /**
     * Menutup tagihan di gerbang pembayaran saat pendaftarannya berhenti aktif.
     *
     * Tanpa ini, membatalkan di layar panitia tidak sampai ke DOKU sama
     * sekali: tagihannya hidup sampai tenggatnya habis, dan pendaftar yang
     * tabnya masih terbuka bisa tetap membayar kursi yang sudah dikembalikan
     * ke kuota — lalu diberikan kepada orang lain.
     *
     * Hanya layanan yang PUNYA kolom tagihan: Webinar Eksklusif dan Scopus
     * Camp. Ketiga sisanya belum memakai gerbang pembayaran sama sekali.
     *
     * Status "tidak aktif"-nya berbeda per layanan karena kosakatanya memang
     * berbeda — dan itu sebabnya daftarnya ditulis per layanan di sini, bukan
     * disamaratakan. 'Pendaftaran Refund' TIDAK ada di dalamnya: uangnya
     * sudah masuk lalu dikembalikan, jadi tidak ada tagihan hidup untuk
     * ditutup, dan DOKU memang menolak membatalkan pesanan yang sudah
     * dibayar.
     */
    private const TIDAK_AKTIF = [
        'webinar_eksklusif' => ['cancel', 'expired'],
        'scopus_camp' => ['Pendaftaran Dibatalkan', 'Pendaftaran Ditolak'],
    ];

    private function tutupTagihanGerbang(
        string $layanan,
        $pendaftaran,
        string $statusLama,
        string $statusBaru,
        ?string $olehSiapa
    ): void {
        $tidakAktif = self::TIDAK_AKTIF[$layanan] ?? null;

        if ($tidakAktif === null) {
            return;
        }

        // Hanya perpindahan dari AKTIF ke tidak aktif. Yang sudah batal lalu
        // ditandai kedaluwarsa tidak punya tagihan hidup untuk ditutup.
        if (in_array($statusLama, $tidakAktif, true) || ! in_array($statusBaru, $tidakAktif, true)) {
            return;
        }

        if ($pendaftaran->cara_bayar !== 'doku') {
            return;   // peninggalan transfer manual; tidak punya tagihan di sana
        }

        $hasil = app(\App\Services\Doku::class)->batalkanTagihan(
            $pendaftaran->id_transaksi,
            $pendaftaran->bayar_permintaan_id,
            'Dibatalkan panitia' . ($olehSiapa ? ' (' . $olehSiapa . ')' : '')
        );

        \App\PendaftaranJejak::create([
            'layanan' => $layanan,
            'pendaftaran_id' => (string) $pendaftaran->getKey(),
            'aksi' => $hasil['berhasil'] ? 'tagihan-tutup' : 'tagihan-gagal',
            'dari' => $statusLama,
            'ke' => $statusBaru,

            /*
             * Hasilnya DICATAT, berhasil maupun gagal.
             *
             * Yang gagal justru yang paling perlu terlihat: tagihannya masih
             * hidup, dan panitia yang mengira pembatalannya sudah menutup
             * segalanya tidak akan memeriksa apa pun lagi. Kanal kartu dan
             * e-wallet memang TIDAK BISA dibatalkan — jawaban gagal untuk
             * keduanya normal, dan kalimat ini yang membedakannya dari
             * gangguan sungguhan.
             */
            'ringkasan' => $hasil['berhasil']
                ? 'Tagihan di DOKU ditutup; tidak bisa dibayar lagi'
                : 'Tagihan di DOKU TIDAK tertutup — ' . $hasil['pesan']
                    . ' Pembayaran yang telanjur masuk tetap ditangkap sistem, '
                    . 'tetapi tagihannya masih bisa dibuka pendaftarnya.',

            'oleh_id' => \Illuminate\Support\Facades\Auth::id(),
            'oleh_nama' => $olehSiapa,
        ]);
    }

    /**
     * Kursi webinar diambil atau dikembalikan mengikuti perpindahan statusnya.
     *
     * Kursinya sudah dipotong saat orangnya menekan "Daftar", jadi yang
     * dikerjakan di sini hanya selisihnya: dikembalikan saat pendaftarannya
     * berhenti aktif, dan diambil lagi saat ia aktif kembali. Memotongnya
     * sekali lagi tanpa memeriksa status lamanya akan menghilangkan kursi
     * yang sebenarnya masih ada.
     */
    private function geserKuotaWebinar($pendaftaran, string $statusLama, string $statusBaru): void
    {
        $tidakAktif = ['cancel', 'expired'];

        $duluAktif = ! in_array($statusLama, $tidakAktif, true);
        $kiniAktif = ! in_array($statusBaru, $tidakAktif, true);

        if ($duluAktif === $kiniAktif) {
            return;
        }

        $angkatan = KategoriLayanan::whereKey($pendaftaran->kategori_id)->lockForUpdate()->first();

        if ($angkatan === null || $angkatan->total_kuota === null) {
            return;
        }

        $jumlah = (int) $pendaftaran->jumlah_pendaftar;
        $sisa = (int) $angkatan->sisa_kuota;

        $angkatan->forceFill([
            'sisa_kuota' => (string) ($kiniAktif
                // Aktif kembali: kursinya diambil lagi, tidak boleh di bawah nol.
                ? max(0, $sisa - $jumlah)
                // Berhenti aktif: kursinya dikembalikan, tidak boleh di atas totalnya.
                : min((int) $angkatan->total_kuota, $sisa + $jumlah)),
        ])->save();
    }

}
