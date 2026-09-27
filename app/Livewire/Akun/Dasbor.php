<?php

namespace App\Livewire\Akun;

use App\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Dasbor akun.
 *
 * Menggantikan DashboardController yang dulu menyiapkan 30-an variabel lalu
 * memilih salah satu dari dua tampilan (ponsel / bukan ponsel) memakai
 * Jenssegers\Agent. Pemisahan itu membuat tablet ikut memakai tampilan
 * desktop dan dua berkas tampilan lambat laun berbeda isi. Di sini hanya ada
 * satu tampilan yang menyesuaikan diri lewat CSS, dan datanya dihitung saat
 * dipakai saja lewat properti terhitung.
 */
class Dasbor extends Component
{
    /** Kapan angka di layar ini terakhir diambil. */
    public string $dimuatPada = '';

    public function mount(): void
    {
        $this->dimuatPada = now()->format('H:i');
    }

    /** Ambil ulang seluruh angka tanpa memuat ulang halaman. */
    public function segarkan(): void
    {
        // Properti terhitung disimpan per permintaan, jadi cukup membuang
        // simpanannya; permintaan ini akan menghitung ulang semuanya.
        unset($this->gaji, $this->tim, $this->tugas, $this->pengajuan,
            $this->artikel, $this->kehadiran, $this->cuti, $this->presensiHariIni,
            $this->ringkasan);

        $this->dimuatPada = now()->format('H:i');
    }

    /**
     * Perusahaan pengguna, atau null bila belum diisi.
     *
     * Penting: where('company', null) diterjemahkan Laravel menjadi
     * "company IS NULL", sehingga manajer yang perusahaannya belum diisi akan
     * melihat SELURUH akun yang juga kosong perusahaannya. Semua pemakaian
     * perusahaan harus lewat sini.
     */
    private function perusahaan(): ?string
    {
        $nama = trim((string) $this->pengguna()->company);

        return $nama === '' ? null : $nama;
    }

    // ------------------------------------------------------------- pengguna

    #[Computed]
    public function pengguna(): User
    {
        return auth()->user();
    }

    #[Computed]
    public function pengelolaTim(): bool
    {
        return in_array($this->pengguna()->level, ['manager', 'ceo'], true);
    }

