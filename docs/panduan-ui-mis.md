# Panduan UI/UX MIS

Acuan rupa dan perilaku untuk **seluruh layar** MIS Rumah Scopus. Disarikan
dari dua layar yang sudah digarap sampai tuntas — **Profil** dan **Data
Pelanggan** — dan sejak sekarang keduanya berlaku sebagai contoh yang disalin,
bukan dua layar yang kebetulan bagus.

Aturan di sini bukan selera. Hampir semuanya lahir dari kekeliruan yang sudah
terjadi, dan angkanya diukur di peramban sungguhan. Karena itu tiap aturan
menyebut **sebabnya** — supaya yang membaca tahu kapan boleh menyimpang.

---

## 1. Yang paling sering bikin celaka

Baca bagian ini lebih dulu. Delapan hal berikut memakan waktu paling banyak,
dan tidak satu pun menimbulkan pesan galat yang menunjuk sebabnya.

| Jebakan | Gejalanya | Yang benar |
|---|---|---|
| **Cabang ponsel dipilih peladen** dari User-Agent, bukan lebar layar | menyempitkan jendela peramban TIDAK pernah menukar tampilan | timpa User-Agent saat menguji; `layouts/account.blade.php` bercabang lewat `new Agent()` yang membaca `$_SERVER` |
| **Font Awesome 5** yang dibundel | nama gaya FA6 (`fa-circle-info`, `fa-mug-hot`) tidak menimbulkan galat — ikonnya sekadar tidak tergambar | `tests/Unit/IkonAdaGlifnyaTest.php` menjaganya; ia memindai Blade **dan** `mis-ui.js` |
| **Layout memasang `.fas { font-size: 20px }`** untuk seluruh halaman | mengecilkan pembungkus ikon tidak mengubah glifnya — ikonnya tetap 20px berapa kali pun ubinnya dikecilkan | tiap pembungkus ikon memberi glifnya `font-size: inherit`, plus `width: 100%` dan `text-align: center` supaya ikon yang tidak persegi tidak bergeser dari titik tengah |
| **Stisla memaksa `min-width: 800px`** pada tabel di dalam `.table-responsive` | tiap daftar harus digeser ke samping di ponsel — bukan salah markah halamannya | `.mis-tabel-kartu` menimpanya |
| **`line-height` MUTLAK dari tema Stisla** | sel berisi satu lencana 20px tetap setinggi 28px; kalimat dua baris terbaca seperti dua paragraf | setel `line-height` sendiri di layar yang rapat |
| **`.mis-isian` memasang `align-self: start`** | di susunan menurun, tiap isian selebar isinya — tepinya ragged | timpa `align-self: stretch` di dalam wadah menurun |
| **`flex-basis` berganti sumbu** | `flex: 1 1 200px` yang benar di baris mendatar jadi tinggi 200px di susunan menurun | setel ulang `flex` di dalam media query ponsel |
| **`1fr` tidak menyusut di bawah isinya** | dua kolom "sama" ternyata 144px lawan 119px | pakai `minmax(0, 1fr)` |
| **Blade: `@php(...)` sebaris bentrok dengan blok `@php … @endphp`** | galat 500 "unexpected token" menunjuk baris yang tidak disentuh; menyebut nama direktifnya di dalam komentar pun mengulang persoalan | hitung di pengendali, jangan tambah blok PHP mentah ke berkas yang sudah memuat yang sebaris |
| **Komentar CSS ikut terkirim ke peramban** | `assertDontSee('Simpan kontak')` gagal karena labelnya ada di komentar `<style>` | uji dari markahnya (`type="submit"`), bukan dari tulisannya |

Tambahan yang sudah pernah menggigit:

- **Kaki halaman `position: fixed`, `z-index: 1000`.** Apa pun yang melekat di
  bawah layar harus di atas itu, dan diangkat setinggi kakinya (45px).
- **`display` di dalam media query menimpa `display: none` bawaan.** Kalau
  sebuah elemen disembunyikan sampai dipicu, jangan pernah menulis `display`
  pada aturan dasarnya di media query mana pun — taruh di kelas pemicunya.
- **`#[DataProvider]` tanpa `use` diabaikan diam-diam** oleh PHPUnit; ujinya
  jalan tanpa argumen dan gagal dengan `ArgumentCountError`.
- **Bobot pemilih `mis-ui.css` sering (0,2,1).** Aturan layar yang berbobot
  (0,1,1) kalah walau ditulis belakangan.

---

## 2. Cara memeriksa: ukur, jangan dikira-kira

Ini bagian dari aturannya, bukan saran.

- **Kendalikan Chrome lewat DevTools Protocol**, bukan `--dump-dom`: yang
  terakhir tidak menjalankan tata letak dan memalsukan hasil.
- **Ukuran kotak saja tidak cukup untuk "terlihat".** Sebuah elemen bisa
  melaporkan 183×40 padahal berada di dalam `<details>` yang tertutup, atau
  di dalam `<thead>` yang terklip 1×1. Pakai `document.elementFromPoint()` di
  titik tengahnya: kalau ia tidak mengembalikan elemen itu sendiri, ia tidak
  bisa disentuh siapa pun.
- **Bandingkan dengan angka Profil**, bukan dengan ingatan.
- **Potret lewat `file://` menipu**: font Font Awesome diblokir CORS lintas
  asal, dan ikon jadi kotak kosong. Ukur di peladen sungguhan.
- Kalau arah sebuah efek meragukan (mis. sisi mana yang dipudarkan
  `mask-image`), **besarkan efeknya sementara di peramban** sampai tidak bisa
  salah baca, lalu kembalikan.

---

## 3. Token & ukuran

Semua ada di `public/assets/css/mis-ui.css`. **Jangan menulis angka sendiri**
di layar baru; pakai custom property-nya.

| Peran | Token |
|---|---|
| Tinta | `--mis-tinta`, `--mis-tinta-2`, `--mis-tinta-3`, `--mis-tinta-4` |
| Garis & kartu | `--mis-garis`, `--mis-kartu`, `--mis-bayang` |
| Sudut | `--mis-radius` (18px), `--mis-radius-kecil` (14px) |
| Bantalan & jarak | `--mis-isi-kartu` `clamp(16px, 2.2vw, 22px)`, `--mis-jarak` `clamp(12px, 1.2vw, 16px)` |
| Gradien aksen | `--mis-ungu`, `--mis-hijau`, `--mis-merah`, `--mis-biru`, `--mis-kuning`, `--mis-jingga` |

