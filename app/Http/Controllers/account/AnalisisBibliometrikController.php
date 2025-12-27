<?php

namespace App\Http\Controllers\account;

use App\AnalisisBibliometrik;
use App\CategoriesAnalisisBibliometrik;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnalisisBibliometrikUpdateDiterimaMail;
use App\Mail\AnalisisBibliometrikUpdateResheduleMail;
use App\Exports\PendaftaranAnalisisBibliometrikExport;
use Maatwebsite\Excel\Facades\Excel;

class AnalisisBibliometrikController extends Controller
{
    /**
     * PenyewaanController constructor.
     */
    function generateRandomToken($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$&-_?';
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }

    public function generateRandomId($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $id;
    }

    // <!--================== MENAMPILKAN DATA ==================-->
    public function index(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
        }
        $datas = DB::table('analisis_bibliometrik')
            ->join('categories_analisis_bibliometrik', 'analisis_bibliometrik.categories_analisis_bibliometrik_id', '=', 'categories_analisis_bibliometrik.id')
            ->select(
                'analisis_bibliometrik.*',
                'categories_analisis_bibliometrik.nama as kategori_nama',
                'categories_analisis_bibliometrik.nama_ke as kategori_nama_ke',
                'categories_analisis_bibliometrik.mulai as kategori_tanggal_mulai',
                'categories_analisis_bibliometrik.selesai as kategori_tanggal_selesai',
                'categories_analisis_bibliometrik.id as kategori_id'
            )
            ->latest('analisis_bibliometrik.created_at')
            ->paginate(10);


