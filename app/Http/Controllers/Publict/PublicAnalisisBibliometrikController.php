<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\CategoriesAnalisisBibliometrik;
use App\AnalisisBibliometrik;
use App\Mail\AnalisisBibliometrikMail;
use Illuminate\Support\Facades\Mail;

class PublicAnalisisBibliometrikController extends Controller
{

    // <!--================== NAMPILIN KATEGORI ==================-->
    public function public(Request $request)
    {

        $categories = DB::table('categories_analisis_bibliometrik')
            ->where('status', 'publish')
            ->latest()
            ->paginate(6);

        return view('public.analisis_bibliometrik.index', compact('categories'));
    }
    // <!--================== END ==================-->

    // <!--================== NAMPILIN DETAIL KATEGORI ==================-->
    public function selengkapnya($id, $token)
    {
        $item = CategoriesAnalisisBibliometrik::findOrFail($id);
        $terbaru = CategoriesAnalisisBibliometrik::orderBy('created_at', 'desc')->take(6)->get();

        return view('public.analisis_bibliometrik.selengkapnya', compact('item', 'terbaru'));
    }
    // <!--================== END ==================-->

    // <!--================== NAMPILIN FORM PENDAFTARAN ==================-->
    public function FormPendaftaran($id, $token)
    {
        $item = CategoriesAnalisisBibliometrik::findOrFail($id);
        return view('public.analisis_bibliometrik.form_pendaftaran', compact('item'));
    }
    // <!--================== END ==================-->

    // <!--================== CEK KODE DISKON ==================-->
    public function cekKodeDiskon(Request $request, $id)
    {
        $kode = $request->query('kode_diskon');

        \Log::info('Cek Kode Diskon', [
            'id_diterima' => $id,
            'kode_diterima' => $kode,
        ]);

        $diskon = DB::table('categories_analisis_bibliometrik')
            ->where('id', $id)
            ->first();

        if (!$diskon) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan. ID: ' . $id,
            ]);
        }

        if (is_null($diskon->kode_diskon)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode diskon tidak tersedia untuk Batch ini.',
            ]);
        }

        if ($diskon->kode_diskon !== $kode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode diskon tidak cocok.',
            ]);
        }

        $nominal = trim((string)$diskon->nominal_diskon);
        $casted = is_numeric($nominal) ? (int)$nominal : 0;

        return response()->json([
            'status' => 'success',
            'kode_input' => $kode,
            'nominal_diskon' => $nominal,
            'casted_nominal_diskon' => $casted,
        ]);
    }
    // <!--================== END ==================-->

    // <!--================== KIRIM DATA PENDAFTARAN ==================-->
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

    public function store(Request $request)
    {
        $token = $this->generateRandomToken(30);
        $id_transaksi = $this->generateRandomId(5);

        // MENGHITUNG JUMLAH SISA KUOTA YANG TIDAK BOLEH MELEBIHI TOTAL KUOTA
        $kategoriId = $request->input('categories_analisis_bibliometrik_id');
        $kategori = CategoriesAnalisisBibliometrik::findOrFail($kategoriId);

        // Hitung jumlah pendaftar saat ini
        $jumlahPendaftarBaru = (int) $request->input('jumlah_pendaftar');
        $totalTerdaftarSaatIni = AnalisisBibliometrik::where('categories_analisis_bibliometrik_id', $kategoriId)
            ->sum('jumlah_pendaftar');

        $totalSetelahPendaftaran = $totalTerdaftarSaatIni + $jumlahPendaftarBaru;

        if ($totalSetelahPendaftaran > $kategori->total_kuota) {
            return back()->with('error', 'Pendaftaran gagal! Jumlah pendaftar melebihi kuota maksimal.');
        }
        // END

        // SAVE IMAGE
        $imagePath = null;
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '_Tumbnail_Analisis_Bibliometrik.' . $image->getClientOriginalExtension();
            $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
            $image->move(public_path('bibliometrik'), $imageName); // Pindahkan gambar ke direktori public/images
        }
        // END

        // CLEAR FORMAT RUPIAH
        $cleanPPN = str_replace('.', '', $request->input('ppn'));
        $cleanNominalDiskon = str_replace('.', '', $request->input('nominal_diskon'));
        $cleanTotalPembayaran = str_replace('.', '', $request->input('total_pembayaran'));
        // END

        // CEK APAKAH KODE DISKON NULL ATAU TIDAK
        $kodeDiskon = $request->input('kode_diskon');
        $nominalDiskon = null;

        if (!empty($kodeDiskon)) {
            $nominalDiskon = str_replace('.', '', $request->input('nominal_diskon'));
        }
        // END

        $save = AnalisisBibliometrik::create([
            'token'                                                     => $token,
            'id_transaksi'                                              => $id_transaksi,
            'categories_analisis_bibliometrik_id'                       => $request->input('categories_analisis_bibliometrik_id'),
            'email'                                                     => $request->input('email'),
            'nama'                                                      => $request->input('nama'),
            'telp'                                                      => $request->input('telp'),
            'affiliasi'                                                 => $request->input('affiliasi'),
            'ppn'                                                       => $cleanPPN,
            'kode_unik'                                                 => $request->input('kode_unik'),
            'gambar'                                                    => $imagePath,
            'jumlah_pendaftar'                                          => $jumlahPendaftarBaru,
            'kode_diskon'                                               => $kodeDiskon,
            'nominal_diskon'                                            => $nominalDiskon,
            'total_pembayaran'                                          => $cleanTotalPembayaran,
            'status'                                                    => $request->input('status') ?? 'diproses',
            'note'                                                      => $request->input('note'),
        ]);

        if ($save) {
            // UPDATE SISA KUOTA
            $totalTerdaftar = AnalisisBibliometrik::where('categories_analisis_bibliometrik_id', $kategoriId)
                ->sum('jumlah_pendaftar');

            $kategori->sisa_kuota = max(0, $kategori->total_kuota - $totalTerdaftar);
            $kategori->save();
            // END

            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');

            Mail::to($emailTo)->send(new AnalisisBibliometrikMail($save, $kategori, $appName));
            return redirect()->route('public.analisisbibliometrik.index')->with('success', 'Pendaftaran Analisis Bibliometrik Terkirim!');
        } else {
            return redirect()->route('public.analisisbibliometrik.index')->with('error', 'Pendaftaran Analisis Bibliometrik Gagal Terkirim!');
        }
    }

    // <!--================== END ==================-->

}