Ukuran baku, dibaca langsung dari `mis-ui.css`: tombol **tinggi 42px,
bantalan 0 18px, radius 12px, huruf .85rem**; tombol ikon **34×34, radius
10px**; pil **3px 10px, radius 999px**.

**Lebar isi dibatasi 1600px** (`.mis-badan > .section`), dipusatkan. Tanpa itu,
pada 2560px tabel melar jadi 2248px dengan kolom email 585px — sekitar tiga
kali yang dibutuhkan.

---

## 4. Kerangka layar

### Layar daftar

Urutannya tetap, dari atas:

1. **Kepala** — `.mis-kepala` berisi `.mis-medali`, `.mis-judul`, `.mis-sub`,
   dan `.mis-kepala-aksi` untuk unduhan/tombol utama.
2. **Ubin ringkasan** — empat angka yang paling sering ditanyakan, dihitung
   dari SELURUH data, bukan dari halaman yang sedang tampil.
   **Tiap ubin adalah tautan saringan.** Angka yang menarik perhatian selalu
   memancing "yang mana saja?", dan tanpa itu pertanyaannya tidak terjawab.
   Berupa `<a>`, bukan tombol berskrip, supaya alamatnya bisa disalin.
   *Jangan* memasang ubin yang angkanya selalu sama dengan ubin lain — periksa
   dulu ke datanya.
3. **Kartu penyaring** — berkartu putih, bukan mengambang. Kendalinya berbagi
   baris dengan perbandingan **2:1:1** (cari : dua menu) tanpa batas atas;
   yang keliru bukan lebar mutlaknya melainkan perbandingannya.
4. **Tabel** — `.mis-tabel.mis-tabel-kartu` di dalam `.mis-tabel-bungkus`.
5. **Penomoran halaman** — `vendor.pagination.bootstrap-4`.

### Layar rincian

Dua kolom: kartu identitas di kiri (tetap), kartu bertab di kanan (melar).
Tabnya `.mis-tab` + `.mis-tab-kepala` + `.mis-tab-isi`.

Kolom berdampingan memakai `align-items: start`, **bukan** `stretch`. Dengan
stretch, kartu yang isinya pendek ikut setinggi kolom sebelahnya dan sisanya
jadi petak putih **di dalam** kartu — pernah terukur 341px kosong di bawah
daftar syarat kata sandi. Kartu yang berhenti di ujung isinya jauh lebih enak
dilihat daripada kartu yang dipaksa rata bawah.

Kisi isian memakai `repeat(auto-fit, minmax(210px, 1fr))`, bukan `col-md-*`:
tiga kolom di layar lebar, dua di tablet, satu di ponsel, tanpa titik putus
yang harus dijaga satu per satu. Kartunya wajib `align-content: start`.

---

## 5. Aturan responsif

**Ambangnya hanya tiga.** Jangan menambah yang baru tanpa alasan terukur.

| Ambang | Berlakunya |
|---|---|
| `767.98px` | tabel berubah jadi kartu; susunan dua kolom jadi menurun |
| `575.98px` | strip tab jadi satu baris bergeser; kendali khusus ponsel muncul |
| `359.98px` | penyesuaian terakhir untuk layar tersempit |

> Komentar lama di `mis-ui.css` menyebut mode kartu "di ponsel … 390px".
> **Ambang sebenarnya 767.98px.** Jangan percaya komentar itu.

### Daftar di ponsel

Tiap baris jadi **kartu terpisah berjarak 8px**, bertepi 1px dan bersudut
13px — bukan baris bergaris pemisah. Satu baris memuat enam keterangan
berlabel; garis 1px terlalu sepi untuk menandai di mana satu baris berakhir.

### Tab di ponsel

Satu baris yang bisa digeser (`overflow-x: auto`, `scroll-snap-type: x
proximity`, scrollbar disembunyikan) — **wajib disertai penanda**. Tanpa tanda
bahwa masih ada isi di samping, tab terakhir praktis tidak ada. Penandanya
tepi memudar lewat `mask-image`, dipasang skrip sesuai posisi geseran, dan
**hilang begitu sudah mentok**: tepi yang selalu pudar menjanjikan isi yang
sudah habis.

Ambang "sudah di paling kiri" dihitung dari **bantalan strip**, bukan nol —
`scroll-snap-align: start` membuat posisi diam paling kiri sama dengan
bantalannya.

### Yang melekat di bawah layar

Angkat di atas yang menghalanginya: **57px** di layar lebar (kaki halaman
45px + 12px), **96px** di ponsel (bilah navigasi mengambang). `z-index`
minimal **1001**.

---

## 6. Komponen: pakai ulang, jangan bikin baru

| Kebutuhan | Pakai | Jangan |
|---|---|---|
| Pemberitahuan singkat | `misToast('berhasil'\|'gagal'\|'info', pesan)` | alert Bootstrap, modal Swal buatan sendiri |
| Konfirmasi | `misKonfirmasi({judul, pesan, sorot, tombol, jenis})` | `Swal.fire` langsung |
| Tombol menghapus **berteks** | `.mis-tombol-hapus` | `.mis-tombol-bahaya` — itu kotak **34×34 untuk ikon saja**; diberi teks, tulisannya menembus keluar |
| Tombol ikon di baris tabel | `.mis-tombol-bahaya`, `.mis-tombol-garis` | — |
| Lencana keadaan | `.mis-pil` + `.mis-pil-{hijau,kuning,biru,merah,ungu,abu}` | warna sendiri |
| Keadaan akun berdenyut | `.mis-lencana` + `.mis-lencana-titik` | — |
| Foto/inisial | `@include('partials.avatar', [...])` | `<img>` langsung |
| Medan terkunci | `.mis-statis` | isian yang di-`disabled` |
| Daftar kosong | `.mis-kosong` | teks polos |

Dalam `misKonfirmasi`, nama orang **selalu** lewat `sorot`, bukan dirangkai ke
`pesan` — ia disisipkan sebagai teks, jadi tanda `<` di dalam nama tidak
pernah tertafsir sebagai markah.

### Warna status

Status dari kolom teks bebas **dipetakan di PHP, bukan dengan `@if` di Blade**
— lihat `App\Support\PesananPelanggan::rupaStatus()` sebagai contoh. Tiga hal
yang wajib ditiru:

