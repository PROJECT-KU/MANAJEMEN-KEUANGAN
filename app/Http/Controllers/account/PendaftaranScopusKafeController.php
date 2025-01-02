<?php

namespace App\Http\Controllers\account;

use App\PendaftaranScopusKafe;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Mail\UpdatePublicPendaftaranScopusKafeMail;
use Illuminate\Support\Facades\Mail;

class PendaftaranScopusKafeController extends Controller
{
    // <!--================== TAMPILAN DATA ==================-->
    public function index(Request $request)
    {
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        // Tentukan rentang tanggal
        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 23:59:59', strtotime($endDate)); // Akhiri pada akhir hari
        }

        $datas = DB::table('pendaftaran_scopus_kafe')
            ->whereBetween('pendaftaran_scopus_kafe.tanggal_pemesanan', [$currentMonth, $nextMonth])
            ->orderBy('pendaftaran_scopus_kafe.created_at', 'DESC')
            ->paginate(10);

        return view('account.pendaftaran_scopus_kafe.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== FILTER & SEARCH ==================-->
    public function filter(Request $request)
    {
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        // Tentukan rentang tanggal
        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 23:59:59', strtotime($endDate)); // Akhiri pada akhir hari
        }

        // Ambil data meme untuk paginasi
        $datas = DB::table('pendaftaran_scopus_kafe')
            ->whereBetween('pendaftaran_scopus_kafe.tanggal_pemesanan', [$currentMonth, $nextMonth])
            ->orderBy('pendaftaran_scopus_kafe.created_at', 'DESC')
            ->paginate(10);

        // Kembalikan view dengan semua data yang diperlukan
        return view('account.pendaftaran_scopus_kafe.index', compact('datas', 'startDate', 'endDate'));
    }

    public function search(Request $request)
    {
        $search = $request->get('q');

        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
        }

