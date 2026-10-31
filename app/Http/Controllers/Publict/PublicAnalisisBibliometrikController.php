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

        /*
         * MODEL, bukan DB::table().
         *
         * Baris mentah stdClass tidak punya accessor, jadi
         * `harga_setelah_promo` padanya adalah properti yang tidak ada —
         * halamannya 500 dengan "Undefined property", dan tanpa accessor itu
         * harga promo tidak akan pernah terlihat di halaman ini.
         */
        $categories = CategoriesAnalisisBibliometrik::where('layanan', 'bibliometrik')
            ->where('status', 'active')
            ->latest()
            ->paginate(6);

        // Promonya dimuat berkelompok: satu kueri untuk seluruh halaman,
        // bukan satu per kartu.
        \App\Support\PromoAngkatan::muat($categories->getCollection());

        return view('public.analisis_bibliometrik.index', compact('categories'));
    }
    // <!--================== END ==================-->

    // <!--================== NAMPILIN DETAIL KATEGORI ==================-->
    public function selengkapnya($id)
    {
        $item = CategoriesAnalisisBibliometrik::findOrFail($id);
        $terbaru = CategoriesAnalisisBibliometrik::orderBy('created_at', 'desc')->take(6)->get();

        return view('public.analisis_bibliometrik.selengkapnya', compact('item', 'terbaru'));
    }
    // <!--================== END ==================-->

    // <!--================== NAMPILIN FORM PENDAFTARAN ==================-->
    public function FormPendaftaran($id)
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

        $diskon = DB::table('kategori_layanan')->where('layanan', 'bibliometrik')
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
        /*
         * Nomornya dirakit di SATU tempat untuk kelima layanan.
         *
         * Dulu tiap pengendali umum punya salinan pembuat nomornya sendiri —
         * lima aksara acak — sementara jalur panitia memakai pola berawalan
         * dan bertanggal. Dua pembuat nomor untuk satu layanan berarti bentuk
         * nomornya bergantung pada SIAPA yang mendaftarkan, dan itu persis
         * yang tidak boleh terjadi: pendaftar yang mendaftar sendiri dan yang
         * didaftarkan panitia harus menerima nomor yang sama bentuknya.
         */
        $id_transaksi = \App\Support\PendaftaranSemuaLayanan::nomorBaru('bibliometrik');

        // MENGHITUNG JUMLAH SISA KUOTA YANG TIDAK BOLEH MELEBIHI TOTAL KUOTA
        $kategoriId = $request->input('kategori_id');
        $kategori = CategoriesAnalisisBibliometrik::findOrFail($kategoriId);

        // Hitung jumlah pendaftar saat ini
        $jumlahPendaftarBaru = (int) $request->input('jumlah_pendaftar');
        $totalTerdaftarSaatIni = AnalisisBibliometrik::where('kategori_id', $kategoriId)
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

        /*
         * YANG DITERIMA DARI BORANG HANYA KODENYA. Nominalnya dicari peladen.
         *
         * Lubang yang sama persis dengan Scopus Camp, terukur 31 Okt 2026:
         * besar potongannya diambil mentah dari `nominal_diskon` kiriman
         * borang, tanpa satu baris pun yang memeriksa apakah kodenya sah.
         * Kode karangan beserta nominal karangan menghasilkan tagihan
         * sebesar kode uniknya saja.
         */
        $hasilKode = \App\Support\PotonganKode::hitung(
            $kategori,
            $request->input('kode_diskon'),
            (int) $kategori->harga_setelah_promo * $jumlahPendaftarBaru
        );

        $kodeDiskon = $hasilKode['kode'];
        $nominalDiskon = $hasilKode['potongan'] > 0 ? (string) $hasilKode['potongan'] : null;

        /*
         * Harganya DIHITUNG ULANG di peladen, bukan diterima dari borangnya.
         *
         * Sebelum ini `total_pembayaran`, `ppn`, dan `kode_unik` disimpan
         * mentah dari kiriman — nominal yang ditagihkan sepenuhnya ditentukan
         * sisi yang tidak bisa dipercaya.
         *
         * Rumusnya di App\Support\HargaUmum, satu tempat bersama borangnya —
         * jadi angka yang dipajang dan angka yang ditagih tidak bisa berselisih.
         */
        $harga = \App\Support\HargaUmum::hitung(
            $kategori,
            $jumlahPendaftarBaru,
            $request->input('kode_unik'),
            $nominalDiskon
        );

        $cleanPPN = (string) $harga['ppn'];
        $cleanTotalPembayaran = (string) $harga['total'];

        $save = AnalisisBibliometrik::create([
            'token'                                                     => $token,
            'id_transaksi'                                              => $id_transaksi,
            'kategori_id'                       => $request->input('kategori_id'),
            'email'                                                     => $request->input('email'),
            'nama'                                                      => $request->input('nama'),
            'telp'                                                      => $request->input('telp'),
            'affiliasi'                                                 => $request->input('affiliasi'),
            'ppn'                                                       => $cleanPPN,
            'kode_unik'                                                 => $harga['kode_unik'],
            'gambar'                                                    => $imagePath,
            'jumlah_pendaftar'                                          => $jumlahPendaftarBaru,
            'kode_diskon'                                               => $kodeDiskon,
            'nominal_diskon'                                            => $nominalDiskon,
            'total_pembayaran'                                          => $cleanTotalPembayaran,
            // Tali ke promo yang memotong harganya; dibaca layar dampak promo.
            'promo_id'                                                  => $harga['promo']?->getKey(),
            'status'                                                    => $request->input('status') ?? 'diproses',
            'note'                                                      => $request->input('note'),
        ]);

        if ($save) {
            // UPDATE SISA KUOTA
            $totalTerdaftar = AnalisisBibliometrik::where('kategori_id', $kategoriId)
                ->sum('jumlah_pendaftar');

            $kategori->sisa_kuota = max(0, $kategori->total_kuota - $totalTerdaftar);
            $kategori->save();
            // END

            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');

            /*
             * DIBUNGKUS. Alamat email yang salah ketik melempar, dan sampai
             * sekarang lemparan itu membatalkan SELURUH pendaftaran yang
             * barisnya sudah tersimpan, kuotanya sudah terpotong, dan bukti
             * bayarnya sudah terunggah — orangnya melihat halaman galat lalu
             * mendaftar lagi, dan panitia menerima dua baris untuk satu
             * orang. Sama seperti yang sudah dibetulkan di Scopus Camp.
             */
            try {
                Mail::to($emailTo)->send(new AnalisisBibliometrikMail($save, $kategori, $appName));
            } catch (\Throwable $e) {
                report($e);
            }

            /*
             * WhatsApp-nya sepasang dengan suratnya, dan sebabnya sama:
             * tidak semua orang membuka emailnya — dan yang tidak
             * membukanya kehilangan nomor pendaftarannya, satu-satunya hal
             * yang disebut panitia saat pembayarannya ditelusuri.
             *
             * try-nya TERPISAH dari milik surat: yang satu gagal tidak boleh
             * ikut membatalkan yang lain.
             */
            try {
                app(\App\Actions\Pendaftaran\KabariWhatsApp::class)
                    ->pendaftaranMasuk('bibliometrik', $save, $kategori);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Kabar WhatsApp pendaftaran Analisis Bibliometrik gagal.', [
                    'id' => $save->getKey(),
                    'pesan' => $e->getMessage(),
                ]);
            }

            return redirect()->route('public.analisisbibliometrik.index')->with('success', 'Pendaftaran Analisis Bibliometrik Terkirim!');
        } else {
            return redirect()->route('public.analisisbibliometrik.index')->with('error', 'Pendaftaran Analisis Bibliometrik Gagal Terkirim!');
        }
    }

    // <!--================== END ==================-->

}
