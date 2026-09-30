# CLAUDE.md

Catatan untuk Claude Code saat bekerja di repositori ini.

## Tentang proyek

MIS Rumah Scopus Foundation — back office Laravel 12 + Livewire 3, berbahasa
Indonesia seluruhnya (nama kolom, rute, tulisan di layar, dan komentar kode).
Sebagian layar masih memakai templat Stisla lama; lapis penyeragamnya ada di
`public/assets/css/mis-ui.css` dan `public/assets/js/mis-ui.js`.

## Sebelum menyentuh tampilan mana pun

**Baca `docs/panduan-ui-mis.md` lebih dulu.** Panduan itu menyimpan aturan rupa
dan perilaku yang sudah disepakati, beserta jebakan yang sudah terbukti memakan
waktu — cabang ponsel yang dipilih peladen, Font Awesome 5 yang membiarkan nama
FA6 gagal tanpa pesan, `line-height` mutlak dari Stisla, dan beberapa lainnya.
Layar **Profil** dan **Data Pelanggan** adalah contoh yang disalin.

Tiga hal yang paling sering terlewat:

1. **Menyempitkan jendela peramban tidak menukar tampilan ponsel.** Cabangnya
   dipilih peladen dari User-Agent; timpa User-Agent saat menguji.
2. **Pakai `misToast()` dan `misKonfirmasi()`**, jangan `Swal.fire` langsung.
3. **`.mis-tombol-bahaya` itu kotak 34×34 untuk IKON saja.** Tombol menghapus
   yang berteks memakai `.mis-tombol-hapus`.

## Perintah

```bash
php artisan serve          # http://127.0.0.1:8000
php artisan test           # seluruh suite
php artisan test --filter NamaTest
php artisan view:clear     # wajib setelah menyentuh Blade yang di-cache
```

## Uji

- `DatabaseTransactions`, **bukan** `RefreshDatabase` — dijaga di
  `tests/TestCase.php`. RefreshDatabase pernah menghapus seluruh tabel `rsc`.
- Suite harus hijau sebelum didorong.
- Naikkan penanda singgahan (`?v=`) di `layouts/account.blade.php` setiap kali
  `mis-ui.css` atau `mis-ui.js` berubah.

## Alur git

`need` → `dev` → `main`, memakai merge commit. Dorong sendiri begitu pekerjaan
selesai dan suite hijau; bandingkan SHA tiap ref sesudahnya, sebab merge yang
tidak bergerak tetap melaporkan sukses. **Deploy menunggu perintah eksplisit.**
