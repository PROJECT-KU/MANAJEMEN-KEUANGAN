<?php

namespace App\Console\Commands;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Melepas kembali kursi yang dipesan tetapi tidak jadi dibayar.
 *
 * Kuota dipotong saat orang menekan "Daftar", bukan saat ia membayar — kalau
 * tidak, kursi yang sedang dibayar masih terlihat kosong dan bisa diambil
 * orang lain, dan yang terlanjur membayar tidak punya tempat.
 *
 * Akibatnya, pendaftaran yang ditinggalkan di tengah jalan menahan kursinya
 * selamanya. Perintah ini yang melepaskannya.
 *
 * DOKU juga mengirim pemberitahuan "expired" sendiri, dan itu jalur yang
 * pertama. Perintah ini jaring pengamannya: pemberitahuan bisa tidak sampai —
 * peladen sedang mati, jaringannya putus — dan kursi yang hilang karena itu
 * tidak akan pernah kembali sendiri.
 */
class KedaluwarsakanPendaftaranWebinarEksklusif extends Command
{
    protected $signature = 'webinar-eksklusif:kedaluwarsakan {--kering : Hanya melaporkan, tidak mengubah apa pun}';

    protected $description = 'Melepas kursi dari pendaftaran yang batas bayarnya sudah lewat';

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');

        $lewat = WebinarEksklusifPendaftaran::where('status', 'pending')
            ->whereNotNull('kedaluwarsa_pada')
            ->where('kedaluwarsa_pada', '<', now())
            ->get();

        if ($lewat->isEmpty()) {
            $this->info('Tidak ada pendaftaran yang batas bayarnya lewat.');

            return self::SUCCESS;
        }

        $kursi = 0;

        foreach ($lewat as $p) {
            $this->line(sprintf(
                '  %s — %s, %d peserta, batas %s',
                $p->id_transaksi,
                $p->nama,
                $p->jumlah_pendaftar,
                $p->kedaluwarsa_pada->format('d M Y H:i')
            ));

            $kursi += $p->jumlah_pendaftar;

            if ($kering) {
                continue;
            }

            /*
             * Status dan kuota diubah dalam SATU transaksi. Terpisah, proses
             * yang gagal di tengah meninggalkan pendaftaran yang sudah
             * kedaluwarsa tetapi kursinya belum kembali — dan tidak ada yang
             * akan mengulangnya karena statusnya sudah bukan 'pending'.
             */
            DB::transaction(function () use ($p) {
                $p->forceFill(['status' => 'expired', 'bayar_status' => 'kedaluwarsa'])->save();

                $sesi = KategoriLayanan::whereKey($p->kategori_id)->lockForUpdate()->first();

                if ($sesi === null || $sesi->total_kuota === null) {
                    return;
                }

                $sesi->forceFill([
                    // Tidak boleh melebihi totalnya: pengembalian ganda akan
                    // membuka kursi yang sebenarnya tidak ada.
                    'sisa_kuota' => (string) min(
                        (int) $sesi->total_kuota,
                        (int) $sesi->sisa_kuota + $p->jumlah_pendaftar
                    ),
                ])->save();
            });
        }

        $this->info(sprintf(
            '%d pendaftaran %sdikedaluwarsakan, %d kursi %sdilepas.',
            $lewat->count(),
            $kering ? 'AKAN ' : '',
            $kursi,
            $kering ? 'AKAN ' : ''
        ));

        return self::SUCCESS;
    }
}