    #[Computed]
    public function sapaan(): string
    {
        $jam = (int) now()->format('H');

        return match (true) {
            $jam < 11 => 'Selamat pagi',
            $jam < 15 => 'Selamat siang',
            $jam < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    // ---------------------------------------------------------------- gaji

    /** @return array<string,mixed> */
    #[Computed]
    public function gaji(): array
    {
        $pengguna = $this->pengguna();
        $tahun = now()->year;

        $kueri = DB::table('gaji')
            ->selectRaw('MONTH(gaji.tanggal) as bulan, SUM(gaji.total) as total')
            ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
            ->where('gaji.status', 'terbayar')
            ->whereYear('gaji.tanggal', $tahun);

        if ($this->pengelolaTim() && $this->perusahaan() !== null) {
            $kueri->where('users.company', $this->perusahaan());
        } else {
            $kueri->where('gaji.user_id', $pengguna->getKey());
        }

        $baris = $kueri->groupBy('bulan')->orderBy('bulan')->get();

        $perBulan = array_fill(1, 12, 0.0);
        $total = 0.0;

        foreach ($baris as $b) {
            $perBulan[(int) $b->bulan] = (float) $b->total;
            $total += (float) $b->total;
        }

        $bulanTerisi = count(array_filter($perBulan, fn ($n) => $n > 0));

        return [
            'tahun' => $tahun,
            'per_bulan' => $perBulan,
            'total' => $total,
            'tertinggi' => max($perBulan) ?: 0.0,
            // Rata-rata dihitung dari bulan yang benar-benar ada gajinya;
            // membagi dengan 12 membuat angkanya menyesatkan di awal tahun.
            'rata' => $bulanTerisi > 0 ? $total / $bulanTerisi : 0.0,
            'bulan_terisi' => $bulanTerisi,
        ];
    }

    /**
     * Empat angka pembuka.
     *
     * Kartu uang masuk/keluar dihapus bersama fiturnya (27 September 2026),
     * jadi pembukanya kini bicara soal pekerjaan: gaji, kehadiran, cuti, dan
     * tugas — atau jumlah karyawan bagi manajer.
     *
     * @return array<int,array<string,mixed>>
     */
    #[Computed]
    public function ringkasan(): array
    {
        $gaji = $this->gaji();
        $hadir = $this->kehadiran();
        $cuti = $this->cuti();

        $kartu = [];

        $penuh = 'Rp ' . number_format($gaji['total'], 0, ',', '.');

        $kartu[] = [
            'warna' => 'biru',
            'ikon' => 'fa-money-check-alt',
            'label' => ($this->pengelolaTim() ? 'Gaji terbayar' : 'Gaji diterima') . ' · ' . $gaji['tahun'],
            // Angka rupiah besar tidak muat di kartu selebar seperempat layar;
            // yang tampil bentuk ringkasnya, nilai penuhnya ada di tooltip dan
            // di halaman Gaji.
            'nilai' => $gaji['total'] >= 10_000_000 ? 'Rp ' . $this->singkat($gaji['total']) : $penuh,
            'nilai_penuh' => $penuh,
            'catatan' => $gaji['bulan_terisi'] > 0
                ? 'Rata-rata Rp ' . number_format($gaji['rata'], 0, ',', '.') . ' / bulan'
                : 'Belum ada gaji terbayar tahun ini.',
        ];

        if ($this->pengelolaTim()) {
            $tim = $this->tim();

            $kartu[] = [
                'warna' => 'ungu',
                'ikon' => 'fa-users',
                'label' => 'Karyawan aktif',
                'nilai' => (string) $tim['aktif'],
                'catatan' => $tim['total'] . ' akun terdaftar · ' . $tim['nonaktif'] . ' nonaktif',
            ];
        } else {
            $kartu[] = [
                'warna' => 'ungu',
                'ikon' => 'fa-briefcase',
                'label' => 'Masa kerja',
                'nilai' => $hadir['masa_kerja'],
                'catatan' => 'Sejak ' . ($this->pengguna()->created_at
                    ? $this->pengguna()->created_at->locale('id')->translatedFormat('d F Y')
                    : '-'),
            ];
        }

        $kartu[] = [
            'warna' => 'hijau',
            'ikon' => 'fa-fingerprint',
            'label' => 'Hadir bulan ini',
            'nilai' => $hadir['hadir_bulan_ini'] . ' hari',
            'catatan' => $hadir['izin_bulan_ini'] . ' izin · ' . $hadir['lembur_bulan_ini'] . ' lembur',
        ];

        $kartu[] = [
            'warna' => 'kuning',
            'ikon' => 'fa-umbrella-beach',
            'label' => 'Sisa cuti',
            'nilai' => $cuti['boleh'] ? $cuti['sisa'] . ' hari' : 'Belum berhak',
            'catatan' => $cuti['boleh']
                ? $cuti['terpakai'] . ' dari ' . $cuti['jatah'] . ' hari terpakai'
                : 'Terbuka setelah 1 tahun masa kerja.',
        ];

        return $kartu;
    }

    /** Rupiah ringkas untuk label grafik: 1.250.000 -> 1,3 jt. */
    public function singkat(float $nilai): string
    {
        return match (true) {
            $nilai >= 1_000_000_000 => rtrim(rtrim(number_format($nilai / 1_000_000_000, 1, ',', '.'), '0'), ',') . ' M',
            $nilai >= 1_000_000 => rtrim(rtrim(number_format($nilai / 1_000_000, 1, ',', '.'), '0'), ',') . ' jt',
            $nilai >= 1_000 => rtrim(rtrim(number_format($nilai / 1_000, 0, ',', '.'), '0'), ',') . ' rb',
            default => number_format($nilai, 0, ',', '.'),
        };
    }

    // ------------------------------------------------------------ kehadiran

    #[Computed]
    public function presensiHariIni()
    {
        return DB::table('presensi')
            ->where('user_id', $this->pengguna()->getKey())
            ->whereDate('created_at', now()->toDateString())
            ->first();
    }

    /** @return array<string,mixed> */
    #[Computed]
    public function kehadiran(): array
    {
        $pengguna = $this->pengguna();
        $awalBulan = now()->startOfMonth();

        $baris = DB::table('presensi')
            ->selectRaw('COUNT(*) as jumlah, SUM(izin) as izin, SUM(lembur) as lembur')
            ->where('user_id', $pengguna->getKey())
            ->whereBetween('created_at', [$awalBulan, now()->endOfMonth()])
            ->first();

        $masaKerja = $pengguna->created_at ? $pengguna->created_at->diff(now()) : null;

        return [
            'hadir_bulan_ini' => (int) ($baris->jumlah ?? 0),
            'izin_bulan_ini' => (int) ($baris->izin ?? 0),
            'lembur_bulan_ini' => (int) ($baris->lembur ?? 0),
            'hari_kerja_lewat' => now()->day,
            'masa_kerja' => $masaKerja
                ? trim(($masaKerja->y ? $masaKerja->y . ' tahun ' : '') . ($masaKerja->m ? $masaKerja->m . ' bulan ' : '') . $masaKerja->d . ' hari')
                : '-',
        ];
    }

    /** @return array<string,mixed> */
    #[Computed]
    public function cuti(): array
    {
        $pengguna = $this->pengguna();
        $setahun = $pengguna->created_at && $pengguna->created_at->diffInYears(now()) >= 1;

        $terpakai = (int) DB::table('cuti')
            ->where('user_id', $pengguna->getKey())
            ->whereIn('status', ['disetujui', 'approved'])
            ->whereYear('tanggal_mulai_cuti', now()->year)
            ->sum('total_hari_cuti');

        $jatah = $setahun ? 12 : 0;

        return [
            'boleh' => $setahun,
            'jatah' => $jatah,
            'terpakai' => $terpakai,
            'sisa' => max(0, $jatah - $terpakai),
            'menunggu' => DB::table('cuti')
                ->where('user_id', $pengguna->getKey())
                ->whereIn('status', ['ajukan', 'pending', 'menunggu'])
                ->count(),
        ];
    }

    // ---------------------------------------------------------------- tim

    /** @return array<string,mixed> */
    #[Computed]
    public function tim(): array
    {
        $perusahaan = $this->perusahaan();

        if ($perusahaan === null) {
            // Tanpa penjaga ini, manajer yang perusahaannya belum diisi akan
            // melihat semua akun yang perusahaannya juga kosong.
            return [
                'total' => 0, 'aktif' => 0, 'nonaktif' => 0, 'belum_verifikasi' => 0,
                'terbaru' => collect(), 'tanpa_perusahaan' => true,
            ];
        }

        $dasar = DB::table('users')->where('company', $perusahaan);

        return [
            'total' => (clone $dasar)->count(),
            'aktif' => (clone $dasar)->where('status', 'active')->count(),
            'nonaktif' => (clone $dasar)->where('status', 'nonactive')->count(),
            'belum_verifikasi' => (clone $dasar)->whereNull('email_verified_at')->count(),
            // Dulu daftar ini mengambil seluruh pengguna tanpa memandang
            // perusahaan, sehingga manajer melihat akun perusahaan lain.
            'terbaru' => (clone $dasar)
                ->whereIn('level', ['staff', 'karyawan', 'trainer'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'full_name', 'level', 'gambar', 'created_at', 'status']),
            'tanpa_perusahaan' => false,
        ];
    }

    // ------------------------------------------------------- tugas & kabar

    #[Computed]
    public function tugas()
    {
        $id = $this->pengguna()->getKey();

        return DB::table('todolist')
            ->where(function ($q) use ($id) {
                $q->where('user_id', $id)->orWhere('user_id_kedua', $id);
            })
            ->whereNotIn('status', ['Selesai', 'selesai', 'Done'])
            ->orderByRaw('CASE WHEN tanggal_deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('tanggal_deadline')
            ->limit(4)
            ->get(['id', 'id_task', 'judul_task', 'status', 'prioritas_task', 'tanggal_deadline']);
    }

    #[Computed]
    public function pengajuan()
    {
        $pengguna = $this->pengguna();

        $kueri = DB::table('perjalanan_dinas')
            ->select(
                'perjalanan_dinas.id',
                'perjalanan_dinas.token',
                'perjalanan_dinas.id_transaksi',
                'perjalanan_dinas.status',
                'perjalanan_dinas.tempat',
                'perjalanan_dinas.tanggal_mulai',
                'users.full_name'
            )
            ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
            ->orderByDesc('perjalanan_dinas.created_at')
            ->limit(5);

        if ($this->pengelolaTim() && $this->perusahaan() !== null) {
            $kueri->where('perjalanan_dinas.status', 'ajukan')
                ->where('users.company', $this->perusahaan());
        } else {
            $kueri->where('perjalanan_dinas.user_id', $pengguna->getKey())
                ->whereIn('perjalanan_dinas.status', ['draft', 'ajukan']);
        }

        return $kueri->get();
    }

    #[Computed]
    public function artikel()
    {
        return DB::table('artikel')
            ->select('artikel.id', 'artikel.token', 'artikel.judul', 'artikel.gambar_depan', 'artikel.created_at', 'categories_artikel.kategori')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            // Draf tidak boleh ikut tampil di dasbor semua orang.
            ->whereIn('artikel.status', ['publish', 'published', 'terbit'])
            ->orderByDesc('artikel.created_at')
            ->limit(4)
            ->get();
    }

    public function render()
    {
        return view('livewire.akun.dasbor');
    }
}
