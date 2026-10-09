<?php

namespace App\Console\Commands;

use App\Actions\Pendaftaran\KirimSuratPengingat;
use App\KategoriLayanan;
use App\PendaftaranJejak;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Mengingatkan pendaftar yang pembayarannya belum masuk — KELIMA layanan.
 *
 * Sebelum ini satu-satunya pengingat yang ada cuma milik Webinar Eksklusif,
 * dan isinya pun bukan soal pembayaran melainkan "sesinya besok". Empat
 * layanan lain: tidak ada yang mengingatkan sama sekali. Pendaftar yang
 * lupa transfer berdiam di "menunggu bayar" sampai ada panitia yang
 * kebetulan menyapanya satu per satu.
 *
 * Penandanya baris jejak, bukan kolom baru di lima tabel pendaftaran. Satu
 * tabel yang sudah ada melayani kelimanya, dan riwayat pengingatnya ikut
 * terbaca panitia di layar rincian — tempat yang sama dengan riwayat surat
 * lainnya.
 */
class KirimPengingatBayar extends Command
{
    protected $signature = 'pendaftaran:ingatkan-bayar
                            {--layanan= : Satu layanan saja, mis. scopus_camp}
                            {--hari=3 : Minimal berapa hari sejak mendaftar}
                            {--ulang=7 : Jarak minimal dengan pengingat sebelumnya, dalam hari}
                            {--maks=3 : Berapa kali satu pendaftaran boleh diingatkan}
                            {--kering : Hanya melaporkan, tidak mengirim apa pun}';

    protected $description = 'Mengirim pengingat ke pendaftar yang pembayarannya belum masuk';

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');
        $hari = max(0, (int) $this->option('hari'));
        $ulang = max(1, (int) $this->option('ulang'));
        $maks = max(1, (int) $this->option('maks'));

        $hanya = (string) ($this->option('layanan') ?? '');
        $katalog = Pendaftaran::katalog();

        if ($hanya !== '' && ! isset($katalog[$hanya])) {
            $this->error('Layanan "' . $hanya . '" tidak dikenal.');

            return self::FAILURE;
        }

        $batasDaftar = now()->subDays($hari);
        $batasUlang = now()->subDays($ulang);

        $pengirim = new KirimSuratPengingat;
        $terkirim = 0;
        $gagal = 0;

        foreach ($katalog as $layanan => $k) {
            if ($hanya !== '' && $layanan !== $hanya) {
                continue;
            }

            $calon = $this->calon($layanan, $batasDaftar);
            $dikirim = 0;

            foreach ($calon as $baris) {
                $sudah = $this->riwayat($layanan, $baris);

                // Sudah cukup sering diingatkan: berhenti, jangan jadi surat
                // sampah. Yang tidak menanggapi tiga pengingat tidak akan
                // menanggapi yang keempat.
                if ($sudah['jumlah'] >= $maks) {
                    continue;
                }

                if ($sudah['terakhir'] !== null && $sudah['terakhir']->gt($batasUlang)) {
                    continue;
                }

                if ($this->sudahLewat($layanan, $baris)) {
                    continue;
                }

                $dikirim++;

                if ($kering) {
                    $terkirim++;

                    continue;
                }

                $umur = $baris->created_at ? Carbon::parse($baris->created_at)->diffInDays(now()) : 0;

                $hasil = $pengirim->jalankan($layanan, $baris, (int) $umur);

                $hasil['terkirim'] ? $terkirim++ : $gagal++;
            }

            $this->line(sprintf('  %-22s %d dari %d pendaftaran', $k['nama'], $dikirim, $calon->count()));
        }

        $this->info(sprintf('%d pengingat %sterkirim%s.',
            $terkirim,
            $kering ? 'AKAN ' : '',
            $gagal > 0 ? ", {$gagal} gagal (tercatat di jejak)" : ''));

        if ($kering) {
            $this->comment('Jalan kering: tidak ada surat yang dikirim.');
        }

        return self::SUCCESS;
    }

    /**
     * Pendaftaran yang pembayarannya belum masuk pada satu layanan.
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    private function calon(string $layanan, Carbon $batasDaftar)
    {
        $model = Pendaftaran::modelUntuk($layanan);
        $sumber = Pendaftaran::sumber($layanan);

        /*
         * Status "menunggu" diambil dari irisan KEADAAN dengan daftar status
         * layanan itu sendiri, bukan diketik di sini. Kelimanya memakai kata
         * yang berbeda untuk keadaan yang sama — 'diproses', 'pending',
         * 'menunggu verifikasi' — dan daftar yang ditulis tangan pasti
         * ketinggalan begitu ada layanan keenam.
         */
        $menunggu = array_values(array_intersect(
            array_keys($sumber['status'] ?? []),
            Pendaftaran::KEADAAN['menunggu']['nilai'],
        ));

        if ($menunggu === []) {
            return collect();
        }

        $kueri = $model::query()
            ->whereIn('status', $menunggu)
            ->where('created_at', '<=', $batasDaftar);

        /*
         * Yang buktinya SUDAH diunggah tidak diingatkan.
         *
         * Scopus Kafe berdiam di 'menunggu verifikasi' justru SESUDAH
         * membayar — mengiriminya "pembayaran Anda belum kami terima" itu
         * salah, dan pendaftar yang sudah transfer akan mengira uangnya
         * hilang. Aturannya dibuat per-bukti, bukan per-layanan, supaya
         * pendaftar Scopus Camp yang sudah mengunggah pun ikut terlewati.
         */
        $kolomBukti = Pendaftaran::kolomPeran($layanan, 'bukti');

        if ($kolomBukti !== null) {
            $kueri->where(fn ($q) => $q->whereNull($kolomBukti)->orWhere($kolomBukti, ''));
        }

        return $kueri->get();
    }

    /**
     * Berapa kali satu pendaftaran sudah diingatkan, dan kapan terakhir.
     *
     * Yang GAGAL ikut dihitung. Kalau tidak, pendaftaran yang alamat
     * emailnya kosong akan dicoba lagi tiap kali perintah ini jalan dan
     * menumpuk satu baris jejak gagal per hari, selamanya.
     *
     * @return array{jumlah: int, terakhir: Carbon|null}
     */
    private function riwayat(string $layanan, $baris): array
    {
        $jejak = PendaftaranJejak::milik($layanan, (string) $baris->getKey())
            ->whereIn('aksi', [KirimSuratPengingat::AKSI, KirimSuratPengingat::AKSI_GAGAL])
            ->orderByDesc('created_at')
            ->get();

        return [
            'jumlah' => $jejak->count(),
            'terakhir' => $jejak->first()?->created_at,
        ];
    }

    /**
     * Angkatannya sudah berlangsung.
     *
     * Menagih pembayaran untuk kegiatan yang sudah lewat tidak ada gunanya,
     * dan bagi penerimanya terbaca seperti sistem yang tidak tahu apa-apa.
     * Layanan tanpa angkatan (Scopus Kafe, Clinik Scopus) tidak punya tanggal
     * yang bisa dibandingkan, jadi selalu dianggap belum lewat.
     */
    private function sudahLewat(string $layanan, $baris): bool
    {
        if (! Pendaftaran::berangkatan($layanan) || empty($baris->kategori_id)) {
            return false;
        }

        $mulai = KategoriLayanan::find($baris->kategori_id)?->mulai;

        return ! empty($mulai) && Carbon::parse($mulai)->isPast();
    }
}
