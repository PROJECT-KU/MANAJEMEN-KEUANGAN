<?php

namespace App\Http\Controllers\account;

use App\PendaftaranScopusCamp;
use App\CategoriesScopusCamp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ScopusCampUpdateDiterimaMail;
use App\Mail\ScopusCampUpdateResheduleMail;
use App\Exports\PendaftaranAnalisisBibliometrikExport;
use Maatwebsite\Excel\Facades\Excel;

class PendaftaranScopusCampController extends Controller
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

        $query = DB::table('scopus_camp_pendaftaran')
            ->join('scopus_camp_kategori', 'scopus_camp_pendaftaran.scopus_camp_kategori_id', '=', 'scopus_camp_kategori.id')
            ->select(
                'scopus_camp_pendaftaran.*',
                'scopus_camp_kategori.nama as kategori_nama',
                'scopus_camp_kategori.nama_ke as kategori_nama_ke',
                'scopus_camp_kategori.mulai as kategori_tanggal_mulai',
                'scopus_camp_kategori.selesai as kategori_tanggal_selesai',
                'scopus_camp_kategori.id as kategori_id'
            )
            ->latest('scopus_camp_pendaftaran.created_at');

        // Filter tanggal
        if ($startDate && $endDate) {
            $query->whereDate('scopus_camp_pendaftaran.created_at', '>=', $startDate)
                ->whereDate('scopus_camp_pendaftaran.created_at', '<=', $endDate);
        }

        $datas = $query->paginate(15);

        return view('account.pendaftaran_scopus_camp.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function edit($id)
    {
        $data = PendaftaranScopusCamp::findOrFail($id);
        $category = CategoriesScopusCamp::find($data->scopus_camp_kategori_id);
        $categories = CategoriesScopusCamp::all();

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

        return view('account.pendaftaran_scopus_camp.edit', compact('data', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $dataUpdate = PendaftaranScopusCamp::findOrFail($id);

        // ===========================
        // CLEAN FORMAT RUPIAH / KONVERSI KE FLOAT
        // ===========================
        $cleanPPN = (float) str_replace('.', '', $request->input('ppn'));
        $cleanKodeUnik = (float) str_replace('.', '', $request->input('kode_unik'));
        $cleanNominalDiskon = (float) str_replace('.', '', $request->input('nominal_diskon') ?? 0);
        $cleanTotalPembayaran = (float) str_replace('.', '', $request->input('total_pembayaran'));

        // ===========================
        // AMBIL KATEGORI LAMA DAN BARU
        // ===========================
        $kategoriLama = CategoriesScopusCamp::find($dataUpdate->scopus_camp_kategori_id);
        $kategoriBaruId = $request->input('scopus_camp_kategori_id');
        $kategoriBaru = CategoriesScopusCamp::find($kategoriBaruId);

        if (!$kategoriBaru) {
            return back()->with('error', 'Kategori Scopus Camp tidak ditemukan.');
        }

        // ===========================
        // HITUNG SELISIH JUMLAH PENDAFTAR
        // ===========================
        $jumlahPendaftarBaru = (int) $request->input('jumlah_pendaftar');
        $jumlahPendaftarLama = $dataUpdate->jumlah_pendaftar;
        $selisih = $jumlahPendaftarBaru - $jumlahPendaftarLama; // positif = bertambah, negatif = berkurang

        // ===========================
        // CEK KUOTA TERSEDIA UNTUK KATEGORI BARU
        // ===========================
        $sisaKuotaBaru = $kategoriBaru->sisa_kuota + ($kategoriBaruId == $kategoriLama->id ? $jumlahPendaftarLama : 0);

        if ($jumlahPendaftarBaru > $sisaKuotaBaru) {
            return back()->with('error', 'Jumlah pendaftar melebihi sisa kuota kategori!');
        }

        // ===========================
        // UPDATE SISA KUOTA KATEGORI LAMA (JIKA BERUBAH)
        // ===========================
        if ($kategoriLama && $kategoriLama->id != $kategoriBaruId) {
            // Kembalikan sisa kuota kategori lama
            $kategoriLama->sisa_kuota += $jumlahPendaftarLama;
            $kategoriLama->save();
        }

        // ===========================
        // UPDATE DATA PENDAFTAR
        // ===========================
        $dataUpdate->update([
            'scopus_camp_kategori_id' => $kategoriBaruId,
            'nama' => $request->input('nama'),
            'email' => $request->input('email'),
            'affiliasi' => $request->input('affiliasi'),
            'telp' => $request->input('telp'),
            'jumlah_pendaftar' => $jumlahPendaftarBaru,
            'ppn' => $cleanPPN,
            'kode_unik' => $cleanKodeUnik,
            'nominal_diskon' => $cleanNominalDiskon,
            'total_pembayaran' => $cleanTotalPembayaran,
            'status' => $request->input('status'),
            'tanggal_reschedule' => $request->input('tanggal_reschedule'),
            'group_wa' => $request->input('group_wa'),
            'note' => $request->input('note'),
        ]);

        // ===========================
        // UPDATE SISA KUOTA KATEGORI BARU
        // ===========================
        if ($selisih != 0) {
            $kategoriBaru->sisa_kuota -= $selisih;
            if ($kategoriBaru->sisa_kuota < 0) $kategoriBaru->sisa_kuota = 0;
            $kategoriBaru->save();
        }

        // ===========================
        // KIRIM EMAIL SESUAI STATUS
        // ===========================
        $appName = 'Rumah Scopus Foundation';
        $emailTo = $dataUpdate->email;

        if ($dataUpdate->status === 'Pendaftaran Diterima') {
            Mail::to($emailTo)->send(new ScopusCampUpdateDiterimaMail($dataUpdate, $kategoriBaru, $appName));
        } elseif ($dataUpdate->status === 'Pendaftaran Reschedule') {
            Mail::to($emailTo)->send(new ScopusCampUpdateResheduleMail($dataUpdate, $kategoriBaru, $appName));
        }

        return redirect()->route('account.pendaftaranscopuscamp.index')->with('success', 'Data Pendaftaran Scopus Camp Berhasil Disimpan!');
    }
    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy($id)
    {
        try {
            $data = PendaftaranScopusCamp::find($id);

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan!'
                ], 404);
            }

            // Ambil kategori terkait
            $kategori = CategoriesScopusCamp::find($data->scopus_camp_kategori_id);
            if ($kategori) {
                // Kembalikan sisa kuota sesuai jumlah pendaftar yang dihapus
                $kategori->sisa_kuota += $data->jumlah_pendaftar;

                // Pastikan sisa_kuota tidak melebihi total_kuota
                if ($kategori->sisa_kuota > $kategori->total_kuota) {
                    $kategori->sisa_kuota = $kategori->total_kuota;
                }

                $kategori->save();
            }

            // Hapus gambar jika ada
            if ($data->gambar) {
                $filePath = public_path('ScopusCamp/' . basename($data->gambar));
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Hapus data pendaftaran
            $data->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data dan gambar berhasil dihapus, sisa kuota kategori telah diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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

        $query = DB::table('scopus_camp_pendaftaran as p')
            ->join('scopus_camp_kategori as k', 'p.scopus_camp_kategori_id', '=', 'k.id')
            ->where(function ($query) use ($search) {
                $query->where('p.id_transaksi', 'LIKE', '%' . $search . '%')
                    ->orWhere('p.nama', 'LIKE', '%' . $search . '%')
                    ->orWhere('p.total_pembayaran', 'LIKE', '%' . $search . '%')
                    ->orWhere('p.status', 'LIKE', '%' . $search . '%')
                    // 🔽 SEARCH NAMA_KE dari tabel kategori
                    ->orWhere('k.nama_ke', 'LIKE', '%' . $search . '%')
                    ->orWhere('k.nama', 'LIKE', '%' . $search . '%')

                    // 🔽 SEARCH TANGGAL MULAI dari tabel kategori
                    ->orWhereRaw("DATE_FORMAT(k.mulai, '%e %M %Y') LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("DATE_FORMAT(k.mulai, '%d-%m-%Y') LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("DATE_FORMAT(k.mulai, '%Y') LIKE ?", ['%' . $search . '%'])

                    // 🔽 SEARCH TANGGAL SELESAI dari tabel kategori
                    ->orWhereRaw("DATE_FORMAT(k.selesai, '%e %M %Y') LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("DATE_FORMAT(k.selesai, '%d-%m-%Y') LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("DATE_FORMAT(k.selesai, '%Y') LIKE ?", ['%' . $search . '%']);
            })
            ->select(
                'p.*',
                'k.nama as kategori_nama',
                'k.nama_ke as kategori_nama_ke',
                'k.mulai as kategori_tanggal_mulai',
                'k.selesai as kategori_tanggal_selesai'
            );


        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$currentMonth, $nextMonth]);
        }

        $categories = $query->orderBy('created_at', 'DESC')->paginate(10);
        $categories->appends(['q' => $search]);

        if ($categories->isEmpty()) {
            return redirect()->route('account.kategoriscopuscamp.index')->with('error', 'Data Laporan Peserta tidak ditemukan.');
        }

        return view('account.pendaftaran_scopus_camp.index', [
            'datas'     => $categories,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);
    }
    // <!--================== END ==================-->


}
