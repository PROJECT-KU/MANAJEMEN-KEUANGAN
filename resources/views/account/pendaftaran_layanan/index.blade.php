@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Pendaftar Layanan | MIS Rumah Scopus
@stop

{{--
  Gaya khusus layar ini lewat @push('gaya'), WAJIB — <style> di badan berkas
  terbit sebelum CSS Bootstrap, sehingga aturan berbobot sama selalu kalah.
  Token, kartu, tombol, pil, dan tabelnya datang dari mis-ui.css; yang di sini
  hanya yang memang khas layar ini.
--}}
@push('gaya')
    <style>
        /* Ubin layanan di kolom pertama: medali kecil + namanya, tidak
           terpatah walau namanya dua kata. */
        .pdl-layanan {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .pdl-layanan-nama {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin: 0;
            font-size: .82rem;
            font-weight: 600;
            color: var(--mis-tinta);
            line-height: 1.3;
        }

        .pdl-orang {
            min-width: 0;
        }

        /* display: flex + wrap, bukan teks biasa: pil rombongan di ujung nama
           ber-nowrap, dan pada nama panjang ia mendorong barisnya melewati
           tepi kartu — terukur 41px di 390px. Dengan wrap, pilnya turun ke
           baris berikutnya. align-items: baseline supaya pil dan nama tetap
           sejajar dasarnya saat keduanya sebaris. */
        .pdl-nama {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 4px 5px;
            margin: 0;
            font-size: .86rem;
            font-weight: 600;
            color: var(--mis-tinta);
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        /*
         * Namanya DIPOTONG dua baris.
         *
         * Ia datang dari isian bebas tanpa batas atas, dan satu nama
         * sepanjang 95 aksara membuat selnya 395px — barisnya 424px, lebih
         * dari sepertiga layar untuk satu pendaftaran. Dipotong lewat CSS,
         * jadi nilai penuhnya tetap ada di markah, di title, dan di halaman
         * rincian.
         *
         * Pemotongnya di <span> sendiri, bukan di .pdl-nama: pembungkusnya
         * flex supaya pil rombongan bisa turun ke baris berikutnya, dan
         * -webkit-line-clamp tidak berlaku pada wadah flex.
         */
        .pdl-nama-teks {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-width: 0;
        }

        /* Sudah jadi anak flex, jadi margin kirinya tidak perlu lagi. */
        .pdl-pil-orang {
            margin-left: 0;
        }

        /*
         * DI DALAM SEL TABEL, pemotongan memakai -webkit-line-clamp — BUKAN
         * `white-space: nowrap` + `text-overflow: ellipsis`.
         *
         * Keduanya sama-sama memotong di layar, tetapi `nowrap` membuat lebar
         * MIN-CONTENT sel itu sama dengan panjang teks penuhnya, dan
         * `overflow: hidden` tidak menguranginya. Algoritma tabel memakai
         * min-content untuk menentukan lebar kolom, jadi selnya memaksa
         * tabelnya melebar — terukur: kolom Pendaftar membengkak 227px jadi
         * 866px dan tabelnya terklip 617px di 1440px, 782px di 768px. Tidak
         * ada galat apa pun; kolom paling kanan sekadar hilang.
         *
         * `line-clamp` membiarkan teksnya tetap boleh membungkus — jadi
         * min-content tetap selebar kata terpanjang — lalu menyembunyikan
         * baris di atas batasnya.
         */
        /*
         * Email dan WhatsApp BERTUMPUK, bukan berdampingan.
         *
         * Berdampingan memang menghemat 19px per baris, tetapi menuntut
         * `nowrap` pada keduanya supaya tidak saling berdesakan — dan itu
         * yang meledakkan lebar kolomnya sampai tabelnya terklip di semua
         * lebar. Dicoba dan dibatalkan; 19px tidak sebanding.
         */
        .pdl-kontak {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 3px;
            min-width: 0;
            font-size: .76rem;
            color: var(--mis-tinta-3);
        }

        /*
         * TIDAK dipotong, dan itu disengaja.
         *
         * Dua percobaan gagal di sini, keduanya karena `-webkit-box`:
         * `-webkit-box-orient: vertical` memperlakukan tiap anak sebaris
         * sebagai anak kotak tersendiri, jadi ikon amplopnya terdorong ke
         * BARIS SENDIRI di atas alamatnya. Dan dengan clamp satu baris,
         * alamat email yang satu kata panjang tampil sebagai ikon plus titik
         * tiga saja — sama sekali tidak ada gunanya untuk kolom yang gunanya
         * menghubungi orangnya.
         *
         * Dibiarkan membungkus biasa, tingginya tetap terbatas: alamat
         * terpanjang di data yang ada muat dalam dua baris pada kolom 229px.
         */
        /*
         * Ikon mendapat KOLOMNYA SENDIRI, teks di kolom kedua.
         *
         * Sebagai elemen sebaris, ikon amplop berbagi baris dengan alamatnya —
         * dan alamat email itu satu kata panjang yang tidak muat di sisa baris
         * pertama, jadi ia turun seluruhnya dan ikonnya tertinggal sendirian
         * di atas. Dengan kisi dua kolom, ikonnya tetap sebaris dengan baris
         * pertama alamatnya berapa pun panjangnya.
         */
        .pdl-kontak > * {
            display: grid;
            grid-template-columns: 14px minmax(0, 1fr);
            gap: 0 5px;
            align-items: start;
            min-width: 0;
        }

        .pdl-kontak > * > i {
            margin-top: 3px;
        }

        .pdl-kontak a {
            color: var(--mis-tinta-2);
            text-decoration: none;
        }

        .pdl-kontak a:hover {
            color: var(--mis-tinta);
            text-decoration: underline;
        }

        /*
         * Afiliasi SATU baris.
         *
         * Isian bebas tanpa batas atas: terukur satu baris afiliasi setinggi
         * 196px — sembilan baris teks untuk satu keterangan pendamping, dan
         * ia memanjangkan seluruh barisnya. Nilai penuhnya tetap di title dan
         * di halaman rincian.
         */
        .pdl-kontak-kosong {
            color: var(--mis-tinta-4);
        }

        /* Masih dipakai mode kartu di rincian? Tidak — tetapi aturannya
           dibiarkan: kelasnya tidak lagi dipakai daftar sejak email dan
           afiliasi pindah ke rincian, dan membuangnya menuntut memeriksa
           ulang seluruh berkas ini untuk satu blok yang tidak merugikan. */

        /*
         * Alamat email WAJIB boleh dipatah di mana saja.
         *
         * Alamat adalah satu kata tanpa spasi, jadi lebar min-content-nya
         * sama dengan panjang penuhnya — dan anak flex tidak pernah menyusut
         * di bawah min-content-nya. Terukur di 320px:
         * "trianggategarutama@gmail.com" menuntut 187px, dan bersama label
         * kartunya membuat sel Pendaftar jadi 294px sementara sel lain 232px.
         * Kartunya meluber 48px dan terpotong, sebab pembungkusnya
         * overflow:hidden.
         */
        .pdl-kontak a,
        .pdl-kontak span,
        .pdl-nama {
            overflow-wrap: anywhere;
        }

        /* Nomor pendaftaran TIDAK boleh terpatah.

           Tanda hubung di dalamnya adalah titik patah yang sah bagi peramban,
           jadi "WE-20261003-0001" pecah jadi tiga baris di kolom sempit dan
           terbaca seperti rusak. Dipotong dengan elipsis kalau memang tidak
           muat, dan nilai penuhnya tetap ada di atribut title. */
        /*
         * Nomor pendaftaran TIDAK dipotong sama sekali.
         *
         * Inilah yang disebut orang saat menghubungi panitia lewat WhatsApp.
         * Dipotong satu baris ia tampil "WE-..." dan tidak ada gunanya;
         * dipotong dengan elipsis pun sama saja. Dibiarkan membungkus, ia
         * paling banyak dua baris — tanda hubung di dalamnya titik patah yang
         * sah, dan itu memang tempat yang wajar untuk patah.
         */
        .pdl-nomor {
            display: block;
            margin-bottom: 2px;
            max-width: 100%;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .74rem;
            line-height: 1.35;
            color: var(--mis-tinta-3);
        }

        /* Uang dirapatkan ke kanan dan tidak dibiarkan terpatah: angka
           berpindah baris di tengahnya terbaca sebagai dua angka. */
        .pdl-uang {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            color: var(--mis-tinta);
        }

        /* Nominalnya TIDAK boleh terpatah — angka yang berpindah baris di
           tengahnya terbaca sebagai dua angka. Keterangan di bawahnya justru
           harus boleh terpatah: ia kalimat, dan "potongan Rp 225.000 ·
           SalamQ1" menuntut 175px sementara selnya di 320px tinggal 46px.
           Itulah yang meluberkan tabelnya 234px. */
        .pdl-uang-ket {
            display: block;
            margin-top: 2px;
            font-size: .72rem;
            font-weight: 400;
            color: var(--mis-tinta-4);
        }

        /* Pembungkus satu-anak untuk sel yang isinya bertingkat. Di mode
           kartu selnya flex, jadi tanpa pembungkus tiap bagian jadi anak
           flex sendiri dan berbaris mendatar alih-alih menumpuk. */
        .pdl-uang-blok,
        .pdl-keadaan-blok {
            display: inline-block;
            min-width: 0;
        }

        /*
         * Nama angkatan DIPOTONG dua baris.
         *
         * Penyumbang tinggi baris terbesar: "Scopus Detective: Trik Cepat
         * Membongkar Kedok Jurnal Palsu dan Predator" terpakai utuh dan
         * memanjangkan SELURUH baris jadi 312px. Terukur sebelum dipotong,
         * tinggi baris rata-rata 154px dan hanya 6,5 baris terlihat sekali
         * layar — sementara layar acuan Data Pelanggan 67px dan 14,9 baris.
         *
         * Teks penuhnya tetap ada di atribut title, dan selengkapnya di
         * halaman rincian. Memotong di MARKAH akan menghilangkannya juga dari
         * cetakan dan penyalinan teks; dipotong lewat CSS, yang hilang cuma
         * tampilannya.
         */
        .pdl-sesi {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: .8rem;
            color: var(--mis-tinta-2);
            line-height: 1.35;
        }

        /* Rombongan ditandai di sebelah nama, bukan di kolom angka sendiri.
           Terukur ada baris berisi 5, 6, dan 13 orang di antara 187 baris. */
        .pdl-pil-orang {
            margin-left: 5px;
            vertical-align: middle;
        }

        /* Keping bukti bayar: teks berikon, bukan lencana. mis-tabel-kartu
           menandai sel status lewat :has(.mis-pil) dan menaikkannya ke baris
           kaki kartu — dua sel berpil berarti keduanya berbagi satu baris
           sempit, dan di 320px masing-masing tinggal 46px. */
        .pdl-bukti-baris {
            display: block;
            margin-top: 4px;
        }

        .pdl-bukti-ada {
            color: #047857;
        }

        .pdl-bukti-nihil {
            color: var(--mis-tinta-4);
        }

        .pdl-bukti-hilang {
            color: #92400e;
            cursor: help;
        }

        /* Keterangan angkatan: tanggal mulai dan sisa kursinya, di bawah
           namanya. Satu baris kecil, bukan kolom sendiri — keduanya
           keterangan TENTANG angkatan itu, bukan nilai yang dibaca sendiri. */
        /* Nomor angkatan. Ungu dan bertebal, bukan abu seperti keterangan
           lain di bawahnya: ia PENANDA yang dipakai merekap, bukan catatan
           pendamping — dan di antara dua ratus angkatan Yogyakarta, nomornya
           satu-satunya yang membedakan. */
        .pdl-angkatan-no {
            display: inline-block;
            margin-left: 5px;
            padding: 1px 6px;
            border-radius: 6px;
            background: #eef2ff;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .72rem;
            font-weight: 700;
            color: #4338ca;
            cursor: help;
        }

        .pdl-sesi-ket {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 3px 7px;
            margin-top: 3px;
            font-size: .72rem;
            color: var(--mis-tinta-4);
        }

        /* "penuh" merah, "sisa N" abu: yang pertama menghalangi pendaftaran
           baru, yang kedua cuma keterangan. Warnanya membedakan keduanya
           tanpa menuntut membaca kata-katanya. */
        .pdl-penuh {
            padding: 1px 6px;
            border-radius: 6px;
            background: #fff1f2;
            font-weight: 700;
            color: #9f1239;
            cursor: help;
        }

        .pdl-sisa {
            padding: 1px 6px;
            border-radius: 6px;
            background: #f1f5f9;
        }

        .pdl-tanggal {
            display: block;
            font-size: .8rem;
            white-space: nowrap;
            color: var(--mis-tinta-2);
        }

        .pdl-jam {
            display: block;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .72rem;
            color: var(--mis-tinta-4);
        }

        /*
         * Garis aksen berwarna layanannya di tepi kiri baris.
         *
         * Lewat box-shadow inset, bukan border-left: border mengubah lebar
         * kotak selnya dan menggeser seluruh kolom 3px; inset shadow digambar
         * di dalam kotak yang sudah ada. Warnanya diambil dari `--warna` yang
         * sudah disetel kelas layanannya, jadi tidak ada daftar warna kedua
         * yang harus dijaga tetap cocok.
         */
        @media (min-width: 768px) {
            .mis-tabel .pdl-baris > td:first-child {
                box-shadow: inset 3px 0 0 0 var(--warna-aksen, #e2e8f0);
            }
        }

        .pdl-baris.mis-ungu { --warna-aksen: #6366f1; }
        .pdl-baris.mis-hijau { --warna-aksen: #10b981; }
        .pdl-baris.mis-biru { --warna-aksen: #0ea5e9; }
        .pdl-baris.mis-kuning { --warna-aksen: #f59e0b; }
        .pdl-baris.mis-jingga { --warna-aksen: #f97316; }
        .pdl-baris.mis-merah { --warna-aksen: #f43f5e; }
        .pdl-baris.mis-abu { --warna-aksen: #94a3b8; }

        /*
         * TABLET (768-1199px): kolomnya dirapatkan supaya tabelnya tidak
         * terklip.
         *
         * Pembungkus tabel ber-`overflow-x: hidden`, jadi tabel yang lebih
         * lebar darinya TIDAK menggulung — ia terpotong, dan kolom paling
         * kanan hilang dari pandangan tanpa gejala apa pun. Terukur: lebar
         * min-content tabel ini 868px sementara pembungkusnya 706px di 768px
         * dan 758px di 820px, jadi kolom Aksi terpotong 161px dan 109px.
         *
         * Dibandingkan layar acuan Data Pelanggan yang 0px di semua lebar,
         * selisihnya ada di dua hal yang tidak bisa menyusut: tombol beteks
         * dan lencana keadaan ber-`white-space: nowrap`. Keduanya dilonggarkan
         * di sini, bukan kolomnya yang disembunyikan — kolom yang hilang
         * berarti keterangan yang tidak bisa dijangkau siapa pun.
         */
        @media (min-width: 768px) and (max-width: 1199.98px) {
            /* Tombolnya jadi ikon saja. Tulisannya disembunyikan untuk MATA,
               bukan dibuang: aria-label dan title tetap ada di markahnya, jadi
               pembaca layar dan tooltip tetap menyebut tujuannya. */
            .pdl-tombol-teks {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0 0 0 0);
                white-space: nowrap;
            }

            .pdl-tombol-buka {
                padding: 0 11px;
            }

            /* Lencana keadaan boleh terpatah: "Menunggu bayar" menuntut
               sekitar 135px dalam satu baris, dan itu sendiri hampir
               seperlima lebar tabel yang tersedia. */
            .pdl-tabel .mis-pil {
                white-space: normal;
                text-align: left;
            }

            .pdl-tabel .pdl-sesi,
            .pdl-tabel .pdl-kontak {
                overflow-wrap: anywhere;
            }

            /* Tanggalnya boleh terpatah di sini. Di layar lebar ia `nowrap`
               supaya "03 Okt 2026" tidak terbelah tanpa alasan, tetapi di
               tablet ~40px itu yang menyisakan selisih 1-3px terakhir. */
            .pdl-tabel .pdl-tanggal {
                white-space: normal;
            }

            /* Keping nomor angkatan turun ke barisnya sendiri, tidak lagi
               menempel di samping nama angkatannya. Terukur: di samping nama,
               ia menambah lebar min-content kolom Sesi secukupnya untuk
               membuat tabelnya terklip 4px di 768px. Nomornya tetap terbaca
               utuh, cuma pindah baris. */
            .pdl-tabel .pdl-angkatan-no {
                display: block;
                width: max-content;
                margin: 3px 0 0;
                padding: 0 5px;
            }
        }

        /* Di mode kartu, garisnya pindah ke tepi kartunya sendiri. */
        @media (max-width: 767.98px) {
            .mis-tabel.mis-tabel-kartu tbody tr.pdl-baris {
                box-shadow: inset 3px 0 0 0 var(--warna-aksen, #e2e8f0);
            }
        }

        /* Keping redup, BUKAN teks bergaris titik.

           Dengan garis titik, nilai seperti "pending" dan "expired" terbaca
           seperti salah ketik yang digarisbawahi pemeriksa ejaan, bukan
           seperti keterangan. Sebagai keping kecil berlatar ia jelas sebuah
           nilai — dan keterangannya tetap di title. */
        .pdl-status-asli {
            display: inline-block;
            margin-top: 3px;
            padding: 1px 6px;
            border-radius: 6px;
            background: #f1f5f9;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .68rem;
            color: var(--mis-tinta-4);
            cursor: help;
        }

        .pdl-bukti {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .76rem;
            text-decoration: none;
        }

        .pdl-bukti i {
            font-size: inherit;
        }

        /* Dua keadaan bukti yang bukan tautan. Warnanya membedakan "memang
           belum diunggah" dari "sudah diunggah tetapi berkasnya lenyap" —
           terukur 72 dari 183 nilai bukti menunjuk berkas yang sudah tidak
           ada, dan keduanya menuntut tindakan yang berbeda. */
        .pdl-bukti-nihil {
            color: var(--mis-tinta-4);
        }

        .pdl-bukti-hilang {
            color: #92400e;
            cursor: help;
        }

        /* Jumlah uang di kepala daftar. Bukan ubin saringan: ia tidak bisa
           jadi tautan ke mana pun, dan panduan menuntut tiap ubin ringkasan
           berupa saringan yang bisa ditekan. */
        /*
         * Kisi yang MELEBAR, bukan sebaris yang menggantung.
         *
         * Sebagai flex sebaris, isinya berakhir di 686px sementara kotaknya
         * 1.160px — terukur 474px petak kosong di ujung kanan, dan ketiga
         * angkanya berdesakan di kiri seolah sisa ruang itu menunggu sesuatu
         * yang tidak pernah datang.
         *
         * auto-fit, bukan jumlah kolom tetap: dua baris terakhirnya muncul
         * hanya kalau memang ada — pendaftaran yang menggantung dan status
         * yang belum dikenali — jadi jumlahnya berubah-ubah antara dua sampai
         * empat, dan kolom tetap akan menganggur persis seperti sebelumnya.
         */
        .pdl-uang-total {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 215px), 1fr));
            gap: 10px;
            margin: 0 0 var(--mis-jarak);
            padding: 0;
            border: 0;
            background: none;
            font-size: .82rem;
            color: var(--mis-tinta-2);
        }

        /* Tiap angka jadi kotaknya sendiri, sebentuk dengan ubin di atasnya —
           keduanya sama-sama "angka yang menerangkan daftar ini", cuma yang
           ini tidak bisa diklik untuk menyaring. */
        .pdl-uang-total > * {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            padding: 11px 14px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: var(--mis-kartu);
            color: inherit;
            text-decoration: none;
        }

        .pdl-uang-total a:hover {
            border-color: #c7d2fe;
        }

        /* Ikonnya tidak ikut menyusut saat angkanya panjang. */
        .pdl-uang-total > * > i {
            flex: 0 0 auto;
        }

        /*
         * Lipatan saringan lain. Sebaris penuh di dalam .mis-saring yang
         * flex, jadi isinya menata diri persis seperti saat ia belum dilipat.
         */
        .pdl-lain {
            flex: 1 1 100%;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 10px;
        }

        .pdl-lain[hidden] {
            display: none;
        }

        /* Tuasnya sejajar dasar kotak isian di sebelahnya, bukan dasar
           labelnya: .mis-saring memakai align-items: flex-end, dan tombol
           tanpa label di atasnya akan duduk lebih tinggi. */
        .pdl-lain-tuas {
            flex: 0 0 auto;
            align-self: flex-end;
        }

        .pdl-uang-total strong {
            display: block;
            font-size: 1.02rem;
            font-variant-numeric: tabular-nums;
            color: var(--mis-tinta);
            overflow-wrap: anywhere;
        }

        .pdl-uang-angka {
            min-width: 0;
            line-height: 1.35;
        }

        .pdl-uang-angka span {
            display: block;
            font-size: .72rem;
            color: var(--mis-tinta-4);
        }

        /* Ikon glifnya WAJIB mewarisi ukuran: layout memasang
           .fas { font-size: 20px } untuk seluruh halaman, dan aturan itu
           menang atas pewarisan — jadi mengecilkan pembungkusnya saja tidak
           pernah mengubah ikonnya. */
        .pdl-tabel .mis-medali i,
        .pdl-bukti i,
        .pdl-kontak i {
            font-size: inherit;
            width: 100%;
            text-align: center;
        }

        .pdl-kontak i,
        .pdl-bukti i {
            width: auto;
        }

        /* Di mode kartu, kolom layanan jadi judul barisnya. */
        @media (max-width: 767.98px) {
            /* Sebaris supaya kartunya tidak memanjang, tetapi TETAP boleh
               terpatah: dipaksa sebaris tanpa itu, kalimat potongan diskon
               mendorong selnya melewati tepi kartu. */
            .pdl-uang-ket {
                display: inline;
                margin-left: 6px;
            }

            /*
             * Sel Pendaftar: label di ATAS, nilai selebar kartu.
             *
             * Pola bersama mode kartu menaruh label di kiri dan nilai di
             * kanan, dan itu pas untuk nilai sebaris seperti di Data
             * Pelanggan. Sel ini memuat empat baris — nama, email, nomor,
             * afiliasi — jadi labelnya menggantung sendirian di kiri dengan
             * ruang kosong lebar, dan afiliasinya terdampar rata kanan.
             * Terukur: selnya 109px tinggi sementara sel lain 19-52px.
             */
            .mis-tabel.mis-tabel-kartu.pdl-tabel tbody td[data-judul="Pendaftar"] {
                display: block;
                text-align: left;
                /* Anak flex TIDAK pernah menyusut di bawah min-content-nya,
                   dan alamat email adalah satu kata panjang. Tanpa ini selnya
                   pernah terukur 344px di dalam kartu selebar 246px. */
                min-width: 0;
            }

            .mis-tabel.mis-tabel-kartu.pdl-tabel tbody td[data-judul="Pendaftar"]::before {
                display: block;
                margin-bottom: 4px;
            }

            /* Sesi boleh tiga baris di kartu: ruangnya selebar kartu, dan
               memotongnya di dua baris di sini justru membuang keterangan
               yang masih muat. */
            .pdl-sesi {
                -webkit-line-clamp: 3;
            }

            /* Kontak kembali bertumpuk di kartu: lebarnya memang cukup untuk
               satu alamat per baris, dan berdampingan di 390px membuat
               keduanya terpotong elipsis padahal ruangnya ada. */
            .pdl-kontak {
                flex-direction: column;
                gap: 3px;
            }

            /* Di kartu ruangnya selebar kartu, jadi kontak boleh dua baris
               penuh — alamat email panjang terbaca utuh di sini. */
            .pdl-kontak > * {
                -webkit-line-clamp: 2;
                overflow-wrap: anywhere;
            }

            /* Sesi dua baris di kartu juga, bukan tiga: tiga baris menambah
               17px pada tiap kartu untuk keterangan yang sudah terbaca dari
               dua baris pertamanya. */
            .pdl-sesi {
                -webkit-line-clamp: 2;
            }

            /*
             * Label "Layanan" dan "Pendaftar" DISEMBUNYIKAN di kartu.
             *
             * Keduanya tidak memberi tahu apa pun: medali berwarna beserta
             * nama layanannya sudah menyebutkan dirinya, dan blok nama +
             * email + nomor jelas seorang pendaftar. Tiap label menyumbang
             * satu baris penuh pada kartunya — terukur 21px masing-masing,
             * pada 20 kartu per halaman.
             *
             * Label sel LAIN tetap ada: "Rp 129.000" tanpa label tidak
             * menyebutkan dirinya total bayar, dan "03 Okt 2026" tidak
             * menyebutkan dirinya tanggal mendaftar.
             *
             * CATATAN percobaan yang GAGAL: memasangkan sel pendek
             * berdampingan lewat `order` + `flex: 1 1 0` justru menaikkan
             * maksimum kartu dari 480px jadi 890px dan mengembalikan luberan
             * 86px — sel yang menyempit membuat isinya membungkus lebih
             * banyak daripada yang dihemat. Jangan diulang tanpa mengukur.
             */
            .mis-tabel.mis-tabel-kartu.pdl-tabel tbody td[data-judul="Pendaftar"]::before {
                display: none;
            }

            /* Keterangan status aslinya ikut boleh terpatah; di 320px
               "Pendaftaran Dibatalkan" menuntut 78px dalam sel yang
               tersisa 46px. */
            .pdl-status-asli {
                overflow-wrap: anywhere;
            }
        }

        /*
         * Layar tersempit: sel keadaan mendapat barisnya SENDIRI.
         *
         * mis-tabel-kartu menaikkan sel berpil ke baris kaki kartu bersama
         * sel aksinya, dan keduanya berbagi lebar lewat `flex: 1 1 0`.
         * Terukur di 320px sel keadaan tinggal 101px sementara lencana
         * "Menunggu bayar" sendiri menuntut 125px dan tidak boleh terpatah —
         * jadi kartunya meluber 48px dan terpotong.
         *
         * Diberi lebar penuh, ia turun ke barisnya sendiri dan mendapat 232px.
         * Ambang 359.98px memang disediakan panduan untuk penyesuaian terakhir
         * semacam ini, jadi bukan titik putus baru.
         */
        /*
         * Sel keadaan mendapat BARISNYA SENDIRI di seluruh mode kartu.
         *
         * mis-tabel-kartu menaikkan sel berpil ke baris kaki kartu bersama sel
         * aksinya, dan keduanya berbagi lebar lewat `flex: 1 1 0`. Sel ini
         * memuat tiga hal bertumpuk — lencana keadaan, keping bukti, dan
         * status aslinya — jadi separuh baris kaki tidak cukup: terukur
         * meluber 38px di 390px dan lebih lagi di 320px. Aturan ini sempat
         * dibatasi 359.98px dan itu keliru; keluhannya ada sejak 767.98px.
         *
         * Pemilihnya menyebut .mis-tabel.mis-tabel-kartu juga, bukan
         * .pdl-tabel saja: aturan di mis-ui.css berbobot (0,3,2) dan pemilih
         * (0,2,2) kalah walau ditulis belakangan. Versi pertama aturan ini
         * tidak mengubah apa pun justru karena itu.
         */
        @media (max-width: 767.98px) {
            .mis-tabel.mis-tabel-kartu.pdl-tabel tbody td:has(.mis-pil) {
                flex: 1 1 100%;
            }
        }
    </style>
@endpush

@section('content')
@php
    /*
     * Dirakit di sini sekali, bukan diulang di tiap tautan.
     *
     * $bawa ikut dibawa oleh kepala kolom yang bisa diurutkan dan oleh
     * penomoran halaman; tanpa itu, mengurutkan diam-diam menghapus
     * saringannya. Dan $lingkup dipakai ubin ringkasan supaya menekan satu
     * keadaan tidak melepaskan pilihan layanannya.
     *
     * Semuanya blok @php, tidak ada @php(...) sebaris di berkas ini: Blade
     * memproses blok lebih dulu, dan penanda sebaris di atas sebuah blok ikut
     * dianggap pembukanya sehingga seluruh markah di antaranya tertelan.
     */
    $bawa = request()->only('cari', 'layanan', 'keadaan', 'bukti', 'angkatan', 'dari', 'sampai', 'lama');
    /* Lingkup = saringan yang BUKAN keadaan. Dipakai ubin ringkasan supaya
       menekan satu keadaan tidak melepaskan pilihan layanan dan tanggalnya. */
    $lingkup = array_filter(request()->only('cari', 'layanan', 'angkatan', 'dari', 'sampai'));

    $rute = 'account.pendaftaran-layanan.index';

    $ariaUrut = function ($kolom) use ($urut, $arah) {
        if ($urut !== $kolom) {
            return 'none';
        }

        return $arah === 'asc' ? 'ascending' : 'descending';
    };

    $pilihanUrut = [
        ['waktu', 'turun', 'Terbaru mendaftar'],
        ['waktu', 'naik', 'Terlama mendaftar'],
        ['nama', 'naik', 'Nama A–Z'],
        ['total', 'turun', 'Bayar terbesar'],
        ['layanan', 'naik', 'Dikelompokkan per layanan'],
        ['status', 'naik', 'Dikelompokkan per status'],
    ];
    $arahSekarang = $arah === 'asc' ? 'naik' : 'turun';
@endphp
<div class="main-content mis-badan">
    <section class="section">

        {{-- Pemberitahuan lewat misToast(), bukan alert Bootstrap.

             Sempat TERLEWAT di berkas ini: menghapus pendaftaran mengalihkan
             ke halaman ini dengan pesan sukses, dan tanpa partial ini pesannya
             tidak pernah muncul — barisnya hilang tanpa satu kata pun. --}}
        @include('account.pendaftaran_layanan.partials.pesan')

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Pendaftar Layanan</h1>
                <p class="mis-sub">
                    Semua yang mendaftar layanan jasa Rumah Scopus, dari lima layanan sekaligus.
                </p>
            </div>
            <div class="mis-kepala-aksi mis-kepala-aksi-pasangan">
                {{-- Tombol UTAMA, diletakkan pertama dan berwarna penuh:
                     mendaftarkan orang itu tindakan, sementara kedua unduhan
                     adalah pelengkap. Sebelum ada ini, yang mendaftar lewat
                     WhatsApp atau datang langsung tidak bisa dimasukkan sama
                     sekali. --}}
                <a class="mis-tombol mis-tombol-ungu"
                    href="{{ route('account.pendaftaran-layanan.baru') }}">
                    <i class="fas fa-user-plus" aria-hidden="true"></i> Daftarkan
                </a>
                {{-- Unduhan membawa saringan yang sedang dipakai, bukan seluruh
                     tabel: yang diunduh orang hampir selalu yang dilihatnya.
                     Dua bentuk, bukan satu — PDF untuk dibaca dan dilampirkan,
                     lembar kerja untuk diolah jadi daftar hadir dan sertifikat. --}}
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.pendaftaran-layanan.excel', $bawa + request()->only('urut', 'arah')) }}">
                    <i class="fas fa-file-excel mis-ikon-hijau"></i> Unduh Excel
                </a>
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.pendaftaran-layanan.pdf', $bawa + request()->only('urut', 'arah')) }}">
                    <i class="fas fa-file-pdf mis-ikon-merah"></i> Unduh PDF
                </a>
            </div>
        </div>

        {{-- ---------------------------------------------- ringkasan --}}
        {{-- Lima angka yang paling sering ditanyakan, dihitung dari SELURUH
             baris pada lingkup layanan yang dipilih — bukan dari halaman yang
             sedang tampil.

             Tiap ubin sekaligus pintasan saringan, berupa tautan dan bukan
             tombol berskrip: alamatnya bisa disalin dan tetap bekerja tanpa
             JavaScript. Angka yang menarik perhatian selalu memancing "yang
             mana saja?", dan tanpa itu pertanyaannya tidak terjawab. --}}
        <div class="mis-ringkas-geser" data-mis-geser>
        <div class="mis-ringkas mis-ringkas-5" aria-label="Ringkasan pendaftar">
            <a class="mis-ubin {{ $keadaanDipilih === '' && $bukti === '' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup) }}"
                title="Tampilkan semua pendaftaran">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['semua'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Seluruh pendaftaran</p>
                </div>
            </a>

            {{-- Yang paling menuntut tindakan ditaruh kedua, bukan di ujung:
                 bukti transfernya sudah diunggah tetapi statusnya masih
                 menunggu, jadi ada orang yang sedang menunggu dicek. --}}
            <a class="mis-ubin {{ $bukti === 'ada' && $keadaanDipilih === 'menunggu' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup + ['keadaan' => 'menunggu', 'bukti' => 'ada']) }}"
                title="Saring: sudah unggah bukti tapi belum ditandai lunas">
                <span class="mis-medali kecil mis-merah" aria-hidden="true"><i class="fas fa-receipt"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['perlu_diperiksa'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Bukti perlu diperiksa</p>
                </div>
            </a>

            <a class="mis-ubin {{ $keadaanDipilih === 'menunggu' && $bukti === '' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup + ['keadaan' => 'menunggu']) }}"
                title="Saring: hanya yang belum bayar">
                <span class="mis-medali kecil mis-kuning" aria-hidden="true"><i class="fas fa-clock"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['menunggu'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Menunggu bayar</p>
                </div>
            </a>

            <a class="mis-ubin {{ $keadaanDipilih === 'lunas' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup + ['keadaan' => 'lunas']) }}"
                title="Saring: hanya yang sudah lunas">
                <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['lunas'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Lunas</p>
                </div>
            </a>

            <a class="mis-ubin {{ $keadaanDipilih === 'tidak_jadi' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup + ['keadaan' => 'tidak_jadi']) }}"
                title="Saring: dibatalkan, ditolak, atau kedaluwarsa">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-times-circle"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['tidak_jadi'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Tidak jadi</p>
                </div>
            </a>
        </div>
            <p class="mis-ringkas-petunjuk" aria-hidden="true">
                <i class="fas fa-arrows-alt-h"></i> Geser untuk lihat semua
            </p>
        </div>

        {{-- Jumlah orang dan uangnya di luar ubin: keduanya tidak bisa jadi
             saringan, dan panduan menuntut tiap ubin ringkasan berupa tautan
             yang menyaring.

             Angkanya DI ATAS keterangannya, bukan di tengah kalimat. "Uang
             masuk dari yang lunas Rp 495.438.943" menuntut dibaca sampai habis
             sebelum angkanya ketemu; angka dulu lalu keterangan kecil terbaca
             sekali lihat, dan itu bentuk yang sama dengan ubin di atasnya. --}}
        <div class="pdl-uang-total">
            <span>
                <i class="fas fa-users mis-ikon-ungu" aria-hidden="true"></i>
                <span class="pdl-uang-angka">
                    <strong>{{ number_format($ringkasan['orang'], 0, ',', '.') }}</strong>
                    <span>orang terdaftar</span>
                </span>
            </span>

            <span>
                <i class="fas fa-wallet mis-ikon-hijau" aria-hidden="true"></i>
                <span class="pdl-uang-angka">
                    <strong>Rp {{ number_format($ringkasan['uang_lunas'], 0, ',', '.') }}</strong>
                    <span>uang masuk dari yang lunas</span>
                </span>
            </span>

            @if ($ringkasan['menggantung'] > 0)
                {{-- Pendaftaran yang menunggu terlalu lama. Ditaruh di baris
                     ini, bukan jadi ubin keenam: ubinnya sudah lima dan
                     kisinya dipatok lima kolom — tetapi angkanya tetap harus
                     bisa DITEKAN, sebab "ada 5 yang menggantung" tanpa cara
                     melihat yang mana tidak menjawab apa pun.

                     Empat dari lima layanan tidak punya kedaluwarsa sama
                     sekali, jadi pendaftaran yang transfernya tidak pernah
                     datang menunggu selamanya tanpa ada yang menengok. --}}
                <a href="{{ route($rute, $lingkup + ['lama' => '1']) }}"
                    title="Saring: menunggu bayar lebih dari {{ $ringkasan['hari_menggantung'] }} hari">
                    <i class="fas fa-hourglass-half mis-ikon-merah" aria-hidden="true"></i>
                    <span class="pdl-uang-angka">
                        <strong>{{ $ringkasan['menggantung'] }}</strong>
                        <span>menunggu lebih dari {{ $ringkasan['hari_menggantung'] }} hari</span>
                    </span>
                </a>
            @endif

            @if ($ringkasan['lain'] > 0)
                {{-- Hanya muncul kalau memang ada. Status di kelima tabel
                     berupa varchar bebas, jadi nilai baru bisa muncul kapan
                     saja tanpa migrasi — dan baris bernilai baru harus tetap
                     bisa ditemukan, bukan hilang dari semua saringan. --}}
                <a href="{{ route($rute, $lingkup + ['keadaan' => 'lain']) }}">
                    <i class="fas fa-exclamation-triangle mis-ikon-kuning" aria-hidden="true"></i>
                    <span class="pdl-uang-angka">
                        <strong>{{ $ringkasan['lain'] }}</strong>
                        <span>status belum dikenali</span>
                    </span>
                </a>
            @endif
        </div>

        {{-- Saringan yang dipasang lewat TAUTAN, bukan lewat borang, wajib
             terlihat dan bisa dilepas di sini — saringan yang bekerja tanpa
             terlihat membuat orang menyimpulkan datanya yang kurang. --}}
        @if ($menggantung)
            <p class="pdl-uang-total">
                @if ($menggantung)
                    <span>
                        <i class="fas fa-hourglass-half mis-ikon-merah" aria-hidden="true"></i>
                        Hanya yang menunggu lebih dari
                        <strong>{{ $ringkasan['hari_menggantung'] }} hari</strong>
                    </span>
                    <a href="{{ route($rute, array_filter(request()->only('cari', 'layanan', 'keadaan', 'bukti', 'angkatan', 'dari', 'sampai'))) }}">
                        <i class="fas fa-times" aria-hidden="true"></i> Lepaskan saringan ini
                    </a>
                @endif
            </p>
        @endif

        {{-- ---------------------------------------------- penyaring --}}
        {{-- <details> membungkusnya: di ponsel empat kendali yang selalu
             terbuka memakan satu layar penuh sebelum baris pertama kelihatan.
             Di layar lebar ia dipaksa terbuka oleh mis-ui.js dan ringkasannya
             disembunyikan, jadi tampak seperti baris penyaring biasa. --}}
        <details class="mis-lipat" id="pdl-penyaring" data-mis-lipat>
            <summary>
                <i class="fas fa-sliders-h mis-ikon-ungu" aria-hidden="true"></i>
                Cari &amp; saring
                @if ($adaSaringan)
                    <span class="mis-pil mis-pil-ungu">aktif</span>
                @endif
            </summary>

        <div class="mis-saring-kartu">
        <form method="GET" action="{{ route($rute) }}" class="mis-saring" id="pdl-borang" data-mis-saring="pdl-hasil">
            {{-- Urutan ikut terbawa saat menyaring; tanpa ini, menekan tombol
                 terapkan diam-diam mengembalikan urutannya ke bawaan. --}}
            <input type="hidden" name="urut" value="{{ $urut }}">
            <input type="hidden" name="arah" value="{{ $arahSekarang }}">

            <div class="mis-isian mis-saring-cari">
                <label class="mis-label" for="pdl-cari">Cari</label>
                <input type="search" class="form-control-modern" id="pdl-cari" name="cari" data-mis-cari
                    value="{{ $cari }}" placeholder="Nama, email, nomor WA, nomor pendaftaran, afiliasi, atau angkatan"
                    autocomplete="off" aria-controls="pdl-hasil">
                {{-- Tombol hapus ketikan, type=button supaya tidak ikut
                     mengirim formulir, dan disembunyikan saat kotaknya kosong. --}}
                <button type="button" class="mis-saring-hapus" id="pdl-hapus" data-mis-kosongkan
                    aria-label="Hapus kata kunci pencarian" title="Hapus kata kunci"
                    @if ($cari === '') hidden @endif>
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <span class="mis-saring-sibuk" id="pdl-sibuk" aria-hidden="true"></span>
            </div>

            {{-- Pembuka saringan lipat. type=button supaya ia tidak ikut
                 mengirim borangnya; jumlah yang sedang terpasang disebut di
                 pilnya supaya yang terlipat tidak pernah jadi saringan
                 siluman. --}}
            <button type="button" class="mis-tombol mis-tombol-halus pdl-lain-tuas" id="pdl-lain-tuas"
                aria-expanded="{{ $adaSaringanLain ? 'true' : 'false' }}" aria-controls="pdl-lain">
                <i class="fas fa-sliders-h" aria-hidden="true"></i>
                <span>Saringan lain</span>
                @if ($jumlahSaringanLain > 0)
                    <span class="mis-pil mis-pil-ungu">{{ $jumlahSaringanLain }}</span>
                @endif
            </button>

            <div class="mis-isian mis-saring-pilih">
                <label class="mis-label" for="pdl-layanan">Layanan</label>
                <select class="form-control-modern" id="pdl-layanan" name="layanan">
                    <option value="">Semua layanan</option>
                    @foreach ($katalog as $kunci => $l)
                        <option value="{{ $kunci }}" @selected($layanan === $kunci)>{{ $l['nama'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mis-isian mis-saring-pilih">
                <label class="mis-label" for="pdl-keadaan">Keadaan</label>
                <select class="form-control-modern" id="pdl-keadaan" name="keadaan">
                    <option value="">Semua keadaan</option>
                    @foreach ($keadaan as $kunci => $k)
                        <option value="{{ $kunci }}" @selected($keadaanDipilih === $kunci)>{{ $k['label'] }}</option>
                    @endforeach
                    @if ($ringkasan['lain'] > 0)
                        <option value="lain" @selected($keadaanDipilih === 'lain')>Status belum dikenali</option>
                    @endif
                </select>
            </div>

            {{-- Reset DI SINI, sebelum lipatannya.

                 Lipatan di bawah memakai flex-basis 100% supaya isinya
                 menempati barisnya sendiri — dan apa pun yang ditaruh
                 SESUDAHNYA terdorong ke baris berikutnya. Di belakang
                 lipatan, tombol ini berdiri sendirian di satu baris dengan
                 1.029px petak kosong di kanannya. --}}
            @if ($adaSaringan)
                <a href="{{ route($rute) }}" class="mis-tombol mis-tombol-halus pdl-lain-tuas"
                    title="Hapus semua saringan">
                    <i class="fas fa-times" aria-hidden="true"></i> Reset
                </a>
            @endif

            {{-- Lima saringan yang jarang dipakai, DILIPAT.

                 Delapan kendali sekaligus adalah delapan hal yang harus
                 dibaca sebelum satu nama ditemukan, dan yang memakai layar ini
                 bukan orang yang terbiasa dengan borang. Terukur: delapan
                 saringan memakan dua baris penuh, padahal mencari orang
                 hampir selalu cukup dengan kotak Cari.

                 Dibuka sendiri kalau salah satunya sedang terpasang — lihat
                 skrip di bawah. Saringan aktif yang tersembunyi membuat daftar
                 terlihat kurang isinya tanpa ada yang bisa menjelaskan kenapa.

                 Isiannya tetap di dalam borang walau terlipat, jadi nilainya
                 ikut terkirim seperti biasa. --}}
            <div class="pdl-lain" id="pdl-lain" @if (! $adaSaringanLain) hidden @endif>
                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="pdl-bukti">Bukti bayar</label>
                    <select class="form-control-modern" id="pdl-bukti" name="bukti">
                        <option value="">Semua</option>
                        <option value="ada" @selected($bukti === 'ada')>Sudah diunggah</option>
                        <option value="belum" @selected($bukti === 'belum')>Belum diunggah</option>
                    </select>
                </div>

                {{-- Seluruh katalog ditawarkan, bukan hanya yang boleh dipilih
                     panitia: baris berbayar DOKU tidak bisa dibuat dari layar ini,
                     tetapi tetap harus bisa DICARI dari sini. --}}
                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="pdl-bayar">Cara bayar</label>
                    <select class="form-control-modern" id="pdl-bayar" name="bayar">
                        <option value="">Semua</option>
                        @foreach (\App\Support\PendaftaranSemuaLayanan::CARA_BAYAR as $kb => $cb)
                            <option value="{{ $kb }}" @selected($caraBayar === $kb)>{{ $cb['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Rentang tanggal pendaftaran.

                     DIKEMBALIKAN, bukan fitur baru: ketiga layar pendaftaran yang
                     dibuang punya kendali ini dan benar-benar dipakai. type=date
                     memakai pemilih tanggal bawaan peramban, jadi orang yang tidak
                     terbiasa mengetik format tanggal tidak perlu menebaknya. --}}
                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="pdl-dari">Mendaftar sejak</label>
                    <input type="date" class="form-control-modern" id="pdl-dari" name="dari"
                        value="{{ $dari }}" max="{{ $sampai ?: now()->toDateString() }}">
                </div>

                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="pdl-sampai">Sampai</label>
                    <input type="date" class="form-control-modern" id="pdl-sampai" name="sampai"
                        value="{{ $sampai }}" min="{{ $dari }}" max="{{ now()->toDateString() }}">
                </div>

                {{-- Saringan "menggantung lama" dibawa sebagai isian tersembunyi:
                     ia dipasang lewat tautan di baris ringkasan, bukan dari borang
                     ini, dan tanpa ini ia hilang begitu penyaring lain diterapkan. --}}
                @if ($menggantung)
                    <input type="hidden" name="lama" value="1">
                @endif

                {{-- Menu angkatan.

                     Sebelumnya saringan ini hanya bisa dipasang lewat tautan dari
                     layar Angkatan Layanan — bekerja, tetapi untuk melihat semua
                     pendaftar satu angkatan orang harus memutar lewat layar lain.

                     Dikelompokkan per layanan dengan <optgroup>, dan skrip di
                     bawah menyembunyikan kelompok yang bukan layanan terpilih.
                     TANPA JavaScript seluruh kelompoknya tetap terlihat dan
                     menunya masih bisa dipakai — label kelompoknya yang
                     memberitahu mana milik siapa. --}}
                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="pdl-angkatan">Angkatan</label>
                    <select class="form-control-modern" id="pdl-angkatan" name="angkatan">
                        <option value="">Semua angkatan</option>
                        @foreach ($pilihanAngkatan as $kunciLayanan => $daftar)
                            <optgroup label="{{ $katalog[$kunciLayanan]['nama'] ?? $kunciLayanan }}"
                                data-layanan="{{ $kunciLayanan }}">
                                @foreach ($daftar as $a)
                                    <option value="{{ $a['id'] }}" @selected($angkatan === $a['id'])>
                                        {{ $a['ringkas'] }}@if ($a['nomor']) &middot; angkatan ke-{{ $a['nomor'] }}@endif@if ($a['mulai']) &middot; {{ \Illuminate\Support\Carbon::parse($a['mulai'])->translatedFormat('M Y') }}@endif
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Pengurut KHUSUS ponsel. Kepala kolom yang bisa diurutkan ada di
                 dalam <thead>, dan di mode kartu <thead> disembunyikan untuk
                 pembaca layar saja — terukur berukuran 1x1 dan terklip. Tanpa
                 menu ini kelima kolom yang bisa diurutkan tidak bisa dijangkau
                 sama sekali dari ponsel. --}}
            <div class="mis-isian mis-saring-pilih mis-urut-ponsel">
                <label class="mis-label" for="pdl-urut-pilih">Urutkan</label>
                <select class="form-control-modern" id="pdl-urut-pilih" name="urutgabung" data-mis-urut-ponsel>
                    @foreach ($pilihanUrut as $pilihan)
                        <option value="{{ $pilihan[0] }}|{{ $pilihan[1] }}"
                            @selected($urut === $pilihan[0] && $arahSekarang === $pilihan[1])>{{ $pilihan[2] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombolnya tetap ada di markah dan baru disembunyikan oleh
                 mis-ui.js. Tanpa JavaScript penyaringnya masih bisa dipakai
                 seperti formulir biasa. --}}
            <button type="submit" class="mis-tombol mis-tombol-ungu" id="pdl-terapkan" data-mis-terapkan>
                <i class="fas fa-search"></i> Terapkan
            </button>

        </form>
        </div>
        </details>

        {{-- --------------------------------------------------- daftar --}}
        {{-- Dibungkus dan diberi id: hanya bagian inilah yang ditukar saat
             mengetik, jadi kepala, ringkasan, dan kotak pencariannya tidak
             ikut digambar ulang — dan fokus ketikan tidak hilang.

             role=status + aria-live: isinya ditukar diam-diam tiap ketikan, dan
             tanpa penanda ini pembaca layar tidak mengumumkan apa pun. --}}
        <div class="mis-hasil" id="pdl-hasil" role="status" aria-live="polite" aria-atomic="false">
        @if ($baris->isEmpty())
            <div class="mis-bagian">
                {{-- Dua keadaan yang terasa sama di layar padahal jalan
                     keluarnya berbeda: yang satu ganti kata kunci, yang lain
                     tunggu ada yang mendaftar. Hanya yang pertama dapat ikon
                     bergerak. --}}
                <div class="mis-kosong {{ $adaSaringan ? 'mis-kosong-cari' : '' }}">
                    <span class="mis-kosong-ikon" aria-hidden="true">
                        <i class="fas {{ $adaSaringan ? 'fa-search' : 'fa-clipboard-list' }}"></i>
                    </span>
                    <p class="mis-kosong-judul">
                        {{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada pendaftar' }}
                    </p>
                    <p class="mis-kosong-teks">
                        @if ($adaSaringan)
                            @if ($cari !== '')
                                Tidak ada pendaftar yang cocok dengan &ldquo;<strong>{{ $cari }}</strong>&rdquo;.
                            @endif
                            Coba kata kunci lain, atau hapus saringannya.
                        @else
                            Pendaftar muncul di sini begitu ada yang mendaftar lewat halaman layanan.
                        @endif
                    </p>
                    @if ($adaSaringan)
                        <a href="{{ route($rute) }}" class="mis-tombol mis-tombol-halus mis-kosong-aksi">
                            <i class="fas fa-times"></i> Hapus saringan
                        </a>
                    @endif
                </div>
            </div>
        @else
            {{-- mis-tabel-kartu: di bawah 768px tabelnya berubah jadi tumpukan
                 kartu, jadi tidak perlu digeser ke samping di ponsel. Stisla
                 memaksa min-width 800px pada tabel di dalam .table-responsive,
                 dan kelas inilah yang menimpanya. --}}
            <div class="mis-tabel-bungkus">
                <table class="mis-tabel mis-tabel-kartu pdl-tabel">
                    <thead>
                        <tr>
                            <th aria-sort="{{ $ariaUrut('layanan') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'layanan', 'label' => 'Layanan'])
                            </th>
                            <th aria-sort="{{ $ariaUrut('nama') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'nama', 'label' => 'Pendaftar'])
                            </th>
                            <th>Sesi</th>
                            <th class="text-right" aria-sort="{{ $ariaUrut('total') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'total', 'label' => 'Total bayar'])
                            </th>
                            <th aria-sort="{{ $ariaUrut('status') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'status', 'label' => 'Keadaan'])
                            </th>
                            <th aria-sort="{{ $ariaUrut('waktu') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'waktu', 'label' => 'Daftar'])
                            </th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($baris as $b)
                            @include('account.pendaftaran_layanan.baris', ['b' => $b, 'katalog' => $katalog])
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $baris->links('vendor.pagination.bootstrap-4') }}
        @endif
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        /*
         * Tuas lipatan "Saringan lain".
         *
         * Keadaan AWALNYA ditentukan peladen, bukan skrip ini: lipatannya
         * sudah terbuka dari markah kalau ada saringan lipat yang terpasang.
         * Dibiarkan skrip yang membukanya, ia sempat berkedip tertutup dulu —
         * dan tanpa JavaScript, saringan yang sedang aktif tidak akan pernah
         * terlihat sama sekali.
         */
        var tuasLain = document.getElementById('pdl-lain-tuas');
        var kotakLain = document.getElementById('pdl-lain');

        if (tuasLain && kotakLain) {
            tuasLain.addEventListener('click', function () {
                var tutup = kotakLain.hidden;

                kotakLain.hidden = !tutup;
                tuasLain.setAttribute('aria-expanded', tutup ? 'true' : 'false');
            });
        }

        /*
         * Kelompok angkatan disempitkan mengikuti layanan yang dipilih.
         *
         * Menunya memuat seluruh angkatan yang punya pendaftar — 39 dari 59 —
         * dan menawarkan angkatan Bibliometrik saat yang disaring Scopus Camp
         * cuma memberi pilihan yang pasti mengembalikan nol baris.
         *
         * Dikerjakan SESUDAH halaman terkirim, bukan sebagai syarat: tanpa
         * JavaScript seluruh kelompoknya tetap terlihat, dan label
         * <optgroup>-nya yang memberitahu mana milik layanan siapa.
         */
        var menuLayanan = document.getElementById('pdl-layanan');
        var menuAngkatan = document.getElementById('pdl-angkatan');

        if (!menuLayanan || !menuAngkatan) {
            return;
        }

        var kelompok = Array.prototype.slice.call(menuAngkatan.querySelectorAll('optgroup'));

        var sempitkan = function (bersihkanPilihan) {
            var layanan = menuLayanan.value;

            kelompok.forEach(function (g) {
                // disabled, bukan display:none — Safari mengabaikan `display`
                // pada <optgroup>, dan kelompok yang tetap terlihat di sana
                // membuat aturan ini berlaku di sebagian peramban saja.
                var cocok = !layanan || g.dataset.layanan === layanan;
                g.hidden = !cocok;
                g.disabled = !cocok;
            });

            /*
             * Pilihan yang jadi tidak berlaku DIBUANG, bukan dibiarkan.
             *
             * Angkatan Scopus Camp yang masih terpilih saat layanannya
             * ditukar ke Bibliometrik akan menyaring ke dua hal yang
             * bertentangan, dan hasilnya nol baris tanpa ada yang keliru di
             * layar. Tidak dijalankan saat halaman baru dibuka, supaya
             * saringan yang datang dari alamat halaman tidak ikut terhapus.
             */
            if (bersihkanPilihan) {
                var terpilih = menuAngkatan.options[menuAngkatan.selectedIndex];

                if (terpilih && terpilih.parentElement
                    && terpilih.parentElement.tagName === 'OPTGROUP'
                    && terpilih.parentElement.disabled) {
                    menuAngkatan.value = '';
                }
            }
        };

        menuLayanan.addEventListener('change', function () {
            sempitkan(true);
        });

        sempitkan(false);
    })();
</script>
@endpush
