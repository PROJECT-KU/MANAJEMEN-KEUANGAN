<?php

namespace App\Console\Commands;

use App\Mail\VerifikasiEmailMail;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Pengingat verifikasi email untuk akun yang mendaftar tetapi tautannya
 * belum pernah diklik.
 *
 * Jendela waktunya sempit dan bergerak (akun yang dibuat 47–49 jam lalu),
 * sehingga tiap akun hanya kena sekali tanpa perlu kolom penanda tambahan.
 */
class PengingatVerifikasi extends Command
{
    protected $signature = 'verifikasi:ingatkan {--jam=48 : Usia akun yang diingatkan, dalam jam}
                                                {--kering : Hanya menghitung, tidak mengirim surat}';

    protected $description = 'Kirim pengingat verifikasi email untuk akun yang belum terverifikasi';

    /** Lama tautan berlaku (jam). */
    private const JAM_BERLAKU = 48;

    public function handle(): int
    {
        $jam = (int) ($this->option('jam') ?? 48);
        $kering = (bool) $this->option('kering');

        $akun = User::whereNull('email_verified_at')
            ->whereBetween('created_at', [now()->subHours($jam + 1), now()->subHours($jam - 1)])
            ->get();

        if ($akun->isEmpty()) {
            $this->info('Tidak ada akun yang perlu diingatkan.');

            return self::SUCCESS;
        }

        $terkirim = 0;

        foreach ($akun as $pengguna) {
            if ($kering) {
                $this->line('(kering) ' . $pengguna->email);

                continue;
            }

            $tautan = URL::temporarySignedRoute('verification.verify', now()->addHours(self::JAM_BERLAKU), [
                'id' => $pengguna->getKey(),
                'hash' => sha1($pengguna->getEmailForVerification()),
            ]);

            try {
                Mail::to($pengguna->email)->send(new VerifikasiEmailMail($pengguna, $tautan, self::JAM_BERLAKU));
                $terkirim++;
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim pengingat verifikasi ke ' . $pengguna->email . ': ' . $e->getMessage());
            }
        }

        $this->info($kering
            ? $akun->count() . ' akun akan diingatkan.'
            : $terkirim . ' dari ' . $akun->count() . ' pengingat terkirim.');

        return self::SUCCESS;
    }
}
