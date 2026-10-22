<?php

namespace App\Http\Controllers\Publict;

use App\Artikel;
use App\CategoriesArtikel;
use App\ArtikelKomentar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublicArticleController extends Controller
{

    function generateRandomToken($length)
    {
        // First character must be a letter
        $firstCharacter = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // Remaining characters can include numbers and symbols
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$&-_?';
        $token = '';

        // Ensure the first character is a letter
        $token .= $firstCharacter[rand(0, strlen($firstCharacter) - 1)];

        // Generate the rest of the token
        for ($i = 1; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }

    // <!--================== PUBLIC ARTIKEL ==================-->

    // HALAMAN AWAL
    /**
     * Daftar tulisan di /blog.
     *
     * Dua hal dibetulkan 19 Okt 2026.
     *
     * PERTAMA, saringan status. Kueri ini dulu mengambil SELURUH baris
     * artikel apa pun statusnya; yang menyaring 'publish' cuma tampilannya,
     * di dalam @php per kategori. Artinya draf yang belum selesai ikut
     * terkirim ke peramban — tersembunyi dari mata, tetapi ada utuh di
     * sumber halaman, termasuk isinya. Siapa pun yang menekan "lihat sumber"
     * membacanya. Sekarang disaring di kueri, jadi yang belum terbit tidak
     * pernah meninggalkan peladen.
     *
     * KEDUA, dua tulisan hilang. Tampilannya mengelompokkan per kategori lalu
     * take(3) tiap kelompok — kategori Journal punya lima, jadi dua di
     * antaranya tidak pernah muncul di /blog sama sekali. Halaman barunya
     * memajang semuanya.
     *
     * Perhitungan $currentMonth/$nextMonth yang lama dibuang: keduanya
     * dihitung lalu tidak pernah dipakai di kueri mana pun.
     */
    /** Sebanyak ini tulisan per halaman di /blog. */
    private const PER_HALAMAN = 9;

    public function public(Request $request)
    {
        $cari = trim((string) $request->input('cari'));

        $kueri = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('artikel.status', 'publish')
            ->orderBy('artikel.created_at', 'DESC');

        /*
         * Pencarian menyapu judul, kata kunci, DAN isinya.
         *
         * Judul saja tidak cukup: orang mencari "predator" sementara
         * judulnya "Cara Menghindari Jurnal Cloning dan Jurnal Predator" —
         * itu masih ketemu — tetapi ia juga mencari "reviewer" atau
         * "sitasi", kata yang hampir selalu ada di badan tulisan dan hampir
         * tidak pernah di judul. Pencarian yang diam untuk kata yang jelas
         * ada di situs terbaca sebagai situs yang tidak punya tulisannya.
         *
         * Tanda % dan _ miliknya pengguna di-escape: tanpa itu, mengetik "%"
         * memulangkan SELURUH tulisan, dan yang mengetiknya menyimpulkan
         * pencariannya rusak.
         */
        if ($cari !== '') {
            $pola = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cari) . '%';

            $kueri->where(function ($q) use ($pola) {
                $q->where('artikel.judul', 'like', $pola)
                    ->orWhere('artikel.kata_kunci', 'like', $pola)
                    ->orWhere('artikel.isi', 'like', $pola);
            });
        }

        /*
         * Dipaginasi. Seluruhnya dulu dicetak di satu halaman — dua belas
         * tulisan masih muat, lima puluh tidak, dan halaman inilah yang
         * paling dulu jebol begitu tulisannya bertambah.
         *
         * withQueryString: tanpa itu, menekan "2" pada hasil pencarian
         * membuang kata yang dicari dan memulangkan seluruh tulisan — orang
         * mengira hasilnya berubah sendiri.
         */
        $halaman = $kueri->paginate(self::PER_HALAMAN)->withQueryString();

        $artikel = collect($halaman->items());

        /*
         * Kategori yang KOSONG tidak ditawarkan.
         *
         * Keripik kategori di halaman barunya mengantar orang ke halaman
         * kategori; yang tidak punya tulisan terbit mengantarnya ke halaman
         * kosong, dan halaman kosong terbaca sebagai situs yang rusak.
         */
        /*
         * Hitungan keripik dari SELURUH tulisan terbit, bukan dari halaman
         * yang sedang dibuka. Dihitung dari $artikel, angkanya berubah tiap
         * kali orang pindah halaman — "Journal 3" di halaman satu, "Journal
         * 2" di halaman dua — untuk kategori yang isinya tidak berubah.
         */
        $jumlahPerKategori = DB::table('artikel')
            ->where('status', 'publish')
            ->whereNotNull('categories_artikel_id')
            ->selectRaw('categories_artikel_id, COUNT(*) as jumlah')
            ->groupBy('categories_artikel_id')
            ->pluck('jumlah', 'categories_artikel_id');

        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token')
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->get()
            ->filter(fn ($k) => ($jumlahPerKategori[$k->id] ?? 0) > 0)
            ->values();

        return view('public.article.public', compact('artikel', 'categories_artikel', 'jumlahPerKategori', 'halaman', 'cari'));
    }

    /**
     * Umpan RSS blog.
     *
     * Sebelum ini satu-satunya cara "berlangganan" adalah tombol di layar
     * pertama yang berbunyi "Langganan Update Artikel" lalu membuka kanal
     * YouTube — bukan tempat tulisannya terbit.
     *
     * Dibatasi dua puluh tulisan terbaru: umpan memuat isi penuh tiap
     * tulisan, dan yang memuat seluruh arsip tumbuh tanpa batas sampai
     * pembacanya berhenti mengunduhnya.
     */
    public function rss()
    {
        $artikel = DB::table('artikel')
            ->select('artikel.id', 'artikel.judul', 'artikel.isi', 'artikel.gambar_depan',
                'artikel.created_at', 'artikel.updated_at', 'categories_artikel.kategori')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('artikel.status', 'publish')
            ->orderBy('artikel.created_at', 'DESC')
            ->limit(20)
            ->get();

        /*
         * Content-Type-nya application/rss+xml, bukan text/html. Dengan tipe
         * yang salah, peramban menggambarnya sebagai halaman kosong dan
         * pembaca umpan menolaknya — padahal isinya sudah benar.
         */
        return response()
            ->view('public.article.rss', ['artikel' => $artikel])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /**
     * Peta situs.
     *
     * /sitemap.xml memulangkan 404 sebelum ini. robots.txt ada dan terbuka,
     * tetapi tidak ada yang menuntun mesin pencari ke kedua belas tulisan —
     * ia harus menemukannya sendiri dengan menelusuri tautan.
     *
     * Halaman layanan ikut didaftar, bukan cuma blog: peta situs memang
     * untuk seluruh situs, dan halaman-halaman itulah yang menjual.
     */
    public function sitemap()
    {
        $artikel = DB::table('artikel')
            ->select('id', 'created_at', 'updated_at')
            ->where('status', 'publish')
            ->orderBy('created_at', 'DESC')
            ->get();

        $kategori = DB::table('categories_artikel')
            ->join('artikel', function ($gabung) {
                $gabung->on('categories_artikel.id', '=', 'artikel.categories_artikel_id')
                    ->where('artikel.status', '=', 'publish');
            })
            ->select('categories_artikel.id')
            ->distinct()
            ->pluck('categories_artikel.id');

        /*
         * Halaman tetap ditulis sebagai jalur, bukan dirakit dari daftar
         * rute: tidak semua rute pantas masuk peta situs — borang
         * pendaftaran, halaman status ber-UUID, dan alur pembayaran justru
         * tidak boleh diindeks.
         */
        $halaman = [
            ['/', '1.0', 'daily'],
            ['/blog', '0.9', 'daily'],
            ['/Scopus-Camp', '0.9', 'weekly'],
            ['/Webinar-Eksklusif', '0.9', 'weekly'],
            ['/Analisis-Bibliometrik', '0.8', 'weekly'],
            ['/Scopus-Kafe', '0.8', 'weekly'],
            ['/Clinik-Scopus', '0.8', 'weekly'],
            ['/Refrensi-Paper', '0.6', 'weekly'],
            ['/paperisasi/public/data', '0.5', 'monthly'],
            ['/contact', '0.5', 'monthly'],
            ['/ketentuan-layanan', '0.3', 'yearly'],
        ];

        return response()
            ->view('public.article.sitemap', compact('artikel', 'kategori', 'halaman'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    // MENAMPILKAN ARTIKEL BERDASARKAN KATEGORI
    public function publickategori(Request $request, $categories_artikel_id)
    {
        $articlesQuery = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('categories_artikel.id', $categories_artikel_id)
            ->where('artikel.status', 'publish')
            ->orderBy('artikel.created_at', 'DESC');

        $totalArticles = $articlesQuery->count();

        $articles = $articlesQuery->paginate(9);

        /*
         * Hitungannya CUMA yang terbit.
         *
         * COUNT(artikel.id) tanpa syarat status membuat keripik di sini
         * menyebut angka yang berbeda dari keripik yang sama di /blog —
         * "Journal 7" di satu halaman, "Journal 5" di halaman berikutnya,
         * untuk daftar yang sama persis. Yang melihatnya menyimpulkan ada
         * tulisan yang hilang.
         *
         * Kategori yang kosong ikut disaring, dengan alasan yang sama
         * seperti di /blog: tidak ada keripik yang mengantar ke halaman
         * tanpa isi.
         */
        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->leftJoin('artikel', function ($gabung) {
                $gabung->on('categories_artikel.id', '=', 'artikel.categories_artikel_id')
                    ->where('artikel.status', '=', 'publish');
            })
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token', DB::raw('COUNT(artikel.id) as jumlah_artikel'))
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->groupBy('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori')
            ->having('jumlah_artikel', '>', 0)
            ->get();

        // Kategori yang sedang dibuka: untuk judul halaman, <title>, dan
        // penanda keripik mana yang sedang aktif.
        $kategoriIni = $categories_artikel->firstWhere('id', $categories_artikel_id)
            ?? DB::table('categories_artikel')->where('id', $categories_artikel_id)->first();

        return view('public.article.publickategori', compact('articles', 'categories_artikel', 'totalArticles', 'kategoriIni'));
    }

    // MENAMPILKAN DATA ARTIKEL 
    public function blogsingle($id)
    {
        $user = Auth::user();
        $artikel = Artikel::findOrFail($id);

        $datas = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('artikel.status', 'publish')
            ->orderBy('artikel.created_at', 'DESC')
            ->get();

        $artikel_komentar = DB::table('artikel_komentar')
            ->select('artikel_komentar.id', 'artikel_komentar.categories_artikel_id', 'artikel_komentar.artikel_id', 'artikel_komentar.reply', 'artikel_komentar.token', 'artikel_komentar.nama', 'artikel_komentar.email', 'artikel_komentar.link_website', 'artikel_komentar.komentar', 'artikel_komentar.created_at')
            ->join('artikel', 'artikel_komentar.artikel_id', '=', 'artikel.id')
            // Hanya yang sudah dilepas orang dalam. Tanpa saringan ini,
            // kolom status yang baru ditambahkan tidak mengubah apa pun:
            // komentar tetap tampil seketika seperti sebelumnya.
            ->where('artikel_komentar.status', 'terbit')
            ->orderBy('artikel_komentar.created_at', 'DESC')
            ->get();

        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->leftJoin('artikel', 'categories_artikel.id', '=', 'artikel.categories_artikel_id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token', DB::raw('COUNT(artikel.id) as jumlah_artikel'))
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->groupBy('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori')
            ->get();

        /*
         * Satu kenaikan, dikerjakan peladen basis data.
         *
         * Bentuk lamanya baca-tambah-simpan: $artikel->dilihat += 1 lalu
         * save(). Dua orang yang membuka tulisan yang sama pada saat
         * bersamaan sama-sama membaca nilai lama, sama-sama menyimpan
         * nilai+1 yang sama, dan satu bacaan hilang tanpa jejak.
         *
         * Bentuk itu juga MENIMPA apa pun yang menaikkan kolom ini di
         * tengah permintaan yang sama: modelnya sudah dimuat di awal dan
         * membawa nilai basi sampai akhir. Sempat ada increment() kedua
         * disisipkan di awal metode ini, dan hasilnya tetap +1 — kenaikannya
         * ditelan save() di bawah, tanpa galat dan tanpa selisih yang
         * kelihatan.
         *
         * save() juga menggerakkan updated_at, sehingga daftar "baru
         * disunting" di sisi admin selalu berisi tulisan yang justru tidak
         * disentuh siapa pun — cuma dibaca orang. increment() tidak.
         */
        DB::table('artikel')->where('id', $artikel->id)->increment('dilihat');

        /*
         * Tulisan sebidang, untuk ditawarkan di akhir bacaan.
         *
         * Sebelumnya yang ada cuma "Tulisan terbaru" di bilah samping —
         * daftar yang SAMA PERSIS di kedua belas halaman. Orang yang baru
         * selesai membaca soal jurnal predator tidak pernah ditawari tulisan
         * lain tentang jurnal.
         *
         * Kalau kategorinya belum cukup isinya, sisanya diisi tulisan
         * terbaru mana pun: tiga kotak kosong lebih buruk daripada tiga
         * kotak yang salah satunya kurang nyambung.
         */
        $terkait = DB::table('artikel')
            ->select('artikel.id', 'artikel.judul', 'artikel.gambar_depan', 'artikel.created_at', 'categories_artikel.kategori')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('artikel.status', 'publish')
            ->where('artikel.id', '!=', $artikel->id)
            ->where('artikel.categories_artikel_id', $artikel->categories_artikel_id)
            ->orderByDesc('artikel.created_at')
            ->limit(3)
            ->get();

        if ($terkait->count() < 3) {
            $sudah = $terkait->pluck('id')->push($artikel->id)->all();

            $terkait = $terkait->concat(
                DB::table('artikel')
                    ->select('artikel.id', 'artikel.judul', 'artikel.gambar_depan', 'artikel.created_at', 'categories_artikel.kategori')
                    ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
                    ->where('artikel.status', 'publish')
                    ->whereNotIn('artikel.id', $sudah)
                    ->orderByDesc('artikel.created_at')
                    ->limit(3 - $terkait->count())
                    ->get()
            );
        }

        return view('public.article.blogsingle', compact('artikel', 'datas', 'categories_artikel', 'artikel_komentar', 'terkait'));
    }

    // MENAMBAH DATA KOMENTAR
    /**
     * Menyimpan komentar dari pengunjung.
     *
     * Sampai 22 Okt 2026 metode ini menerima kiriman siapa pun apa adanya:
     * nol validasi, rutenya tanpa batas laju, langsung tampil di halaman,
     * dan tidak ada satu pun layar untuk menghapusnya kembali.
     *
     * Yang paling halus dari semuanya: `user_id` diambil dari
     * $request->input('user_id') — isian borang, bukan sesi. Siapa pun bisa
     * mengirim komentar atas nama id pengguna mana pun, dan di layar ia
     * tampil lengkap dengan nama serta foto orang itu.
     */
    public function storekomentar(Request $request)
    {
        $data = $request->validate([
            'artikel_id' => ['required', 'integer', 'exists:artikel,id'],
            'reply' => ['nullable', 'integer', 'exists:artikel_komentar,id'],
            'nama' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:120'],
            'link_website' => ['nullable', 'string', 'max:160'],
            'komentar' => ['required', 'string', 'min:3', 'max:3000'],
        ], [
            'artikel_id.exists' => 'Tulisan yang dikomentari tidak ditemukan.',
            'nama.required' => 'Namanya diisi dulu ya.',
            'email.email' => 'Alamat emailnya belum benar.',
            'komentar.required' => 'Komentarnya masih kosong.',
            'komentar.max' => 'Komentarnya kepanjangan, maksimal 3.000 huruf.',
        ]);

        /*
         * Perangkap lebah: isian yang TIDAK terlihat orang. Peramban biasa
         * membiarkannya kosong; bot pengisi borang mengisi semua yang
         * ditemukannya.
         *
         * Dijawab dengan pesan sukses yang sama persis, bukan dengan
         * penolakan: bot yang diberi tahu ia tertangkap akan dicoba lagi
         * dengan cara lain.
         */
        if (trim((string) $request->input('alamat_kantor')) !== '') {
            return redirect()->back()->with('success', self::PESAN_KOMENTAR);
        }

        ArtikelKomentar::create([
            // Dari SESI, bukan dari borang. Tamu menyimpan null, dan
            // namanya diambil dari isian seperti biasa.
            'user_id' => Auth::id(),
            'categories_artikel_id' => DB::table('artikel')->where('id', $data['artikel_id'])->value('categories_artikel_id'),
            'artikel_id' => $data['artikel_id'],
            'reply' => $data['reply'] ?? null,
            'token' => $this->generateRandomToken(30),
            'nama' => $data['nama'],
            'email' => $data['email'],
            'link_website' => $data['link_website'] ?? null,
            'komentar' => $data['komentar'],
        ]);

        /*
         * `status` SENGAJA tidak ikut dikirim dan tidak ada di $fillable
         * model: nilainya datang dari bawaan kolom ('tertahan'). Kalau suatu
         * saat ada yang menulis create($request->all()) di sini, status
         * tidak ikut bisa disetel dari luar.
         */
        return redirect()->back()->with('success', self::PESAN_KOMENTAR);
    }

    /** Satu kalimat, dipakai jalur sukses dan jalur perangkap lebah. */
    private const PESAN_KOMENTAR = 'Komentarmu sudah masuk dan akan tampil setelah dicek admin. Terima kasih!';

    public function contact()
    {
        return view('public.article.contact');
    }
    // <!--================== END ==================-->

}
