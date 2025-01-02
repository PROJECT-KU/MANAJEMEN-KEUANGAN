<?php

namespace App\Http\Controllers\Publict;

use App\PendaftaranScopusKafe;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\CreatePublicPendaftaranScopusKafeMail;

class PublicPendaftaranScopusKafeController extends Controller
{
    public function generateRandomId($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $id;
    }

    public function create()
    {
        return view('public.scopus_kafe.form_pendaftaran');
    }

    public function store(Request $request)
    {
        $id_pemesanan = $this->generateRandomId(5);

        // Maksimal ukuran file dalam byte (2 MB)
        $maxFileSize = 2 * 1024 * 1024;

        // Validation rules
        $rules = [
            'nama' => 'required|string|max:100|min:5',
            'tanggal_pemesanan' => 'required|date',
            'email' => 'required|email|max:255',
            'telp' => 'required|string|max:15',
            'gambar' => 'required|file|mimes:jpeg,png,jpg|max:' . $maxFileSize / 1024, // 2048 KB = 2 MB
            'sesi' => 'required|not_in:',
        ];

        // Custom validation messages
        $messages = [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama tidak boleh lebih dari 100 karakter.',
            'nama.min' => 'Nama tidak boleh kurang dari 5 karakter.',
            'tanggal_pemesanan.required' => 'Tanggal pemesanan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'telp.required' => 'Nomor telepon wajib diisi.',
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

        // Handle file upload
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $gambarName = time() . '_' . Str::uuid() . '_pendaftaran_scopus_kafe.' . $file->getClientOriginalExtension();
            $gambarPathFirst = 'pendaftaran_scopus_kafe/' . $gambarName;
            $file->move(public_path('pendaftaran_scopus_kafe'), $gambarName);
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

        $save = PendaftaranScopusKafe::create([
            'id_pemesanan'                  => $id_pemesanan,

            // detail data diri
            'nama'                          => $request->input('nama'),
            'tanggal_pemesanan'             => $request->input('tanggal_pemesanan'),
            'email'                         => $request->input('email'),
            'telp'                          => $request->input('telp'),

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

        if ($save) {
            $datas = PendaftaranScopusKafe::findOrFail($save->id);
            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');

            Mail::to($emailTo)->send(new CreatePublicPendaftaranScopusKafeMail($datas, $save, $appName));

            return redirect()->route('public.scopuskafe.index')->with('success', 'Data Pendaftaran Scopus Kafe Berhasil Disimpan!');
        } else {
            return redirect()->route('public.scopuskafe.index')->with('error', 'Data Pendaftaran Scopus Kafe Gagal Disimpan!');
        }
    }
}
