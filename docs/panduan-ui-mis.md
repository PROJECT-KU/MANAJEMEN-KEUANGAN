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
