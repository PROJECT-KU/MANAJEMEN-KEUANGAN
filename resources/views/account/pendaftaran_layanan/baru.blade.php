@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Daftarkan Pendaftar | MIS Rumah Scopus
@stop

@push('gaya')
    <style>
        /*
         * TIDAK ada batas lebar sendiri di sini.
         *
         * Sempat dibatasi 1.000px untuk menahan isian yang merentang terlalu
         * jauh. Itu salah sasaran: patokan rumahnya `.mis-badan > .section {
         * max-width: 1600px }`, dan borang yang berhenti di 1.000px jadi
         * satu-satunya layar yang tidak penuh — terlihat seperti layar yang
         * belum jadi, bukan seperti layar yang rapi.
         *
         * Yang menahan sapuan mata bukan lebar halamannya melainkan lebar
         * ISIANNYA (lihat .bar-penuh di bawah) dan kolom ringkasan di kanan
         * yang memakai sisa lebarnya untuk sesuatu yang berguna.
         */

        /*
         * Ruang kerja: langkah di kiri, ringkasan biaya menempel di kanan.
         *
         * Di 1.600px, satu lajur berarti kartu selebar 1.560px berisi isian
         * selebar 520px — sisanya kosong, dan total bayarnya terdorong jauh
         * di bawah lipatan sehingga tidak terlihat saat isiannya diubah.
         */
        .bar-kerja {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: var(--mis-jarak);
            align-items: start;
        }

        /*
         * Kolom ringkasannya baru muncul di 1500px, dan angkanya diukur bukan
         * ditebak.
         *
         * Titik potongnya menghitung LEBAR JENDELA, sementara yang menentukan
         * adalah lebar isi — dan bilah samping aplikasi sudah memakan ~310px
         * lebih dulu. Versi pertama memakai 1200px: terukur di jendela
         * 1280px, lajur kirinya tinggal 615px dan kisi isiannya jatuh ke dua
         * jalur. 1500px menyisakan ~830px untuk lajur kiri, cukup untuk empat
         * kartu layanan sebaris.
         *
         * Di bawahnya borangnya satu lajur PENUH — tetap selebar layar,
         * sebagaimana layar lain di aplikasi ini.
         */
        @media (max-width: 1499.98px) {
            .bar-kerja {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        /*
         * Menempel saat digulir. Angka atasnya mengikuti tinggi batang kepala
         * yang melayang di layout ini (.mis-badan padding-top 110px).
         */
        .bar-samping {
            position: sticky;
            top: 96px;
        }

        /*
         * Di bawah 1500px kolom ringkasannya turun ke bawah — dan di sana ia
         * MENEMPEL DI DASAR layar, bukan diam di ujung halaman.
         *
         * Terukur di jendela 1470x900: batang biayanya duduk di y=1520
         * sedangkan tingginya halaman 1706px, jadi total bayarnya tidak pernah
         * terlihat tanpa menggulir sampai habis. Angka yang berubah tiap kali
         * isiannya disentuh tetapi tidak pernah terlihat sama saja dengan
         * tidak ada.
         */
        @media (max-width: 1499.98px) {
            .bar-samping {
                position: sticky;
                bottom: 12px;
                top: auto;
                z-index: 3;
            }

            .bar-samping .bar-biaya {
                box-shadow: 0 -8px 24px -16px rgba(15, 23, 42, .55);
            }

            /* Keterangan "nomor dibuat sistem" tidak ikut menempel: ia dibaca
               sekali lalu tidak dilihat lagi, dan menempel berarti ia menutupi
               isian setiap saat. */
            .bar-samping .bar-catatan-bawah {
                display: none;
            }
        }

        /*
         * Rel langkah: empat kartu dirangkai jadi SATU alur.
         *
         * Sebelumnya empat kartu putih bertumpuk dengan jarak 16px dan nomor
         * kecil di dalam masing-masing — terbaca seperti empat kotak yang
         * kebetulan bersebelahan, bukan satu borang berurutan. Garisnya
         * menyambungkan nomor satu ke nomor berikutnya.
         */
        .bar-utama {
            position: relative;
        }

        .bar-utama::before {
            content: '';
            position: absolute;
            left: 18px;
            /* Dari tengah nomor pertama sampai tengah nomor terakhir, bukan
               dari tepi atas: garis yang melewati nomornya terlihat seperti
               garis yang kelebihan. */
            top: 41px;
            bottom: 41px;
            width: 2px;
            background: linear-gradient(180deg, #c7d2fe 0%, #e0e7ff 55%, transparent 100%);
        }

        @media (max-width: 575.98px) {
            /* Di ponsel kartunya sudah mepet tepi layar; rel menggeser seluruh
               isinya 22px dan menyisakan lebar yang tidak sepadan dengan apa
               yang didapat. */
            .bar-utama::before {
                display: none;
            }
        }

        .bar-langkah {
            position: relative;
            margin-bottom: 15px;
            margin-left: 46px;
        }

        @media (max-width: 575.98px) {
            .bar-langkah {
                margin-left: 0;
            }
        }

        .bar-langkah-kepala {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 14px;
            padding-bottom: 12px;
            /* Garis tipis memisahkan pertanyaan dari isiannya; tanpa itu judul
               dan isian pertama terbaca sebagai satu gumpalan. */
            border-bottom: 1px solid var(--mis-garis);
        }

        /* Nomor langkah sebagai ubin bergradien — sama bahasanya dengan
           medali ikon, jadi tidak ada bentuk baru yang harus dipelajari. */
        /*
         * Nomornya keluar dari kartu dan duduk di rel. Dilingkari putih supaya
         * relnya terputus tepat di belakangnya, bukan menembusnya.
         */
        .bar-nomor {
            position: absolute;
            left: -46px;
            top: 15px;
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            box-shadow: 0 0 0 5px #f4f7ff, 0 6px 14px -8px rgba(79, 70, 229, .85);
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            font-size: .85rem;
            font-weight: 800;
        }

        @media (max-width: 575.98px) {
            .bar-nomor {
                position: static;
                width: 30px;
                height: 30px;
                border-radius: 9px;
                box-shadow: none;
            }
        }

        .bar-langkah-judul {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 700;
            color: var(--mis-tinta);
        }

        .bar-langkah-sub {
            margin: 1px 0 0;
            font-size: .8rem;
            color: var(--mis-tinta-3);
        }

        /*
         * Pilihan layanan sebagai KARTU, bukan menu jatuh.
         *
         * Empat pilihan tetap, masing-masing punya warna dan ikonnya sendiri
         * yang sudah dipakai di seluruh layar pendaftaran — jadi kartunya
         * sekaligus mengajarkan warna mana milik layanan mana. Menu jatuh
         * menyembunyikan ketiganya sampai ditekan dan tidak menampilkan apa
         * pun selain nama.
         */
        .bar-pilihan {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr));
            gap: 11px;
        }

        /*
         * Cara bayarnya hanya dua kartu. Dulu dibatasi 780px lalu
         * keterangannya ditaruh di bawahnya, sehingga separuh kanan kartu
         * langkah 3 menganggur sepenuhnya. Sekarang keterangannya naik ke
         * sebelahnya: kartu di kiri, penjelasan di kanan.
         */
        .bar-bayar-kisi {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
            gap: 14px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .bar-bayar-kisi {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .bar-bayar-ket {
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        /* Nota pertama di kolom kanan tidak perlu jarak atas lagi — kolomnya
           sudah merapatkannya sendiri. */
        .bar-bayar-ket > .bar-nota {
            margin-top: 0;
        }

        .bar-bayar-ket > .mis-isian {
            margin: 0;
        }

        .bar-pilihan input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        /* Radio-nya unsur borang SUNGGUHAN yang disembunyikan, bukan dihapus:
           papan ketik, pembaca layar, dan pengiriman borang tetap bekerja. */
        .bar-kartu {
            /* Setinggi kartu tertinggi di barisnya. Terukur tanpa ini: tiga
               kartu 86px bersanding dengan satu kartu 68px, sebab
               keterangannya cuma sebaris — tepi bawah barisnya jadi
               bergerigi. */
            height: 100%;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 14px;
            border: 1.5px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .bar-kartu:hover {
            border-color: #c7d2fe;
            transform: translateY(-1px);
        }

        .bar-pilihan input:focus-visible + .bar-kartu {
            outline: 3px solid rgba(99, 102, 241, .5);
            outline-offset: 2px;
        }

        .bar-pilihan input:checked + .bar-kartu {
            border-color: #6366f1;
            box-shadow: 0 8px 20px -14px rgba(79, 70, 229, .9);
        }

        /* display: block WAJIB — keduanya <span> di dalam <label>, dan sebagai
           unsur sebaris nama dan keterangannya menyatu dalam satu baris.
           Terlihat pada kartu Scopus Kafe, yang namanya cukup pendek sehingga
           keterangannya ikut naik: "Scopus Kafe harga diketik sendiri". */
        .bar-kartu-nama {
            display: block;
            margin: 0;
            font-size: .88rem;
            font-weight: 700;
            color: var(--mis-tinta);
            line-height: 1.3;
        }

        .bar-kartu-ket {
            display: block;
            margin: 2px 0 0;
            font-size: .74rem;
            color: var(--mis-tinta-4);
        }

        /* Tanda centang muncul hanya pada yang terpilih. */
        .bar-kartu-centang {
            margin-left: auto;
            flex: 0 0 auto;
            color: #6366f1;
            opacity: 0;
            transition: opacity .18s ease;
        }

        .bar-pilihan input:checked + .bar-kartu .bar-kartu-centang {
            opacity: 1;
        }

        .bar-kartu-centang i {
            font-size: 17px;
        }

        /* Pilihan alumni: kotak centang berkartu, bukan centang telanjang di
           antara isian teks. Seluruh kartunya bisa ditekan, jadi sasaran
           sentuhnya jauh lebih besar daripada kotak 16px-nya sendiri. */
        /*
         * Bentuknya SAMA dengan kartu pilihan di langkah 1 dan 3 — medali
         * ikon di kiri, keterangan di tengah, penanda centang di kanan.
         *
         * Sebelumnya berupa kotak centang bawaan peramban di dalam bingkai
         * polos: di antara kartu-kartu berwarna di langkah sebelum dan
         * sesudahnya, ia terbaca seperti unsur yang belum selesai digarap.
         */
        .bar-centang {
            /* Setinggi isian di sebelahnya, bukan setinggi isinya sendiri:
               kartu yang lebih pendek meninggalkan celah di bawahnya. */
            height: 100%;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            margin: 0;
            padding: 13px 15px;
            border: 1.5px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, background .18s ease,
                box-shadow .18s ease;
        }

        .bar-centang:hover {
            border-color: #c7d2fe;
        }

        .bar-centang:has(input:focus-visible) {
            outline: 2px solid #6366f1;
            outline-offset: 2px;
        }

        .bar-centang:has(input:checked) {
            border-color: #10b981;
            background: #ecfdf5;
            box-shadow: 0 8px 20px -14px rgba(16, 185, 129, .9);
        }

        /*
         * Kotak bawaannya disembunyikan, BUKAN dibuang: ia tetap unsur borang
         * sungguhan yang terkirim, tetap bisa dicapai papan tik, dan tetap
         * dibacakan pembaca layar. Yang diganti hanya rupanya.
         */
        .bar-centang input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .bar-centang-tanda {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 11px;
            background: #eef2ff;
            color: #6366f1;
            font-size: .92rem;
            transition: background .18s ease, color .18s ease;
        }

        .bar-centang:has(input:checked) .bar-centang-tanda {
            background: #d1fae5;
            color: #059669;
        }

        .bar-centang-judul {
            display: block;
            font-size: .85rem;
            font-weight: 700;
            color: var(--mis-tinta);
        }

        .bar-centang-ket {
            display: block;
            margin-top: 2px;
            font-size: .76rem;
            line-height: 1.45;
            color: var(--mis-tinta-3);
        }

        /*
         * Penanda centang di kanan. Selalu ada tempatnya — disembunyikan
         * dengan opacity, bukan display — supaya menyalakannya tidak
         * menggeser teks di sebelahnya.
         */
        .bar-centang-pilih {
            font-size: 1.06rem;
            color: #10b981;
            opacity: .22;
            transition: opacity .18s ease;
        }

        .bar-centang:has(input:checked) .bar-centang-pilih {
            opacity: 1;
        }

        /*
         * Panel potongan.
         *
         * Alumni dan dua isian potongan dulu berdiri sendiri-sendiri di kisi
         * langkah 2. Kartu alumni hanya muncul kalau tarifnya menyetelnya,
         * jadi saat ia tidak ada, baris itu tinggal dua isian di separuh kiri
         * dan separuh kanannya menganggur — terbaca seperti ada yang belum
         * selesai dimuat.
         *
         * Dikelompokkan jadi satu panel sebaris penuh, dan isinya auto-fit:
         * dua isian memakai separuh-separuh, tiga memakai sepertiga-sepertiga.
         * Berapa pun yang tampil, panelnya tetap terisi.
         */
        .bar-potongan-panel {
            padding: 13px 15px 15px;
            border: 1px dashed var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fcfcfe;
        }

        .bar-potongan-panel[hidden] {
            display: none;
        }

        .bar-potongan-judul {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 11px;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mis-tinta-3);
        }

        .bar-potongan-judul span {
            font-weight: 500;
            text-transform: none;
            letter-spacing: 0;
            color: var(--mis-tinta-4);
        }

        .bar-potongan-isi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
            gap: 14px;
            align-items: start;
        }

        /*
         * Ringkas angkatan terpilih.
         *
         * Baris pertama langkah 2 menyisakan satu jalur kosong di layar lebar
         * — menu angkatan memakai dua jalur, jumlah orang satu. Jalur itu
         * diisi keterangan yang memang dicari saat mendaftarkan orang, bukan
         * kotak hiasan.
         */
        .bar-sekilas {
            align-self: stretch;
            display: flex;
            flex-direction: column;
            gap: 9px;
            padding: 12px 14px;
            border: 1px solid #e0e7ff;
            border-radius: var(--mis-radius-kecil);
            background: linear-gradient(135deg, #faf5ff 0%, #eef2ff 100%);
        }

        .bar-sekilas[hidden] {
            display: none;
        }

        .bar-sekilas-kepala {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #4338ca;
        }

        .bar-sekilas-isi {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin: 0;
        }

        .bar-sekilas-isi > div {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
        }

        .bar-sekilas-isi dt {
            margin: 0;
            font-size: .74rem;
            font-weight: 500;
            color: var(--mis-tinta-3);
        }

        .bar-sekilas-isi dd {
            margin: 0;
            font-size: .8rem;
            font-weight: 700;
            text-align: right;
            color: var(--mis-tinta);
        }

        /* Isian yang dimatikan karena potongan alumni dipakai: terlihat
           nonaktif, bukan sekadar tidak bisa diketik tanpa penjelasan. */
        .bar-isian-mati {
            opacity: .45;
        }

        /*
         * Lebar terkecil jalurnya 240px, dan angkanya dihitung bukan ditebak.
         *
         * 215px membuka LIMA jalur — empat isian "siapa yang mendaftar" jadi
         * satu baris plus satu jalur kosong menggantung. 260px menutupnya di
         * empat, TETAPI rel langkah kemudian memakan 46px dari lebar kartunya
         * dan empatnya jatuh jadi tiga: terukur, isian angkatan dan jumlah
         * orang memenuhi satu baris sendiri sementara kartu ringkasnya turun
         * ke baris berikutnya.
         *
         * Ruang kisi di 1470px = 1114 - 46 (rel) - 44 (bantalan kartu) = 1024.
         * Empat jalur menuntut lebar terkecil <= (1024 - 3x14) / 4 = 245.
         */
        .bar-isian-kisi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
            gap: 14px;
            align-content: start;
        }

        .bar-penuh {
            grid-column: 1 / -1;
        }

        /*
         * Dua jalur, bukan sebaris penuh. Dipakai menu angkatan: isinya
         * terpanjang di borang ini ("Scopus Camp Yogyakarta — Rp 5.500.000 ·
         * sisa 20 kursi"), jadi satu jalur memotongnya dengan elipsis,
         * sedangkan sebaris penuh menyisakan jumlah orang sendirian di baris
         * berikutnya.
         */
        .bar-lebar {
            grid-column: span 2;
        }

        @media (max-width: 575.98px) {
            .bar-lebar {
                grid-column: 1 / -1;
            }
        }

        /*
         * Isian yang nilainya pendek TIDAK melar selebar kartu.
         *
         * Menu angkatan sempat merentang 1.500px untuk satu baris teks, dan
         * isian selebar itu membuat mata menyapu jauh tanpa alasan. Dibatasi
         * 520px — cukup untuk nama angkatan terpanjang beserta harga dan sisa
         * kursinya — dan tetap menyusut sendiri di layar sempit.
         */
        .bar-penuh > select,
        .bar-penuh > input,
        .bar-lebar > select {
            max-width: 520px;
        }

        /* Batang biaya: angka besar di kiri, rincian di tengah, tombol di
           kanan. Satu baris di layar lebar, menumpuk di ponsel. */
        .bar-biaya {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px 22px;
            padding: 16px 20px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius);
            background: linear-gradient(135deg, #faf5ff 0%, #eef2ff 100%);
        }

        .bar-biaya-angka-blok {
            flex: 0 0 auto;
        }

        /*
         * Rincian biaya sebagai daftar istilah, bukan kalimat.
         *
         * <dl> dipakai memang untuk pasangan label-nilai, dan pembaca layar
         * mengumumkannya sebagai pasangan — kalimat "harga 5.500.000 dikali 2
         * dikurangi 500.000" menuntut didengar sampai habis untuk tahu
         * angkanya.
         */
        .bar-rinci {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 20px;
            margin: 0;
            padding-left: 20px;
            border-left: 1px solid #ddd6fe;
        }

        .bar-rinci > div {
            display: flex;
            align-items: baseline;
            gap: 7px;
        }

        .bar-rinci dt {
            margin: 0;
            font-size: .7rem;
            font-weight: 600;
            color: var(--mis-tinta-4);
        }

        .bar-rinci dd {
            margin: 0;
            font-size: .82rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--mis-tinta-2);
        }

        /* Potongan bertinta hijau dan berawalan minus: ia MENGURANGI, dan
           angka yang mengurangi di antara angka yang menambah harus terbaca
           berbeda tanpa membaca labelnya. */
        .bar-rinci-kurang {
            color: #047857 !important;
        }

        .bar-catatan-bawah {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 11px 2px 0;
            font-size: .76rem;
            line-height: 1.5;
            color: var(--mis-tinta-4);
        }

        .bar-catatan-bawah i {
            flex: 0 0 auto;
            margin-top: 3px;
            font-size: inherit;
        }

        .bar-biaya-angka {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: var(--mis-tinta);
            line-height: 1.15;
        }

        .bar-biaya-label {
            margin: 1px 0 0;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .bar-biaya-aksi {
            margin-left: auto;
            align-self: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 575.98px) {
            .bar-biaya-aksi {
                margin-left: 0;
                width: 100%;
            }

            .bar-biaya-aksi .mis-tombol {
                flex: 1 1 100%;
                justify-content: center;
            }
        }

        /*
         * Di kolom samping, batang biayanya berdiri jadi panel.
         *
         * Aturannya di bawah .bar-samping, bukan menimpa .bar-biaya langsung:
         * di bawah 1500px kolomnya turun jadi selebar halaman, dan di sana
         * bentuk mendatarnya yang benar — angka besar di kiri, tombol di
         * kanan.
         */
        @media (min-width: 1500px) {
            .bar-samping .bar-biaya {
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
            }

            .bar-samping .bar-biaya-angka-blok {
                padding-bottom: 13px;
                border-bottom: 1px dashed var(--mis-garis);
            }

            .bar-samping .bar-rinci {
                width: 100%;
            }

            .bar-samping .bar-biaya-aksi {
                margin-left: 0;
                /*
                 * align-self WAJIB disebut: aturan dasarnya memasang
                 * `align-self: center`, dan itu menang atas `align-items:
                 * stretch` milik wadahnya. Terukur tanpa baris ini, blok
                 * tombolnya menyusut ke 192px di panel selebar 340px dan
                 * kedua tombolnya menggantung di tengah.
                 */
                align-self: stretch;
                flex-direction: column-reverse;
                gap: 9px;
            }

            /* Tombol simpannya di ATAS tombol batal — itu yang dituju, dan
               urutan sumbernya tetap Batal lalu Simpan supaya runtun papan
               tiknya tidak terbalik. */
            .bar-samping .bar-biaya-aksi .mis-tombol {
                width: 100%;
                justify-content: center;
            }

            .bar-samping .bar-catatan-bawah {
                margin-top: 13px;
            }
        }

        .bar-nota {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 13px 0 0;
            padding: 11px 14px;
            border: 1px solid #bfdbfe;
            border-radius: var(--mis-radius-kecil);
            background: #eff6ff;
            font-size: .79rem;
            line-height: 1.5;
            color: #1d4ed8;
        }

        .bar-nota i {
            flex: 0 0 auto;
            margin-top: 2px;
            font-size: inherit;
        }

        /* Bagian yang baru berlaku sesudah layanannya dipilih. Disembunyikan
           lewat atribut hidden, bukan kelas: tanpa JavaScript seluruhnya
           tetap terlihat dan borangnya masih bisa dipakai. */
        .bar-langkah[hidden] {
            display: none;
        }
    </style>
@endpush

@section('content')
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    /*
     * Angkatan dirakit jadi data yang bisa dibaca skrip: pilihan angkatan
     * ditukar di peramban saat layanannya berganti, tanpa memuat ulang
     * halaman. Hanya kolom yang memang dipakai yang ikut — harga, kursi, dan
     * tanggalnya — bukan seluruh baris.
     */
    $angkatanJson = [];

    foreach ($angkatan as $kunciLayanan => $daftar) {
        $angkatanJson[$kunciLayanan] = $daftar->map(fn ($a) => [
            'id' => $a->id,
            'varian' => $a->varian ?? '',
            'nama' => $a->nama,
            // Nomor angkatannya WAJIB ikut: tiap angkatan punya tanggal
            // pelaksanaan sendiri, dan lima angkatan Scopus Camp yang akan
            // datang bernama sama persis.
            'nomor' => ($a->nama_ke === null || $a->nama_ke === '') ? null : (string) $a->nama_ke,
            // Tanggalnya dirangkai di PHP, bukan di JS: APP_LOCALE=en, jadi
            // nama bulan Indonesia hanya keluar lewat Carbon ->locale('id').
            'tanggal' => $a->mulai
                ? \Illuminate\Support\Carbon::parse($a->mulai)->locale('id')->translatedFormat('j M Y')
                : null,
            'mulai' => $a->mulai,
            'harga' => (int) ($a->total_biaya ?: $a->biaya),
            'total_kuota' => $a->total_kuota === null ? null : (int) $a->total_kuota,
            'sisa_kuota' => $a->sisa_kuota === null ? null : (int) $a->sisa_kuota,
        ])->values();
    }
@endphp
<div class="main-content mis-badan">
    <section class="section bar-wadah">

        <div class="mis-kepala">
            <span class="mis-medali mis-hijau" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Daftarkan Pendaftar</h1>
                <p class="mis-sub">
                    Untuk yang mendaftar lewat WhatsApp, datang langsung, atau membayar di tempat.
                </p>
            </div>
            <div class="mis-kepala-aksi">
                <a class="mis-tombol mis-tombol-halus" href="{{ route('account.pendaftaran-layanan.index') }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar
                </a>
            </div>
        </div>

        @include('account.pendaftaran_layanan.partials.pesan')

        <form method="POST" action="{{ route('account.pendaftaran-layanan.simpan') }}" id="bar-borang">
            @csrf

            <div class="bar-kerja">
            <div class="bar-utama">

            {{-- ------------------------------------ langkah 1: layanan --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">1</span>
                    <div>
                        <p class="bar-langkah-judul">Layanan apa?</p>
                        <p class="bar-langkah-sub">Pilih satu; isian berikutnya menyesuaikan sendiri.</p>
                    </div>
                </div>

                <div class="bar-pilihan">
                    @foreach ($katalog as $kunci => $l)
                        <label>
                            <input type="radio" name="layanan" value="{{ $kunci }}"
                                data-berangkatan="{{ $l['berangkatan'] ? '1' : '0' }}"
                                data-potongan="{{ $l['bisa_potongan'] ? '1' : '0' }}"
                                data-varian="{{ $l['varian_sendiri'] && $varian[$kunci] !== [] ? '1' : '0' }}"
                                @checked(old('layanan', $terpilih) === $kunci) required>
                            <span class="bar-kartu">
                                <span class="mis-medali {{ $l['warna'] }}" aria-hidden="true">
                                    <i class="fas {{ $l['ikon'] }}"></i>
                                </span>
                                <span style="min-width: 0;">
                                    <span class="bar-kartu-nama">{{ $l['nama'] }}</span>
                                    <span class="bar-kartu-ket">
                                        {{ $l['berangkatan'] ? 'berangkatan, harga ikut angkatan' : 'harga diketik sendiri' }}
                                    </span>
                                </span>
                                <span class="bar-kartu-centang" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                            </span>
                        </label>
                    @endforeach
                </div>

                {{-- Disebut apa adanya, bukan dibiarkan jadi pertanyaan: layanan
                     yang tidak ada di sini memang tidak bisa didaftarkan dari
                     layar ini, dan alasannya bukan kelalaian. --}}
                <p class="bar-nota">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>
                        <strong>Clinik Scopus tidak ada di sini.</strong> Pemesanannya mengikat
                        sesi tertentu, trainer yang mendampingi, dan akun pelanggan — ketiganya
                        dipilih lewat alur pemesanan Clinik Scopus sendiri.
                    </span>
                </p>
            </div>

            {{-- ------------------------------ langkah 2: angkatan & biaya --}}
            {{-- SELALU ada, tidak pernah disembunyikan.

                 Versi sebelumnya menyembunyikan langkah ini untuk layanan
                 tanpa angkatan, sehingga nomor langkahnya melompat 1 ke 3 dan
                 terbaca seperti ada yang rusak. Sekarang isinya yang berganti:
                 angkatan untuk yang berangkatan, nominal ketik untuk yang
                 tidak — keduanya sama-sama "berapa yang dibayar". --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">2</span>
                    <div>
                        <p class="bar-langkah-judul">Berapa yang dibayar?</p>
                        <p class="bar-langkah-sub" id="bar-sub-biaya">Pilih layanannya dulu.</p>
                    </div>
                </div>

                <div class="bar-isian-kisi">
                    <div class="mis-isian bar-lebar" id="bar-bungkus-angkatan">
                        <label class="mis-label" for="bar-angkatan">Angkatan</label>
                        <select class="form-control-modern" id="bar-angkatan" name="kategori_id">
                            <option value="">Pilih layanan dulu</option>
                        </select>
                        <p class="mis-bantuan" id="bar-angkatan-ket">
                            Hanya angkatan yang belum lewat yang ditawarkan.
                        </p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-jumlah">
                        <label class="mis-label" for="bar-jumlah">Jumlah orang</label>
                        <input type="number" class="form-control-modern" id="bar-jumlah" name="jumlah"
                            value="{{ old('jumlah', 1) }}" min="1" max="99">
                        <p class="mis-bantuan">Untuk pendaftaran rombongan.</p>
                    </div>

                    {{-- Pilihan varian untuk layanan TANPA angkatan.

                         Empat layanan lain mewarisi variannya dari angkatan
                         yang dipilih. Scopus Kafe tidak berangkatan, jadi
                         sebelum ini varian Online/Offline yang disetel di
                         layar Layanan tidak muncul di mana pun — borangnya
                         hanya menyodorkan kotak nominal kosong. --}}
                    <div class="mis-isian bar-lebar" id="bar-bungkus-varian" hidden>
                        <span class="mis-label">Pilih varian</span>
                        <div class="bar-pilihan bar-pilihan-bayar" id="bar-varian-kartu"></div>
                        <p class="mis-bantuan" id="bar-varian-ket">
                            Harganya ikut tarif varian yang dipilih.
                        </p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-total" hidden>
                        <label class="mis-label" for="bar-total">Total bayar</label>
                        <input type="text" class="form-control-modern" id="bar-total" name="total"
                            value="{{ old('total') }}" inputmode="numeric" placeholder="contoh: 250000">
                        <p class="mis-bantuan">Dalam rupiah, tanpa titik.</p>
                    </div>

                    {{-- Mengisi jalur yang sebelumnya menganggur di baris ini,
                         dan isinya bukan sekadar pengisi: ketiga angka inilah
                         yang ditanyakan pendaftar lewat WhatsApp — tanggal
                         mulai, sisa kursi, dan harganya. Sebelumnya panitia
                         harus membuka layar Angkatan Layanan untuk menjawab. --}}
                    <div class="bar-sekilas" id="bar-sekilas" hidden>
                        <p class="bar-sekilas-kepala">
                            <i class="fas fa-layer-group" aria-hidden="true"></i>
                            <span id="bar-sekilas-judul">Angkatan terpilih</span>
                        </p>
                        <dl class="bar-sekilas-isi">
                            <div>
                                <dt>Mulai</dt>
                                <dd id="bar-sekilas-tanggal">—</dd>
                            </div>
                            <div>
                                <dt>Sisa kursi</dt>
                                <dd id="bar-sekilas-sisa">—</dd>
                            </div>
                            <div>
                                <dt>Harga per orang</dt>
                                <dd id="bar-sekilas-harga">—</dd>
                            </div>
                        </dl>
                    </div>


                    {{-- Potongan ALUMNI, disetel sekali di Tarif Layanan.

                         Dicentang, ia MENGGANTIKAN potongan khusus — bukan
                         menambahnya. Dua potongan yang ditumpuk membuat harga
                         akhirnya tidak bisa dijelaskan dari salah satunya, dan
                         panitia yang memberi potongan khusus kepada seorang
                         alumni hampir selalu bermaksud menggantikannya. --}}
                    <div class="bar-potongan-panel bar-penuh" id="bar-panel-potongan" hidden>
                    <p class="bar-potongan-judul">
                        <i class="fas fa-tags" aria-hidden="true"></i> Potongan <span>opsional</span>
                    </p>
                    <div class="bar-potongan-isi">

                    <div class="mis-isian" id="bar-bungkus-alumni" hidden>
                        <label class="bar-centang" for="bar-alumni">
                            <input type="checkbox" id="bar-alumni"
                                name="alumni" value="1" @checked(old('alumni'))>
                            <span class="bar-centang-tanda" aria-hidden="true">
                                <i class="fas fa-user-graduate"></i>
                            </span>
                            <span>
                                <span class="bar-centang-judul">Pendaftar ini alumni</span>
                                <span class="bar-centang-ket" id="bar-alumni-ket">
                                    Potongannya ikut aturan di Tarif Layanan.
                                </span>
                            </span>
                            <span class="bar-centang-pilih" aria-hidden="true">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        </label>
                    </div>

                    {{-- Potongan KHUSUS, di atas potongan bawaan angkatannya.

                         Angkatan sudah punya diskonnya sendiri dan itu sudah
                         terhitung di harganya; yang ini untuk hal yang tidak
                         bisa diketahui angkatan — peserta yang disponsori,
                         harga mitra, atau kesepakatan di tempat. --}}
                    <div class="mis-isian" id="bar-bungkus-potongan">
                        <label class="mis-label" for="bar-potongan">Potongan khusus</label>
                        <input type="text" class="form-control-modern" id="bar-potongan" name="potongan"
                            value="{{ old('potongan') }}" inputmode="numeric" placeholder="boleh dikosongkan">
                        <p class="mis-bantuan">Rupiah, di luar diskon angkatannya.</p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-kode">
                        <label class="mis-label" for="bar-kode-potongan">Alasan potongan</label>
                        <input type="text" class="form-control-modern" id="bar-kode-potongan"
                            name="kode_potongan" value="{{ old('kode_potongan') }}" maxlength="40"
                            placeholder="mis. SPONSOR, MITRA">
                        {{-- Tanpa keterangan, potongan Rp 500.000 pada satu
                             pendaftaran tidak bisa dijelaskan siapa pun enam
                             bulan kemudian. --}}
                        <p class="mis-bantuan">Supaya potongannya bisa dijelaskan nanti.</p>
                    </div>

                    </div>{{-- /bar-potongan-isi --}}
                    </div>{{-- /bar-panel-potongan --}}
                </div>
            </div>

            {{-- -------------------------------- langkah 3: cara bayarnya --}}
            {{-- Ada demi pendaftar yang DIBANTU panitia.

                 Sebelum ini satu-satunya cara bayar yang bisa dicatat adalah
                 transfer, jadi yang menyerahkan uang di tempat tercatat
                 "menunggu bayar" tanpa bukti — tidak bisa dibedakan dari yang
                 memang belum membayar, dan ikut tertandai menggantung setelah
                 7 hari. --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">3</span>
                    <div>
                        <p class="bar-langkah-judul">Bagaimana bayarnya?</p>
                        <p class="bar-langkah-sub">Pilih yang sesuai; sisanya menyesuaikan sendiri.</p>
                    </div>
                </div>

                <div class="bar-bayar-kisi">
                <div class="bar-pilihan">
                    @foreach ($caraBayar as $kunci => $c)
                        <label>
                            <input type="radio" name="cara_bayar" value="{{ $kunci }}"
                                data-perlu-bukti="{{ $c['perlu_bukti'] ? '1' : '0' }}"
                                @checked(old('cara_bayar', 'transfer') === $kunci) required>
                            <span class="bar-kartu">
                                <span class="mis-medali {{ $c['warna'] }}" aria-hidden="true">
                                    <i class="fas {{ $c['ikon'] }}"></i>
                                </span>
                                <span style="min-width: 0;">
                                    <span class="bar-kartu-nama">{{ $c['label'] }}</span>
                                    <span class="bar-kartu-ket">{{ $c['ket'] }}</span>
                                </span>
                                <span class="bar-kartu-centang" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="bar-bayar-ket">

                {{-- Hanya muncul untuk tunai: melunaskan transfer dari borang
                     ini berarti melunaskan sebelum ada yang mencocokkannya
                     dengan mutasi rekening. --}}
                <div class="mis-isian bar-penuh" id="bar-bungkus-terima" hidden>
                    <label class="bar-centang" for="bar-terima">
                        <input type="checkbox" id="bar-terima"
                            name="uang_diterima" value="1" @checked(old('uang_diterima'))>
                        <span class="bar-centang-tanda" aria-hidden="true">
                            <i class="fas fa-hand-holding-usd"></i>
                        </span>
                        <span>
                            <span class="bar-centang-judul">Uangnya sudah saya terima</span>
                            <span class="bar-centang-ket">
                                Pendaftarannya langsung dicatat lunas. Biarkan kosong kalau
                                orangnya baru akan membayar saat datang.
                            </span>
                        </span>
                        <span class="bar-centang-pilih" aria-hidden="true">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    </label>
                </div>

                <p class="bar-nota" id="bar-nota-bukti">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>
                        <strong>Bukti transfernya diunggah nanti</strong> dari halaman rincian
                        pendaftaran ini, setelah uangnya masuk — borang ini tidak memintanya
                        supaya pendaftarnya bisa dicatat lebih dulu.
                    </span>
                </p>

                </div>{{-- /kolom kanan --}}
                </div>{{-- /bar-bayar-kisi --}}

                <p class="bar-nota" id="bar-nota-doku" hidden>
                    <i class="fas fa-credit-card" aria-hidden="true"></i>
                    <span>
                        <strong>Pembayaran daring (DOKU)</strong> tidak dipilih dari sini.
                        Pendaftar yang membayar sendiri lewat halaman DOKU mendapat tandanya
                        otomatis saat tagihannya dibuat.
                    </span>
                </p>
            </div>

            {{-- ------------------------------------- langkah 4: orangnya --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">4</span>
                    <div>
                        <p class="bar-langkah-judul">Siapa yang mendaftar?</p>
                        <p class="bar-langkah-sub">Empat isian; nomor, status, dan kode uniknya diisi sistem.</p>
                    </div>
                </div>

                <div class="bar-isian-kisi">
                    <div class="mis-isian">
                        <label class="mis-label" for="bar-nama">Nama lengkap</label>
                        <input type="text" class="form-control-modern" id="bar-nama" name="nama"
                            value="{{ old('nama') }}" required maxlength="255" autocomplete="off">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-email">Email</label>
                        <input type="email" class="form-control-modern" id="bar-email" name="email"
                            value="{{ old('email') }}" required maxlength="255" autocomplete="off">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-telp">Nomor WhatsApp</label>
                        <input type="text" class="form-control-modern" id="bar-telp" name="telp"
                            value="{{ old('telp') }}" required maxlength="30" autocomplete="off"
                            inputmode="tel" placeholder="08xx atau 62xx">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-affiliasi">Afiliasi / instansi</label>
                        <input type="text" class="form-control-modern" id="bar-affiliasi" name="affiliasi"
                            value="{{ old('affiliasi') }}" maxlength="255" autocomplete="off"
                            placeholder="boleh dikosongkan">
                    </div>

                    <div class="mis-isian bar-penuh">
                        <label class="mis-label" for="bar-note">Catatan panitia</label>
                        <textarea class="form-control-modern" id="bar-note" name="note" rows="2"
                            maxlength="1000" placeholder="boleh dikosongkan">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            </div>{{-- /bar-utama --}}

            {{-- ------------------------------- ringkasan biaya, menempel --}}
            <aside class="bar-samping">
            <div class="bar-biaya">
                <div class="bar-biaya-angka-blok">
                    <p class="bar-biaya-angka" id="bar-angka">Rp 0</p>
                    <p class="bar-biaya-label">Total bayar</p>
                </div>

                {{-- Rinciannya disebut, bukan cuma hasil akhirnya: panitia yang
                     memberi potongan perlu melihat potongannya memang masuk,
                     dan yang tidak memberi potongan tidak perlu melihat baris
                     yang selalu nol. Karena itu tiap barisnya hanya muncul
                     saat memang ada isinya. --}}
                <dl class="bar-rinci" id="bar-rinci" hidden>
                    <div id="bar-rinci-satuan" hidden>
                        <dt>Harga satuan</dt>
                        <dd id="bar-nilai-satuan">—</dd>
                    </div>
                    <div id="bar-rinci-jumlah" hidden>
                        <dt>Jumlah orang</dt>
                        <dd id="bar-nilai-jumlah">—</dd>
                    </div>
                    <div id="bar-rinci-potongan" hidden>
                        <dt>Potongan khusus</dt>
                        <dd id="bar-nilai-potongan" class="bar-rinci-kurang">—</dd>
                    </div>
                </dl>

                <div class="bar-biaya-aksi">
                    <a class="mis-tombol mis-tombol-halus" href="{{ route('account.pendaftaran-layanan.index') }}">
                        Batal
                    </a>
                    <button type="submit" class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-save" aria-hidden="true"></i> Simpan pendaftaran
                    </button>
                </div>
            </div>

            {{-- Keterangan apa yang dibuat sistem ditaruh DI BAWAH batang
                 biaya, bukan di dalamnya: ia dibaca sekali lalu tidak pernah
                 dilihat lagi, sementara angka di atasnya dilihat tiap kali
                 isiannya berubah. --}}
            <p class="bar-catatan-bawah">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                Nomor pendaftaran, status, dan kode unik dibuat sistem sesudah disimpan.
                Kode unik itu yang membuat nominalnya bisa dicocokkan dengan mutasi rekening.
            </p>
            </aside>
            </div>{{-- /bar-kerja --}}

        </form>

    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        /*
         * Pilihan angkatan ditukar di peramban saat layanannya berganti —
         * tanpa memuat ulang halaman, dan tanpa satu kueri pun tambahan:
         * seluruh angkatan keempat layanan sudah ikut terkirim bersama
         * halamannya. Daftarnya 59 baris di seluruh basis data, jadi
         * mengirimnya sekaligus jauh lebih murah daripada satu permintaan
         * tiap kali pilihannya berubah.
         */
        var ANGKATAN = @json($angkatanJson);
        var VARIAN = @json($varian);
        var TARIF = @json($tarif);
        var LAMA_VARIAN = @json(old('varian'));
        var LAMA_ANGKATAN = @json(old('kategori_id'));

        /*
         * Potongan alumni tiap tarif aktif, berkunci "layanan|varian".
         *
         * Dikunci varian pula, sebab satu layanan bisa punya beberapa tarif
         * dengan harga berbeda — Scopus Camp punya jawa dan luar_jawa — dan
         * potongan alumninya disetel per tarif.
         */
        var ALUMNI = @json($alumni);

        var borang = document.getElementById('bar-borang');

        if (!borang) {
            return;
        }

        var el = function (id) { return document.getElementById(id); };

        var menuAngkatan = el('bar-angkatan');
        var bungkusAngkatan = el('bar-bungkus-angkatan');
        var bungkusJumlah = el('bar-bungkus-jumlah');
        var bungkusTotal = el('bar-bungkus-total');
        var bungkusPotongan = el('bar-bungkus-potongan');
        var bungkusKode = el('bar-bungkus-kode');
        var isianTotal = el('bar-total');
        var isianJumlah = el('bar-jumlah');
        var isianPotongan = el('bar-potongan');
        var isianKode = el('bar-kode-potongan');
        var centangAlumni = el('bar-alumni');
        var bungkusAlumni = el('bar-bungkus-alumni');
        var ketAlumni = el('bar-alumni-ket');
        var bungkusVarian = el('bar-bungkus-varian');
        var kartuVarian = el('bar-varian-kartu');
        var ketVarian = el('bar-varian-ket');
        var panelPotongan = el('bar-panel-potongan');
        var sekilas = el('bar-sekilas');
        var sekilasJudul = el('bar-sekilas-judul');
        var sekilasTanggal = el('bar-sekilas-tanggal');
        var sekilasSisa = el('bar-sekilas-sisa');
        var sekilasHarga = el('bar-sekilas-harga');
        var bungkusTerima = el('bar-bungkus-terima');
        var centangTerima = el('bar-terima');
        var notaBukti = el('bar-nota-bukti');
        var angka = el('bar-angka');
        var ket = el('bar-angkatan-ket');
        var subBiaya = el('bar-sub-biaya');

        var rinci = el('bar-rinci');
        var rinciSatuan = el('bar-rinci-satuan');
        var rinciJumlah = el('bar-rinci-jumlah');
        var rinciPotongan = el('bar-rinci-potongan');
        var nilaiSatuan = el('bar-nilai-satuan');
        var nilaiJumlah = el('bar-nilai-jumlah');
        var nilaiPotongan = el('bar-nilai-potongan');

        var rupiah = function (n) {
            return 'Rp ' + (n || 0).toLocaleString('id-ID');
        };

        var angkaDari = function (teks) {
            var n = parseInt(String(teks || '').replace(/\D+/g, ''), 10);
            return isNaN(n) ? 0 : n;
        };

        /* Berapa persen potongan alumni untuk pilihan yang sedang dibuat. */
        var persenAlumni = function (pilih) {
            if (!pilih) {
                return 0;
            }

            var varian = '';

            if (pilih.berangkatan) {
                var o = menuAngkatan.options[menuAngkatan.selectedIndex];
                varian = o ? (o.dataset.varian || '') : '';
            }

            return ALUMNI[pilih.nilai + '|' + varian] || 0;
        };

        var layananTerpilih = function () {
            var r = borang.querySelector('input[name="layanan"]:checked');

            return r ? {
                nilai: r.value,
                berangkatan: r.dataset.berangkatan === '1',
                bisaPotongan: r.dataset.potongan === '1',
                punyaVarian: r.dataset.varian === '1',
            } : null;
        };

        var isiAngkatan = function (layanan) {
            var daftar = ANGKATAN[layanan] || [];

            menuAngkatan.innerHTML = '';

            if (!daftar.length) {
                var kosong = document.createElement('option');
                kosong.value = '';
                kosong.textContent = 'Belum ada angkatan yang akan datang';
                menuAngkatan.appendChild(kosong);
                ket.textContent = 'Buat angkatannya dulu di layar Angkatan Layanan.';
                return;
            }

            /*
             * Dikelompokkan per NAMA angkatan, dan itu bukan hiasan.
             *
             * Terukur: lima angkatan Scopus Camp yang akan datang bernama
             * "Scopus Camp Yogyakarta" semua. Ditulis namanya saja, kelima
             * pilihannya identik huruf per huruf dan panitia memilih secara
             * untung-untungan. Nama pindah ke kepala kelompok, dan yang
             * membedakan — nomor angkatan dan TANGGAL PELAKSANAANNYA — yang
             * ditulis di tiap barisnya.
             */
            var kelompok = {};

            daftar.forEach(function (a) {
                var o = document.createElement('option');
                o.value = a.id;
                o.dataset.harga = a.harga;
                o.dataset.varian = a.varian || '';

                /*
                 * Harga dan sisa kursi ikut tertulis di pilihannya.
                 *
                 * Tanpa itu panitia harus membuka layar Angkatan Layanan untuk
                 * tahu angkatan mana yang masih longgar — dan angkatan yang
                 * sudah penuh baru ketahuan sesudah kirimannya ditolak.
                 */
                var sisa = a.sisa_kuota === null
                    ? 'tanpa batas kuota'
                    : ('sisa ' + a.sisa_kuota + ' kursi');

                var bagian = [];

                // "Angkatan ke-", bukan "#": penggunanya bukan orang teknis,
                // dan tanda pagar tidak terbaca sebagai nomor urut.
                if (a.nomor) {
                    bagian.push('Angkatan ke-' + a.nomor);
                }

                if (a.tanggal) {
                    bagian.push(a.tanggal);
                }

                bagian.push(rupiah(a.harga));
                bagian.push(sisa);

                o.textContent = bagian.join(' · ');

                /*
                 * Angkatan tanpa nomor DAN tanpa tanggal tidak punya apa pun
                 * yang membedakannya; namanya tetap ditulis di barisnya supaya
                 * ia tidak jadi baris yang hanya berisi harga.
                 */
                if (!a.nomor && !a.tanggal) {
                    o.textContent = a.nama + ' · ' + o.textContent;
                }

                if (!kelompok[a.nama]) {
                    var g = document.createElement('optgroup');
                    g.label = a.nama;
                    kelompok[a.nama] = g;
                    menuAngkatan.appendChild(g);
                }

                kelompok[a.nama].appendChild(o);
            });

            ket.textContent = 'Hanya angkatan yang belum lewat yang ditawarkan.';

            if (LAMA_ANGKATAN) {
                menuAngkatan.value = LAMA_ANGKATAN;
                LAMA_ANGKATAN = null;
            }
        };

        /**
         * Menegaskan angkatan yang sedang terpilih di bawah menunya.
         *
         * Menu tertutup MEMOTONG teksnya di layar sempit: terukur di 320px,
         * ruang teksnya 209px sedangkan "Angkatan ke-202 · 30 Okt 2026"
         * menuntut 210px — tanggalnya terpotong tepat di huruf terakhir.
         * Padahal justru nomor dan tanggal itu yang membedakan kelima
         * angkatan Scopus Camp yang bernama sama.
         *
         * Baris ini membungkus, jadi ia tidak pernah terpotong di lebar mana
         * pun.
         */
        var tegaskanAngkatan = function (layanan) {
            var daftar = ANGKATAN[layanan] || [];

            if (!daftar.length) {
                ket.textContent = 'Buat angkatannya dulu di layar Angkatan Layanan.';
                return;
            }

            var id = menuAngkatan.value;
            var a = null;

            for (var i = 0; i < daftar.length; i++) {
                if (daftar[i].id === id) {
                    a = daftar[i];
                    break;
                }
            }

            if (!a) {
                ket.textContent = 'Hanya angkatan yang belum lewat yang ditawarkan.';
                tampil(sekilas, false);
                return;
            }

            var kata = ['Terpilih: ' + a.nama];

            if (a.nomor) {
                kata.push('angkatan ke-' + a.nomor);
            }

            if (a.tanggal) {
                kata.push('mulai ' + a.tanggal);
            }

            ket.textContent = kata.join(', ') + '.';

            sekilasJudul.textContent = a.nomor ? ('Angkatan ke-' + a.nomor) : a.nama;
            sekilasTanggal.textContent = a.tanggal || 'belum dijadwalkan';
            sekilasSisa.textContent = a.sisa_kuota === null
                ? 'tanpa batas'
                : (a.sisa_kuota + ' kursi');
            sekilasHarga.textContent = rupiah(a.harga);

            tampil(sekilas, true);
        };

        var tampil = function (unsur, tampak) {
            if (unsur) {
                unsur.hidden = !tampak;
            }
        };

        /**
         * Menyesuaikan langkah cara bayar dengan pilihannya.
         *
         * "Uangnya sudah saya terima" HANYA untuk tunai: melunaskan transfer
         * dari borang ini berarti melunaskan sebelum ada yang mencocokkannya
         * dengan mutasi rekening. Centangnya ikut dilepas saat disembunyikan —
         * yang tersembunyi tidak terkirim, dan nilai lama yang menempel
         * membuat layar dan yang tersimpan berbeda.
         */
        var segarkanBayar = function () {
            var pilih = borang.querySelector('input[name="cara_bayar"]:checked');
            var tunai = !!pilih && pilih.value === 'tunai';

            tampil(bungkusTerima, tunai);
            tampil(notaBukti, !tunai);

            if (!tunai && centangTerima) {
                centangTerima.checked = false;
            }
        };

        var hitung = function () {
            var pilih = layananTerpilih();

            if (!pilih) {
                angka.textContent = 'Rp 0';
                tampil(rinci, false);
                return;
            }

            var satuan = 0;
            var jml = 1;

            if (pilih.berangkatan) {
                var o = menuAngkatan.options[menuAngkatan.selectedIndex];
                satuan = o ? parseInt(o.dataset.harga || '0', 10) : 0;
                jml = Math.max(1, angkaDari(isianJumlah.value) || 1);
            } else {
                // Nominal diketik: yang bukan angka dibuang, sama dengan cara
                // peladen membacanya — jadi angka di layar dan yang tersimpan
                // tidak pernah berbeda.
                satuan = angkaDari(isianTotal.value);
            }

            var subtotal = satuan * jml;
            var persen = persenAlumni(pilih);
            var pakaiAlumni = centangAlumni.checked && persen > 0;

            /*
             * Alumni MENGGANTIKAN potongan khusus, tidak menambahnya — sama
             * dengan aturan di peladen. Isian potongan khususnya dimatikan
             * supaya tidak ada yang mengetik angka yang kemudian diabaikan
             * diam-diam.
             */
            var potongan;

            if (pakaiAlumni) {
                potongan = Math.round(subtotal * persen / 100);
            } else {
                potongan = pilih.bisaPotongan ? angkaDari(isianPotongan.value) : 0;
            }

            potongan = Math.min(subtotal, potongan);

            [isianPotongan, isianKode].forEach(function (n) {
                n.disabled = pakaiAlumni;
                n.closest('.mis-isian').classList.toggle('bar-isian-mati', pakaiAlumni);
            });

            angka.textContent = rupiah(subtotal - potongan);

            nilaiSatuan.textContent = rupiah(satuan);
            nilaiJumlah.textContent = jml + ' orang';
            nilaiPotongan.textContent = '− ' + rupiah(potongan)
                + (pakaiAlumni ? ' (alumni ' + persen + '%)' : '');

            // Tiap baris rincian hanya muncul kalau memang ada isinya; baris
            // yang selalu nol cuma menambah yang harus dibaca.
            tampil(rinciSatuan, pilih.berangkatan && satuan > 0);
            tampil(rinciJumlah, pilih.berangkatan && jml > 1);
            tampil(rinciPotongan, potongan > 0);

            tampil(rinci, (pilih.berangkatan && satuan > 0 && (jml > 1 || potongan > 0)) || potongan > 0);
        };

        /**
         * Menawarkan potongan alumni, kalau tarifnya memang menyetelnya.
         *
         * Dipanggil SESUDAH pilihan angkatannya ada. Potongannya disetel per
         * VARIAN, dan variannya dibaca dari pilihan angkatan yang sedang
         * terpilih — dihitung sebelum pilihannya ada, variannya masih kosong
         * dan tawarannya tidak pernah muncul. Terukur: pilihan alumni tidak
         * tampil sama sekali padahal tarifnya menyetel 15%.
         */
        var tawarkanAlumni = function (pilih) {
            var persen = persenAlumni(pilih);

            tampil(bungkusAlumni, pilih.bisaPotongan && persen > 0);

            if (persen > 0) {
                ketAlumni.textContent = 'Potongan ' + persen
                    + '% dari Tarif Layanan. Tidak bisa digabung dengan potongan khusus.';
            } else {
                // Tersembunyi berarti juga tidak ikut terkirim; centangnya
                // dilepas supaya nilai lama tidak menempel saat berganti
                // layanan.
                centangAlumni.checked = false;
            }
        };

        /**
         * Merakit kartu varian untuk layanan yang memilih variannya sendiri.
         *
         * Bentuknya sama dengan kartu cara bayar, bukan menu tarik: pilihannya
         * cuma dua atau tiga, dan kartu menampung harganya sekaligus — yang
         * justru paling dicari saat memilih.
         */
        var isiVarian = function (layanan) {
            var daftar = VARIAN[layanan] || {};

            kartuVarian.innerHTML = '';

            var kunci = Object.keys(daftar);
            var pertama = null;

            kunci.forEach(function (kode) {
                var id = 'bar-varian-' + kode;
                var harga = TARIF[layanan + '|' + kode];

                var label = document.createElement('label');
                var radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'varian';
                radio.value = kode;
                radio.id = id;

                var kartu = document.createElement('span');
                kartu.className = 'bar-kartu';

                var medali = document.createElement('span');
                medali.className = 'mis-medali mis-biru';
                medali.setAttribute('aria-hidden', 'true');
                medali.innerHTML = '<i class="fas fa-tag"></i>';

                var teks = document.createElement('span');
                teks.style.minWidth = '0';

                var nama = document.createElement('span');
                nama.className = 'bar-kartu-nama';
                nama.textContent = daftar[kode];

                var ket = document.createElement('span');
                ket.className = 'bar-kartu-ket';
                /*
                 * Varian tanpa tarif aktif disebut apa adanya. Dibiarkan
                 * kosong, panitia menyangka harganya nol — padahal yang
                 * sebenarnya terjadi tarifnya belum disetel.
                 */
                ket.textContent = harga ? rupiah(harga) : 'tarifnya belum disetel';

                teks.appendChild(nama);
                teks.appendChild(ket);

                var tanda = document.createElement('span');
                tanda.className = 'bar-kartu-centang';
                tanda.setAttribute('aria-hidden', 'true');
                tanda.innerHTML = '<i class="fas fa-check-circle"></i>';

                kartu.appendChild(medali);
                kartu.appendChild(teks);
                kartu.appendChild(tanda);
                label.appendChild(radio);
                label.appendChild(kartu);
                kartuVarian.appendChild(label);

                if (pertama === null) {
                    pertama = radio;
                }

                if (LAMA_VARIAN === kode) {
                    radio.checked = true;
                }
            });

            // Satu varian selalu terpilih: layanan yang punya varian TIDAK
            // punya harga tanpa varian, jadi keadaan "belum memilih" berarti
            // kotak nominal yang tidak bisa diisi sendiri.
            if (pertama && !kartuVarian.querySelector('input:checked')) {
                pertama.checked = true;
            }

            LAMA_VARIAN = null;
            terapkanVarian(layanan);
        };

        /** Mengisi nominal dan ringkasnya dari tarif varian yang terpilih. */
        var terapkanVarian = function (layanan) {
            var r = kartuVarian.querySelector('input[name="varian"]:checked');

            if (!r) {
                tampil(sekilas, false);
                return;
            }

            var daftar = VARIAN[layanan] || {};
            var harga = TARIF[layanan + '|' + r.value];

            if (harga) {
                isianTotal.value = rupiah(harga).replace('Rp ', '');
                ketVarian.textContent = 'Harganya ikut tarif ' + daftar[r.value]
                    + '. Masih boleh diubah kalau sesinya lebih dari satu.';
            } else {
                ketVarian.textContent = 'Tarif ' + daftar[r.value]
                    + ' belum disetel di Tarif Layanan, jadi nominalnya diketik sendiri.';
            }

            sekilasJudul.textContent = daftar[r.value] || 'Varian terpilih';
            sekilasTanggal.textContent = 'menyesuaikan jadwal sesi';
            sekilasSisa.textContent = 'tanpa batas';
            sekilasHarga.textContent = harga ? rupiah(harga) : 'belum disetel';
            tampil(sekilas, true);

            hitung();
        };

        var segarkan = function () {
            var pilih = layananTerpilih();

            if (!pilih) {
                subBiaya.textContent = 'Pilih layanannya dulu.';
                tampil(bungkusAngkatan, false);
                tampil(bungkusJumlah, false);
                tampil(bungkusTotal, false);
                tampil(sekilas, false);
                tampil(bungkusVarian, false);
                tampil(panelPotongan, false);
                tampil(bungkusPotongan, false);
                tampil(bungkusKode, false);
                hitung();
                return;
            }

            tampil(bungkusAngkatan, pilih.berangkatan);
            tampil(bungkusJumlah, pilih.berangkatan);
            tampil(bungkusTotal, !pilih.berangkatan);

            // Layanan tanpa angkatan tidak punya apa pun untuk diringkas;
            // tegaskanAngkatan() yang menyalakannya kembali kalau ada.
            tampil(sekilas, false);
            tampil(bungkusVarian, pilih.punyaVarian);
            tampil(bungkusPotongan, pilih.bisaPotongan);
            tampil(bungkusKode, pilih.bisaPotongan);


            menuAngkatan.required = pilih.berangkatan;
            isianTotal.required = !pilih.berangkatan;

            subBiaya.textContent = pilih.berangkatan
                ? 'Harga dan sisa kursinya ikut dari angkatan yang dipilih.'
                : 'Layanan ini tidak berangkatan, jadi nominalnya diketik sendiri.';

            if (pilih.berangkatan) {
                isiAngkatan(pilih.nilai);
                tegaskanAngkatan(pilih.nilai);
            } else if (pilih.punyaVarian) {
                isiVarian(pilih.nilai);
            }

            /*
             * Kartunya DIBUANG untuk layanan yang tidak memakainya, bukan
             * sekadar disembunyikan: radio tersembunyi TETAP terkirim, jadi
             * berpindah dari Scopus Kafe ke layanan lain akan membawa serta
             * varian yang tidak ada hubungannya dengan kiriman itu.
             *
             * Syaratnya bukan cabang `else` dari pengisian angkatan — layanan
             * berangkatan masuk cabang pertama dan tidak akan pernah sampai ke
             * sana. Terukur: dua kartu varian Scopus Kafe tetap tertinggal
             * saat berpindah ke Analisis Bibliometrik.
             */
            if (!pilih.punyaVarian) {
                kartuVarian.innerHTML = '';
            }

            tawarkanAlumni(pilih);

            /*
             * Panel potongan menyala hanya kalau ADA yang bisa ditampilkan di
             * dalamnya. Panel kosong berbingkai lebih buruk daripada tidak ada
             * panelnya: ia terbaca seperti isian yang gagal dimuat.
             */
            tampil(panelPotongan, pilih.bisaPotongan || !bungkusAlumni.hidden);

            hitung();
        };

        borang.addEventListener('change', function (e) {
            if (e.target.name === 'layanan') {
                segarkan();
            } else if (e.target.name === 'varian') {
                var pl = layananTerpilih();

                if (pl) {
                    terapkanVarian(pl.nilai);
                }
            } else if (e.target.name === 'cara_bayar') {
                segarkanBayar();
            } else if (e.target === menuAngkatan) {
                /*
                 * TIDAK memanggil segarkan(): fungsi itu merakit ulang seluruh
                 * pilihan angkatan, dan merakit ulang berarti pilihannya
                 * kembali ke angkatan pertama.
                 *
                 * Terukur: memilih "Angkatan ke-202 · 30 Okt 2026" langsung
                 * melompat kembali ke ke-198. Harga kelimanya sama persis,
                 * jadi tidak ada satu pun angka di layar yang berubah —
                 * pendaftarnya masuk angkatan dan tanggal yang salah tanpa
                 * tanda apa pun.
                 *
                 * Yang memang perlu dihitung ulang hanya tiga: tawaran alumni
                 * (disetel per varian angkatan), penegas di bawah menunya, dan
                 * totalnya.
                 */
                var pilihLayanan = layananTerpilih();

                if (pilihLayanan) {
                    tawarkanAlumni(pilihLayanan);
                    tegaskanAngkatan(pilihLayanan.nilai);
                }

                hitung();
            } else {
                hitung();
            }
        });

        [isianTotal, isianJumlah, isianPotongan].forEach(function (n) {
            n.addEventListener('input', hitung);
        });

        segarkan();
        segarkanBayar();
    })();
</script>
@endpush