1. Nilai yang **tidak dikenali jadi abu-abu**, bukan hijau. Lencana hijau pada
   keadaan yang tidak dipahami lebih menyesatkan daripada lencana netral.
2. Penebak kata kunci memeriksa **yang menggagalkan lebih dulu** — "pembayaran
   ditolak" memuat kata "bayar" DAN "tolak".
3. Ada uji yang membaca nilai status yang **benar-benar tersimpan** di basis
   data dan gagal kalau ada yang jatuh ke abu-abu.

---

## 7. Perilaku yang diharapkan

- **Pencarian jalan sambil mengetik**, jeda 300 ms, permintaan lama dibatalkan
  (`AbortController`), dan alamat halaman ikut diperbarui. Tombol "Terapkan"
  tetap ada di markah dan baru disembunyikan skrip — tanpa JavaScript,
  penyaringnya masih bisa dipakai.
- **Nomor telepon dicocokkan tanpa tanda baca**, di kedua sisi, dan bentuk
  kode negara ikut diterima. 93 dari 101 nomor pelanggan bertanda hubung;
  mencari tanpa tanda hubung akan mengembalikan nol hasil.
- **Pilihan bertahan** menyeberangi pencarian dan penomoran halaman. Simpan di
  `Set`, bukan dibaca dari kotak centang yang sedang tampil — isinya ditukar
  tiap ketikan.
- **Aksi massal tidak menghapus.** Mengaktifkan/menonaktifkan saja; penghapusan
  massal berarti satu salah klik menghilangkan puluhan baris, dan belum ada
  tong sampah.
- **Penghapusan diperiksa dulu, bukan dicoba lalu ditangkap.** Galat kunci
  asing hanya menyebut nama constraint-nya. Jawabannya harus menyebut APA yang
  menghalangi dan APA yang sebaiknya dilakukan.
- **Kirim surat ke orang luar dibatasi laju** (3× per 10 menit), **menolak
  alamat yang bentuknya tidak sah**, dan **meninggalkan jejak**.
- **Galat validasi membuka tab tempat galatnya**, bukan tab pertama.
- **Berkas unduhan menyebut saringan yang dipakai.** Berkas berisi 29 dari 101
  baris tidak boleh terbaca seperti daftar yang lengkap.

---

## 8. Aksesibilitas — minimum yang wajib

- Wadah yang isinya ditukar tanpa memuat ulang halaman: `role="status"` +
  `aria-live="polite"`.
- Kepala kolom yang bisa diurutkan: `aria-sort` pada `<th>`
  (`ascending` / `descending` / `none`). Ikon panahnya `aria-hidden`, dan
  `title`-nya menjelaskan AKSI — itu tidak menyatakan keadaan.
- Tiap kotak centang, tombol ikon, dan tautan ikon punya `aria-label`.
- Ikon yang cuma hiasan: `aria-hidden="true"`.
- Bahasa Indonesia di seluruh tulisan yang dibaca orang. Nilai yang tersimpan
  boleh tetap bahasa Inggris (ia kunci), tetapi diterjemahkan saat ditampilkan
  — lihat `User::peranTerbaca()`.

---

## 9. Hak akses di tampilan

Layar tidak boleh menyuguhkan sesuatu yang kirimannya akan ditolak.

- Sembunyikan tombol kirimnya, **dan** matikan isiannya (`@disabled`) — tombol
  yang hilang saja masih menyisakan isian yang bisa dikirim lewat Enter.
- Tulis alasannya di tempat tombol itu seharusnya berada.
- Jangan menunjuk ke halaman lain kecuali orangnya memang ada di sana.

---

## 10. Menulis komentar

Ikuti gaya yang sudah ada: komentar menjelaskan **kenapa**, bukan apa.
Sertakan **angka hasil pengukuran** kalau keputusannya lahir dari pengukuran —
itu yang membuat orang berikutnya tahu kapan aturannya boleh dilanggar.

Contoh yang dipakai di repo ini:

```css
/*
 * Kolom kanan DIBATASI 46%, bukan `auto`.
 *
 * `auto` mengukur dirinya dari isi terlebar, dan isi terlebar di kolom itu
 * bukan lencananya melainkan teks alasan — "PIN salah tiga kali
 * berturut-turut" menuntut 300px dari 331px yang ada.
 */
```

Ingat: **komentar CSS ikut terkirim ke peramban.** Jangan mengutip label
tombol di dalamnya kalau ada uji yang memeriksa tulisan halaman.

## Kartu berisi borangnya sendiri

Dipakai pertama kali di **Tarif layanan** (`clinik_scopus_biaya_persesi`), untuk
layar yang isinya beberapa hal sejenis dan tiap hal punya sedikit isian.

- Kisinya `repeat(auto-fit, minmax(320px, 1fr))` — jumlah kartunya boleh
  bertambah tanpa titik putus baru yang harus dijaga.
- Kisinya **wajib** `align-items: start`. Dengan `stretch` bawaan, membuka
  borang di satu kartu menarik seisi barisnya jadi setinggi itu (terukur 294px
  jadi 590px) dan kartu sebelahnya menyisakan petak putih hampir 300px.
- Borangnya di dalam `<details>`, tertutup secara bawaan. Tujuh borang terbuka
  sekaligus menuntut menggulung jauh hanya untuk melihat nilai yang berlaku —
  padahal itu yang paling sering dicari.
- Hal yang belum terisi tetap ditampilkan, ditandai tepi kuning. Kartunya
  disusun dari **katalog di kode**, bukan dari isi tabel: disusun dari tabel,
  yang belum terisi hilang dari layar dan tidak ada yang tahu ia terlewat.
- Satuan seperti "Rp" dan "%" menempel di dalam kotaknya sebagai awalan atau
  akhiran, bukan jadi label sendiri.
- Pratinjau hitungan diberi `aria-live="polite"` dan tulisan tombolnya ikut
  berubah mengikuti apa yang sebenarnya akan terjadi (di sini: "Berlakukan"
  untuk nilai baru, "Perbaiki" untuk nilai yang sama).

Terukur di 1470/820/390/320px: tiga, dua, satu, satu kolom; tidak ada gulung
mendatar; tidak ada yang meluber.

## `.mis-tombol-garis` itu tombol IKON SAJA

