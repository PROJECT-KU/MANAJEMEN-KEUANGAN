<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengirim WhatsApp lewat Fonnte.
 *
 * Dipakai untuk pemberitahuan yang SAMA dengan yang dikirim lewat email —
 * tidak semua orang membuka emailnya, dan yang tidak membukanya kehilangan
 * nomor pendaftaran, tagihan, dan batas waktunya sekaligus.
 *
 * TIDAK PERNAH MENGGAGALKAN PEMANGGILNYA. Kelas ini memulangkan hasil,
 * bukan melempar: pendaftaran yang batal hanya karena WhatsApp sedang mati
 * adalah kerugian yang jauh lebih besar daripada satu pesan yang tidak
 * sampai. Pemanggilnya memutuskan sendiri apa yang dilakukan dengan
 * kegagalannya, dan semuanya tetap tercatat di log.
 */
class Fonnte
{
    private const ALAMAT = 'https://api.fonnte.com/send';

    /** Fonnte menolak permintaan yang terlalu lama; 15 detik sudah longgar. */
    private const BATAS_DETIK = 15;

    /** Tersetel dan siap dipakai. */
    public function siap(): bool
    {
        return trim((string) config('services.fonnte.token')) !== '';
    }

    /**
     * @return array{berhasil: bool, pesan: string, id: ?string}
     */
    public function kirim(?string $nomor, string $pesan): array
    {
        if (! $this->siap()) {
            // Bukan galat: di mesin pengembang dan di rangkaian uji
            // tokennya memang tidak ada.
            return ['berhasil' => false, 'pesan' => 'Token Fonnte belum disetel.', 'id' => null];
        }

        /*
         * Nomornya lewat PesananPelanggan::nomorWa(), BUKAN dinormalkan di
         * sini. Fungsi itu yang menolak nomor di bawah sembilan angka, dan
         * layar pendaftaran sudah memakai penolakan yang sama untuk
         * memutuskan kapan menampilkan "nomor ini tidak bisa dihubungi".
         * Dinormalkan sendiri di sini, keduanya akan berselisih.
         */
        $tujuan = \App\Support\PesananPelanggan::nomorWa($nomor);

        if ($tujuan === '') {
            return ['berhasil' => false, 'pesan' => 'Nomornya tidak bisa dihubungi.', 'id' => null];
        }

        try {
            $jawab = Http::withHeaders([
                // TANPA "Bearer" — Fonnte memintanya telanjang.
                'Authorization' => trim((string) config('services.fonnte.token')),
            ])->asForm()->timeout(self::BATAS_DETIK)->post(self::ALAMAT, [
                'target' => $tujuan,
                'message' => $pesan,

                /*
                 * '0' MEMATIKAN penambahan kode negara oleh Fonnte.
                 *
                 * Bawaannya '62', yang mengganti angka 0 di depan. Nomor
                 * kita sudah berawalan 62 lewat nomorWa(), jadi dibiarkan
                 * bawaan pun tidak berubah — tetapi menyebutnya tegas
                 * membuat perilakunya tidak bergantung pada nilai bawaan
                 * yang bisa mereka ubah sewaktu-waktu.
                 */
                'countryCode' => '0',
            ]);
        } catch (\Throwable $e) {
            Log::error('Fonnte tidak bisa dihubungi.', ['galat' => $e->getMessage()]);

            return ['berhasil' => false, 'pesan' => 'Fonnte tidak bisa dihubungi.', 'id' => null];
        }

        $data = $jawab->json() ?? [];

        /*
         * Kuncinya dibaca HURUF BESAR MAUPUN KECIL.
         *
         * Dokumentasi Fonnte sendiri menyebut sebagian balasan gagal
         * memakai "Status" berhuruf besar sementara sisanya "status".
         * Dibaca satu ejaan saja, sebagian kegagalan terbaca sebagai
         * BERHASIL — dan yang tercatat di log adalah kebalikan dari yang
         * terjadi.
         */
        $status = $data['status'] ?? $data['Status'] ?? null;
        $alasan = (string) ($data['reason'] ?? $data['Reason'] ?? '');

        if ($status === true || $status === 'true') {
            $id = $data['id'][0] ?? null;

            return ['berhasil' => true, 'pesan' => (string) ($data['detail'] ?? 'Terkirim.'), 'id' => $id ? (string) $id : null];
        }

        Log::warning('Fonnte menolak pengiriman.', [
            'alasan' => $alasan ?: 'tidak disebutkan',
            'kode' => $jawab->status(),
        ]);

        return [
            'berhasil' => false,
            'pesan' => $alasan ?: 'Fonnte menolak pengiriman.',
            'id' => null,
        ];
    }
}
