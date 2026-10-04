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
        /*
         * Di LAYAR SEMPIT ringkasannya tidak menempel sama sekali.
         *
         * Terukur di 390x844 pada tata letak ponsel: kartunya setinggi 247px —
         * hampir sepertiga layar — dan menempel di dasar berarti sepertiga
         * layar itu tertutup selamanya. Ia bahkan menimpa bilah menu bawah
         * yang tingginya 75px, sehingga tombol Simpan tertutup separuh.
         *
         * Menempel baru berguna kalau yang tertutupinya sedikit. Di layar
         * sempit ia merugi, jadi di sana ia duduk di ujung borang seperti
         * biasa.
         */
        @media (max-width: 767.98px) {
            .bar-samping {
                position: static;
            }

            .bar-samping .bar-biaya {
                box-shadow: none;
            }

            .bar-samping .bar-catatan-bawah {
                display: block;
            }
        }

        /*
         * Bilah menu bawah ponsel melayang (position: fixed, bottom 20px,
         * tinggi 75px), dan halamannya tidak punya bantalan bawah sama sekali
         * — jadi isian terakhir berakhir di bawahnya dan tidak bisa dicapai.
         */
        body.is-mobile .mis-badan {
            padding-bottom: 110px;
        }

        @media (min-width: 768px) and (max-width: 1499.98px) {
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

        /*
         * Di ponsel relnya TETAP ADA, hanya lebih rapat.
         *
         * Versi sebelumnya membuangnya sama sekali supaya kartunya tidak
         * tergeser 46px. Akibatnya urutan langkahnya justru terbaca paling
         * lemah di layar yang paling sering dipakai. 26px cukup untuk
         * menggambar relnya dan nomor yang lebih kecil, tanpa memakan lebar
         * yang berarti.
         */
        @media (max-width: 575.98px) {
            .bar-utama::before {
                left: 12px;
                top: 28px;
                bottom: 28px;
            }
        }

        .bar-langkah {
            position: relative;
            margin-bottom: 13px;
            margin-left: 46px;
            /* Bantalannya 18px, bukan 22px bawaan .mis-kartu. Borang ini
               punya empat kartu bertumpuk, jadi tiap 4px bantalan berlipat
               delapan kali ke tinggi halamannya. */
            padding: 18px;
        }

        @media (max-width: 575.98px) {
            .bar-langkah {
                margin-left: 26px;
            }
        }

        .bar-langkah-kepala {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 11px;
            padding-bottom: 9px;
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
            top: 11px;
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
                left: -26px;
                top: 9px;
                width: 26px;
                height: 26px;
                font-size: .76rem;
                box-shadow: 0 0 0 4px #f4f7ff;
            }
        }

        /*
         * line-height disebut sendiri, dan itu bukan kerapian belaka.
         *
         * Tanpa itu keduanya mewarisi tinggi baris dari gaya global —
         * terukur: baris judul DAN baris sub sama-sama 28px, padahal
         * hurufnya 16px dan 13px. Kepala langkahnya jadi 69px untuk dua
         * baris teks, dan jaraknya terbaca terlalu longgar.
         */
        .bar-langkah-judul {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--mis-tinta);
        }

        .bar-langkah-sub {
            margin: 2px 0 0;
            font-size: .8rem;
            line-height: 1.4;
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
            /*
             * align-self: stretch WAJIB disebut: mis-ui.css memasang
             * `align-self: start` pada .mis-isian, dan di wadah flex berarah
             * KOLOM itu sumbu mendatar — jadi isiannya menyusut selebar isinya
             * alih-alih mengisi kolomnya. Terukur: kotak bukti 395px di kolom
             * selebar 494px, menyisakan 99px kosong di sampingnya.
             */
            align-self: stretch;
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

        /*
         * Setinggi isian di sebelahnya HANYA di dalam sel kisi.
         *
         * Semula berlaku untuk setiap .bar-centang. Sakelar yang berdiri
         * langsung di dalam panel potongan lalu ikut meregang: terukur 175px
         * untuk isi yang tingginya sekitar 60px, dan panelnya terdorong
         * keluar kartu langkahnya — kartu alumni tergambar menimpa langkah
         * berikutnya.
         */
        .mis-isian > .bar-centang {
            height: 100%;
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
         * Penanda isian wajib.
         *
         * Tujuh isian memakai `required` tetapi tidak satu pun ditandai di
         * labelnya, jadi orang baru tidak bisa tahu mana yang boleh dilewati
         * sebelum mencoba mengirim. Hanya dipasang pada yang validatornya
         * memang menuntutnya — bintang pada isian opsional lebih menyesatkan
         * daripada tidak ada bintang.
         */
        .bar-wajib {
            margin-left: 3px;
            color: #e11d48;
            font-weight: 700;
        }

        /*
         * Galat per isian.
         *
         * Sebelumnya galat validasi HANYA muncul sebagai toast berisi pesan
         * pertama, tanpa satu pun penanda di isian yang salah — terukur: nol
         * isian bertepi merah, nol penanda sebaris. Membetulkan satu galat
         * lalu mengirim ulang baru memunculkan galat berikutnya, satu
         * bolak-balik per kesalahan.
         */
        .bar-salah {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            margin: 5px 0 0;
            font-size: .76rem;
            line-height: 1.45;
            color: #be123c;
        }

        .bar-salah i {
            margin-top: 2px;
        }

        /* Ringkasan galat di kepala borang: SELURUH kesalahan sekaligus, dan
           ia tidak hilang sendiri seperti toast. */
        .bar-galat-ringkas {
            margin-bottom: var(--mis-jarak);
            padding: 14px 16px;
            border: 1px solid #fecdd3;
            border-radius: var(--mis-radius-kecil);
            background: #fff1f2;
        }

        .bar-galat-ringkas-judul {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 7px;
            font-size: .88rem;
            font-weight: 700;
            color: #be123c;
        }

        .bar-galat-ringkas ul {
            margin: 0;
            padding-left: 20px;
            font-size: .8rem;
            line-height: 1.6;
            color: #9f1239;
        }

        /* Berkas bukti: kotak unggah yang terbaca sebagai tempat menjatuhkan
           berkas, bukan tombol "Choose file" bawaan peramban. */
        .bar-berkas {
            /*
             * min-width: 0 di SINI juga, bukan hanya di nama berkasnya.
             *
             * .mis-isian ternyata `display: grid`, dan lebar jalurnya
             * mengikuti min-content anaknya. Anak grid berbawaan
             * min-width:auto, jadi kotak ini memaksa jalurnya selebar teks
             * nowrap di dalamnya — 388px di dalam kolom selebar 254px.
             */
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 13px;
            border: 1.5px dashed var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            cursor: pointer;
        }

        .bar-berkas:hover {
            border-color: #c7d2fe;
        }

        .bar-berkas input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        /*
         * min-width: 0 WAJIB di sini, dan bukan sekadar kerapian.
         *
         * `white-space: nowrap` membuat lebar terkecil unsurnya sama dengan
         * lebar penuh teksnya, dan `overflow: hidden` TIDAK menguranginya.
         * Anak flex berbawaan min-width:auto, jadi ia menolak menyusut di
         * bawah itu dan mendorong seluruh kartunya keluar layar — terukur
         * luberan 108px di 320px dan 39px di 390px.
         */
        .bar-berkas-nama {
            flex: 1 1 auto;
            min-width: 0;
            font-size: .8rem;
            color: var(--mis-tinta-3);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Tombol yang sedang mengirim: terkunci dan terlihat terkunci. */
        .mis-tombol[aria-busy="true"] {
            opacity: .65;
            pointer-events: none;
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
            /*
             * Jarak dari sakelar di atasnya. Sakelarnya anak langsung panel
             * ini sedangkan kisinya blok tersendiri, jadi tanpa margin
             * keduanya benar-benar bersentuhan — terukur 0px.
             */
            margin-top: 11px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
            gap: 14px;
            align-items: start;
        }

        /*
         * Nama melebar saat afiliasi tidak diminta.
         *
         * Di jalur lembaga, afiliasi disembunyikan sebab nama lembaganya SUDAH
         * afiliasinya — dan barisnya tinggal tiga isian di kisi empat jalur.
         * Jalur keempat tidak runtuh sendiri: sakelar di baris berikutnya
         * memakai dua jalur, jadi ia tidak muat di sisa satu jalur dan
         * selnya ditinggalkan kosong.
         */
        .bar-isian-kisi:has(> #bar-bungkus-affiliasi[hidden]) > #bar-bungkus-nama {
            grid-column: span 2;
        }

        @media (max-width: 575.98px) {
            .bar-isian-kisi:has(> #bar-bungkus-affiliasi[hidden]) > #bar-bungkus-nama {
                grid-column: 1 / -1;
            }
        }

        /*
         * Kartu ringkas melebar saat jumlah orang tidak diminta.
         *
         * Di jalur perorangan jumlahnya selalu satu, jadi isiannya
         * disembunyikan — dan barisnya tinggal menu angkatan (dua jalur) plus
         * kartu ringkas (satu jalur), menyisakan satu jalur menganggur di
         * ujung kanan.
         *
         * Dipilih lewat :has() alih-alih kelas yang ditempel JS: keadaannya
         * SUDAH tergambar oleh atribut hidden, dan kelas kedua yang menyatakan
         * hal sama adalah kesempatan keduanya berselisih.
         */
        .bar-isian-kisi:has(> #bar-bungkus-jumlah[hidden]) > #bar-sekilas {
            grid-column: span 2;
        }

        @media (max-width: 575.98px) {
            .bar-isian-kisi:has(> #bar-bungkus-jumlah[hidden]) > #bar-sekilas {
                grid-column: 1 / -1;
            }
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

        /* Nota yang memperingatkan, bukan sekadar memberi tahu. */
        .bar-nota-awas {
            border-color: #fed7aa;
            background: #fff7ed;
            color: #9a3412;
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

        {{-- SELURUH kesalahan sekaligus, dan tidak hilang sendiri seperti
             toast. Sebelumnya hanya pesan pertama yang muncul, jadi kesalahan
             kedua baru ketahuan sesudah yang pertama dibetulkan dan
             dikirim ulang. --}}
        @if ($errors->any())
            <div class="bar-galat-ringkas" role="alert" tabindex="-1" id="bar-galat">
                <p class="bar-galat-ringkas-judul">
                    <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                    {{ $errors->count() === 1 ? 'Ada satu isian yang perlu diperbaiki' : 'Ada ' . $errors->count() . ' isian yang perlu diperbaiki' }}
                </p>
                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>

                {{-- Pemeriksaan ganda bukan larangan keras: dua orang berbeda
                     bisa berbagi satu nomor WhatsApp keluarga. Panitia yang
                     tahu itu harus tetap bisa melanjutkan. --}}
                @if (session('ganda'))
                    <label class="bar-centang" for="bar-abaikan" style="margin-top: 11px;">
                        <input type="checkbox" id="bar-abaikan" name="abaikan_ganda" value="1"
                            form="bar-borang">
                        <span class="bar-centang-tanda" aria-hidden="true">
                            <i class="fas fa-user-check"></i>
                        </span>
                        <span>
                            <span class="bar-centang-judul">Ini memang orang yang berbeda</span>
                            <span class="bar-centang-ket">
                                Centang lalu simpan lagi kalau Anda sudah memeriksanya.
                            </span>
                        </span>
                        <span class="bar-centang-pilih" aria-hidden="true">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    </label>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('account.pendaftaran-layanan.simpan') }}" id="bar-borang"
            enctype="multipart/form-data">
            @csrf

            <div class="bar-kerja">
            <div class="bar-utama">

            {{-- ------------------------------- langkah 1: untuk siapa --}}
            {{-- Pertanyaan ini DI DEPAN, dan itu perubahan pokoknya.

                 Dulu "pesanan lembaga?" ada di langkah TERAKHIR, padahal
                 jawabannya menentukan isi langkah-langkah sebelumnya: jumlah
                 orang, nama peserta rombongan, potongan alumni, dan seluruh
                 identitas lembaga. Admin mengisi semuanya dulu, baru diberi
                 tahu bahwa sebagian tadi tidak relevan.

                 Dengan ditanya lebih dulu, tiap jalur hanya menampilkan apa
                 yang memang dibutuhkannya — terukur: 29 nama isian di borang
                 ini, dan pendaftar perorangan sekarang menghadapi 5. --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">1</span>
                    <div>
                        <p class="bar-langkah-judul">Untuk siapa?</p>
                        <p class="bar-langkah-sub">Yang menentukan siapa membayar; pilih dulu yang ini.</p>
                    </div>
                </div>

                <div class="bar-pilihan bar-pilihan-bayar">
                    <label>
                        <input type="radio" name="jenis" value="perorangan"
                            @checked(old('jenis', 'perorangan') === 'perorangan') required>
                        <span class="bar-kartu">
                            <span class="mis-medali mis-biru" aria-hidden="true">
                                <i class="fas fa-user"></i>
                            </span>
                            <span style="min-width: 0;">
                                <span class="bar-kartu-nama">Perorangan</span>
                                <span class="bar-kartu-ket">
                                    Dibayar sendiri — boleh sendirian, boleh mengajak teman.
                                </span>
                            </span>
                            <span class="bar-kartu-centang" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                        </span>
                    </label>
                    <label>
                        <input type="radio" name="jenis" value="lembaga"
                            @checked(old('jenis') === 'lembaga')>
                        <span class="bar-kartu">
                            <span class="mis-medali mis-ungu" aria-hidden="true">
                                <i class="fas fa-building"></i>
                            </span>
                            <span style="min-width: 0;">
                                <span class="bar-kartu-nama">Lembaga / instansi</span>
                                <span class="bar-kartu-ket">
                                    Dibayar lembaga, dan fakturnya atas nama lembaga.
                                </span>
                            </span>
                            <span class="bar-kartu-centang" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- ------------------------------------ langkah 2: layanan --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">2</span>
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
                                data-sesi="{{ $l['pakai_sesi'] ? '1' : '0' }}"
                                data-pola="{{ $l['pola_nomor_kata'] }}"
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
                    <span class="bar-nomor" aria-hidden="true">3</span>
                    <div>
                        <p class="bar-langkah-judul">Berapa yang dibayar?</p>
                        <p class="bar-langkah-sub" id="bar-sub-biaya">Pilih layanannya dulu.</p>
                    </div>
                </div>

                <div class="bar-isian-kisi">
                    <div class="mis-isian bar-lebar" id="bar-bungkus-angkatan">
                        <label class="mis-label" for="bar-angkatan">Angkatan<span class="bar-wajib" aria-hidden="true">*</span></label>

                        {{-- Kotak saring, HANYA kalau angkatannya banyak.

                             Menu bawaan peramban tidak bisa dicari. Sekarang
                             paling banyak lima angkatan akan datang, jadi
                             kotaknya menyusahkan lebih daripada menolong —
                             tetapi Scopus Camp punya 48 angkatan seluruhnya,
                             dan menu berisi dua puluhan sudah berat digulir. --}}
                        <input type="text" class="form-control-modern" id="bar-cari-angkatan" hidden
                            placeholder="Saring: ketik nomor, kota, atau bulannya"
                            autocomplete="off" style="margin-bottom: 8px;">
                        <select class="form-control-modern @error('kategori_id') is-invalid @enderror" id="bar-angkatan" @error('kategori_id') aria-invalid="true" @enderror name="kategori_id">
                            <option value="">Pilih layanan dulu</option>
                        </select>
                        @error('kategori_id')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <p class="mis-bantuan" id="bar-angkatan-ket">
                            Hanya angkatan yang belum lewat yang ditawarkan.
                        </p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-jumlah">
                        <label class="mis-label" for="bar-jumlah">Jumlah orang</label>
                        <input type="number" class="form-control-modern @error('jumlah') is-invalid @enderror" id="bar-jumlah" @error('jumlah') aria-invalid="true" @enderror name="jumlah"
                            value="{{ old('jumlah', 1) }}" min="1" max="99">
                        @error('jumlah')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
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
                        <label class="mis-label" for="bar-total">Total bayar<span class="bar-wajib" aria-hidden="true">*</span></label>
                        <input type="text" class="form-control-modern @error('total') is-invalid @enderror" id="bar-total" @error('total') aria-invalid="true" @enderror name="total"
                            value="{{ old('total') }}" inputmode="numeric" placeholder="contoh: 250000">
                        @error('total')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <p class="mis-bantuan">Dalam rupiah, tanpa titik.</p>
                    </div>

                    {{-- Nama sesi, hanya untuk layanan yang tabelnya punya
                         kolomnya (Scopus Kafe). Tanpa ini, pendaftaran Scopus
                         Kafe lewat jalur panitia tidak pernah menyebut sesi
                         apa yang diambil. --}}
                    <div class="mis-isian bar-lebar" id="bar-bungkus-sesi" hidden>
                        <label class="mis-label" for="bar-sesi">Sesi yang diambil</label>
                        <input type="text" class="form-control-modern @error('sesi') is-invalid @enderror" id="bar-sesi" name="sesi"
                            value="{{ old('sesi') }}" maxlength="120"
                            placeholder="mis. Sesi 1 — Menyusun pendahuluan">
                        @error('sesi')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <p class="mis-bantuan">Supaya jadwalnya bisa ditelusuri nanti.</p>
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
                            <div id="bar-sekilas-baris-tanggal">
                                <dt>Mulai</dt>
                                <dd id="bar-sekilas-tanggal">—</dd>
                            </div>
                            <div id="bar-sekilas-baris-sisa">
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
                    {{-- Peringatan kelebihan kuota. Diperbolehkan bukan berarti
                         tidak perlu disebut: yang menyiapkan ruangan dan
                         konsumsinya harus tahu angkatannya kelebihan berapa. --}}
                    <p class="bar-nota bar-nota-awas bar-penuh" id="bar-nota-kuota" hidden>
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        <span></span>
                    </p>

                    <div class="bar-potongan-panel bar-penuh" id="bar-panel-potongan" hidden>
                    <p class="bar-potongan-judul">
                        <i class="fas fa-tags" aria-hidden="true"></i> Potongan <span>opsional</span>
                    </p>

                    {{-- Isian potongannya DILIPAT di balik sakelar.

                         Terukur: dari 29 nama isian di borang ini, sebagian
                         besar tidak pernah diisi pada pendaftaran biasa.
                         Isian yang jarang dipakai tetapi selalu terlihat
                         menambah beban baca di setiap pendaftaran, bukan
                         hanya di yang membutuhkannya. --}}
                    <label class="bar-centang" for="bar-pakai-potongan" id="bar-sakelar-potongan">
                        <input type="checkbox" id="bar-pakai-potongan">
                        <span class="bar-centang-tanda" aria-hidden="true">
                            <i class="fas fa-percent"></i>
                        </span>
                        <span>
                            <span class="bar-centang-judul">Beri potongan khusus</span>
                            <span class="bar-centang-ket">
                                Untuk peserta yang disponsori, harga mitra, atau kesepakatan di tempat.
                            </span>
                        </span>
                        <span class="bar-centang-pilih" aria-hidden="true">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    </label>
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
                        <input type="text" class="form-control-modern @error('potongan') is-invalid @enderror" id="bar-potongan" @error('potongan') aria-invalid="true" @enderror name="potongan"
                            value="{{ old('potongan') }}" inputmode="numeric" placeholder="boleh dikosongkan">
                        @error('potongan')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <p class="mis-bantuan">Rupiah, di luar diskon angkatannya.</p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-kode">
                        <label class="mis-label" for="bar-kode-potongan">Alasan potongan</label>
                        <input type="text" class="form-control-modern @error('kode_potongan') is-invalid @enderror" id="bar-kode-potongan" @error('kode_potongan') aria-invalid="true" @enderror
                            name="kode_potongan" value="{{ old('kode_potongan') }}" maxlength="40"
                            placeholder="mis. SPONSOR, MITRA">
                        @error('kode_potongan')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
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
                    <span class="bar-nomor" aria-hidden="true">4</span>
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

                {{-- Bukti boleh langsung diunggah dari sini. Sebelumnya tidak
                     bisa sama sekali: pendaftar yang datang membawa struk
                     harus disimpan dulu, lalu buktinya diunggah dari halaman
                     rincian. --}}
                <div class="mis-isian" id="bar-bungkus-bukti" hidden>
                    <label class="mis-label" for="bar-bukti">Bukti transfer <span style="font-weight:500;text-transform:none;letter-spacing:0;">(boleh menyusul)</span></label>
                    <label class="bar-berkas" for="bar-bukti">
                        <input type="file" id="bar-bukti" name="bukti"
                            accept="image/jpeg,image/png,image/webp">
                        <span class="mis-medali kecil mis-biru" aria-hidden="true">
                            <i class="fas fa-paperclip"></i>
                        </span>
                        <span class="bar-berkas-nama" id="bar-berkas-nama">
                            Pilih gambar — JPG, PNG, atau WebP, maksimal 4 MB
                        </span>
                    </label>
                    @error('bukti')
                        <p class="bar-salah">
                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>



                </div>{{-- /kolom kanan --}}
                </div>{{-- /bar-bayar-kisi --}}

                {{-- Notanya SEBARIS PENUH di bawah kedua kolom, bukan di
                     kolom kanan. Kartu cara bayar lebih pendek daripada
                     notanya, jadi di sebelah kiri nota tersisa ruang kosong
                     selebar kolom kiri. --}}
                <p class="bar-nota" id="bar-nota-bukti">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>
                        <strong>Bukti transfernya diunggah nanti</strong> dari halaman rincian
                        pendaftaran ini, setelah uangnya masuk — borang ini tidak memintanya
                        supaya pendaftarnya bisa dicatat lebih dulu.
                    </span>
                </p>

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
                    <span class="bar-nomor" aria-hidden="true">5</span>
                    <div>
                        <p class="bar-langkah-judul">Siapa yang mendaftar?</p>
                        {{-- Bukan lagi "empat isian": blok lembaga dilebur ke
                             langkah ini, jadi jumlahnya berbeda tiap jalur. --}}
                        <p class="bar-langkah-sub">Nomor, status, dan kode uniknya diisi sistem.</p>
                    </div>
                </div>

                <div class="bar-isian-kisi">
                    <div class="mis-isian" id="bar-bungkus-nama">
                        <label class="mis-label" for="bar-nama">Nama lengkap<span class="bar-wajib" aria-hidden="true">*</span></label>
                        <input type="text" class="form-control-modern @error('nama') is-invalid @enderror" id="bar-nama" @error('nama') aria-invalid="true" @enderror name="nama"
                            value="{{ old('nama') }}" required maxlength="255" autocomplete="off">
                        @error('nama')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-email">Email <span style="font-weight:500;text-transform:none;letter-spacing:0;">(boleh kosong)</span></label>
                        <input type="email" class="form-control-modern @error('email') is-invalid @enderror" id="bar-email" @error('email') aria-invalid="true" @enderror name="email"
                            value="{{ old('email') }}" maxlength="255" autocomplete="off">
                        @error('email')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <p class="mis-bantuan">
                            Yang datang langsung sering tidak punya; nomor WhatsApp-nya yang wajib.
                        </p>
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-telp">Nomor WhatsApp<span class="bar-wajib" aria-hidden="true">*</span></label>
                        <input type="text" class="form-control-modern @error('telp') is-invalid @enderror" id="bar-telp" @error('telp') aria-invalid="true" @enderror name="telp"
                            value="{{ old('telp') }}" required maxlength="30" autocomplete="off"
                            inputmode="tel" placeholder="08xx atau 62xx">
                        @error('telp')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div class="mis-isian" id="bar-bungkus-affiliasi">
                        <label class="mis-label" for="bar-affiliasi">Afiliasi / instansi</label>
                        <input type="text" class="form-control-modern @error('affiliasi') is-invalid @enderror" id="bar-affiliasi" @error('affiliasi') aria-invalid="true" @enderror name="affiliasi"
                            value="{{ old('affiliasi') }}" maxlength="255" autocomplete="off"
                            placeholder="boleh dikosongkan">
                        @error('affiliasi')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    {{-- Nama peserta lain, hanya saat rombongan.

                         Kelima tabel pendaftaran hanya punya SATU nama,
                         sementara jumlahnya bisa lebih dari satu — jadi nama
                         peserta selain pemesannya tidak tercatat di mana pun,
                         dan daftar hadir rombongan tidak bisa dibuat dari
                         sistem. Satu kotak teks, bukan sederet isian:
                         menempelkan daftar nama dari pesan WhatsApp jauh
                         lebih cepat daripada mengetik ke lima kotak. --}}
                    <div class="mis-isian bar-penuh" id="bar-bungkus-peserta" hidden>
                        <label class="mis-label" for="bar-peserta">
                            Nama peserta lain <span id="bar-peserta-sisa"
                                style="font-weight:500;text-transform:none;letter-spacing:0;"></span>
                        </label>
                        <textarea class="form-control-modern @error('peserta') is-invalid @enderror" id="bar-peserta"
                            name="peserta" rows="3"
                            placeholder="Satu nama per baris, misalnya:&#10;Budi Santoso&#10;Siti Rahma">{{ old('peserta') }}</textarea>
                        @error('peserta')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <p class="mis-bantuan">
                            Pemesannya sudah terisi di atas; kotak ini untuk sisanya.
                            Boleh ditempel langsung dari WhatsApp — penomorannya dibuang sendiri.
                        </p>
                    </div>

                    <div class="mis-isian bar-lebar">
                        <label class="bar-centang" for="bar-pakai-catatan">
                            <input type="checkbox" id="bar-pakai-catatan">
                            <span class="bar-centang-tanda" aria-hidden="true">
                                <i class="fas fa-sticky-note"></i>
                            </span>
                            <span>
                                <span class="bar-centang-judul">Tambah catatan panitia</span>
                                <span class="bar-centang-ket">
                                    Keterangan untuk rekan panitia; tidak dikirim ke pendaftarnya.
                                </span>
                            </span>
                            <span class="bar-centang-pilih" aria-hidden="true">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        </label>
                    </div>

                    {{-- Tanpa label di atasnya: sakelar di sebelahnya tidak
                         punya label, jadi keduanya tidak pernah sejajar.
                         Kalimat di dalam kartunya sudah menyebutkan apa ini. --}}
                    <div class="mis-isian bar-lebar" id="bar-bungkus-kabari">
                        <label class="bar-centang" for="bar-kabari">
                            <input type="checkbox" id="bar-kabari" name="kabari" value="1"
                                @checked(old('kabari', true))>
                            <span class="bar-centang-tanda" aria-hidden="true">
                                <i class="fas fa-paper-plane"></i>
                            </span>
                            <span>
                                <span class="bar-centang-judul">Kirimkan nomor pendaftarannya lewat email</span>
                                <span class="bar-centang-ket">
                                    Berisi nomor, total bayar, dan kode uniknya. Lepas centangnya
                                    kalau emailnya tidak yakin benar.
                                </span>
                            </span>
                            <span class="bar-centang-pilih" aria-hidden="true">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        </label>
                    </div>

                    <div class="mis-isian bar-lebar" id="bar-bungkus-catatan" hidden>
                        <label class="mis-label" for="bar-note">Catatan panitia</label>
                        <textarea class="form-control-modern @error('note') is-invalid @enderror" id="bar-note" @error('note') aria-invalid="true" @enderror name="note" rows="2"
                            maxlength="1000" placeholder="boleh dikosongkan">{{ old('note') }}</textarea>
                        @error('note')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    {{-- Blok lembaga, hanya untuk pesanan lembaga.

                         Dulu kartu tersendiri di langkah terakhir. Dilebur ke
                         sini sebab untuk pesanan lembaga, lembaganya MEMANG
                         bagian dari "siapa yang mendaftar" — dan satu langkah
                         lebih sedikit berarti satu kartu lebih sedikit yang
                         harus dibaca. --}}
                    <div class="bar-penuh" id="bar-blok-lembaga" hidden>
                        <p class="bar-potongan-judul" style="margin-top: 4px;">
                            <i class="fas fa-building" aria-hidden="true"></i> Lembaganya
                        </p>
                        <div class="bar-isian-kisi">

                    {{-- Menu ini HANYA muncul kalau memang ada pesanan yang
                         masih terbuka.

                         Tanpa itu, admin yang baru saja memilih jalur lembaga
                         disuguhi menu berisi satu pilihan bertuliskan "Bukan
                         pesanan lembaga" — menyangkal pilihan yang baru saja
                         ia buat, dan tidak ada yang bisa dipilih di sana. --}}
                    <div class="mis-isian bar-lebar" @if ($pemesanan->isEmpty()) hidden @endif>
                        <label class="mis-label" for="bar-pemesanan">Gabung ke pesanan yang sudah ada?</label>
                        <select class="form-control-modern" id="bar-pemesanan" name="pemesanan_id">
                            <option value="">Buat pesanan baru</option>
                            @foreach ($pemesanan as $pm)
                                <option value="{{ $pm->id }}"
                                    @selected(old('pemesanan_id', $terpilihPemesanan) === $pm->id)>
                                    {{ $pm->kode }} — {{ $pm->nama_lembaga }}
                                </option>
                            @endforeach
                        </select>
                        @error('pemesanan_id')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <p class="mis-bantuan" id="bar-pemesanan-ket">
                            Pilih yang sudah ada, atau isi nama lembaganya di bawah untuk membuat baru.
                        </p>
                    </div>

                    <div class="mis-isian bar-lebar{{ $pemesanan->isEmpty() ? ' bar-penuh' : '' }}" id="bar-bungkus-lembaga-nama">
                        <label class="mis-label" for="bar-lembaga-nama">Nama lembaga</label>
                        <input type="text" class="form-control-modern @error('lembaga_nama') is-invalid @enderror"
                            id="bar-lembaga-nama" name="lembaga_nama" value="{{ old('lembaga_nama') }}"
                            maxlength="255" placeholder="mis. Universitas Ahmad Dahlan">
                        @error('lembaga_nama')
                            <p class="bar-salah">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <p class="mis-bantuan">Diisi hanya kalau pesanannya belum ada di daftar atas.</p>
                    </div>

                    {{-- Alamat, NPWP, dan nomor surat pesanan: tiganya yang dituntut
                         lembaga untuk pencairan, dan tidak satu pun punya
                         tempat sebelum ini. --}}
                    <div class="mis-isian bar-penuh" id="bar-bungkus-lembaga" hidden>
                        <div class="bar-isian-kisi">
                            <div class="mis-isian bar-penuh">
                                <label class="bar-centang" for="bar-perlu-faktur">
                                    <input type="checkbox" id="bar-perlu-faktur">
                                    <span class="bar-centang-tanda" aria-hidden="true">
                                        <i class="fas fa-file-invoice"></i>
                                    </span>
                                    <span>
                                        <span class="bar-centang-judul">Lembaganya minta faktur resmi</span>
                                        <span class="bar-centang-ket">
                                            Alamat, NPWP, dan nomor surat pesanannya — hanya perlu
                                            kalau fakturnya dipakai untuk pencairan.
                                        </span>
                                    </span>
                                    <span class="bar-centang-pilih" aria-hidden="true">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </label>
                            </div>

                            <div class="mis-isian bar-lebar bar-faktur" hidden>
                                <label class="mis-label" for="bar-lembaga-alamat">Alamat lembaga</label>
                                <input type="text" class="form-control-modern" id="bar-lembaga-alamat"
                                    name="lembaga_alamat" value="{{ old('lembaga_alamat') }}" maxlength="1000"
                                    placeholder="untuk dicantumkan di faktur">
                            </div>
                            <div class="mis-isian bar-faktur" hidden>
                                <label class="mis-label" for="bar-lembaga-npwp">NPWP</label>
                                <input type="text" class="form-control-modern" id="bar-lembaga-npwp"
                                    name="lembaga_npwp" value="{{ old('lembaga_npwp') }}" maxlength="40"
                                    placeholder="boleh dikosongkan">
                            </div>
                            <div class="mis-isian bar-faktur" hidden>
                                {{-- "Surat pesanan", bukan "PO": penggunanya bukan
                                     orang pengadaan, dan singkatan Inggris
                                     memaksa mereka menebak apa yang diminta. --}}
                                <label class="mis-label" for="bar-lembaga-po">Nomor surat pesanan</label>
                                <input type="text" class="form-control-modern" id="bar-lembaga-po"
                                    name="lembaga_po" value="{{ old('lembaga_po') }}" maxlength="60"
                                    placeholder="kalau lembaganya menerbitkan">
                            </div>
                            <div class="mis-isian bar-penuh">
                                <label class="bar-centang" for="bar-pic-beda">
                                    <input type="checkbox" id="bar-pic-beda">
                                    <span class="bar-centang-tanda" aria-hidden="true">
                                        <i class="fas fa-user-tie"></i>
                                    </span>
                                    <span>
                                        <span class="bar-centang-judul">Penanggung jawabnya orang lain</span>
                                        <span class="bar-centang-ket">
                                            Biarkan kosong kalau yang mengurus administrasi sama dengan
                                            pendaftarnya.
                                        </span>
                                    </span>
                                    <span class="bar-centang-pilih" aria-hidden="true">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </label>
                            </div>

                            <div class="mis-isian bar-pic bar-lebar" hidden>
                                <label class="mis-label" for="bar-lembaga-pic">PIC lembaga</label>
                                <input type="text" class="form-control-modern" id="bar-lembaga-pic"
                                    name="lembaga_pic" value="{{ old('lembaga_pic') }}" maxlength="255"
                                    placeholder="kosong = sama dengan pendaftarnya">
                            </div>
                            <div class="mis-isian bar-pic" hidden>
                                <label class="mis-label" for="bar-lembaga-pic-email">Email PIC</label>
                                <input type="email" class="form-control-modern @error('lembaga_pic_email') is-invalid @enderror"
                                    id="bar-lembaga-pic-email" name="lembaga_pic_email"
                                    value="{{ old('lembaga_pic_email') }}" maxlength="255"
                                    placeholder="untuk mengirim fakturnya">
                                @error('lembaga_pic_email')
                                    <p class="bar-salah">
                                        <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>
                            <div class="mis-isian bar-pic" hidden>
                                <label class="mis-label" for="bar-lembaga-pic-telp">Nomor PIC</label>
                                <input type="text" class="form-control-modern" id="bar-lembaga-pic-telp"
                                    name="lembaga_pic_telp" value="{{ old('lembaga_pic_telp') }}" maxlength="40"
                                    placeholder="kosong = sama dengan pendaftarnya">
                            </div>
                        </div>
                    </div>
                                        </div>
                <p class="bar-nota">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>
                        <strong>Kursinya tetap dihitung per angkatan.</strong> Pesanan yang
                        melebihi kuota satu angkatan disimpan beberapa kali ke angkatan
                        berbeda — pesanan ini yang mengikatnya jadi satu faktur.
                    </span>
                </p>
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
                    {{-- Dua tombol kirim, dibedakan oleh satu penanda.

                         Mendaftarkan rombongan satu per satu sebelumnya
                         berarti sepuluh kali kembali ke borang dan sepuluh
                         kali memilih ulang layanan serta angkatannya. --}}
                    <button type="submit" class="mis-tombol mis-tombol-halus" id="bar-simpan-lagi"
                        name="lagi" value="1">
                        <i class="fas fa-user-plus" aria-hidden="true"></i> Simpan &amp; tambah lagi
                    </button>
                    <button type="submit" class="mis-tombol mis-tombol-ungu" id="bar-simpan">
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
                Nomor pendaftaran, status, dan kode unik dibuat sistem sesudah disimpan —
                <span id="bar-pola-nomor">nomornya mengikuti pola layanan yang dipilih</span>.
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
        var LAMA_ANGKATAN = @json(old('kategori_id') ?: ($terpilihAngkatan ?: null));

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
        var bungkusAffiliasi = el('bar-bungkus-affiliasi');
        var bungkusCatatan = el('bar-bungkus-catatan');
        var blokLembaga = el('bar-blok-lembaga');
        var sakelarPotongan = el('bar-sakelar-potongan');
        var pakaiPotongan = el('bar-pakai-potongan');
        var pakaiCatatan = el('bar-pakai-catatan');
        var perluFaktur = el('bar-perlu-faktur');
        var picBeda = el('bar-pic-beda');
        var isianAffiliasi = el('bar-affiliasi');
        var cariAngkatan = el('bar-cari-angkatan');
        var bungkusPeserta = el('bar-bungkus-peserta');
        var sisaPeserta = el('bar-peserta-sisa');
        var bungkusSesi = el('bar-bungkus-sesi');
        var bungkusBukti = el('bar-bungkus-bukti');
        var isianBukti = el('bar-bukti');
        var namaBerkas = el('bar-berkas-nama');
        var polaNomor = el('bar-pola-nomor');
        var bungkusVarian = el('bar-bungkus-varian');
        var kartuVarian = el('bar-varian-kartu');
        var ketVarian = el('bar-varian-ket');
        var panelPotongan = el('bar-panel-potongan');
        var sekilas = el('bar-sekilas');
        var sekilasJudul = el('bar-sekilas-judul');
        var barisTanggal = el('bar-sekilas-baris-tanggal');
        var barisSisa = el('bar-sekilas-baris-sisa');
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
                pakaiSesi: r.dataset.sesi === '1',
                pola: r.dataset.pola || '',
            } : null;
        };

        var isiAngkatan = function (layanan, saring) {
            var semua = ANGKATAN[layanan] || [];

            /*
             * Disaring di sini, bukan dengan menyembunyikan <option>:
             * menyembunyikan pilihan di menu bawaan tidak dihormati seragam
             * oleh peramban, dan pilihan tersembunyi yang masih bisa terpilih
             * lebih buruk daripada tidak ada saringan.
             */
            var cari = (saring || '').trim().toLowerCase();
            var daftar = cari === '' ? semua : semua.filter(function (a) {
                return [a.nama, a.nomor, a.tanggal].join(' ').toLowerCase().indexOf(cari) >= 0;
            });

            // Kotak saringnya baru berguna kalau menunya memang panjang.
            tampil(cariAngkatan, semua.length > 8);

            menuAngkatan.innerHTML = '';

            if (!daftar.length) {
                var kosong = document.createElement('option');
                kosong.value = '';
                kosong.textContent = cari === ''
                    ? 'Belum ada angkatan yang akan datang'
                    : 'Tidak ada angkatan yang cocok';
                menuAngkatan.appendChild(kosong);
                ket.textContent = cari === ''
                    ? 'Buat angkatannya dulu di layar Angkatan Layanan.'
                    : 'Hapus isian saringnya untuk melihat semuanya lagi.';
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
                /*
                 * Angkatan yang kursinya habis DITANDAI dan tidak bisa
                 * dipilih. Peladen memang menolaknya, tetapi orangnya baru
                 * tahu sesudah seluruh borang terisi — dan pilihan yang pasti
                 * ditolak lebih baik tidak ditawarkan sejak awal.
                 */
                var penuh = a.sisa_kuota !== null && a.sisa_kuota <= 0;

                // Dikunci HANYA untuk pendaftar perorangan; pesanan lembaga
                // memang boleh melebihi kuotanya.
                o.disabled = penuh && !lembagaDipakai();

                var sisa = a.sisa_kuota === null
                    ? 'tanpa batas kuota'
                    : (a.sisa_kuota > 0
                        ? 'sisa ' + a.sisa_kuota + ' kursi'
                        : (a.sisa_kuota === 0 ? 'PENUH' : 'KELEBIHAN ' + (-a.sisa_kuota) + ' kursi'));

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
            tampil(barisTanggal, true);
            tampil(barisSisa, true);
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
            tampil(bungkusBukti, !tunai);
            tampil(notaBukti, !tunai);

            /*
             * Berkas yang sudah dipilih DILEPAS saat pindah ke tunai: isian
             * berkas yang tersembunyi tetap terkirim, dan bukti transfer pada
             * pendaftaran yang ditandai bayar tunai tidak bisa dijelaskan
             * siapa pun.
             */
            if (tunai && isianBukti) {
                isianBukti.value = '';
                namaBerkas.textContent = 'Pilih gambar — JPG, PNG, atau WebP, maksimal 4 MB';
            }

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
                /*
                 * SATU KURSI, bukan subtotal — sama dengan aturan di peladen.
                 *
                 * Dihitung dari subtotal, angka di layar akan berbeda dari
                 * yang tersimpan, dan panitia menyebut nominal yang salah ke
                 * pendaftarnya sebelum sempat ada yang menyadarinya.
                 */
                potongan = Math.round(satuan * persen / 100);
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

            tampil(bungkusAlumni, pilih.bisaPotongan && persen > 0 && !lembagaDipakai());

            if (persen > 0) {
                var jml = Math.max(1, angkaDari(isianJumlah.value) || 1);

                /*
                 * Disebutkan "untuk satu kursi" begitu rombongannya lebih dari
                 * satu orang. Tanpa itu panitia mengira seluruh rombongan ikut
                 * berdiskon, dan angka di layar terbaca seperti salah hitung.
                 */
                ketAlumni.textContent = 'Potongan ' + persen + '% dari Tarif Layanan'
                    + (jml > 1 ? ', untuk satu kursi saja' : '')
                    + ' — menggantikan potongan khusus.';
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

            /*
             * Dua baris pertama DISEMBUNYIKAN untuk layanan tanpa angkatan.
             *
             * Scopus Kafe tidak punya tanggal mulai maupun kuota, jadi
             * sebelumnya keduanya terisi kalimat pengisi — "menyesuaikan
             * jadwal sesi" dan "tanpa batas" — dan dua dari tiga baris
             * kartunya tidak membawa keterangan apa pun.
             */
            sekilasJudul.textContent = daftar[r.value] || 'Varian terpilih';
            tampil(barisTanggal, false);
            tampil(barisSisa, false);
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
                tampil(bungkusSesi, false);
                tampil(panelPotongan, false);
                tampil(bungkusPotongan, false);
                tampil(bungkusKode, false);
                hitung();
                return;
            }

            tampil(bungkusAngkatan, pilih.berangkatan);

            /*
             * Syaratnya menyebut jenisnya juga, bukan hanya berangkatan.
             *
             * segarkanJenis() sempat menyembunyikannya, lalu fungsi ini
             * menampilkannya lagi — dua fungsi mengatur satu unsur, dan yang
             * terakhir menang. Terukur: isian "jumlah orang" tetap muncul di
             * jalur perorangan walau selalu bernilai satu.
             */
            /*
             * Jumlah orang ditawarkan di KEDUA jalur.
             *
             * Dulu hanya di jalur lembaga, jadi satu orang yang mengajak enam
             * temannya dan membayar sendiri tidak punya jalan sama sekali —
             * satu-satunya pilihan memilih "Lembaga", padahal tidak ada
             * lembaga yang membayar, dan di sana centang alumninya justru
             * disembunyikan.
             */
            tampil(bungkusJumlah, pilih.berangkatan);
            tampil(bungkusTotal, !pilih.berangkatan);

            // Layanan tanpa angkatan tidak punya apa pun untuk diringkas;
            // tegaskanAngkatan() yang menyalakannya kembali kalau ada.
            tampil(sekilas, false);
            tampil(bungkusVarian, pilih.punyaVarian);
            tampil(bungkusSesi, pilih.pakaiSesi);

            if (polaNomor && pilih.pola) {
                polaNomor.textContent = pilih.pola;
            }
            /*
             * Sakelarnya ikut jadi syarat; tanpa itu fungsi ini membuka lagi
             * isian yang baru saja dilipat, dan sakelarnya tampak tidak
             * bekerja.
             */
            tampil(bungkusPotongan, pilih.bisaPotongan && pakaiPotongan.checked);
            tampil(bungkusKode, pilih.bisaPotongan && pakaiPotongan.checked);


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
            tampil(sakelarPotongan, pilih.bisaPotongan);

            batasiJumlah();
            segarkanPeserta();
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

                batasiJumlah();
                hitung();
            } else {
                hitung();
            }
        });

        [isianTotal, isianJumlah, isianPotongan].forEach(function (n) {
            n.addEventListener('input', hitung);
        });

        /*
         * Jumlah orang DIBATASI sisa kursi angkatannya, bukan 99.
         *
         * Peladen memang menolak yang melebihi — dan penolakannya terkunci
         * dengan aman — tetapi orangnya baru tahu sesudah mengisi seluruh
         * borang dan menekan simpan. Batas di sini membuatnya ketahuan saat
         * angkanya diketik.
         */
        var batasiJumlah = function () {
            var pilih = layananTerpilih();

            /*
             * Pesanan LEMBAGA boleh melebihi kuota angkatannya, jadi batasnya
             * dilepas. Kuota 20 kursi sedangkan lembaga rutin memesan lebih,
             * dan memaksa pesanan 30 orang dipecah berarti memecah satu
             * rombongan yang seharusnya berangkat bersama.
             */
            if (!pilih || !pilih.berangkatan || menuPemesanan.value !== '' || namaLembaga.value.trim() !== '') {
                isianJumlah.removeAttribute('max');
                return;
            }

            var o = menuAngkatan.options[menuAngkatan.selectedIndex];
            var daftar = ANGKATAN[pilih.nilai] || [];
            var a = null;

            for (var i = 0; i < daftar.length; i++) {
                if (o && daftar[i].id === o.value) {
                    a = daftar[i];
                    break;
                }
            }

            if (!a || a.sisa_kuota === null) {
                isianJumlah.removeAttribute('max');
                return;
            }

            isianJumlah.max = Math.max(1, a.sisa_kuota);

            if (parseInt(isianJumlah.value || '1', 10) > a.sisa_kuota) {
                isianJumlah.value = a.sisa_kuota;
            }
        };

        /*
         * Kotak nama peserta muncul begitu jumlahnya lebih dari satu, dan
         * menyebutkan BERAPA nama yang diharapkan — tanpa angka itu panitia
         * tidak tahu apakah pemesannya ikut dihitung.
         */
        var segarkanPeserta = function () {
            var pilih = layananTerpilih();
            var jml = Math.max(1, angkaDari(isianJumlah.value) || 1);
            // Nama peserta dipakai membuat daftar hadir, dan rombongan yang
            // dibayar perorangan sama saja butuhnya.
            var perlu = (pilih && pilih.berangkatan) ? jml - 1 : 0;

            tampil(bungkusPeserta, perlu > 0);

            if (perlu > 0) {
                sisaPeserta.textContent = '(' + perlu + ' nama lagi, selain pemesannya)';
            }
        };

        isianJumlah.addEventListener('input', function () {
            segarkanPeserta();

            // Keterangan alumninya menyebut "untuk satu kursi saja" hanya saat
            // rombongan, jadi ia ikut berubah saat jumlahnya berubah.
            var pl = layananTerpilih();

            if (pl) {
                tawarkanAlumni(pl);
            }
        });

        /*
         * Isian yang dilipat: nilainya DIKOSONGKAN saat sakelarnya dimatikan.
         *
         * Isian tersembunyi tetap terkirim, jadi angka potongan yang sempat
         * diketik lalu dilipat akan tetap memotong tagihan — tanpa ada yang
         * terlihat di layar.
         */
        var lipat = function (sakelar, pemilih, sesudah) {
            if (!sakelar) {
                return;
            }

            var kerjakan = function () {
                var buka = sakelar.checked;

                document.querySelectorAll(pemilih).forEach(function (n) {
                    tampil(n, buka);

                    if (!buka) {
                        n.querySelectorAll('input, textarea').forEach(function (i) { i.value = ''; });
                    }
                });

                if (sesudah) {
                    sesudah();
                }
            };

            sakelar.addEventListener('change', kerjakan);
            kerjakan();
        };

        lipat(pakaiPotongan, '#bar-bungkus-potongan, #bar-bungkus-kode', function () {
            var pilih = layananTerpilih();

            if (pilih) {
                tampil(bungkusPotongan, pilih.bisaPotongan && pakaiPotongan.checked);
                tampil(bungkusKode, pilih.bisaPotongan && pakaiPotongan.checked);
            }

            hitung();
        });
        lipat(pakaiCatatan, '#bar-bungkus-catatan');
        lipat(perluFaktur, '.bar-faktur');
        lipat(picBeda, '.bar-pic');

        borang.addEventListener('change', function (e) {
            if (e.target.name === 'jenis') {
                segarkanJenis();
                segarkan();
                segarkanPeserta();
                batasiJumlah();
                ingatkanKuota();
            }
        });

        /*
         * Isian lembaga hanya tampil saat memang dibutuhkan.
         *
         * Pesanan yang SUDAH ada tidak perlu diisi identitasnya lagi — ia
         * sudah punya. Dan pendaftar perorangan tidak perlu melihat enam
         * isian kosong yang tidak akan ia isi sama sekali.
         */
        var menuPemesanan = el('bar-pemesanan');
        var namaLembaga = el('bar-lembaga-nama');
        var bungkusNamaLembaga = el('bar-bungkus-lembaga-nama');
        var bungkusLembaga = el('bar-bungkus-lembaga');
        var ketPemesanan = el('bar-pemesanan-ket');
        var notaKuota = el('bar-nota-kuota');

        /** 'perorangan' atau 'lembaga'; penentu seluruh isian berikutnya. */
        var jenisTerpilih = function () {
            var r = borang.querySelector('input[name="jenis"]:checked');

            return r ? r.value : 'perorangan';
        };

        /** Benar kalau pendaftaran ini bagian dari pesanan lembaga. */
        var lembagaDipakai = function () {
            return jenisTerpilih() === 'lembaga';
        };

        /*
         * Menyesuaikan SELURUH borang dengan jawaban "untuk siapa".
         *
         * Dulu pertanyaan ini ada di langkah terakhir, jadi admin mengisi
         * jumlah orang, nama peserta rombongan, dan afiliasi lebih dulu — baru
         * tahu belakangan bahwa sebagiannya tidak relevan. Sekarang jalur yang
         * tidak dipakai tidak pernah muncul.
         */
        var segarkanJenis = function () {
            var lembaga = lembagaDipakai();
            var pilih = layananTerpilih();

            tampil(blokLembaga, lembaga);

            tampil(bungkusJumlah, !!pilih && pilih.berangkatan);

            /*
             * Afiliasi disembunyikan untuk pesanan lembaga: nama lembaganya
             * SUDAH afiliasinya, dan mengetiknya dua kali membuat keduanya
             * bisa berselisih.
             */
            tampil(bungkusAffiliasi, !lembaga);

            /*
             * Potongan alumni milik ORANGNYA, bukan pesanannya — tidak ada
             * artinya pada pesanan atas nama lembaga.
             */
            if (lembaga) {
                centangAlumni.checked = false;
            }

            namaLembaga.required = lembaga && menuPemesanan.value === '';
        };

        /*
         * Memberi tahu saat jumlahnya MELEBIHI sisa kursi.
         *
         * Diperbolehkan bukan berarti tidak perlu disebut: panitia yang
         * menyiapkan ruangan dan konsumsinya harus tahu angkatannya kelebihan
         * berapa, dan mengetahuinya sesudah tersimpan terlambat.
         */
        var ingatkanKuota = function () {
            var pilih = layananTerpilih();

            if (!pilih || !pilih.berangkatan) {
                tampil(notaKuota, false);
                return;
            }

            var o = menuAngkatan.options[menuAngkatan.selectedIndex];
            var daftar = ANGKATAN[pilih.nilai] || [];
            var a = null;

            for (var i = 0; i < daftar.length; i++) {
                if (o && daftar[i].id === o.value) { a = daftar[i]; break; }
            }

            var jml = Math.max(1, angkaDari(isianJumlah.value) || 1);
            var lebih = (a && a.sisa_kuota !== null) ? jml - a.sisa_kuota : 0;

            tampil(notaKuota, lebih > 0);

            if (lebih > 0) {
                /*
                 * Kalimatnya BERBEDA tergantung ada pesanan lembaganya atau
                 * tidak. Satu kalimat "diperbolehkan" untuk keduanya menyesatkan
                 * pendaftar perorangan: kirimannya justru akan ditolak peladen,
                 * dan ia baru tahu sesudah seluruh borang terisi.
                 */
                notaKuota.querySelector('span').innerHTML = lembagaDipakai()
                    ? '<strong>Melebihi kuota angkatan ' + lebih + ' kursi.</strong> '
                        + 'Diperbolehkan untuk pesanan lembaga — tetapi ruangan dan '
                        + 'konsumsinya perlu disiapkan untuk jumlah itu.'
                    : '<strong>Kursinya tidak cukup, kurang ' + lebih + '.</strong> '
                        + 'Kirimannya akan ditolak. Melebihi kuota hanya boleh untuk '
                        + 'pesanan lembaga — isi nama lembaganya di langkah 5, atau '
                        + 'kurangi jumlahnya.';
            }
        };

        var segarkanLembaga = function () {
            var adaPesanan = menuPemesanan.value !== '';

            tampil(bungkusNamaLembaga, !adaPesanan);
            tampil(bungkusLembaga, !adaPesanan && namaLembaga.value.trim() !== '');

            if (adaPesanan) {
                // Nilainya dikosongkan: isian tersembunyi tetap terkirim, dan
                // nama lembaga yang terbawa akan membuat pesanan KEDUA untuk
                // lembaga yang pesanannya sudah dipilih.
                namaLembaga.value = '';
                ketPemesanan.textContent = 'Pendaftaran ini akan diikat ke pesanan tersebut.';
            } else {
                ketPemesanan.textContent = namaLembaga.value.trim() === ''
                    ? 'Pilih yang sudah ada, atau isi nama lembaganya di bawah untuk membuat baru.'
                    : 'Pesanan baru akan dibuat atas nama lembaga itu.';
            }
        };

        /*
         * Keduanya menentukan hal yang sama — apakah pendaftaran ini pesanan
         * lembaga — jadi keduanya harus mengerjakan hal yang sama.
         *
         * Versi sebelumnya hanya merakit ulang pilihan angkatan saat pesanan
         * LAMA dipilih, jadi mengetik nama lembaga baru tidak membuka kunci
         * angkatan yang penuh: jalan yang justru paling sering dipakai
         * — pesanan lembaga pertama kali — adalah yang tidak bekerja.
         */
        var lembagaBerubah = function () {
            segarkanLembaga();

            var pl = layananTerpilih();

            if (pl && pl.berangkatan) {
                // Angkatan penuh berubah bisa/tidak bisa dipilih, jadi
                // daftarnya dirakit ulang — pilihannya dikembalikan sesudahnya.
                var terpilih = menuAngkatan.value;
                isiAngkatan(pl.nilai, cariAngkatan.value);
                menuAngkatan.value = terpilih;
                tegaskanAngkatan(pl.nilai);
            }

            batasiJumlah();
            ingatkanKuota();
        };

        menuPemesanan.addEventListener('change', lembagaBerubah);
        namaLembaga.addEventListener('input', lembagaBerubah);

        isianJumlah.addEventListener('input', ingatkanKuota);

        cariAngkatan.addEventListener('input', function () {
            var pilih = layananTerpilih();

            if (pilih && pilih.berangkatan) {
                isiAngkatan(pilih.nilai, cariAngkatan.value);
                tegaskanAngkatan(pilih.nilai);
                batasiJumlah();
                hitung();
            }
        });

        if (isianBukti) {
            isianBukti.addEventListener('change', function () {
                var f = isianBukti.files && isianBukti.files[0];
                namaBerkas.textContent = f
                    ? f.name + ' · ' + Math.round(f.size / 1024) + ' KB'
                    : 'Pilih gambar — JPG, PNG, atau WebP, maksimal 4 MB';
            });
        }

        /*
         * Tombol dikunci begitu borangnya dikirim.
         *
         * Tanpa itu tombolnya tetap bisa ditekan selagi permintaannya
         * berjalan, dan tekan dua kali berarti dua baris pendaftaran untuk
         * satu orang — beserta dua kursi yang terpakai.
         */
        borang.addEventListener('submit', function () {
            [el('bar-simpan'), el('bar-simpan-lagi')].forEach(function (b) {
                if (b) {
                    b.setAttribute('aria-busy', 'true');
                }
            });

            var utama = el('bar-simpan');

            if (utama) {
                utama.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Menyimpan…';
            }
        });

        // Ringkasan galat dibawa ke layar dan diberi fokus: pesan yang tidak
        // terlihat sama saja dengan pesan yang tidak ada.
        var ringkasGalat = el('bar-galat');

        if (ringkasGalat) {
            ringkasGalat.scrollIntoView({ block: 'center' });
            ringkasGalat.focus();
        }

        segarkanJenis();
        segarkan();
        segarkanBayar();
        batasiJumlah();
        segarkanPeserta();
        segarkanLembaga();
        ingatkanKuota();
    })();
</script>
@endpush