Di `mis-ui.css` kelas itu didefinisikan **34x34, `display: inline-grid`,
padding nol** — bentuk untuk tombol aksi baris yang isinya cuma satu glif.
Dipakai untuk tombol bertulisan, teksnya terjepit: terukur "Kembali" butuh
41px di dalam kotak 32px, dan tidak ada galat apa pun yang memberi tahu.

Tombol bertulisan memakai `.mis-tombol-halus` (putih, bertepi) atau
`.mis-tombol-ungu` / `.mis-tombol-biru` / `.mis-tombol-hijau` untuk yang
utama. `.mis-tombol-bahaya` juga 34x34 dan ikon saja; yang bertulisan merah
memakai `.mis-tombol-hapus`.

## Borang panjang: dialog, bukan lipatan di dalam kartu

Tarif layanan sempat memuat tujuh borang di tujuh kartu, masing-masing
terlipat di dalam kartunya. Dua akibatnya:

- Membuka satu borang menarik **seisi barisnya** jadi setinggi itu — terukur
  294px jadi 590px — dan kartu sebelahnya menyisakan petak putih hampir 300px.
- Untuk menghindarinya, kisinya dipaksa `align-items: start`, yang membuat
  dasar kartunya bergerigi dan tombolnya tidak sejajar.

Dipindah ke `<dialog>`, keduanya hilang sekaligus: kisinya boleh kembali
`stretch` (tinggi seragam, tombol sejajar lewat `margin-top: auto` pada kaki
kartu), dan membuka borang tidak menggeser tata letak sama sekali. Bonusnya
`<dialog>` memberi jebakan fokus dan tombol Esc tanpa kode tambahan.

Ukurannya `width: min(620px, calc(100vw - 32px))`, dan di bawah 768px jadi
selayar penuh tanpa radius. Isinya yang menggulung (`overflow-y: auto` pada
badan dialog), bukan halamannya, supaya kepala dan tombol simpannya selalu
terlihat.

**Jebakan saat mengukurnya lewat CDP:** dengan modal terbuka, timer halaman
dicekik. `setTimeout` di dalam `Runtime.evaluate` yang menunggu janji tidak
pernah selesai dan skripnya menggantung diam tanpa galat. Tunggu di Node,
jangan di halaman.

## Daftar panjang di kartu ringkasan dipotong

Kartu yang memajang daftar (fasilitas, kegiatan) memotongnya di **4 butir**
lalu menulis "+N lainnya". Selengkapnya ada di borangnya. Tanpa dipotong,
kartu berisi 7 butir memaksa seluruh barisnya setinggi itu, dan kartu yang
cuma punya 3 butir jadi separuh kosong.

## Daftar pilihan yang tersimpan di basis data

Ikon dan warna yang disimpan admin lewat borang **wajib** dari daftar tertutup
(`Rule::in`), bukan isian bebas. Alasannya bukan kerapian: proyek ini memakai
Font Awesome 5, dan nama FA6 tidak merender apa pun **tanpa galat sama sekali**.
`IkonAdaGlifnyaTest` memindai berkas Blade dan `mis-ui.js`, tetapi nama yang
datang dari basis data lolos dari pemindaian itu — jadi penjaganya harus di
validator, ditambah satu uji yang memeriksa seluruh daftar pilihan itu benar
ada glifnya.

## MySQL mengurutkan ulang kunci objek JSON

Terukur: `{zulu, alfa, bravo_panjang}` yang disimpan ke kolom `json` terbaca
kembali sebagai `{alfa, zulu, bravo_panjang}` — menurut panjang lalu abjad.
Larik JSON **tidak** diurutkan ulang.

Jadi apa pun yang urutannya berarti bagi orang (varian layanan, langkah,
pilihan) disimpan sebagai **larik berurut** `[{kode, nama}, ...]`, bukan objek
`{kode: nama}`. Urutan yang diketik admin hilang tanpa jejak kalau memakai
objek.

## Menyembunyikan sesuatu harus ada jalan kembalinya

Layar yang menyaring daftarnya (`where('aktif', true)`) membuat yang
dinonaktifkan **lenyap sama sekali** — termasuk dari jangkauan admin yang baru
saja menonaktifkannya. Di Tarif layanan, pesan penolakan hapus bahkan
menyarankan "nonaktifkan saja", lalu menutup pintunya sendiri.

Aturan: setiap layar yang bisa menonaktifkan sesuatu **wajib** punya bagian
terpisah yang memajang yang nonaktif, redup, dengan tombol mengaktifkan lagi.

## Dua pintu ke satu tempat harus punya aturan sama

Borang penyetelan tarif menolak nominal nol; tombol "Berlakukan lagi" di
riwayat dulu tidak memeriksa apa pun, jadi satu klik bisa menjadikan baris
Rp 0 sebagai tarif yang berlaku. Setiap kali ada jalan pintas ke keadaan yang
sama, aturannya disalin — bukan diandaikan sudah dijaga di tempat lain.

## Angka yang tidak diketahui bukan nol

Kolom "Dipakai" di riwayat tarif menghitung `biaya_persesi_id`, yang hanya
dimiliki sesi Clinik Scopus. Angkatan layanan lain menyalin angkanya, tidak
menunjuk barisnya — jadi untuk mereka jumlahnya **tidak diketahui**, bukan nol.
Menuliskannya "Belum dipakai" adalah angka yang berbohong, dan angka yang
berbohong lebih berbahaya daripada kolom kosong. Tulis "tidak tertaut".

## `@media (pointer: coarse)` saja tidak cukup

Ciri itu tidak selalu dilaporkan peramban, dan emulasi DevTools pun tidak
menyetelnya — jadi aturan yang hanya bergantung padanya bisa **tidak pernah
aktif tanpa ada yang tahu**, dan tidak bisa dibuktikan lewat pengukuran.
Sebutkan lebar ponsel juga: `@media (pointer: coarse), (max-width: 767.98px)`.
Sasaran sentuh minimal 44x44.

## Status baru wajib punya barisnya sendiri

Menambah status ke sebuah data (`terjadwal` di samping `active`/`non active`)
tidak selesai di modelnya. Kalau daftar di layar menyaring satu status saja —
tabel riwayat memuat `non active` — maka baris berstatus baru itu **tidak
punya baris di mana pun**, dan tidak ada satu tombol pun yang bisa
menyentuhnya. Di layar tarif, kartunya sempat mengumumkan kenaikan harga yang
tidak bisa dibatalkan siapa pun.