        $datas = DB::table('pendaftaran_scopus_kafe')
            ->where(function ($query) use ($search) {
                $query->orWhere('pendaftaran_scopus_kafe.nama', 'LIKE', '%' . $search . '%')
                    ->orWhere('pendaftaran_scopus_kafe.id_pemesanan', 'LIKE', '%' . $search . '%')
                    ->orWhere('pendaftaran_scopus_kafe.total_keseluruhan_pembayaran', 'LIKE', '%' . $search . '%')
                    ->orWhere('pendaftaran_scopus_kafe.status', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('pendaftaran_scopus_kafe.created_at', 'DESC')
            ->paginate(10);
        $datas->appends(['q' => $search]);

        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        if ($datas->isEmpty()) {
            return redirect()->route('account.pendaftaran_scopus_kafe.index')->with('error', 'Data Pendaftaran tidak ditemukan.');
        }
        return view('account.pendaftaran_scopus_kafe.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== EDIT DATA ==================-->
    public function edit(Request $request, $id)
    {
        $datas = PendaftaranScopusKafe::findOrFail($id);
        return view('account.pendaftaran_scopus_kafe.edit', compact('datas'));
    }

    public function update(Request $request, $id)
    {
        $datas = PendaftaranScopusKafe::findOrFail($id);

        // Maksimal ukuran file dalam byte (2 MB)
        $maxFileSize = 2 * 1024 * 1024;

        // Validation rules
        $rules = [
            'nama' => 'string|max:100|min:5',
            'tanggal_pemesanan' => 'date',
            'email' => 'email|max:255',
            'telp' => 'string|max:15',
            'gambar' => 'file|mimes:jpeg,png,jpg|max:' . $maxFileSize / 1024, // 2048 KB = 2 MB
            'sesi' => 'not_in:',
        ];

        // Custom validation messages
        $messages = [
            'gambar.required' => 'Gambar wajib diunggah.',
            'gambar.mimes' => 'Gambar harus dalam format jpeg, png, atau jpg',
            'gambar.max' => 'Ukuran gambar tidak boleh melebihi 2 MB.',
            'sesi.required' => 'Sesi wajib dipilih.',
            'sesi.not_in' => 'Silakan pilih sesi yang valid.',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle File Anatomy Upload
        if ($request->hasFile('gambar')) { // Pastikan ini sesuai dengan input form
            $file = $request->file('gambar'); // Gunakan 'gambar', bukan 'file_anatomi'
            $gambarName = time() . '_' . Str::uuid() . '_pendaftaran_scopus_kafe.' . $file->getClientOriginalExtension();
            $gambarPathFirst = 'pendaftaran_scopus_kafe/' . $gambarName;

            // Periksa dan hapus file lama jika ada
            if ($datas->gambar && file_exists(public_path($datas->gambar))) {
                unlink(public_path($datas->gambar));
            }

            // Pindahkan file yang baru diunggah ke direktori tujuan
            $file->move(public_path('pendaftaran_scopus_kafe'), $gambarName);
            $datas->gambar = $gambarPathFirst; // Perbarui jalur file di database
        } else {
            $gambarPathFirst = $datas->gambar; // Jika tidak ada file baru, gunakan yang lama
        }

        // form sesi 1
        $biaya = $request->input('biaya');
        $biaya = empty($biaya) ? 0 : str_replace(",", "", $biaya);
        $kode_unik_pembayaran = $request->input('kode_unik_pembayaran');
        $kode_unik_pembayaran = empty($kode_unik_pembayaran) ? 0 : str_replace(",", "", $kode_unik_pembayaran);
        $subtotal_pembayaran = $request->input('subtotal_pembayaran');
        $subtotal_pembayaran = empty($subtotal_pembayaran) ? 0 : str_replace(",", "", $subtotal_pembayaran);

        // form sesi 2
        $biaya_kedua = $request->input('biaya_kedua');
        $biaya_kedua = empty($biaya_kedua) ? 0 : str_replace(",", "", $biaya_kedua);
        $kode_unik_pembayaran_kedua = $request->input('kode_unik_pembayaran_kedua');
        $kode_unik_pembayaran_kedua = empty($kode_unik_pembayaran_kedua) ? 0 : str_replace(",", "", $kode_unik_pembayaran_kedua);
        $subtotal_pembayaran_kedua = $request->input('subtotal_pembayaran_kedua');
        $subtotal_pembayaran_kedua = empty($subtotal_pembayaran_kedua) ? 0 : str_replace(",", "", $subtotal_pembayaran_kedua);

        // form sesi 3
        $biaya_ketiga = $request->input('biaya_ketiga');
        $biaya_ketiga = empty($biaya_ketiga) ? 0 : str_replace(",", "", $biaya_ketiga);
        $kode_unik_pembayaran_ketiga = $request->input('kode_unik_pembayaran_ketiga');
        $kode_unik_pembayaran_ketiga = empty($kode_unik_pembayaran_ketiga) ? 0 : str_replace(",", "", $kode_unik_pembayaran_ketiga);
        $subtotal_pembayaran_ketiga = $request->input('subtotal_pembayaran_ketiga');
        $subtotal_pembayaran_ketiga = empty($subtotal_pembayaran_ketiga) ? 0 : str_replace(",", "", $subtotal_pembayaran_ketiga);

        $total_keseluruhan_pembayaran = $request->input('total_keseluruhan_pembayaran');
        $total_keseluruhan_pembayaran = empty($total_keseluruhan_pembayaran) ? 0 : str_replace(",", "", $total_keseluruhan_pembayaran);

        $datas->update([
            // detail data diri
            'nama'                          => $request->input('nama'),
            'tanggal_pemesanan'             => $request->input('tanggal_pemesanan'),
            'email'                         => $request->input('email'),
            'telp'                          => $request->input('telp'),
            'status'                        => $request->input('status'),

            // form sesi 1
            'sesi'                          => $request->input('sesi'),
            'waktu_mulai'                   => $request->input('waktu_mulai'),
            'waktu_selesai'                 => $request->input('waktu_selesai'),
            'lokasi'                        => $request->input('lokasi'),
            'biaya'                         => $biaya,
            'kode_unik_pembayaran'          => $kode_unik_pembayaran,
            'subtotal_pembayaran'           => $subtotal_pembayaran,

            // form sesi 2
            'sesi_kedua'                          => $request->input('sesi_kedua'),
            'waktu_mulai_kedua'                   => $request->input('waktu_mulai_kedua'),
            'waktu_selesai_kedua'                 => $request->input('waktu_selesai_kedua'),
            'lokasi_kedua'                        => $request->input('lokasi_kedua'),
            'biaya_kedua'                         => $biaya_kedua,
            'kode_unik_pembayaran_kedua'          => $kode_unik_pembayaran_kedua,
            'subtotal_pembayaran_kedua'           => $subtotal_pembayaran_kedua,

            // form sesi 3
            'sesi_ketiga'                          => $request->input('sesi_ketiga'),
            'waktu_mulai_ketiga'                   => $request->input('waktu_mulai_ketiga'),
            'waktu_selesai_ketiga'                 => $request->input('waktu_selesai_ketiga'),
            'lokasi_ketiga'                        => $request->input('lokasi_ketiga'),
            'biaya_ketiga'                         => $biaya_ketiga,
            'kode_unik_pembayaran_ketiga'          => $kode_unik_pembayaran_ketiga,
            'subtotal_pembayaran_ketiga'           => $subtotal_pembayaran_ketiga,

            'total_keseluruhan_pembayaran'         => $total_keseluruhan_pembayaran,
            'gambar'                               => $gambarPathFirst ?? null,
        ]);

        if ($datas) {
            $data = PendaftaranScopusKafe::findOrFail($id);
            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');
            $isStatus = $request->input('status') == 'pembayaran diterima';

            if ($isStatus) {
                Mail::to($emailTo)->send(new UpdatePublicPendaftaranScopusKafeMail($datas, $data, $appName, $isStatus));
            }
            return redirect()->route('account.pendaftaran-scopus-kafe.index')->with('success', 'Data Pendaftaran Scopus Kafe Berhasil Disimpan!');
        } else {
            return redirect()->route('account.pendaftaran-scopus-kafe.index')->with('error', 'Data Pendaftaran Scopus Kafe Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy(Request $request, $id)
    {
        // Temukan data berdasarkan ID
        $datas = PendaftaranScopusKafe::findOrFail($id);

        // Array file yang akan dicek dan dihapus
        $files = [
            $datas->gambar,
        ];

        // Iterasi untuk menghapus file
        foreach ($files as $filePath) {
            if ($filePath && file_exists(public_path($filePath))) {
                unlink(public_path($filePath));
            }
        }

        // Hapus data dari database
        $datas->delete();

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus beserta file terkait.',
        ]);
    }
    // <!--================== END ==================-->
}
