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

    /** Tahun yang sedang ditampilkan pada grafik gaji. */
    public int $tahunGaji = 0;

    public function mount(): void
    {
        $this->dimuatPada = now()->format('H:i');
        $this->tahunGaji = now()->year;
    }

    /** Geser grafik gaji ke tahun sebelum/sesudahnya. */
    public function geserTahun(int $arah): void
    {
        $tahun = $this->tahunGaji + ($arah >= 0 ? 1 : -1);

        // Tidak ada gunanya melihat tahun yang belum tiba, dan lima tahun ke
        // belakang sudah lebih dari cukup untuk data gaji.
        $this->tahunGaji = max(now()->year - 5, min(now()->year, $tahun));

        unset($this->gaji);
    }

    /** Ambil ulang seluruh angka tanpa memuat ulang halaman. */
    public function segarkan(): void
    {
        // Properti terhitung disimpan per permintaan, jadi cukup membuang
        // simpanannya; permintaan ini akan menghitung ulang semuanya.
        unset($this->gaji, $this->tim, $this->tugas, $this->pengajuan,
            $this->artikel, $this->kehadiran, $this->cuti, $this->presensiHariIni,
            $this->ringkasan, $this->perluTindakan, $this->antreanCuti,
            $this->ringkasSistem, $this->presensiTim, $this->clinikScopus);

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
        $nama = trim((string) $this->pengguna->company);

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
        return in_array($this->pengguna->level, ['manager', 'ceo'], true);
    }

    /** Pelanggan biasa: tidak punya presensi, gaji, maupun cuti. */
    #[Computed]
    public function pelanggan(): bool
    {
        return $this->pengguna->level === 'user';
    }

    #[Computed]
    public function pengawas(): bool
    {
        return in_array($this->pengguna->level, ['admin', 'manager', 'ceo'], true);
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
        $pengguna = $this->pengguna;
        $tahun = $this->tahunGaji > 0 ? $this->tahunGaji : now()->year;

        $kueri = DB::table('gaji')
            ->selectRaw('MONTH(gaji.tanggal) as bulan, SUM(gaji.total) as total')
            ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
            ->where('gaji.status', 'terbayar')
            ->whereYear('gaji.tanggal', $tahun);

        if ($this->pengelolaTim && $this->perusahaan() !== null) {
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

        // Pembanding tahun lalu, supaya angkanya punya konteks.
        $lalu = DB::table('gaji')
            ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
            ->where('gaji.status', 'terbayar')
            ->whereYear('gaji.tanggal', $tahun - 1);

        if ($this->pengelolaTim && $this->perusahaan() !== null) {
            $lalu->where('users.company', $this->perusahaan());
        } else {
            $lalu->where('gaji.user_id', $pengguna->getKey());
        }

        $totalLalu = (float) ($lalu->sum('gaji.total') ?? 0);

        return [
            'tahun' => $tahun,
            'tahun_pertama' => now()->year - 5,
            'tahun_terakhir' => now()->year,
            'total_lalu' => $totalLalu,
            'selisih_tahun' => $totalLalu > 0 ? round((($total - $totalLalu) / $totalLalu) * 100, 1) : null,
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
        $gaji = $this->gaji;
        $hadir = $this->kehadiran;
        $cuti = $this->cuti;

        $kartu = [];

        $penuh = 'Rp ' . number_format($gaji['total'], 0, ',', '.');

        if ($this->pelanggan) {
            return $this->ringkasanPelanggan();
        }

        $kartu[] = [
            'warna' => 'biru',
            'ikon' => 'fa-money-check-alt',
            'tautan' => route('account.gaji.index'),
            'label' => ($this->pengelolaTim ? 'Gaji terbayar' : 'Gaji diterima') . ' · ' . $gaji['tahun'],
            // Angka rupiah besar tidak muat di kartu selebar seperempat layar;
            // yang tampil bentuk ringkasnya, nilai penuhnya ada di tooltip dan
            // di halaman Gaji.
            'nilai' => $gaji['total'] >= 10_000_000 ? 'Rp ' . $this->singkat($gaji['total']) : $penuh,
            'nilai_penuh' => $penuh,
            'catatan' => $gaji['bulan_terisi'] > 0
                ? 'Rata-rata Rp ' . number_format($gaji['rata'], 0, ',', '.') . ' / bulan'
                : 'Belum ada gaji terbayar tahun ini.',
        ];

        if ($this->pengelolaTim) {
            $tim = $this->tim;

            $kartu[] = [
                'warna' => 'ungu',
                'ikon' => 'fa-users',
                'tautan' => route('account.pengguna.index'),
                'label' => 'Karyawan aktif',
                'nilai' => (string) $tim['aktif'],
                'catatan' => $tim['total'] . ' akun terdaftar · ' . $tim['nonaktif'] . ' nonaktif',
            ];
        } else {
            $kartu[] = [
                'warna' => 'ungu',
                'ikon' => 'fa-briefcase',
                'tautan' => route('account.profil.show', $this->pengguna->getKey()),
                'label' => 'Masa kerja',
                'nilai' => $hadir['masa_kerja'],
                'catatan' => 'Sejak ' . ($this->pengguna->created_at
                    ? $this->pengguna->created_at->locale('id')->translatedFormat('d F Y')
                    : '-'),
            ];
        }

        $kartu[] = [
            'warna' => 'hijau',
            'ikon' => 'fa-fingerprint',
            'tautan' => route('account.presensi.index'),
            'label' => 'Hadir bulan ini',
            'nilai' => $hadir['hadir_bulan_ini'] . ' hari',
            'catatan' => $hadir['izin_bulan_ini'] . ' izin · ' . $hadir['lembur_bulan_ini'] . ' lembur',
        ];

        $kartu[] = [
            'warna' => 'kuning',
            'ikon' => 'fa-umbrella-beach',
            'tautan' => route('account.cuti.index'),
            'label' => 'Sisa cuti',
            'nilai' => $cuti['boleh'] ? $cuti['sisa'] . ' hari' : 'Belum berhak',
            'catatan' => $cuti['boleh']
                ? $cuti['terpakai'] . ' dari ' . $cuti['jatah'] . ' hari terpakai'
                : 'Terbuka setelah 1 tahun masa kerja.',
        ];

        return $kartu;
    }

    /**
     * Kartu untuk pelanggan biasa. Gaji, presensi, dan cuti tidak pernah
     * terisi bagi mereka, jadi kartunya bicara soal akun dan layanan.
     *
     * @return array<int,array<string,mixed>>
     */
    private function ringkasanPelanggan(): array
    {
        $pengguna = $this->pengguna;

        // Halaman riwayat memakai customer_id; ikuti aturan yang sama.
        $pesanan = DB::table('clinikscopus_pemesanan')
            ->where('customer_id', $pengguna->getKey())
            ->count();

        return [
            [
                'warna' => 'ungu',
                'ikon' => 'fa-user-check',
                'tautan' => route('account.profil.show', $pengguna->getKey()),
                'label' => 'Status akun',
                'nilai' => $pengguna->email_verified_at ? 'Terverifikasi' : 'Belum verifikasi',
                'catatan' => $pengguna->email_verified_at
                    ? 'Semua layanan terbuka untuk Anda.'
                    : 'Verifikasi email untuk membuka semua layanan.',
            ],
            [
                'warna' => 'biru',
                'ikon' => 'fa-clipboard-list',
                'tautan' => route('account.Clinik-Scopus-Riwayat-Pemesanan.index'),
                'label' => 'Pemesanan saya',
                'nilai' => $pesanan . ' pesanan',
                'catatan' => $pesanan > 0 ? 'Lihat riwayat dan statusnya.' : 'Belum ada pemesanan tercatat.',
            ],
            [
                'warna' => 'hijau',
                'ikon' => 'fa-shield-alt',
                'tautan' => route('account.profil.show', $pengguna->getKey()),
                'label' => 'Keamanan',
                'nilai' => $pengguna->pinAktif() ? 'PIN aktif' : 'Kata sandi',
                'catatan' => $pengguna->pinAktif()
                    ? 'Masuk cukup dengan 6 angka di perangkat ini.'
                    : 'Aktifkan PIN agar masuk lebih cepat.',
            ],
            [
                'warna' => 'kuning',
                'ikon' => 'fa-calendar-alt',
                'tautan' => route('account.profil.show', $pengguna->getKey()),
                'label' => 'Bergabung sejak',
                'nilai' => $pengguna->created_at
                    ? $pengguna->created_at->locale('id')->translatedFormat('M Y')
                    : '-',
                'catatan' => 'Terima kasih sudah bersama kami.',
            ],
        ];
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
            ->where('user_id', $this->pengguna->getKey())
            ->whereDate('created_at', now()->toDateString())
            ->first();
    }

    /** @return array<string,mixed> */
    #[Computed]
    public function kehadiran(): array
    {
        $pengguna = $this->pengguna;
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
            // Ringkas: dua satuan terbesar saja, supaya muat di kartu sempit.
            'masa_kerja' => $masaKerja
                ? ($masaKerja->y > 0
                    ? $masaKerja->y . ' thn' . ($masaKerja->m > 0 ? ' ' . $masaKerja->m . ' bln' : '')
                    : ($masaKerja->m > 0
                        ? $masaKerja->m . ' bln' . ($masaKerja->d > 0 ? ' ' . $masaKerja->d . ' hr' : '')
                        : $masaKerja->d . ' hari'))
                : '-',
        ];
    }

    /** @return array<string,mixed> */
    #[Computed]
    public function cuti(): array
    {
        $pengguna = $this->pengguna;
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

        // Empat angka sekaligus dalam satu kueri, bukan empat count() terpisah.
        $hitung = DB::table('users')
            ->where('company', $perusahaan)
            ->selectRaw("COUNT(*) as total,
                SUM(status = 'active') as aktif,
                SUM(status = 'nonactive') as nonaktif,
                SUM(email_verified_at IS NULL) as belum_verifikasi")
            ->first();

        return [
            'total' => (int) ($hitung->total ?? 0),
            'aktif' => (int) ($hitung->aktif ?? 0),
            'nonaktif' => (int) ($hitung->nonaktif ?? 0),
            'belum_verifikasi' => (int) ($hitung->belum_verifikasi ?? 0),
            // Dulu daftar ini mengambil seluruh pengguna tanpa memandang
            // perusahaan, sehingga manajer melihat akun perusahaan lain.
            'terbaru' => DB::table('users')
                ->where('company', $perusahaan)
                ->whereIn('level', ['staff', 'karyawan', 'trainer'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'full_name', 'level', 'gambar', 'created_at', 'status']),
            'tanpa_perusahaan' => false,
        ];
    }

    /**
     * Daftar "perlu tindakan": hal-hal yang menunggu diputuskan atau
     * diselesaikan, dikumpulkan dari beberapa sumber menjadi satu tempat.
     *
     * @return array<int,array<string,mixed>>
     */
    #[Computed]
    public function perluTindakan(): array
    {
        $pengguna = $this->pengguna;
        $daftar = [];

        if (! $pengguna->email_verified_at) {
            $daftar[] = [
                'warna' => 'kuning',
                'ikon' => 'fa-envelope-open-text',
                'judul' => 'Email belum diverifikasi',
                'teks' => 'Sebagian fitur terkunci sampai verifikasi selesai.',
                'tautan' => route('account.profil.show', $pengguna->getKey()),
            ];
        }

        if (! $this->pelanggan && ! $this->presensiHariIni && now()->format('H:i:s') >= '07:00:00' && now()->format('H:i:s') <= '22:00:00') {
            $daftar[] = [
                'warna' => 'merah',
                'ikon' => 'fa-fingerprint',
                'judul' => 'Belum presensi hari ini',
                'teks' => 'Jam presensi 07.00 – 22.00 WIB.',
                'tautan' => route('account.presensi.create'),
            ];
        }

        $tugasLewat = $this->tugas->filter(
            fn ($t) => $t->tanggal_deadline && \Illuminate\Support\Carbon::parse($t->tanggal_deadline)->isPast()
        )->count();

        if ($tugasLewat > 0) {
            $daftar[] = [
                'warna' => 'merah',
                'ikon' => 'fa-tasks',
                'judul' => $tugasLewat . ' tugas lewat tenggat',
                'teks' => 'Perbarui statusnya atau minta perpanjangan.',
                'tautan' => route('account.todolist.index'),
            ];
        }

        if ($this->pengelolaTim) {
            $dinas = $this->pengajuan->count();

            if ($dinas > 0) {
                $daftar[] = [
                    'warna' => 'biru',
                    'ikon' => 'fa-plane-departure',
                    'judul' => $dinas . ' perjalanan dinas menunggu',
                    'teks' => 'Menunggu persetujuan Anda.',
                    'tautan' => route('account.PerjalananDinas.index'),
                ];
            }

            $cuti = $this->antreanCuti->count();

            if ($cuti > 0) {
                $daftar[] = [
                    'warna' => 'hijau',
                    'ikon' => 'fa-umbrella-beach',
                    'judul' => $cuti . ' pengajuan cuti menunggu',
                    'teks' => 'Setujui atau tolak dari halaman Cuti.',
                    'tautan' => route('account.cuti.index'),
                ];
            }

            $belum = $this->tim['belum_verifikasi'];

            if ($belum > 0) {
                $daftar[] = [
                    'warna' => 'kuning',
                    'ikon' => 'fa-user-clock',
                    'judul' => $belum . ' karyawan belum verifikasi email',
                    'teks' => 'Ingatkan mereka agar aksesnya tidak tertahan.',
                    'tautan' => route('account.pengguna.index'),
                ];
            }
        }

        if ($this->pengelolaTim || in_array($pengguna->level, ['admin', 'staff'], true)) {
            $menunggu = $this->clinikScopus['menunggu'];

            if ($menunggu > 0) {
                $daftar[] = [
                    'warna' => 'kuning',
                    'ikon' => 'fa-file-invoice-dollar',
                    'judul' => $menunggu . ' pemesanan menunggu pembayaran',
                    'teks' => 'Periksa bukti transfer yang masuk.',
                    'tautan' => route('account.Clinik-Scopus-Riwayat-Pemesanan.index'),
                ];
            }
        }

        if ($this->pengawas) {
            $gagal = $this->ringkasSistem['gagal_24_jam'];

            if ($gagal >= 10) {
                $daftar[] = [
                    'warna' => 'merah',
                    'ikon' => 'fa-user-lock',
                    'judul' => $gagal . ' percobaan masuk gagal (24 jam)',
                    'teks' => 'Periksa jejak aktivitas masuk.',
                    'tautan' => route('account.aktivitas-masuk.index'),
                ];
            }
        }

        return $daftar;
    }

    /** Pengajuan cuti yang menunggu keputusan pengelola. */
    #[Computed]
    public function antreanCuti()
    {
        if (! $this->pengelolaTim || $this->perusahaan() === null) {
            return collect();
        }

        return DB::table('cuti')
            ->select('cuti.id', 'cuti.id_pengajuan', 'cuti.jenis_cuti', 'cuti.tanggal_mulai_cuti',
                'cuti.total_hari_cuti', 'users.full_name')
            ->leftJoin('users', 'cuti.user_id', '=', 'users.id')
            ->where('users.company', $this->perusahaan())
            ->whereIn('cuti.status', ['ajukan', 'pending', 'menunggu'])
            ->orderByDesc('cuti.created_at')
            ->limit(5)
            ->get();
    }

    /**
     * Angka pengawasan untuk admin, manajer, dan CEO.
     *
     * @return array<string,mixed>
     */
    #[Computed]
    public function ringkasSistem(): array
    {
        $perusahaan = $this->perusahaan();

        $akun = DB::table('users')
            ->selectRaw("COUNT(*) as total_akun,
                SUM(email_verified_at IS NULL) as belum_verifikasi,
                SUM(status = 'nonactive') as nonaktif,
                SUM(pin_aktif = 1) as pin_aktif");

        // Admin mengawasi seluruh sistem; manajer/CEO hanya perusahaannya.
        if ($this->pengguna->level !== 'admin' && $perusahaan !== null) {
            $akun->where('company', $perusahaan);
        }

        $hitung = $akun->first();

        // Dua angka jejak masuk juga digabung dalam satu kueri.
        $jejak = DB::table('aktivitas_masuk')
            ->where('created_at', '>=', now()->subDay())
            ->selectRaw('SUM(berhasil = 1) as berhasil, SUM(berhasil = 0) as gagal')
            ->first();

        return [
            'total_akun' => (int) ($hitung->total_akun ?? 0),
            'belum_verifikasi' => (int) ($hitung->belum_verifikasi ?? 0),
            'nonaktif' => (int) ($hitung->nonaktif ?? 0),
            'gagal_24_jam' => (int) ($jejak->gagal ?? 0),
            'berhasil_24_jam' => (int) ($jejak->berhasil ?? 0),
            'pin_aktif' => (int) ($hitung->pin_aktif ?? 0),
        ];
    }

    /**
     * Presensi tim hari ini: sudah, belum, dan siapa saja yang sudah masuk.
     *
     * @return array<string,mixed>
     */
    #[Computed]
    public function presensiTim(): array
    {
        if (! $this->pengelolaTim || $this->perusahaan() === null) {
            return ['total' => 0, 'sudah' => 0, 'belum' => 0, 'daftar' => collect()];
        }

        $anggota = DB::table('users')
            ->where('company', $this->perusahaan())
            ->where('status', 'active')
            ->whereIn('level', ['staff', 'karyawan', 'trainer', 'manager'])
            ->pluck('id');

        $sudah = DB::table('presensi')
            ->select('presensi.user_id', 'presensi.created_at', 'presensi.status_pulang', 'users.full_name')
            ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
            ->whereIn('presensi.user_id', $anggota)
            ->whereDate('presensi.created_at', now()->toDateString())
            ->orderBy('presensi.created_at')
            ->get();

        return [
            'total' => $anggota->count(),
            'sudah' => $sudah->count(),
            'belum' => max(0, $anggota->count() - $sudah->count()),
            'daftar' => $sudah->take(5),
        ];
    }

    /**
     * Menu akses cepat bawaan hanya berisi pintasan untuk manajer dan
     * karyawan; peran lain melihat kartu yang nyaris kosong. Untuk mereka
     * dibuatkan pintasan sendiri.
     */
    #[Computed]
    public function menuPenuh(): bool
    {
        return in_array($this->pengguna->level, ['manager', 'karyawan'], true);
    }

    /** @return array<int,array<string,string>> */
    #[Computed]
    public function pintasan(): array
    {
        $pengguna = $this->pengguna;
        $profil = route('account.profil.show', $pengguna->getKey());

        if ($this->pelanggan) {
            return [
                ['warna' => 'biru', 'ikon' => 'fa-clipboard-list', 'judul' => 'Riwayat pemesanan',
                    'teks' => 'Status dan bukti pembayaran Anda.', 'tautan' => route('account.Clinik-Scopus-Riwayat-Pemesanan.index')],
                ['warna' => 'ungu', 'ikon' => 'fa-comments', 'judul' => 'Konsultasi Clinik Scopus',
                    'teks' => 'Pesan sesi dengan pendamping.', 'tautan' => route('account.clinikscopus.index')],
                ['warna' => 'hijau', 'ikon' => 'fa-user-cog', 'judul' => 'Profil & keamanan',
                    'teks' => 'Ubah data, kata sandi, dan PIN.', 'tautan' => $profil],
            ];
        }

        $daftar = [
            ['warna' => 'hijau', 'ikon' => 'fa-fingerprint', 'judul' => 'Presensi',
                'teks' => 'Riwayat kehadiran Anda.', 'tautan' => route('account.presensi.index')],
            ['warna' => 'kuning', 'ikon' => 'fa-umbrella-beach', 'judul' => 'Cuti',
                'teks' => 'Ajukan dan pantau pengajuan.', 'tautan' => route('account.cuti.index')],
            ['warna' => 'biru', 'ikon' => 'fa-money-check-alt', 'judul' => 'Gaji',
                'teks' => 'Slip dan riwayat pembayaran.', 'tautan' => route('account.gaji.index')],
        ];

        if ($this->pengawas) {
            $daftar[] = ['warna' => 'merah', 'ikon' => 'fa-user-shield', 'judul' => 'Jejak aktivitas masuk',
                'teks' => 'Pantau percobaan masuk ke sistem.', 'tautan' => route('account.aktivitas-masuk.index')];
            $daftar[] = ['warna' => 'ungu', 'ikon' => 'fa-users', 'judul' => 'Data pengguna',
                'teks' => 'Kelola akun dan perannya.', 'tautan' => route('account.pengguna.index')];
        }

        $daftar[] = ['warna' => 'ungu', 'ikon' => 'fa-user-cog', 'judul' => 'Profil & keamanan',
            'teks' => 'Ubah data, kata sandi, dan PIN.', 'tautan' => $profil];

        return $daftar;
    }

    /**
     * Ringkasan Clinik Scopus — layanan utama, tetapi selama ini sama sekali
     * tidak tampil di dasbor padahal angkanya sudah dihitung untuk lencana
     * di bilah samping.
     *
     * @return array<string,mixed>
     */
    #[Computed]
    public function clinikScopus(): array
    {
        $pengguna = $this->pengguna;

        $dasar = function () use ($pengguna) {
            $kueri = DB::table('clinikscopus_pemesanan');

            // Trainer hanya melihat sesi yang dipegangnya sendiri.
            if (! $this->pengelolaTim && ! in_array($pengguna->level, ['admin', 'staff'], true)) {
                $kueri->where('trainer_id', $pengguna->getKey());
            }

            return $kueri;
        };

        $hitung = $dasar()
            ->selectRaw("COUNT(*) as total,
                SUM(status = 'pending') as menunggu,
                SUM(status = 'paid') as terbayar,
                SUM(DATE(tanggal_booking) = ?) as hari_ini,
                SUM(status = 'paid' OR status = 'completed') as selesai", [now()->toDateString()])
            ->first();

        $pendapatanBulanIni = (float) $dasar()
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('tanggal', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_pembayaran');

        return [
            'total' => (int) ($hitung->total ?? 0),
            'menunggu' => (int) ($hitung->menunggu ?? 0),
            'terbayar' => (int) ($hitung->terbayar ?? 0),
            'hari_ini' => (int) ($hitung->hari_ini ?? 0),
            'pendapatan_bulan_ini' => $pendapatanBulanIni,
            'mendatang' => $dasar()
                ->select('id', 'kode_booking', 'nama_pemesan', 'sesi', 'jam_sesi', 'tanggal_booking', 'status')
                ->whereDate('tanggal_booking', '>=', now()->toDateString())
                ->orderBy('tanggal_booking')
                ->limit(4)
                ->get(),
        ];
    }

    // ------------------------------------------------------- tugas & kabar

    #[Computed]
    public function tugas()
    {
        $id = $this->pengguna->getKey();

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
        $pengguna = $this->pengguna;

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

        if ($this->pengelolaTim && $this->perusahaan() !== null) {
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