Ini kembaran dari aturan "menyembunyikan sesuatu harus ada jalan kembalinya",
dan sama mudahnya terlewat: keduanya muncul dari fitur yang *menambah*
keadaan, bukan dari kode yang salah.

**Periksa tiap kali menambah status atau saringan:** untuk setiap keadaan yang
mungkin, di layar mana ia muncul, dan tombol apa yang bisa mengubahnya?

## `minmax(300px, 1fr)` tidak pernah menyusut

`repeat(auto-fit, minmax(280px, 1fr))` meluber di layar 320px — lebar tetap
pada argumen pertama adalah lantai yang tidak bisa ditembus, dan ruang yang
tersedia di sana cuma ~258px. Pakai `minmax(min(100%, 280px), 1fr)`.

Terukur di kisi jadwal tarif; halamannya sendiri tidak menggulung mendatar,
jadi pemeriksaan "ada gulung mendatar?" saja TIDAK menangkapnya — yang
menangkap perbandingan `scrollWidth` tiap unsur dengan `clientWidth`-nya.

## Jejak "siapa" harus mencatat yang terakhir, bukan yang pertama

Kolom penginput yang diisi hanya saat baris DIBUAT menunjuk orang keliru
begitu ada yang memperbaikinya — dan memperbaiki justru cara nilai paling
sering berubah. Isi juga pada cabang pembaruan.

## Jebakan uji: dua pengguna dalam satu uji

`actingAs($a)->post(...)` lalu `actingAs($b)->post(...)` membuat permintaan
kedua tertolak ke `/login` **tanpa galat maupun pesan**, jadi ujinya gagal
seolah kodenya yang salah. Sesi permintaan pertama masih memegang pengguna A.
Panggil `$this->flushSession()` di antaranya.

## Memotong daftar untuk layar tidak boleh memotongnya untuk cetak

Kartu ringkasan memotong daftar di 4 butir (lihat aturan sebelumnya). Kalau
pemotongan itu terjadi di **markah** — `array_slice()` di Blade — maka apa pun
yang membaca halaman itu selain layar ikut kehilangan sisanya: cetakan,
pembaca layar, dan penyalinan teks.

Render **seluruh** butirnya, sembunyikan kelebihannya dengan kelas
(`.tar-lebih { display: none }`), lalu tampilkan lagi di `@media print`
sekaligus sembunyikan penanda "+N lainnya" yang jadi tidak perlu.

## Berkas yang keluar dari MIS memakai satu cetakan

Daftar harga sempat memakai `window.print()` dengan `@media print`, sementara
Data Pelanggan memakai Dompdf dengan logo, kepala berulang, dan kaki — dua
hasil yang tidak serupa dari satu sistem.

Acuannya `resources/views/account/customer/ekspor-pdf.blade.php`: logo sebagai
**data URI** (Dompdf tidak mengambil berkas luar kecuali `isRemoteEnabled`),
kepala dan kaki `position: fixed` supaya terulang tiap halaman, garis indigo
2px di bawah kepala, strip "keterangan berkas", dan tabel berkepala indigo.

Dompdf hanya mengenal sebagian kecil CSS: tidak ada flexbox, grid, maupun
custom property. Tata letaknya tabel dan lebar persen, satuan **pt** bukan px
(dompdf memampatkan px dengan 0,75). Kirim lewat `response()`, bukan
`$dompdf->stream()` — stream memanggil `header()` dan `echo` sendiri sehingga
kepalanya lewat dari lapisan respons Laravel.

Jangan sisakan jalur cetak kedua. Dua pintu ke satu keluaran pasti berselisih.

## Gaya cetak diperiksa dengan melihat hasilnya

`@media print` tidak pernah terlihat saat mengembangkan, jadi ia lolos dari
semua pemeriksaan biasa. Di layar tarif, cetakan pertama memuat tombol roda
gigi, rencana kenaikan harga internal, layanan bertuliskan "Belum disetel",
dan daftar fasilitas yang terpotong diam-diam — empat hal yang tidak boleh
sampai ke kertas yang dibagikan ke luar.

Periksa dengan `Emulation.setEmulatedMedia { media: 'print' }` lewat CDP, lalu
**potret dan lihat**. Pertanyaannya: siapa yang menerima kertas ini, dan apa
yang tidak boleh ia lihat?

## Kueri yang tumbuh seiring data

Layar tarif sempat menjalankan 40 kueri untuk 5 layanan, dan borang angkatan
23 — dengan ~4 kueri tambahan per layanan baru, tepat setelah katalognya
dibuat supaya bisa tumbuh. Penyebabnya tiga pola yang sama:

- `count()` di accessor, dipanggil per baris → satu kueri berkelompok
  (`groupBy`) untuk semuanya.
- Pencarian "yang berlaku" per baris → satu kueri, lalu dipetakan dengan
  `keyBy('layanan|varian')`.
- Pemeriksaan yang sama diulang per baris → penanda statis, sekali per
  permintaan (dan sediakan pembuangnya untuk uji, karena statis berumur satu
  PROSES di uji, bukan satu permintaan).

Jadi 40 → 20 dan 23 → 11, tanpa satu kueri pun yang berulang. Hitung kuerinya
dengan `DB::listen` sebelum menyatakan layar selesai.

## Ikon di dalam isian diposisikan terhadap ISIANNYA

`position: absolute` mencari pembungkus ber-`position` terdekat. Kalau itu
baris saringan yang juga memuat keterangan hasil, maka di ponsel — tempat
barisnya menumpuk jadi dua — ikonnya ikut turun ke tengah keduanya, bukan ke
tengah isiannya.

Bungkus isiannya sendiri (`position: relative`), taruh ikon dan tombol
kosongkan di dalamnya, dan pakai `top: 0; bottom: 0` daripada `height: 100%`.
Terukur: selisih titik tengah ikon terhadap isian 0px di 1470/820/390/320.

## Kotak pencarian wajib punya tombol kosongkan

Dan silang bawaan peramban dimatikan
(`input[type="search"]::-webkit-search-cancel-button { display: none }`),
kalau tidak ada dua tombol berdampingan di Chrome — dan yang bawaan tidak
memicu penyaringan ulang, jadi daftarnya tetap tersaring padahal kotaknya
sudah kosong.

