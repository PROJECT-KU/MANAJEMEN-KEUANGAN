<?php

namespace App\Livewire\Akun;

use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
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
    /** 'bulan' | 'tahun' | 'semua' — rentang untuk kartu uang. */
    #[Url(as: 'rentang', keep: false)]
    public string $rentang = 'bulan';

    public function gantiRentang(string $rentang): void
    {
        $this->rentang = in_array($rentang, ['bulan', 'tahun', 'semua'], true) ? $rentang : 'bulan';
    }

    // ------------------------------------------------------------- pengguna

    #[Computed]
    public function pengguna(): User
    {
        return auth()->user();
    }

    #[Computed]
    public function pengelolaKeuangan(): bool
    {
        return in_array($this->pengguna()->level, ['manager', 'staff', 'ceo'], true);
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

    // ------------------------------------------------------------- keuangan

    /**
     * Batas rentang yang sedang dipilih beserta rentang pembandingnya.
     *
     * @return array{mulai:?Carbon,selesai:?Carbon,banding_mulai:?Carbon,banding_selesai:?Carbon,label:string,label_banding:string}
     */
    private function batasRentang(): array
    {
        $kini = now();

        return match ($this->rentang) {
            'tahun' => [
                'mulai' => $kini->copy()->startOfYear(),
                'selesai' => $kini->copy()->endOfYear(),
                'banding_mulai' => $kini->copy()->subYearNoOverflow()->startOfYear(),
                'banding_selesai' => $kini->copy()->subYearNoOverflow()->endOfYear(),
                'label' => 'Tahun ' . $kini->year,
                'label_banding' => 'tahun lalu',
            ],
            'semua' => [
                'mulai' => null,
                'selesai' => null,
                'banding_mulai' => null,
                'banding_selesai' => null,
                'label' => 'Sejak awal',
                'label_banding' => '',
            ],
            default => [
                'mulai' => $kini->copy()->startOfMonth(),
                'selesai' => $kini->copy()->endOfMonth(),
                // subMonthNoOverflow mencegah 31 Maret melompat ke 3 Maret,
                // dan memakai rentang tanggal membuat Januari tetap
                // dibandingkan dengan Desember tahun sebelumnya. Kode lama
                // memakai whereYear tahun ini + whereMonth bulan lalu,
                // sehingga tiap Januari pembandingnya selalu kosong.
                'banding_mulai' => $kini->copy()->subMonthNoOverflow()->startOfMonth(),
                'banding_selesai' => $kini->copy()->subMonthNoOverflow()->endOfMonth(),
                'label' => $kini->locale('id')->translatedFormat('F Y'),
                'label_banding' => 'bulan lalu',
            ],
        };
    }

    /** Jumlah nominal pada tabel debit/credit sesuai hak lihat pengguna. */
    private function jumlahKas(string $tabel, ?Carbon $mulai, ?Carbon $selesai): float
    {
        $kolomTanggal = $tabel === 'debit' ? 'debit_date' : 'credit_date';
        $pengguna = $this->pengguna();

        $kueri = DB::table($tabel)
            ->leftJoin('users', $tabel . '.user_id', '=', 'users.id');

        if ($this->pengelolaKeuangan()) {
            // Kas perusahaan: milik manager & staf pada perusahaan yang sama.
            $kueri->where(function ($q) use ($pengguna, $tabel) {
                $q->where('users.company', $pengguna->company)
                    ->orWhere($tabel . '.user_id', $pengguna->getKey());
            })->whereIn('users.level', ['manager', 'staff']);
        } else {
            $kueri->where($tabel . '.user_id', $pengguna->getKey());
        }

        if ($mulai && $selesai) {
            $kueri->whereBetween($tabel . '.' . $kolomTanggal, [$mulai->toDateString(), $selesai->toDateString()]);
        }

        return (float) ($kueri->sum($tabel . '.nominal') ?? 0);
    }

    /** @return array<string,mixed> */
    #[Computed]
    public function kas(): array
    {
        $batas = $this->batasRentang();

        $masuk = $this->jumlahKas('debit', $batas['mulai'], $batas['selesai']);
        $keluar = $this->jumlahKas('credit', $batas['mulai'], $batas['selesai']);

        $masukBanding = $batas['banding_mulai']
            ? $this->jumlahKas('debit', $batas['banding_mulai'], $batas['banding_selesai'])
            : null;
        $keluarBanding = $batas['banding_mulai']
            ? $this->jumlahKas('credit', $batas['banding_mulai'], $batas['banding_selesai'])
            : null;

        return [
            'label' => $batas['label'],
            'label_banding' => $batas['label_banding'],
            'masuk' => $masuk,
            'keluar' => $keluar,
            'saldo' => $masuk - $keluar,
            'masuk_selisih' => $this->selisih($masuk, $masukBanding),
            'keluar_selisih' => $this->selisih($keluar, $keluarBanding),
            'saldo_selama_ini' => $this->jumlahKas('debit', null, null) - $this->jumlahKas('credit', null, null),
            'masuk_hari_ini' => $this->jumlahKas('debit', now()->startOfDay(), now()->endOfDay()),
            'keluar_hari_ini' => $this->jumlahKas('credit', now()->startOfDay(), now()->endOfDay()),
        ];
    }

    /** Persentase perubahan terhadap periode pembanding; null bila tak bisa dihitung. */
    private function selisih(float $kini, ?float $lalu): ?float
    {
        if ($lalu === null || $lalu <= 0.0) {
            return null;
        }

        return round((($kini - $lalu) / $lalu) * 100, 1);
    }

    /** Lima kategori pengeluaran terbesar pada rentang terpilih. */
    #[Computed]
    public function kategoriPengeluaran()
    {
        $batas = $this->batasRentang();
        $pengguna = $this->pengguna();

        $kueri = DB::table('credit')
            ->select('categories_credit.name', DB::raw('SUM(credit.nominal) as total'))
            ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
            ->leftJoin('users', 'credit.user_id', '=', 'users.id');

        if ($this->pengelolaKeuangan()) {
            $kueri->where(function ($q) use ($pengguna) {
                $q->where('users.company', $pengguna->company)
                    ->orWhere('credit.user_id', $pengguna->getKey());
            })->whereIn('users.level', ['manager', 'staff']);
        } else {
            $kueri->where('credit.user_id', $pengguna->getKey());
        }

        if ($batas['mulai']) {
            $kueri->whereBetween('credit.credit_date', [$batas['mulai']->toDateString(), $batas['selesai']->toDateString()]);
        }

        return $kueri->groupBy('categories_credit.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
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

        if ($this->pengelolaTim()) {
            $kueri->where('users.company', $pengguna->company);
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
        $perusahaan = $this->pengguna()->company;

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

        if ($this->pengelolaTim()) {
            $kueri->where('perjalanan_dinas.status', 'ajukan')
                ->where('users.company', $pengguna->company);
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
            ->orderByDesc('artikel.created_at')
            ->limit(4)
            ->get();
    }

    public function render()
    {
        return view('livewire.akun.dasbor');
    }
}