        return view('account.analisis_bibliometrik.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function edit($id, $token)
    {
        $data = AnalisisBibliometrik::findOrFail($id);
        $category = CategoriesAnalisisBibliometrik::find($data->categories_analisis_bibliometrik_id);
        $categories = CategoriesAnalisisBibliometrik::all();

        // Inject mulai dan selesai manual jika category ditemukan
        if ($category) {
            $data->mulai = $category->mulai;
            $data->selesai = $category->selesai;
            $data->sisa_kuota = $category->sisa_kuota;
            $data->biaya = $category->biaya;
            $data->kode_diskon = $category->kode_diskon;
            $data->group_wa = $category->group_wa;
        } else {
            $data->mulai = null;
            $data->selesai = null;
            $data->sisa_kuota = null;
            $data->biaya = null;
            $data->kode_diskon = null;
            $data->group_wa = null;
        }

        return view('account.analisis_bibliometrik.edit', compact('data', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $analisisbibliometrik = AnalisisBibliometrik::findOrFail($id);

        // MENGHITUNG JUMLAH SISA KUOTA YANG TIDAK BOLEH MELEBIHI TOTAL KUOTA
        $kategoriBaruId = $request->input('categories_analisis_bibliometrik_id');
        $kategoriLamaId = $analisisbibliometrik->categories_analisis_bibliometrik_id;
        $kategori = CategoriesAnalisisBibliometrik::findOrFail($kategoriBaruId);
        // END

        // MENENTUKAN SISA KUOTA YANG SAMA DENGAN JUMLAH TOTAL KUOTA
        $jumlahPendaftarLama = $analisisbibliometrik->jumlah_pendaftar;
        $jumlahPendaftarBaru = (int) $request->input('jumlah_pendaftar', 0);

        if ($kategoriLamaId != $kategoriBaruId) {
            $kategoriLama = CategoriesAnalisisBibliometrik::find($kategoriLamaId);
            if ($kategoriLama) {
                $kategoriLama->sisa_kuota += $jumlahPendaftarLama;
                $kategoriLama->save();
            }
        }
        // END

        // CLEAR FORMAT RUPIAH
        $cleanPPN = str_replace('.', '', $request->input('ppn'));
        $cleanKodeUnik = str_replace('.', '', $request->input('kode_unik'));
        $cleanNominalDiskon = str_replace('.', '', $request->input('nominal_diskon'));
        $cleanTotalPembayaran = str_replace('.', '', $request->input('total_pembayaran'));
        // END

        $analisisbibliometrik->update([
            'categories_analisis_bibliometrik_id'                     => $request->input('categories_analisis_bibliometrik_id'),
            'nama'                                                    => $request->input('nama'),
            'email'                                                   => $request->input('email'),
            'affiliasi'                                               => $request->input('affiliasi'),
            'telp'                                                    => $request->input('telp'),
            'jumlah_pendaftar'                                        => $jumlahPendaftarBaru,
            'ppn'                                                     => $cleanPPN,
            'kode_unik'                                               => $cleanKodeUnik,
            'nominal_diskon'                                          => $cleanNominalDiskon,
            'total_pembayaran'                                        => $cleanTotalPembayaran,
            'status'                                                  => $request->input('status'),
            'tanggal_reschedule'                                      => $request->input('tanggal_reschedule'),
            'group_wa'                                                => $request->input('group_wa'),
            'note'                                                    => $request->input('note'),
        ]);

        if ($analisisbibliometrik) {

            // UPDATE SISA KUOTA
            $totalTerdaftar = AnalisisBibliometrik::where('categories_analisis_bibliometrik_id', $kategoriBaruId)
                ->sum('jumlah_pendaftar');

            $kategori->sisa_kuota = max(0, $kategori->total_kuota - $totalTerdaftar);
            $kategori->save();
            // END

            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');

            if ($analisisbibliometrik->status == 'Pendaftaran Diterima') {
                Mail::to($emailTo)->send(new AnalisisBibliometrikUpdateDiterimaMail($analisisbibliometrik, $kategori, $appName));
            } elseif ($analisisbibliometrik->status === 'Pendaftaran Reschedule') {
                Mail::to($emailTo)->send(new AnalisisBibliometrikUpdateResheduleMail($analisisbibliometrik, $kategori, $appName));
            }

            return redirect()->route('account.analisisbibliometrik.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
        } else {
            return redirect()->route('account.analisisbibliometrik.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
        }
    }

    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $data = AnalisisBibliometrik::findOrFail($id);

            // ===== UPDATE KUOTA =====
            $kategori = CategoriesAnalisisBibliometrik::find($data->categories_analisis_bibliometrik_id);

            if ($kategori) {
                $kategori->sisa_kuota += $data->jumlah_pendaftar;

                if ($kategori->sisa_kuota > $kategori->total_kuota) {
                    $kategori->sisa_kuota = $kategori->total_kuota;
                }

                $kategori->save();
            }

            // ===== HAPUS GAMBAR TANPA exists =====
            if (!empty($data->gambar)) {
                @unlink(public_path('bibliometrik/' . $data->gambar));
            }

            // ===== HAPUS DATA =====
            $data->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data dan gambar berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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
        $search     = $request->get('q');
        $startDate  = $request->input('tanggal_awal');
        $endDate    = $request->input('tanggal_akhir');

        // Atur default tanggal
        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth    = date('Y-m-t 23:59:59');
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth    = date('Y-m-d 23:59:59', strtotime($endDate));
        }

        // Query search
        $query = DB::table('analisis_bibliometrik')
            ->join('categories_analisis_bibliometrik', 'analisis_bibliometrik.categories_analisis_bibliometrik_id', '=', 'categories_analisis_bibliometrik.id')
            ->select(
                'analisis_bibliometrik.*',
                'categories_analisis_bibliometrik.nama as kategori_nama',
                'categories_analisis_bibliometrik.nama_ke as kategori_nama_ke',
                'categories_analisis_bibliometrik.mulai as kategori_tanggal_mulai',
                'categories_analisis_bibliometrik.selesai as kategori_tanggal_selesai',
                'categories_analisis_bibliometrik.id as kategori_id'
            )
            ->where(function ($q) use ($search) {
                $q->where('analisis_bibliometrik.id_transaksi', 'LIKE', "%{$search}%")
                    ->orWhere('analisis_bibliometrik.nama', 'LIKE', "%{$search}%")
                    ->orWhere('categories_analisis_bibliometrik.nama', 'LIKE', "%{$search}%")
                    ->orWhere('categories_analisis_bibliometrik.nama_ke', 'LIKE', "%{$search}%")
                    ->orWhere('categories_analisis_bibliometrik.mulai', 'LIKE', "%{$search}%")
                    ->orWhere('categories_analisis_bibliometrik.selesai', 'LIKE', "%{$search}%")
                    ->orWhere('analisis_bibliometrik.total_pembayaran', 'LIKE', "%{$search}%")
                    ->orWhere('analisis_bibliometrik.status', 'LIKE', "%{$search}%");
            });

        // Filter tanggal kalau diinput
        if ($startDate && $endDate) {
            $query->whereBetween('analisis_bibliometrik.created_at', [$currentMonth, $nextMonth]);
        }

        // Ambil data
        $datas = $query->latest('analisis_bibliometrik.created_at')->paginate(10);
        $datas->appends($request->only(['q', 'tanggal_awal', 'tanggal_akhir']));

        // Kalau kosong, kembalikan dengan pesan error
        if ($datas->isEmpty()) {
            return redirect()->route('account.analisisbibliometrik.index')
                ->with('error', 'Data Pendaftaran Peserta tidak ditemukan.');
        }

        return view('account.analisis_bibliometrik.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== FILTER ==================-->
    public function filter(Request $request)
    {
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        if (!$startDate || !$endDate) {
            $startDate = date('Y-m-01 00:00:00');
            $endDate = date('Y-m-d 23:59:59', strtotime('+1 month'));
        } else {
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
        }

        $datas = DB::table('analisis_bibliometrik')
            ->join('categories_analisis_bibliometrik', 'analisis_bibliometrik.categories_analisis_bibliometrik_id', '=', 'categories_analisis_bibliometrik.id')
            ->select(
                'analisis_bibliometrik.*',
                'categories_analisis_bibliometrik.nama as kategori_nama',
                'categories_analisis_bibliometrik.nama_ke as kategori_nama_ke',
                'categories_analisis_bibliometrik.mulai as kategori_tanggal_mulai',
                'categories_analisis_bibliometrik.selesai as kategori_tanggal_selesai',
                'categories_analisis_bibliometrik.id as kategori_id'
            )
            ->whereBetween('analisis_bibliometrik.created_at', [$startDate, $endDate])
            ->orderBy('analisis_bibliometrik.created_at', 'DESC')
            ->paginate(10)
            ->appends($request->except('page'));

        return view('account.analisis_bibliometrik.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== DOWNLOAD ==================-->
    public function downloadExcel(Request $request)
    {
        // (Opsional) kalau data besar
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $search    = $request->input('q');
        $startDate = $request->input('tanggal_awal');
        $endDate   = $request->input('tanggal_akhir');

        $tanggal_awal  = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $tanggal_akhir = $endDate   ? Carbon::parse($endDate)->endOfDay()   : Carbon::now()->endOfMonth();

        $query = DB::table('analisis_bibliometrik')
            ->join(
                'categories_analisis_bibliometrik',
                'analisis_bibliometrik.categories_analisis_bibliometrik_id',
                '=',
                'categories_analisis_bibliometrik.id'
            )
            ->select(
                'analisis_bibliometrik.*',
                'categories_analisis_bibliometrik.nama   as kategori_nama',
                'categories_analisis_bibliometrik.nama_ke as kategori_nama_ke',
                'categories_analisis_bibliometrik.group_wa as kategori_group_wa',
                'categories_analisis_bibliometrik.mulai  as kategori_tanggal_mulai',
                'categories_analisis_bibliometrik.selesai as kategori_tanggal_selesai',
                'categories_analisis_bibliometrik.biaya  as biaya',
                'analisis_bibliometrik.ppn',
                'analisis_bibliometrik.kode_unik',
                'analisis_bibliometrik.nominal_diskon'
            )
            ->whereBetween('analisis_bibliometrik.created_at', [$tanggal_awal, $tanggal_akhir]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('analisis_bibliometrik.id_transaksi', 'LIKE', "%{$search}%")
                    ->orWhere('analisis_bibliometrik.nama', 'LIKE', "%{$search}%")
                    ->orWhere('categories_analisis_bibliometrik.nama', 'LIKE', "%{$search}%")
                    ->orWhere('categories_analisis_bibliometrik.nama_ke', 'LIKE', "%{$search}%")
                    ->orWhere('categories_analisis_bibliometrik.mulai', 'LIKE', "%{$search}%")
                    ->orWhere('categories_analisis_bibliometrik.selesai', 'LIKE', "%{$search}%")
                    ->orWhere('analisis_bibliometrik.total_pembayaran', 'LIKE', "%{$search}%")
                    ->orWhere('analisis_bibliometrik.status', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('analisis_bibliometrik.created_at', 'desc')->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk diexport pada periode/keyword tersebut.');
        }

        $filename = 'Pendaftaran-Analisis-Bibliometrik_' . now()->format('Ymd_His') . '.xlsx';

        // ⤵️ sekarang constructor export menerima tanggal juga
        return Excel::download(
            new PendaftaranAnalisisBibliometrikExport($data, $tanggal_awal, $tanggal_akhir),
            $filename
        );
    }
    // <!--================== END ==================-->
}