Untuk pencarian yang dijalankan peladen, tombolnya berupa **tautan** ke daftar
tanpa kata kunci, bukan tombol berskrip: ia tetap bekerja tanpa JavaScript.
Esc juga mengosongkan, kebiasaan yang sudah dipunyai orang.

## Jebakan mengukur: membaca keadaan SESUDAH menekan

`b.click()` yang dipanggil sebelum `return { tampak: !b.hidden }` membaca
keadaan sesudah tombolnya bekerja — jadi tombol yang benar-benar muncul selalu
terbaca "tersembunyi", dan perbaikan yang benar terlihat gagal. Baca dulu ke
peubah, baru tekan.

## `showModal()` tidak mengunci guliran latar

Ia membuat sisa halaman tidak bisa disentuh, tetapi rodanya tetap menggulung —
terukur 400px bergeser di belakang dialog yang terbuka. Yang dibaca orang
bergeser diam-diam, dan begitu dialognya ditutup ia mendapati dirinya entah di
mana.

`mis-ui.js` sudah menanganinya untuk **dialog mana pun**: ia menyimak
perubahan atribut `open` lewat MutationObserver dan memasang
`body.mis-dialog-terbuka` (`overflow: hidden`), plus mengganti lebar batang
gulir dengan padding supaya halaman tidak melompat. Layar baru tidak perlu
mengulang kodenya.

**Jebakan:** `overscroll-behavior: contain` dipasang ke `dialog`, **jangan ke
`dialog *`**. Ke semua keturunan, tiap `<textarea>` yang isinya muat ikut
menahan guliran karena penerusan ke induknya diblokir — terukur, dialog yang
perlu digulung (781px isi dalam 710px ruang) sama sekali tidak bergerak saat
roda berada di atas kotak isian. Wadah gulir di dalam dialog menyatakan
`contain` sendiri.

**Mengukurnya:** pakai `Input.dispatchMouseEvent { type: 'mouseWheel' }` lewat
CDP, bukan `window.scrollBy` — guliran terprogram tetap jalan walau
`overflow: hidden`, jadi ia mengukur hal yang bukan keluhannya. Dan ambil
patokan lebih dulu (gulir sebelum dialog pernah dibuka): "halaman tidak
bergerak" bisa berarti terkunci, bisa juga berarti titik ujinya kebetulan di
atas bilah sisi.

## Keadaan kosong hasil pencarian punya bentuknya sendiri

`.mis-kosong-cari` di `mis-ui.css`, dipakai Data Pelanggan dan Tarif Layanan.
Bedakan dari keadaan "datanya memang belum ada": keduanya terasa sama di layar
padahal jalan keluarnya berbeda — yang satu ganti kata kunci, yang lain isi
datanya dulu. Hanya yang pertama memakai ikon kaca pembesar bergerak.

Isinya: ubin ikon yang menyapu pelan + dua cincin memuai bergantian, judul
"Tidak ada yang cocok", kalimat yang **menyebut kata kuncinya**, dan tombol
"Hapus saringan" — jalan keluarnya ada di tempat orang menyadari ada masalah,
bukan di ujung lain halaman.

Warnanya tetap keluarga abu-abu sesuai aturan "abu-abu hanya untuk data
kosong"; yang berwarna cuma cincin dendangnya, ungu tipis, supaya terbaca
sebagai gerak dan bukan sebagai data.

`@media (prefers-reduced-motion: reduce)` **wajib**: keadaan kosong yang
berdenyut terus-menerus termasuk yang paling mengganggu bagi orang dengan
sensitivitas gerak, dan ia muncul justru saat orangnya sedang bingung. Uji
dengan `Emulation.setEmulatedMedia { features: [{ name:
'prefers-reduced-motion', value: 'reduce' }] }`.

## Jangan potong-tempel blok besar dengan indeks teks

Memindahkan satu blok Blade dengan `s.index(...)` lalu menyambungnya kembali
menggandakan seluruh saringan dan kisi tarif — 14 kartu muncul di tempat yang
seharusnya 7, dan ujinya tetap hijau karena tidak ada yang menghitungnya.
Ketahuan hanya karena pengukuran di peramban menghitung kartunya.

Pakai suntingan yang menyebut teks lama dan teks barunya secara utuh. Kalau
sudah terlanjur, `git checkout --` berkas itu dan ulangi — jauh lebih murah
daripada menambal hasil gandanya.

## Perbaiki sejenisnya sekaligus, jangan satu per satu

Empat ronde audit berturut-turut di layar Tarif layanan, dan temuan terbesar
tiap ronde selalu pekerjaan ronde sebelumnya: jadwal yang tak bisa dibatalkan,
cetak yang tak lengkap, PDF tanpa nomor halaman, keadaan kosong yang setengah
dibungkus. Penyebabnya sama tiap kali — satu hal diperbaiki, tetangganya yang
sejenis tidak ditengok.

Sebelum menyentuh satu, **daftar dulu semuanya**: semua keadaan kosong, semua
keluaran PDF, semua status, semua dialog di layar itu. Inventarisnya sendiri
yang menemukan cacat: mendaftar keadaan kosong menyingkap riwayat tersaring
yang berbunyi "Belum ada tarif lama" padahal riwayatnya ada.

## Di mode kartu, HANYA SATU sel per baris boleh berisi `.mis-pil`

`mis-tabel-kartu` menandai sel status lewat `:has(.mis-pil)`, lalu
menaikkannya ke baris kaki kartu bersama sel aksinya dengan `flex: 1 1 0`.
Penanda itu menunjuk ISI, bukan posisi — jadi **setiap** sel yang kebetulan
berisi lencana ikut naik ke sana dan ikut membagi lebar baris kaki.

Terukur di layar Pendaftar Layanan pada 320px: tiga sel berpil (Bukti,
Keadaan, Aksi) membuat masing-masing tinggal **46px**, sementara lencana
"Berkas hilang" sendiri menuntut 92px. Kartunya meluber 234px dan terpotong
tanpa gejala apa pun, sebab pembungkusnya `overflow-x: hidden` — halamannya
sendiri melaporkan gulir mendatar 0px.

Keadaan yang bukan status (bukti bayar, keterangan kosong) dipajang sebagai
teks berikon, bukan pil.

## Sel kartu yang isinya bertingkat harus dibungkus satu anak

Di mode kartu selnya `display: flex` dengan label `::before` sebagai anak
pertama. Jadi setiap `<span>` di dalam sel jadi anak flex tersendiri dan
berbaris MENDATAR, bukan menumpuk — dan `display: inline` pun diblokkan jadi
blok begitu induknya flex, sehingga menyetelnya tidak menolong.

