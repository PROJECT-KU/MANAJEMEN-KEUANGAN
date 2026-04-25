<?php

namespace App\Http\Controllers\account;

use App\CategoriesScopusCamp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\PendaftaranScopusCamp;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use App\Exports\KategoriAnalisisExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class CategoriesScopusCampController extends Controller
{

    function generateRandomToken($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }

    // <!--================== NAMPILIN DATA ==================-->
    public function index(Request $request)
    {
        $user = Auth::user();

        // --- LOGIKA OTOMATIS NON-ACTIVE ---
        // Update status menjadi 'non active' jika tanggal 'mulai' sudah terlewati (>= sekarang pukul 00:01)
        // Kita gunakan Carbon untuk mendapatkan waktu sekarang
        $now = \Carbon\Carbon::now();

        DB::table('scopus_camp_kategori')
            ->where('status', 'active') // Hanya cek yang masih aktif
            ->where('mulai', '<=', $now->format('Y-m-d H:i:s'))
            ->update(['status' => 'non active']);
        // ----------------------------------

        // Ambil nilai filter tanggal dari request
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');
        $search = $request->input('q');

        // Mulai query builder
        $query = DB::table('scopus_camp_kategori');

        // Filter berdasarkan rentang tanggal jika ada
        if ($startDate && $endDate) {
            $start = $startDate . ' 00:00:00';
            $end = $endDate . ' 23:59:59';
            $query->whereBetween('created_at', [$start, $end]);
        }

        // Filter berdasarkan kata pencarian jika ada
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('nama_ke', 'like', "%$search%")
                    ->orWhere('total_kuota', 'like', "%$search%")
                    ->orWhere('sisa_kuota', 'like', "%$search%")
                    ->orWhere('mulai', 'like', "%$search%")
                    ->orWhere('selesai', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        // Urutkan dan paginasi hasilnya
        $categories = $query->orderBy('created_at', 'DESC')->paginate(10);

        return view('account.kategori_scopus_camp.index', compact(
            'categories',
            'startDate',
            'endDate'
        ));
    }
    // <!--================== END ==================-->

    // <!--================== TAMBAH DATA ==================-->
    public function create()
    {
        $user = Auth::user();
        $currentTime = now()->format('H:i:s');

        return view('account.kategori_scopus_camp.create', compact('user', 'currentTime'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $token = $this->generateRandomToken(30);

        // SAVE IMAGE
        $imagePath = null;
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $extension = strtolower($image->getClientOriginalExtension());
            $allowedExtensions = ['png', 'jpg', 'jpeg'];

            // Validasi ekstensi file
            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->back()
                    ->with('error', 'Format gambar tidak valid. Gunakan PNG, JPG, atau JPEG.');
            }

            // Nama file pakai UUID
            $imageName = Str::uuid()->toString() . '.' . $extension;

            // Simpan file
            $image->move(public_path('ScopusCamp'), $imageName);

            // Simpan path
            $imagePath = 'ScopusCamp/' . $imageName;
        }
        // END

        // CLEAR FORMAT RUPIAH
        $cleanBiaya = str_replace('.', '', $request->input('biaya'));
        $cleanNominalDiskon = str_replace('.', '', $request->input('nominal_diskon'));
        $cleanTotalBiaya = str_replace('.', '', $request->input('total_biaya'));
        // END

        // MENENTUKAN SISA KUOTA YANG SAMA DENGAN JUMLAH TOTAL KUOTA
        $totalKuota = $request->input('total_kuota');
        $sisaKuota = $totalKuota;
        // END

        $save = CategoriesScopusCamp::create([
            'token'             => $token,
            'nama'              => $request->input('nama'),
            'nama_ke'           => $request->input('nama_ke'),
            'mulai'             => $request->input('mulai'),
            'selesai'           => $request->input('selesai'),
            'total_kuota'       => $totalKuota,
            'sisa_kuota'        => $sisaKuota,
            'desc'              => $request->input('desc'),
            'best_price'        => $request->input('best_price'),
            'lokasi'            => $request->input('lokasi'),
            'biaya'             => $cleanBiaya,
            'ppn'               => $request->input('ppn'),
            'tipe_diskon'       => $request->input('tipe_diskon'),
            'diskon_persentase' => $request->input('diskon_persentase'),
            'nominal_diskon'    => $cleanNominalDiskon,
            'kode_diskon'       => $request->input('kode_diskon'),
            'total_biaya'       => $cleanTotalBiaya,
            'gambar'            => $imagePath,
            'status'            => $request->input('status'),
            'group_wa'          => $request->input('group_wa'),
        ]);

        if ($save) {
            return redirect()->route('account.kategoriscopuscamp.index')->with('success', 'Data Kategori Scopus Camp Berhasil Disimpan!');
        } else {
            return redirect()->route('account.kategoriscopuscamp.index')->with('error', 'Data Kategori Scopus Camp Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function edit($id, $token)
    {
        $user = Auth::user();
        $categories = CategoriesScopusCamp::findOrFail($id);

        // Format rupiah: pakai TITIK
        $categories->nominal_diskon = number_format(
            (int) $categories->nominal_diskon,
            0,
            '',   // TIDAK pakai koma
            '.'   // pakai titik
        );

        $categories->total_biaya = number_format(
            (int) $categories->total_biaya,
            0,
            '',   // TIDAK pakai koma
            '.'   // pakai titik
        );

        return view('account.kategori_scopus_camp.edit', compact('categories'));
    }


    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $categories = CategoriesScopusCamp::findOrFail($id);

        // SAVE IMAGE
        $imagePath = $categories->gambar; // default gambar lama

        if ($request->hasFile('gambar')) {

            // Hapus gambar lama jika ada
            if ($categories->gambar && file_exists(public_path($categories->gambar))) {
                unlink(public_path($categories->gambar));
            }

            // Upload gambar baru
            $image = $request->file('gambar');
            $extension = strtolower($image->getClientOriginalExtension());

            // Nama file pakai UUID
            $imageName = Str::uuid()->toString() . '.' . $extension;

            // Simpan ke folder
            $image->move(public_path('ScopusCamp'), $imageName);

            // Simpan path lengkap
            $imagePath = 'ScopusCamp/' . $imageName;
        }
        // END

        // CLEAR FORMAT RUPIAH
        $cleanBiaya = str_replace('.', '', $request->input('biaya'));
        $cleanNominalDiskon = str_replace('.', '', $request->input('nominal_diskon'));
        $cleanTotalBiaya = str_replace('.', '', $request->input('total_biaya'));
        // END

        // MENENTUKAN SISA KUOTA YANG SAMA DENGAN JUMLAH TOTAL KUOTA
        $totalPendaftarTerdaftar = PendaftaranScopusCamp::where('scopus_camp_kategori_id', $id)
            ->sum('jumlah_pendaftar');

        $totalKuota = (int) $request->input('total_kuota');
        $sisaKuota = $totalKuota - $totalPendaftarTerdaftar;
        // END

        $categories->update([
            'nama'              => $request->input('nama'),
            'nama_ke'           => $request->input('nama_ke'),
            'mulai'             => $request->input('mulai'),
            'selesai'           => $request->input('selesai'),
            'total_kuota'       => $totalKuota,
            'sisa_kuota'        => $sisaKuota,
            'desc'              => $request->input('desc'),
            'best_price'        => $request->input('best_price'),
            'lokasi'        => $request->input('lokasi'),
            'biaya'             => $cleanBiaya,
            'ppn'               => $request->input('ppn'),
            'tipe_diskon'       => $request->input('tipe_diskon'),
            'diskon_persentase' => $request->input('diskon_persentase'),
            'nominal_diskon'    => $cleanNominalDiskon,
            'kode_diskon'       => $request->input('kode_diskon'),
            'total_biaya'       => $cleanTotalBiaya,
            'gambar'            => $imagePath,
            'status'            => $request->input('status'),
            'group_wa'          => $request->input('group_wa'),
        ]);

        if ($categories) {
            return redirect()->route('account.kategoriscopuscamp.index')->with('success', 'Data Kategori Scopus Camp Berhasil Disimpan!');
        } else {
            return redirect()->route('account.kategoriscopuscamp.index')->with('error', 'Data Kategori Scopus Camp Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy($id)
    {
        try {
            $categories = CategoriesScopusCamp::find($id);

            if (!$categories) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan!'
                ], 404);
            }

            // hapus gambar
            if ($categories->gambar && file_exists(public_path($categories->gambar))) {
                unlink(public_path($categories->gambar));
            }

            $categories->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data dan gambar berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // <!--================== END ==================-->

    // <!--================== SEARCH ==================-->
    public function search(Request $request)
    {
        $search = $request->get('q');
        $user = Auth::user();

        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        if ($startDate && $endDate) {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 23:59:59', strtotime($endDate));
        }

        $query = DB::table('scopus_camp_kategori')
            ->where(function ($query) use ($search) {
                $query->where('nama', 'LIKE', '%' . $search . '%')
                    ->orWhere('nama_ke', 'LIKE', '%' . $search . '%')
                    ->orWhere('total_kuota', 'LIKE', '%' . $search . '%')
                    ->orWhere('sisa_kuota', 'LIKE', '%' . $search . '%')
                    ->orWhere('status', 'LIKE', '%' . $search . '%')

                    // 🔽 SEARCH TANGGAL MULAI
                    ->orWhereRaw("DATE_FORMAT(mulai, '%e %M %Y') LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("DATE_FORMAT(mulai, '%d-%m-%Y') LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("DATE_FORMAT(mulai, '%Y') LIKE ?", ['%' . $search . '%'])

                    // 🔽 SEARCH TANGGAL SELESAI
                    ->orWhereRaw("DATE_FORMAT(selesai, '%e %M %Y') LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("DATE_FORMAT(selesai, '%d-%m-%Y') LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("DATE_FORMAT(selesai, '%Y') LIKE ?", ['%' . $search . '%']);
            });

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$currentMonth, $nextMonth]);
        }

        $categories = $query->orderBy('created_at', 'DESC')->paginate(10);
        $categories->appends(['q' => $search]);

        if ($categories->isEmpty()) {
            return redirect()->route('account.kategoriscopuscamp.index')->with('error', 'Data Laporan Peserta tidak ditemukan.');
        }

        return view('account.kategori_scopus_camp.index', compact('categories', 'startDate', 'endDate'));
    }

    // <!--================== END ==================-->

    // <!--================== FILTER ==================-->
    public function filter(Request $request)
    {
        $startDate = $request->input('tanggal_awal');
        $endDate   = $request->input('tanggal_akhir');

        // Jika tidak pilih tanggal → tampilkan semua data
        if (!$startDate || !$endDate) {
            $categories = DB::table('scopus_camp_kategori')
                ->orderBy('mulai', 'DESC')
                ->paginate(10);

            return view(
                'account.kategori_scopus_camp.index',
                compact('categories', 'startDate', 'endDate')
            );
        }

        // Format tanggal
        $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
        $endDate   = date('Y-m-d 23:59:59', strtotime($endDate));

        // FILTER OVERLAP (BENAR)
        $categories = DB::table('scopus_camp_kategori')
            ->where('mulai', '<=', $endDate)
            ->where('selesai', '>=', $startDate)
            ->orderBy('mulai', 'DESC')
            ->paginate(10);

        return view(
            'account.kategori_scopus_camp.index',
            compact('categories', 'startDate', 'endDate')
        );
    }
    // <!--================== END ==================-->

    // <!--================== DOWNLOAD ==================-->
    public function downloadExcel(Request $request)
    {
        $search = $request->input('q');
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        // Gunakan Carbon untuk parsing tanggal
        $tanggal_awal = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $tanggal_akhir = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        // Query dasar berdasarkan tanggal dari kolom 'mulai'
        $query = DB::table('categories_analisis_bibliometrik')
            ->select(
                'id',
                'token',
                'nama',
                'nama_ke',
                'mulai',
                'selesai',
                'total_kuota',
                'sisa_kuota',
                'desc',
                'biaya',
                'ppn',
                'tipe_diskon',
                'diskon_persentase',
                'nominal_diskon',
                'kode_diskon',
                'total_biaya',
                'status',
                'group_wa'
            )
            ->whereBetween('mulai', [$tanggal_awal, $tanggal_akhir]);

        // Jika ada pencarian, terapkan ke hasil yang sudah difilter tanggal
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('nama_ke', 'like', "%$search%")
                    ->orWhere('total_kuota', 'like', "%$search%")
                    ->orWhere('sisa_kuota', 'like', "%$search%")
                    ->orWhere('mulai', 'like', "%$search%")
                    ->orWhere('selesai', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        $data = $query->orderBy('mulai', 'desc')->get();

        return Excel::download(new KategoriAnalisisExport($data), 'Kategori-Analisis_' . now()->format('Ymd_His') . '.xlsx');
    }
    // <!--================== END ==================-->
}
