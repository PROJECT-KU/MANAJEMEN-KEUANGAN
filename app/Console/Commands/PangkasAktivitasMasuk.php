<?php

namespace App\Console\Commands;

use App\AktivitasMasuk;
use Illuminate\Console\Command;

/**
 * Tabel jejak masuk bertambah pada setiap percobaan dan tidak pernah
 * menyusut. Perintah ini membuang catatan yang sudah lewat masa simpan.
 */
class PangkasAktivitasMasuk extends Command
{
    protected $signature = 'aktivitas:pangkas {--hari= : Masa simpan dalam hari}';

    protected $description = 'Hapus catatan aktivitas masuk yang sudah melewati masa simpan';

    public function handle(): int
    {
        // Pakai ?? bukan ?: -- '0' bernilai falsy, sehingga --hari=0 akan
        // diam-diam jatuh ke nilai bawaan alih-alih ditolak.
        $hari = (int) ($this->option('hari') ?? config('auth.simpan_aktivitas_masuk_hari', 90));

        if ($hari < 1) {
            $this->error('Masa simpan minimal 1 hari.');

            return self::FAILURE;
        }

        $batas = now()->subDays($hari);
        $jumlah = AktivitasMasuk::where('created_at', '<', $batas)->delete();

        $this->info("{$jumlah} catatan aktivitas masuk sebelum {$batas->format('d/m/Y')} dihapus.");

        return self::SUCCESS;
    }
}