Terukur: label + nominal + kode unik + potongan diskon = empat anak sebaris
menuntut 314px dalam sel selebar 285px. Dibungkus satu `<span>`, anaknya
tinggal dua dan keterangannya kembali menumpuk di bawah nominalnya.

## Alamat email memaksa lebar sel, dan tidak bisa menyusut

Anak flex tidak pernah menyusut di bawah lebar **min-content**-nya, dan
min-content sebuah alamat email sama dengan panjang penuhnya — ia satu kata
tanpa spasi. Terukur di 320px: `trianggategarutama@gmail.com` menuntut 187px,
dan bersama label kartunya membuat sel Pendaftar jadi **294px** sementara
sembilan sel lain 232px.

Setiap kolom yang memuat email, afiliasi, atau nama panjang diberi
`overflow-wrap: anywhere`. `min-width: 0` pada pembungkusnya TIDAK cukup —
ia melepas batas flex, bukan membuat katanya bisa dipatah.

## Nominal tidak boleh terpatah, keterangannya harus boleh

`white-space: nowrap` benar untuk angkanya — "Rp 4.275.028" yang berpindah
baris di tengahnya terbaca sebagai dua angka. Ia **salah** untuk keterangan
di bawahnya: "potongan Rp 225.000 · SalamQ1" itu kalimat, menuntut 175px, dan
nowrap-nya meluberkan selnya 234px di 320px.

Bedakan keduanya; jangan memberi satu kelas nowrap untuk seluruh isi selnya.

## Menyatukan banyak tabel: `UNION ALL`, dan yang dijaga bukan urutannya

Lima tabel pendaftaran disatukan di `App\Support\PendaftaranSemuaLayanan`.
Tiga hal yang ternyata penting dan satu yang ternyata tidak:

- **`UNION ALL`, bukan `UNION`.** `UNION` membuang baris kembar, jadi dua
  pendaftaran yang isinya kebetulan sama persis akan hilang satu tanpa galat.
  Dijaga uji yang membandingkan jumlah gabungan dengan jumlah kelima tabelnya.
- **Literal untaian di-CAST ke panjang tetap.** Panjang literal yang
  berbeda-beda antar cabang termasuk hal yang bisa memotong nilai di cabang
  berikutnya. Sudah diukur di MySQL yang dipakai dan tidak terjadi, tetapi
  CAST-nya murah dan membuat perkaranya tidak bisa muncul.
- **Pengurut kedua yang pasti unik.** Diurutkan menurut kolom yang nilainya
  berulang ratusan kali (91 baris berstatus sama), basis data boleh menyusunnya
  berbeda tiap permintaan — dan baris yang sama bisa muncul di dua halaman
  sekaligus. Gejalanya urutan yang BOLEH berbeda, bukan yang pasti berbeda,
  jadi uji lewat HTTP bisa hijau berkali-kali di atas kode yang rusak.
- **Urutan daftar kolomnya TIDAK perlu dijaga.** Sempat diberi komentar bahwa
  menukarnya menukarkan nilai; dicoba, dan hasilnya identik — alias tiap kolom
  dirakit dari butir daftar yang sama, jadi urutan dan nama tidak mungkin
  berselisih. Yang benar-benar bisa salah PEMETAANNYA per layanan: lima tabel
  menamai hal yang sama dengan lima nama berbeda, dan satu pemetaan yang salah
  tunjuk cuma menampilkan afiliasi orang di kolom nama, tanpa galat.

Dan **kolom yang tidak dipunyai sebuah tabel diisi NULL, bukan dilewati** —
melewati satu kolom menggeser seluruh kolom sesudahnya. Kecuali yang punya
arti bawaan: jumlah pendaftar yang tidak ada bukan NULL melainkan 1, sebab
barisnya memang selalu satu orang; dibiarkan NULL, penjumlahan "berapa orang"
melewatkan sebelas baris.

## Pencarian menjangkau isi, bukan cuma judul

Orang mencari lewat apa yang mereka ingat. Di Tarif layanan, `data-cari`
sempat berisi nama + varian saja, jadi mengetik "penginapan" tidak menemukan
apa pun padahal itu fasilitas Scopus Camp Pulau Jawa. Sertakan isi yang
membedakan barisnya — fasilitas, satuan, kegiatan.

## `<dialog>` tetap perlu `aria-labelledby`

`showModal()` memberi semantik modal, tetapi tanpa nama pembaca layar
mengumumkan "dialog" saja. Tunjuk judulnya dengan `aria-labelledby`, isinya
dengan `aria-describedby`, dan tulis `aria-modal="true"` eksplisit.

## Penjaga berbasis sumber: periksa yang persis dimaksud

Dua penjaga di repo ini sempat salah menandai kode yang benar: satu memeriksa
`DB::table` per baris sehingga kueri berbilang baris tertuduh, satu memindai
semua `.mis-kosong` dengan jendela 220 aksara sehingga keadaan kosong yang
sudah di dalam kartu tertuduh. Jendela sepanjang apa pun cuma memindah batas
salahnya.

Periksa keadaan yang PERSIS dimaksud — kelas penanda yang spesifik, atau
seluruh pernyataan sampai titik komanya — bukan kemiripan tekstual.

## Sebelum membuang layar, periksa EMPAT hal — bukan tampilannya

Aturan "menyatukan dua layar tidak boleh mencabut kemampuannya" di bawah
ternyata belum cukup. Saat lima layar pendaftaran per layanan dibuang
3 Okt 2026, inventarisnya menyingkap empat hal berbeda, dan hanya yang
pertama kelihatan dari layarnya:

1. **Rutenya.** `route:list | grep` — bukan melihat tombol di layar.
   Terkumpul 26 rute untuk lima layar: rincian, suntingan, hapus, cari,
   saring, dan satu unduhan Excel yang tidak punya tombol di mana pun.
2. **Efek sampingnya.** Tiga dari lima layar **mengirim email ke pelanggan**
   saat statusnya diubah. Itu tidak terlihat di layar, tidak terlihat di
   `route:list`, dan kalau hilang tidak ada galat apa pun — pelanggan sekadar
   berhenti diberi tahu. Cari `Mail::` di pengendalinya.
