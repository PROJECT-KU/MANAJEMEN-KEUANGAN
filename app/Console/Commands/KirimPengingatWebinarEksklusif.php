<?php

namespace App\Console\Commands;

use App\KategoriLayanan;
use App\Mail\WebinarEksklusifPengingatMail;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mengirim pengingat sehari sebelum sesinya berlangsung.
 *
 * Jarak antara mendaftar dan hari-H bisa berminggu-minggu — pendaftaran sesi
 * 21 Oktober sudah dibuka awal Oktober. Tanpa pengingat, yang membayar jauh
 * hari lupa, dan kursinya terpakai tanpa ada orangnya.
 *
 * Dikirim SEKALI per pendaftaran, ditandai di kolom `pengingat_pada`.
 * Perintah ini jalan tiap hari dan bisa dipanggil ulang tangan; tanpa
 * penanda itu, peserta yang sama menerima surat tiap kali ia jalan.
 */
class KirimPengingatWebinarEksklusif extends Command
{
    protected $signature = 'webinar-eksklusif:ingatkan
                            {--kering : Hanya melaporkan, tidak mengirim apa pun}';

    protected $description = 'Mengirim pengingat ke peserta yang sesinya berlangsung besok';

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');

        $besok = now()->addDay()->toDateString();

        $sesi = KategoriLayanan::where('layanan', 'webinar_eksklusif')
            ->whereDate('mulai', $besok)
            ->get();

        if ($sesi->isEmpty()) {
            $this->info('Tidak ada sesi yang berlangsung besok (' . $besok . ').');

            return self::SUCCESS;
        }

        $terkirim = 0;
        $gagal = 0;

        foreach ($sesi as $s) {
            /*
             * Yang batal dan yang kedaluwarsa TIDAK diingatkan: kursinya sudah
             * dilepas, dan mengirimi mereka pengingat berarti menjanjikan
             * tempat yang sudah tidak ada.
             */
            $peserta = WebinarEksklusifPendaftaran::where('kategori_id', $s->getKey())
                ->whereIn('status', ['pending', 'paid'])
                ->whereNull('pengingat_pada')
                ->with('pesertaLain')
                ->get();

            $this->line(sprintf('  %s — %d peserta belum diingatkan', $s->nama, $peserta->count()));

            foreach ($peserta as $p) {
                if ($kering) {
                    $terkirim++;

                    continue;
                }

                try {
                    // Peserta tambahan yang beremail ikut diingatkan — mereka
                    // yang hadir, bukan hanya yang mendaftarkan.
                    $penerima = array_values(array_unique(array_filter(array_merge(
                        [$p->email],
                        $p->pesertaLain->pluck('email')->all()
                    ))));

                    Mail::to($penerima)->send(new WebinarEksklusifPengingatMail($p, $s));

                    /*
                     * Ditandai SESUDAH terkirim. Ditandai lebih dulu, surat
                     * yang gagal terkirim tetap terhitung terkirim dan tidak
                     * akan pernah dicoba lagi.
                     */
                    $p->forceFill(['pengingat_pada' => now()])->save();
                    $terkirim++;
                } catch (\Throwable $e) {
                    $gagal++;

                    Log::error('Pengingat Webinar Eksklusif gagal dikirim', [
                        'pendaftaran' => $p->getKey(),
                        'email' => $p->email,
                        'pesan' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info(sprintf('%d pengingat %sterkirim%s.',
            $terkirim,
            $kering ? 'AKAN ' : '',
            $gagal > 0 ? ", {$gagal} gagal (lihat log)" : ''));

        if ($kering) {
            $this->comment('Jalan kering: tidak ada surat yang dikirim.');
        }

        return self::SUCCESS;
    }
}
