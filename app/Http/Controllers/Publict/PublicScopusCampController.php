<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use App\Mail\ScopusCampMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PublicScopusCampController extends Controller
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
         *
         * Promonya dimuat berkelompok di bawah: satu kueri untuk seluruh
         * daftar, bukan satu per kartu.
         */
        $kueri = CategoriesScopusCamp::where('layanan', 'scopus_camp')
            ->where('status', 'active')
            /*
             * Yang tanggal mulainya SUDAH TIBA tidak ikut dipajang.
             *
             * KategoriLayanan::tanggalMulaiSudahTiba() menyebut angkatan
             * begitu sudah tertutup sejak 5 Okt 2026, atas permintaan
             * pemiliknya. Terukur di halaman ini: tiga angkatan yang sudah
             * berjalan masih terpampang lengkap dengan tombol "Daftar
             * Sekarang" — orang membayar acara yang sudah separuh jalan,
             * dan tidak ada galat apa pun yang memberi tahu.
             */
            ->whereDate('mulai', '>', now()->toDateString())
            ->orderBy('mulai', 'asc');

        /*
         * Dipaginasi. Seluruhnya dulu dicetak di satu halaman: terukur 294
         * kartu setinggi 22.711px dengan markah 627 KB. Angka itu memang
         * sebagian besar baris uji di basis data lokal, tetapi batasnya tetap
         * perlu — tanpa itu halaman ini tumbuh selamanya mengikuti jumlah
         * angkatan yang pernah dibuka.
         */
        $categories = $kueri->paginate(12)->withQueryString();

        \App\Support\PromoAngkatan::muat($categories->getCollection());

        /*
         * Fasilitasnya TIDAK sama untuk semua angkatan, dan itu sempat salah
         * di halaman ini.
         *
         * Versi pertama rombakan ini memajang satu daftar fasilitas di kolom
         * kiri, diambil dari angkatan PERTAMA pada halaman itu. Terukur di
         * tarif yang berlaku, selisihnya nyata:
         *
         *   Pulau Jawa      Rp 5.500.000 — 7 butir, termasuk penginapan di
         *                   tempat acara serta mushola, kolam renang, dan
         *                   treadmill;
         *   Luar Pulau Jawa Rp 6.500.000 — 5 butir, TANPA keduanya.
         *
         * Jadi satu daftar untuk semuanya menjanjikan penginapan dan kolam
         * renang kepada peserta luar Jawa — yang justru membayar satu juta
         * lebih mahal. Tidak ada galat; yang menanggungnya panitia yang
         * harus menjelaskan saat orangnya sudah sampai di lokasi.
         *
         * Dikelompokkan menurut ISI daftarnya, bukan menurut kode variannya:
         * angkatan boleh menyimpan salinan fasilitasnya sendiri yang berbeda
         * dari tarif induk, dan pengelompokan menurut kode akan menyatukan
         * dua daftar yang sebenarnya tidak sama.
         */
        $namaVarian = collect(optional(\App\Layanan::where('kode', 'scopus_camp')->first())->varian ?? [])
            ->pluck('nama', 'kode');

        $paket = $categories->getCollection()
            ->groupBy(fn ($a) => json_encode($a->daftar_fasilitas))
            ->map(function ($kelompok) use ($namaVarian) {
                $varian = $kelompok->pluck('varian')->filter()->unique()->values();

                return [
                    'fasilitas' => $kelompok->first()->daftar_fasilitas,
                    'label' => $varian
                        ->map(fn ($v) => $namaVarian[$v] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $v)))
                        ->implode(' & '),
                    'jumlah' => $kelompok->count(),
                ];
            })
            ->filter(fn ($p) => ! empty($p['fasilitas']))
            ->values();

        return view('public.scopus_camp.index', compact('categories', 'paket', 'namaVarian'));
    }
    // <!--================== END ==================-->

    // <!--================== NAMPILIN DETAIL KATEGORI ==================-->
    public function selengkapnya($id)
    {
        $item = CategoriesScopusCamp::findOrFail($id);

        /*
         * Daftar di bilah kanan dulu `orderBy('created_at','desc')->take(6)`
         * — tanpa saringan apa pun.
         *
         * Akibatnya tiga hal yang semuanya terlihat di layar: angkatan yang
         * kuotanya sudah habis ikut ditawarkan, angkatan yang tanggalnya sudah
         * lewat ikut ditawarkan, dan angkatan yang SEDANG DIBUKA kalah urutan
         * oleh baris yang paling baru dibuat. Diurut menurut tanggal
         * acaranya, bukan tanggal pembuatan barisnya: yang dicari orang
         * "kapan yang berikutnya", bukan "mana yang terakhir diketik admin".
         */
        $terbaru = CategoriesScopusCamp::where('layanan', 'scopus_camp')
            ->where('status', 'active')
            ->whereDate('mulai', '>', now()->toDateString())
            ->where(function ($kueri) {
                $kueri->whereNull('total_kuota')->orWhere('sisa_kuota', '>', 0);
            })
            ->whereKeyNot($item->getKey())
            ->orderBy('mulai')
            ->take(5)
            ->get();

        // Tanpa ini, harga di bilah kanan memakai harga penuh sementara
        // halaman daftarnya menjanjikan harga promo.
        \App\Support\PromoAngkatan::muat($terbaru);

        return view('public.scopus_camp.selengkapnya', compact('item', 'terbaru'));
    }
    // <!--================== END ==================-->

    // <!--================== NAMPILIN FORM PENDAFTARAN ==================-->
    public function FormPendaftaran($id)
    {
        $item = CategoriesScopusCamp::findOrFail($id);
        return view('public.scopus_camp.form_pendaftaran', compact('item'));
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

        $diskon = DB::table('kategori_layanan')->where('layanan', 'scopus_camp')
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
        $id_transaksi = \App\Support\PendaftaranSemuaLayanan::nomorBaru('scopus_camp');

        // MENGHITUNG JUMLAH SISA KUOTA YANG TIDAK BOLEH MELEBIHI TOTAL KUOTA
        $kategoriId = $request->input('kategori_id');
        $kategori = CategoriesScopusCamp::findOrFail($kategoriId);

        // Hitung jumlah pendaftar saat ini
        $jumlahPendaftarBaru = (int) $request->input('jumlah_pendaftar');
        $totalTerdaftarSaatIni = PendaftaranScopusCamp::where('kategori_id', $kategoriId)
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

            // Generate nama file dengan UUID
            $imageName = Str::uuid() . '_Bukti_TF_Scopus_Camp.' . $image->getClientOriginalExtension();

            // Simpan path (jika ingin disimpan ke database)
            $imagePath = 'ScopusCamp/' . $imageName;

            // Pindahkan file ke folder public/ScopusCamp
            $image->move(public_path('ScopusCamp'), $imageName);
        }
        // END

        // CEK APAKAH KODE DISKON NULL ATAU TIDAK
        $kodeDiskon = $request->input('kode_diskon');
        $nominalDiskon = null;

        if (!empty($kodeDiskon)) {
            $nominalDiskon = str_replace('.', '', $request->input('nominal_diskon'));
        }
        // END

        /*
         * Harganya DIHITUNG ULANG di peladen, bukan diterima dari borangnya.
         *
         * Sebelum ini `total_pembayaran`, `ppn`, dan `kode_unik` disimpan
         * mentah dari kiriman — nominal yang ditagihkan sepenuhnya ditentukan
         * sisi yang tidak bisa dipercaya, dan siapa pun bisa mendaftar dengan
         * total nol tanpa satu baris pun yang menolak.
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

        $save = PendaftaranScopusCamp::create([
            'token'                                                     => $token,
            'id_transaksi'                                              => $id_transaksi,
            'kategori_id'                                   => $request->input('kategori_id'),
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
            $totalTerdaftar = PendaftaranScopusCamp::where('kategori_id', $kategoriId)
                ->sum('jumlah_pendaftar');

            $kategori->sisa_kuota = max(0, $kategori->total_kuota - $totalTerdaftar);
            $kategori->save();
            // END

            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');

            Mail::to($emailTo)->send(new ScopusCampMail($save, $kategori, $appName));
            return redirect()->route('public.scopuscamp.index')->with('success', 'Pendaftaran Analisis Bibliometrik Terkirim!');
        } else {
            return redirect()->route('public.scopuscamp.index')->with('error', 'Pendaftaran Analisis Bibliometrik Gagal Terkirim!');
        }
    }

    // <!--================== END ==================-->

}