3. **Penjaga aksesnya.** Terukur: **tiga dari lima tidak punya penjaga sama
   sekali**. Pengguna berperan `user` mendapat 200 di Scopus Camp, Scopus
   Kafe, dan daftar Clinik Scopus, lalu bisa menyunting dan menghapus
   pendaftaran orang lain. Membuangnya menutup lubang; tetapi kalau yang
   dibuang justru yang BERPENJAGA dan yang terbuka dibiarkan, hasilnya
   kebalikannya.
4. **Siapa penerimanya.** Yang paling mudah salah. Satu dari lima layar itu —
   Riwayat Pemesanan Clinik Scopus — ternyata **halaman pelanggan**, bukan
   layar panitia: pengendalinya menyaring ke `customer_id` miliknya, dan
   dasbor pelanggan menautkannya sebagai "Pemesanan saya". Membuangnya akan
   mencabut sesuatu yang bukan milik panitia. Ia dipertahankan, entri menunya
   dijadikan khusus pelanggan, dan sisi panitianya pindah.

Dan satu kejutan: **satu layar sudah mati sejak lama.** Analisis Bibliometrik
galat 500 untuk SEMUA orang, termasuk administrator — `compact('datas',
'startDate', 'endDate', 'kategori')` memanggil `$kategori` yang tidak pernah
didefinisikan di `index()`. Tidak ada uji yang menyentuhnya, dan tidak ada
yang melaporkannya. Periksa tiap layar yang akan dibuang memang MASIH HIDUP
sebelum menyimpulkan apa yang hilang kalau ia dibuang.

## Dua tabel yang "sama" bisa beda satu kolom

Tindakan bersama untuk lima layanan menulis jejak perubahan ke kolom `note`.
Terukur: hanya **tiga dari lima** tabel pendaftaran punya kolom itu. Scopus
Kafe dan Clinik Scopus tidak — dan menulisnya ke sana melempar
`Unknown column 'note'`, sehingga SELURUH perubahan statusnya gagal dengan
galat 500.

Kolom yang tidak dimiliki semua sumber jadi **bagian katalog**
(`'kolom_catatan' => 'note' | null`), bukan diandaikan ada. `Schema::hasColumn`
di jalur permintaan juga bisa, tetapi ia menyembunyikan ketimpangannya
alih-alih menuliskannya.

## Pemisah ribuan: jangan buang satu tanda, buang yang bukan angka

Pengendali lama membuang **titik** untuk Scopus Camp dan **koma** untuk Scopus
Kafe. Jadi nominal "4.275.028" yang diketik di layar Kafe tersimpan sebagai
**nol** — tanpa galat, dan total bayarnya hilang.

`preg_replace('/\D+/', '', $nilai)` menerima keduanya, plus spasi dan awalan
"Rp". Ujinya menyebut keempat bentuk itu satu per satu.

## Menyatukan dua layar tidak boleh mencabut kemampuannya

Layar kategori Bibliometrik punya unduhan PDF dan Excel; layar Angkatan
Layanan yang menggantikan dua layar kategori dibuat tanpa keduanya — jadi
penyatuannya diam-diam membuat penggunanya kehilangan sesuatu.

Sebelum mengganti sebuah layar, **daftar dulu rute yang dipunyai layar lama**
(`route:list | grep`), bukan hanya melihat tampilannya. Kemampuan yang tidak
kelihatan di layar — unduhan, saringan, cetakan — paling gampang terlewat.

## Mengukur kisi: baca dasarnya kalau `align-items: end`

`getBoundingClientRect().top` berbeda-beda untuk item setinggi berbeda di
BARIS YANG SAMA, jadi menghitung baris kisi dari puncaknya memberi angka yang
salah — terukur "2 baris" untuk kisi yang sebenarnya satu baris. Baca
`bottom`-nya, atau `gridTemplateColumns` yang sudah dihitung peramban.

Dan saring dulu anak yang `display: none` (isian tersembunyi tidak membentuk
jalur kisi); menghitung semua anak juga memberi angka yang salah.

## Kuota tidak boleh bisa lebih kecil dari yang sudah terpakai

Angkatan menyimpan `total_kuota` dan `sisa_kuota` terpisah dari pendaftarnya,
dan tidak ada apa pun yang mengikat ketiganya. Validator wajib menolak total
di bawah jumlah pendaftar, dan sisa di atas totalnya. Tanpa itu angka sisanya
jadi tidak berarti — angkatan yang penuh terlihat longgar, atau sebaliknya.

## Menggandakan baris berulang

Kalau data yang sama diketik ulang berkali-kali — 41 angkatan Yogyakarta yang
isinya nyaris sama persis — sediakan penggandaan. Yang TIDAK ikut disalin:
tanggal, sisa kuota, token, dan statusnya. Salinannya selalu **draf**, supaya
tidak ada yang terbit hanya karena tombol tertekan.

## Blok `@php … @endphp` sesudah `@php(...)` sebaris

**Jangan menaruh blok `@php … @endphp` di berkas Blade yang sudah memakai
`@php(...)` sebaris di atasnya.**

Blade memproses blok `@php … @endphp` lebih dulu daripada direktif biasa, dan
pencocokannya hanya "dari `@php` sampai `@endphp` terdekat". Penanda sebaris
`@php($x = ...)` ikut dianggap pembuka blok, lalu dipasangkan dengan `@endphp`
milik blok baru di bawahnya — dan **semua markah di antaranya tertelan** jadi
kode PHP.

Gejalanya menyesatkan: halaman galat 500 sambil menyebut variabel yang
dideklarasikan di blok baru itu ("Undefined variable $x"), padahal yang rusak
justru bagian jauh di atasnya. Diuji sendirian, potongan barunya kompilasi
dengan bersih; rusaknya hanya muncul di dalam berkas utuh.

Cara memeriksanya tanpa menebak:

```php
$h = Blade::compileString(file_get_contents($berkas));
str_contains($h, '<?php(');   // true = ada penanda sebaris yang tertelan
```

Jalan keluarnya: pindahkan perhitungannya ke model atau pengendali. Itu juga
menghilangkan alasan menaruh logika di dalam tampilan sejak awal.

## Kurung di dalam untaian pada `{{ }}`

Hindari `{{ $n > 1 ? ' (' . $n . ')' : '' }}`. Kurung buka di dalam untaian
ikut terbaca pemindai direktif Blade. Rakit kalimatnya di PHP, lalu cetak
hasilnya saja.
