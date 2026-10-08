@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Rincian Pendaftaran | MIS Rumah Scopus
@stop

@push('gaya')
    <style>
        /*
         * Dua kolom: kartu identitas di kiri (tetap), kartu bertab di kanan
         * (melar) — kerangka yang sama dengan layar Profil dan Data Pelanggan.
         *
         * align-items: start, BUKAN stretch. Dengan stretch, kartu identitas
         * yang isinya pendek ikut setinggi kolom tab dan sisanya jadi petak
         * putih DI DALAM kartunya; di layar Data Pelanggan pernah terukur
         * 341px kosong. Kartu yang berhenti di ujung isinya jauh lebih enak
         * dilihat daripada kartu yang dipaksa rata bawah.
         */
        .rin-kisi {
            display: grid;
            grid-template-columns: minmax(min(100%, 290px), 330px) minmax(0, 1fr);
            gap: var(--mis-jarak);
            align-items: start;
        }

        @media (max-width: 991.98px) {
            .rin-kisi {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        /* ------------------------------------------- kartu identitas */

        .rin-identitas {
            text-align: center;
        }

        /*
         * Medali besar: MODIFIER di atas .mis-medali, bukan kelas baru.
         *
         * Dengan begitu ia ikut mewarisi seluruh perbaikan yang sudah ada di
         * sana — latar dari `var(--warna)` yang disetel kelas warnanya,
         * `place-items: center` yang memusatkan glif tanpa bantalan
         * kira-kira, `font-size: inherit` pada glifnya yang mengalahkan
         * aturan global `.fas { font-size: 20px }`, dan `margin: 0 !important`
         * yang membatalkan margin 4px yang style.css pasang pada ikon di
         * dalam <a>. Menulis kelas sendiri berarti mengulang keempatnya, dan
         * melewatkan satu saja membuat ikonnya meleset dari titik tengah.
         */
        .mis-medali.rin-besar {
            width: 62px;
            height: 62px;
            flex: 0 0 62px;
            margin: 0 auto 13px;
            border-radius: 20px;
            font-size: 1.6rem;
        }

        .rin-nama {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--mis-tinta);
            overflow-wrap: anywhere;
        }

        .rin-nomor {
            display: grid;
            gap: 2px;
            margin: 6px 0 0;
            overflow-wrap: anywhere;
        }

        /* Keterangannya kecil dan samar, kodenya yang ditebalkan: yang dicari
           mata orang adalah kodenya, kata "Nomor pendaftaran" hanya perlu ada
           sekali supaya tahu itu kode apa. */
        .rin-nomor-label {
            font-size: .64rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--mis-tinta-3);
        }

        .rin-nomor-baris {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-width: 0;
        }

        .rin-nomor-kode {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .82rem;
            font-weight: 600;
            color: var(--mis-tinta);
            overflow-wrap: anywhere;
        }

        /* Tombol salin: sekecil ikonnya, menempel pada kodenya. */
        .rin-salin {
            display: grid;
            place-items: center;
            flex: 0 0 24px;
            width: 24px;
            height: 24px;
            padding: 0;
            border: 1px solid var(--mis-garis);
            border-radius: 8px;
            background: #fff;
            color: var(--mis-tinta-3);
            cursor: pointer;
            transition: all .2s ease;
        }

        .rin-salin i {
            font-size: 11px !important;
            margin: 0 !important;
        }

        .rin-salin:hover {
            border-color: #6366f1;
            background: #eef2ff;
            color: #4338ca;
        }

        .rin-pil-baris {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
            margin-top: 11px;
        }

        /*
         * Tindakan cepat. Membungkus ke bawah, bukan menyusut: tiga tombol
         * yang dipaksa muat di 258px (ruang yang tersedia di 320px) menyisakan
         * tulisan terpotong, dan tombol bertulisan terpotong tidak memberi
         * tahu apa pun.
         */
        .rin-cepat {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 7px;
            margin-top: 13px;
        }

        .rin-cepat-tombol {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px 7px 7px;
            border: 1px solid var(--mis-garis);
            border-radius: 999px;
            background: #fff;
            font-size: .76rem;
            font-weight: 700;
            color: var(--mis-tinta-2);
            cursor: pointer;
            transition: all .2s ease;
        }

        /* Ikonnya dipusatkan place-items, bukan line-height: glif WhatsApp dan
           glif amplop tingginya berbeda, dan line-height meleset pada salah
           satunya berapa pun nilainya. */
        .rin-cepat-ikon {
            display: grid;
            place-items: center;
            flex: 0 0 24px;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: var(--rin-muda);
            color: var(--rin-tua);
        }

        .rin-cepat-ikon i {
            font-size: 12px !important;
            margin: 0 !important;
        }

        .rin-cepat-tombol:hover {
            border-color: var(--rin-tua);
            background: var(--rin-muda);
            color: var(--rin-tua);
            transform: translateY(-1px);
        }

        /* Warna tautannya dipaksa: aturan global menyetel semua <a> jadi
           indigo, dan dua dari tiga tombol ini memang <a>. */
        a.rin-cepat-tombol,
        a.rin-cepat-tombol:hover {
            text-decoration: none;
        }

        /* Baris keterangan: medali mini + label + nilai. Kisi tiga kolom
           dengan medali merentang dua baris, jadi ia sejajar dengan BLOK
           teksnya — pola yang sama dipakai halaman Profil. */
        /*
         * Tiga lajur: medali, isi, tindakan.
         *
         * Lajur ketiga `auto` dan boleh kosong — baris yang tidak punya
         * tindakan (afiliasi, waktu daftar) tidak menyisakan lubang, sebab
         * jalur auto yang tak terisi lebarnya nol.
         *
         * Medali dan tombolnya merentang DUA baris supaya keduanya sejajar
         * dengan blok teksnya, bukan dengan labelnya saja.
         */
        .rin-baris {
            display: grid;
            grid-template-columns: 25px minmax(0, 1fr) auto;
            align-items: center;
            gap: 2px 10px;
            /* 13px, bukan 10px: terukur sebelumnya labelnya cuma 10px di
               bawah garis pembatas dan terbaca menempel padanya. */
            padding: 13px 0;
            text-align: left;
            border-top: 1px dashed var(--mis-garis);
        }

        /*
         * Label dan nilainya dibungkus SATU unsur.
         *
         * Tanpa pembungkus ini, kisi tiga lajur menempatkan keduanya
         * BERJAJAR, bukan bertumpuk: terukur pada baris Afiliasi, label
         * menempati lajur 215px dan nilainya lajur 24px di sebelahnya. Dengan
         * dua lajur hal itu tidak terjadi karena isian kedua jatuh ke baris
         * berikutnya; menambah lajur ketiga diam-diam mengubahnya.
         */
        /* Jaraknya diatur DI SINI, sesudah ruang mati warisan dibereskan:
           3px yang benar-benar 3px, bukan 2px yang terlihat 16px. */
        .rin-baris-isi {
            display: grid;
            gap: 3px;
            min-width: 0;
        }

        .rin-takpatah {
            white-space: nowrap;
        }

        /*
         * Tombol tindakan di ujung kanan barisnya.
         *
         * Berwarna keadaan yang diwakilinya — hijau WhatsApp, biru email —
         * memakai peubah yang sama dengan tombol status, jadi satu aturan
         * melayani keduanya dan warnanya tidak pernah berbeda sendiri.
         */
        .rin-aksi {
            display: grid;
            place-items: center;
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: var(--rin-muda);
            color: var(--rin-tua);
            transition: all .2s ease;
        }

        .rin-aksi i {
            font-size: 13px !important;
            margin: 0 !important;
        }

        .rin-aksi:hover {
            color: #fff;
            background: var(--rin-tua);
            transform: translateY(-1px);
        }

        a.rin-aksi,
        a.rin-aksi:hover {
            text-decoration: none;
        }

        /*
         * Jarak lencana keadaan ke garis pembatas pertama.
         *
         * Dulu ditulis `.rin-baris:first-of-type` dan aturan itu TIDAK PERNAH
         * cocok: `:first-of-type` menghitung tipe TAG, bukan kelas, dan <div>
         * pertama di kartu ini adalah `.rin-pil-baris`. Jadi tidak ada satu
         * pun `.rin-baris` yang pernah jadi "div pertama", margin-nya tidak
         * pernah berlaku, dan terukur jarak lencana ke garis pertamanya 0 —
         * garisnya menempel persis di bawah lencananya.
         *
         * Diamnya sempurna: CSS yang pemilihnya tidak cocok tidak
         * memberitahu siapa pun. Pemilih sekarang menyebut tetangganya
         * langsung, jadi ia tidak bergantung pada tag apa saja yang kebetulan
         * ada di atasnya.
         */
        .rin-pil-baris + .rin-baris {
            margin-top: 16px;
        }


        /*
         * line-height WAJIB disebut di sini.
         *
         * style.css mewariskan `line-height: 28px` MUTLAK — bukan rasio — ke
         * seluruh badan halaman. Pada label berhuruf 10,2px itu berarti
         * kotaknya 28px: sekitar 9px ruang kosong menggantung di bawah
         * hurufnya, dan 7px lagi di atas nilainya. Terukur, jarak antar
         * kotaknya 0 sementara jarak yang TERLIHAT antara "Email" dan
         * alamatnya ±16px — jadi mengatur `gap` tidak pernah menolong, sebab
         * ruang matinya ada DI DALAM kotak masing-masing.
         */
        .rin-baris-label {
            margin: 0;
            font-size: .64rem;
            line-height: 1.35;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .rin-baris-nilai {
            margin: 0;
            font-size: .84rem;
            /* Lihat catatan di .rin-baris-label: 28px warisan itu berlaku di
               sini juga. 1.45 cukup lapang untuk nilai yang membungkus dua
               baris tanpa menyisakan ruang mati saat isinya sebaris. */
            line-height: 1.45;
            color: var(--mis-tinta);
            overflow-wrap: anywhere;
        }

        .rin-baris-nilai a {
            color: var(--mis-tinta);
            text-decoration: none;
        }

        .rin-baris-nilai a:hover {
            color: var(--mis-ungu-tinta, #6366f1);
            text-decoration: underline;
        }

        .rin-samar {
            color: var(--mis-tinta-4);
        }

        .rin-bukti-gambar {
            display: block;
            width: 100%;
            max-height: 190px;
            object-fit: contain;
            margin-top: 9px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #f8fafc;
        }

        /* Borang daftar peserta, dipisahkan dari daftar bacanya oleh garis. */
        .rin-peserta-borang {
            display: grid;
            gap: 9px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px dashed var(--mis-garis);
        }

        .rin-peserta-kabar {
            margin: 0;
        }

        /* Kabar berhasil hijau, gagal merah — dipasang skripnya. */
        .rin-peserta-kabar.berhasil { color: #047857; }
        .rin-peserta-kabar.gagal { color: #be123c; }

        /*
         * Pengunggah bukti di kartu kiri.
         *
         * Dipisahkan dari baris di atasnya oleh garis, bukan cuma oleh jarak:
         * baris-baris di kartu ini semuanya BACAAN, dan satu-satunya tempat
         * yang bisa ditindak harus terbaca sebagai bagian yang lain.
         */
        .rin-bukti-borang {
            display: grid;
            gap: 9px;
            margin-top: 13px;
            padding-top: 13px;
            border-top: 1px dashed var(--mis-garis);
        }

        /* Tombolnya selebar kartunya: di kolom sesempit ini tombol yang
           mengikuti lebar tulisannya berdiri sendirian di kiri dan terbaca
           seperti tertinggal. */
        .rin-bukti-kirim {
            justify-content: center;
            width: 100%;
        }

        /*
         * Tab di halaman ini menyesuaikan lebarnya dengan tulisannya.
         *
         * Bawaan .mis-tab memberi tiap tab `flex: 1 1 0` — lebar sama rata.
         * Dengan lima tab di kolom kanan, bagian tiap tab tinggal seperlima,
         * dan tulisan tab terpanjang tidak muat di sana. Karena
         * `.mis-tab .nav-link` ber-`white-space: nowrap`, ia TIDAK membungkus
         * melainkan meluber keluar kotaknya sendiri: terukur 11px di 1280px
         * dan 10px di 1024px, menumpuk ke tab sebelahnya, dan tidak ada galat
         * apa pun yang menunjukkannya.
         *
         * `flex: 0 1 auto` membuat tiap tab selebar isinya dan barisnya
         * membungkus kalau memang tidak cukup — bukan terklip. Pemilihnya
         * lewat id supaya layar lain tetap memakai bawaannya.
         *
         * Di bawah 768px aturan bersamanya mengubah strip jadi penggulung
         * satu baris berpenanda tepi; timpaan ini tidak dipakai di sana.
         */
        @media (min-width: 768px) {
            /*
             * KISI berkolom sama rata, bukan deret lentur.
             *
             * Dengan `flex: 1 1 auto` + `max-width: 200px`, lebar tiap tab
             * dibatasi dan sisanya jadi ruang kosong. Terukur sebelum
             * diperbaiki:
             *
             *     3 tab @1440px   sisa kanan 164px
             *     6 tab @1440px   baris ke-2 berisi SATU tab, sisa 576px
             *     6 tab @820px    baris ke-2 berisi SATU tab, sisa 552px
             *
             * Batas 200px itu sendiri dipasang untuk menahan gejala yang lain:
             * saat barisnya membungkus, tab terakhir sendirian melar membagi
             * seluruh lebar baris itu. Kisi menghapus KEDUANYA sekaligus —
             * tidak ada yang membungkus, jadi tidak ada baris yatim, dan
             * kolomnya selalu habis dibagi rata.
             *
             * Yang ditukar: saat barisnya membungkus, tab di baris terakhir
             * ikut melebar membagi lebar baris itu — "Hapus" sendirian bisa
             * jadi selebar separuh kartunya. Itu diterima: tab yang lebar
             * masih terbaca sebagai tab, sedangkan ruang kosong di sebelahnya
             * terbaca seperti ada yang belum selesai dimuat.
             */
            #rin-tab > li {
                /*
                 * Tumbuh dari lebar ISINYA, tanpa batas atas.
                 *
                 * `flex: 1 1 auto` membuat tiap tab berdasar lebar tulisannya
                 * lalu membagi sisa ruang baris itu rata — jadi TIAP BARIS
                 * habis terpakai, termasuk baris terakhir. Itu yang tidak bisa
                 * dilakukan kisi: `repeat(auto-fit, minmax(...))` mengisi
                 * penuh semua baris KECUALI yang terakhir, dan sisa tabnya
                 * duduk di kolom selebar yang lain sambil meninggalkan kolom
                 * kosong di sebelahnya.
                 *
                 * Dan karena dasarnya lebar isi, tabnya hanya pernah TUMBUH —
                 * tulisannya tidak pernah meluber. Kisi berkolom sama rata
                 * sempat dicoba dan justru meluber: 6 tab di kolom kanan
                 * selebar 445px berarti 74px per tab, dan "Data pendaftaran"
                 * tidak muat di situ berapa pun pembungkusannya.
                 */
                flex: 1 1 auto;
                min-width: 0;
                max-width: none;
            }

            /*
             * Tulisan tab MEMBUNGKUS, tidak meluber.
             *
             * .mis-tab menyetel white-space: nowrap, dan itu benar untuk deret
             * yang tabnya selebar isinya. Di kisi, lebarnya dibagi rata
             * sebanyak tabnya — yang terpanjang tidak selalu kebagian cukup,
             * dan dengan nowrap ia meluber keluar kotaknya tanpa galat, tanpa
             * penggulung, dan tanpa tanda apa pun bahwa ada tulisan yang tidak
             * terbaca.
             *
             * Dibiarkan membungkus, stripnya bertambah tinggi di lebar-lebar
             * sempit dan tidak ada satu huruf pun yang hilang.
             */
            #rin-tab .nav-link {
                /* Tetap sebaris: lebarnya memang mengikuti tulisannya, jadi
                   tidak ada yang perlu dibungkus. */
                white-space: nowrap;
            }

            /*
             * Bantalan samping 13px, bukan 15px — DITANYAKAN DULU, 8 Okt 2026.
             *
             * Keenam tab layanan berangkatan kurang 10px untuk muat sebaris di
             * 1440px: lebar alaminya 752px ditambah lima jeda 6px jadi 782px,
             * sementara ruang yang ada 772px. Karena sepuluh piksel itu,
             * "Hapus" pecah ke baris kedua sendirian.
             *
             * Ini MEMBALIK keputusan 4 Okt 2026 di mis-ui.css, yang menuntut
             * jaraknya sama persis dengan halaman Profil dan menutup dengan
             * "JANGAN menimpanya lagi tanpa menanyakan dulu". Ditanyakan, dan
             * pemiliknya memilih deret sebaris — keseragaman jarak dengan
             * Profil yang dikorbankan, sadar dan disengaja.
             *
             * `!important` WAJIB: style.css memasang
             * `.nav-pills .nav-item .nav-link { padding-left: 15px !important }`,
             * dan !important menang atas bobot pemilih apa pun. Timpaan tanpa
             * itu pernah dicoba di sini dan diukur TIDAK berlaku sama sekali —
             * bantalannya tetap 15px, dan yang tersisa cuma aturan mati yang
             * terbaca seolah bekerja.
             *
             * 13px, bukan 12px: 2px per sisi × 12 sisi menghemat 24px, jadi
             * 758px dengan sisa 14px. Cukup longgar untuk tulisan tab yang
             * sedikit lebih panjang nanti, dan sedekat mungkin dengan 15px
             * supaya bedanya dengan Profil tidak terbaca mencolok.
             *
             * Hanya #rin-tab yang disentuh; deret tab layar lain tetap 15px.
             */
            #rin-tab .nav-link {
                padding-left: 13px !important;
                padding-right: 13px !important;
            }

            #rin-tab .rin-tab-teks {
                min-width: 0;
            }
        }

        /* ------------------------------------------------ isi tab */

        /*
         * Tiga angka ringkas memakai ubin BERSAMA .mis-ubin.
         *
         * Dijaga RupaBersamaTest: perangkat ubin ringkasan lahir di layar Data
         * Pelanggan, dan menyalinnya dengan awalan layar sendiri berarti tiap
         * perbaikan rupa dikerjakan dua kali. Versi pertama bagian ini memang
         * begitu, dan penjaganya menangkapnya.
         *
         * Yang TIDAK dipakai pembungkus .mis-ringkas-nya: di bawah 1100px ia
         * berubah jadi penggulung mendatar, dan guliran mendatar di dalam tab
         * tanpa penanda tepi adalah persis yang dilarang panduan. Kisi di sini
         * membungkus ke bawah.
         *
         * minmax(min(100%, 155px), 1fr): lebar tetap pada argumen pertama
         * adalah lantai yang tidak bisa ditembus, dan di 320px ruang yang
         * tersedia cuma sekitar 258px.
         */
        .rin-angka {
            display: grid;
            /* auto-fit DIBATASI tiga kolom lewat lebar maksimum jalurnya:
               tanpa itu, di 1440px kisinya membuka jalur keempat yang kosong
               dan ketiga ubinnya menyusut tanpa alasan. */
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 155px), 1fr));
            gap: 11px;
            margin-bottom: var(--mis-jarak);
        }

        /*
         * Ubin DI DALAM kartu diberi latar lembut, bukan putih.
         *
         * .mis-ubin berlatar putih karena ia dirancang berdiri di atas latar
         * lembut badan tab. Sejak ia dibungkus kartu putih, putih di atas
         * putih tidak terbaca sebagai ubin lagi — yang memisahkannya tinggal
         * garis tepinya saja.
         *
         * Dibalik di sini, DI DALAM .rin-bagian saja: ubin di layar daftar
         * dan di luar kartu tetap memakai putih bawaannya.
         */
        .rin-bagian .mis-ubin {
            background: #f8fafc;
        }

        /*
         * Keterangan kecil di bawah label ubin.
         *
         * Dinamai mengikuti INDUKNYA `.rin-angka`, bukan `.mis-ubin` — nama
         * seperti `rin-ubin-*` terbaca sebagai salinan ubin bersama, dan
         * RupaBersamaTest memang menolaknya. Kelas ini bukan salinan: ia
         * baris tambahan DI DALAM ubin bersama, yang rupanya tetap milik
         * mis-ui.css.
         *
         * line-height disebut sendiri: style.css mewariskan 28px MUTLAK ke
         * tiap <p>, dan pada huruf 11px itu menyisakan ruang mati yang
         * membuat ubinnya tampak renggang. Lihat catatan di .rin-baris-label.
         */
        .rin-angka-ket {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 3px 0 0;
            font-size: .69rem;
            line-height: 1.35;
            font-weight: 600;
            color: var(--mis-tinta-4);
        }

        .rin-angka-ket i {
            flex: 0 0 auto;
            font-size: 10px !important;
            margin: 0 !important;
        }

        /* Kuning, bukan abu: nama yang belum lengkap adalah pekerjaan yang
           belum selesai, bukan sekadar keterangan. */
        .rin-angka-ket-kurang {
            color: #b45309;
        }

        /* Dipatok tiga kolom sejak 576px, bukan 992px: di 820px kolom kanan
           sudah selebar ~758px, dan auto-fit di sana membuka jalur KEEMPAT
           yang kosong sehingga ketiga ubinnya menyusut tanpa alasan. */
        @media (min-width: 576px) {
            .rin-angka {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        /* Kisi isian: tiga kolom di layar lebar, dua di tablet, satu di
           ponsel — tanpa titik putus yang harus dijaga satu per satu. */
        .rin-isian-kisi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 215px), 1fr));
            gap: 14px;
            align-content: start;
        }

        .rin-isian-penuh {
            grid-column: 1 / -1;
        }

        /*
         * Label isian berikon, menyamai baris di kartu identitas kiri.
         *
         * Medalinya 20px, bukan 25px seperti di kartu kiri: label isian
         * hurufnya lebih kecil, dan medali seukuran kartu kiri membuat
         * barisnya lebih tinggi daripada tulisannya sendiri.
         */
        /*
         * Kotak nominal dengan awalan Rp di dalamnya.
         *
         * Awalannya diletakkan MUTLAK di atas kotak lalu kotaknya diberi
         * bantalan kiri — bukan dua unsur berdampingan: dengan berdampingan,
         * garis tepi dan keadaan fokus kotaknya terputus jadi dua kotak yang
         * terlihat terpisah.
         */
        .rin-uang-kotak {
            position: relative;
            display: block;
        }

        /*
         * Pengunggah berkas bergaya Profil.
         *
         * Isian aslinya disembunyikan dengan 1x1 TRANSPARAN, bukan
         * display:none: unsur yang tidak dirender tidak bisa menerima fokus
         * papan ketik, jadi pemakai yang menelusuri dengan Tab akan melewati
         * pengunggahnya begitu saja. Nilainya disalin dari .prof-berkas di
         * halaman Profil supaya keduanya tidak punya dua pengertian.
         */
        .rin-berkas {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .rin-unggah {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            column-gap: 12px;
            align-items: center;
            margin: 0;
            padding: 9px 12px;
            border: 1.5px dashed #c7d2fe;
            border-radius: 13px;
            background: #f8faff;
            cursor: pointer;
            text-align: left;
            transition: border-color .25s ease, background .25s ease;
        }

        .rin-unggah:hover,
        .rin-berkas:focus-visible + .rin-unggah {
            border-color: #6366f1;
            background: #eef2ff;
        }

        .rin-unggah-teks {
            display: grid;
            gap: 1px;
            min-width: 0;
        }

        /* Nama berkas yang panjang dipotong, BUKAN dibiarkan melebarkan
           kotaknya: kotak ini sebaris dengan isian lain di kisinya. */
        .rin-unggah-nama {
            font-size: .82rem;
            line-height: 1.35;
            font-weight: 700;
            color: var(--mis-tinta);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .rin-unggah .mis-bantuan {
            margin: 0;
        }

        .rin-uang-awalan {
            position: absolute;
            top: 50%;
            left: 13px;
            transform: translateY(-50%);
            font-size: .82rem;
            font-weight: 700;
            color: var(--mis-tinta-4);
            pointer-events: none;
        }

        /* Bantalan kirinya !important: aturan global .form-control memasang
           bantalan sendiri dan menang atas kelas biasa. */
        .rin-uang-isian {
            padding-left: 38px !important;
            font-variant-numeric: tabular-nums;
        }

        /* Kalimat di bawah Total bayar. Dipepetkan ke kotaknya (6px) supaya
           terbaca sebagai keterangan kotak itu, bukan sebagai isian baru. */
        .rin-hitung-nota {
            margin: 6px 0 0;
            line-height: 1.45;
        }

        /*
         * Panel Total bayar.
         *
         * Sengaja TIDAK berbentuk kotak isian. Kotak isian berjanji boleh
         * diketik, dan kotak isian yang ternyata tidak boleh diketik adalah
         * janji yang diingkari — panitia mengklik, tidak terjadi apa-apa, dan
         * tidak ada yang menjelaskan kenapa. Jadi bentuknya diganti: latar
         * bergradien, angkanya besar, dan gemboknya terlihat sebelum
         * disentuh.
         *
         * Tingginya dipatok 46px, sama dengan .form-control-modern di
         * sebelahnya — terukur; tanpa itu panel ini duduk lebih pendek dan
         * barisnya terlihat miring.
         */
        .rin-total {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 46px;
            padding: 0 44px 0 14px;
            border: 1.5px solid #bbf7d0;
            border-radius: 12px;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);
        }

        .rin-total-rp {
            flex: 0 0 auto;
            font-size: .82rem;
            font-weight: 700;
            color: #15803d;
        }

        /*
         * Isiannya dibuat tak terlihat sebagai isian: tanpa bingkai, tanpa
         * latar. Ia tetap <input> supaya <label for> tetap sah dan skrip
         * pratinjau bisa menulis .value seperti biasa.
         *
         * color dan -webkit-text-fill-color keduanya disebut: Safari memudarkan
         * teks medan disabled lewat text-fill-color, dan color saja tidak
         * menang atasnya — angkanya terbit kelabu pucat di sana padahal di
         * Chrome tampak pekat.
         */
        .rin-total-isian {
            flex: 1 1 auto;
            min-width: 0;
            width: 100%;
            border: 0;
            background: none;
            padding: 0;
            font-size: 1.02rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            letter-spacing: -.01em;
            color: #14532d;
            -webkit-text-fill-color: #14532d;
            opacity: 1;
        }

        .rin-total-gembok {
            position: absolute;
            top: 50%;
            right: 13px;
            transform: translateY(-50%);
            display: grid;
            place-items: center;
            width: 22px;
            height: 22px;
            border-radius: 7px;
            background: #dcfce7;
            color: #15803d;
            font-size: 10px;
        }

        /* Kedipan sekali jalan saat angkanya berganti sendiri.

           Tanpa penanda apa pun, satu-satunya yang bergerak di layar adalah
           angka di kotak yang TIDAK sedang disentuh — dan mata yang sedang di
           kotak potongan tidak akan menangkapnya. */
        .rin-hitung-berubah {
            animation: rin-hitung-kedip .45s ease-out 1;
        }

        @keyframes rin-hitung-kedip {
            0% { border-color: #22c55e; box-shadow: 0 0 0 4px rgba(34, 197, 94, .18); }
            100% { border-color: #bbf7d0; box-shadow: 0 0 0 4px rgba(34, 197, 94, 0); }
        }

        /* Yang tidak mau gerakan tetap mendapat angkanya, tanpa kedipan. */
        @media (prefers-reduced-motion: reduce) {
            .rin-hitung-berubah { animation: none; }
        }

        .rin-label {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .rin-label .mis-medali {
            flex: 0 0 20px;
            width: 20px;
            height: 20px;
            border-radius: 7px;
        }

        .rin-label .mis-medali i {
            font-size: 10px !important;
            margin: 0 !important;
        }

        /*
         * Jumlah lajurnya dipatok sesuai jumlah isian, lihat catatan di
         * markahnya. Hanya sejak 680px: di bawah itu auto-fit di atas yang
         * berlaku dan isiannya menumpuk satu per baris, seperti seharusnya di
         * ponsel.
         */
        @media (min-width: 680px) {
            .rin-isian-kolom-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rin-isian-kolom-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        /* ------------------------------------------- termin & DP */

        /* Ringkasan uang. Tiga baris, yang terakhir ditebalkan: pertanyaan
           yang dibawa orang ke panel ini selalu "kurang berapa". */
        .rin-uang {
            padding: 14px 16px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
        }

        .rin-uang-baris {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            font-size: .85rem;
            color: var(--mis-tinta-3);
        }

        .rin-uang-baris + .rin-uang-baris { margin-top: 7px; }

        .rin-uang-baris.akhir {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed var(--mis-garis);
            font-size: .95rem;
            color: var(--mis-tinta);
        }

        .rin-uang-baris strong { overflow-wrap: anywhere; }
        .rin-uang-baris .hijau { color: #15803d; }
        .rin-uang-baris .merah { color: #be123c; }

        /* Bilah terbayar. Seberapa jauh terbaca sekali lihat; "12 dari 30
           juta" butuh dibaca dua kali. */
        .rin-uang-bilah {
            margin-top: 12px;
            height: 7px;
            border-radius: 99px;
            background: #eef2ff;
            overflow: hidden;
        }

        .rin-uang-bilah span {
            display: block;
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #7c3aed 0%, #10b981 100%);
        }

        .rin-termin {
            display: grid;
            gap: 9px;
            margin: 15px 0 0;
            padding: 0;
            list-style: none;
        }

        .rin-termin li {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 11px;
            padding: 11px 13px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
        }

        .rin-termin-pil {
            flex: 0 0 auto;
            padding: 4px 10px;
            border-radius: 99px;
            background: #f1f5f9;
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--mis-tinta-3);
        }

        /* min-width: 0 supaya nominal panjang menyusut, bukan mendorong
           tombolnya keluar kartu. */
        .rin-termin-isi {
            flex: 1 1 170px;
            min-width: 0;
            font-size: .9rem;
            color: var(--mis-tinta);
        }

        .rin-termin-ket {
            display: block;
            margin-top: 2px;
            font-size: .74rem;
            font-weight: 500;
            color: var(--mis-tinta-4);
            overflow-wrap: anywhere;
        }

        .rin-termin-alat {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-left: auto;
        }

        .rin-termin-hapus { margin: 0; }

        .rin-termin-borang {
            margin-top: 19px;
            padding-top: 17px;
            border-top: 1px dashed var(--mis-garis);
        }

        /*
         * Tiap bagian berupa KARTU PUTIH sendiri, bukan blok yang dipisah
         * garis putus-putus.
         *
         * Badan tab ini memang berlatar lembut (#f4f6fb di .mis-tab-isi)
         * justru supaya isinya bisa jadi kartu putih — itu yang tertulis di
         * mis-ui.css dan itu yang dipakai halaman Profil lewat .prof-bagian.
         * Layar ini satu-satunya yang belum memakainya, jadi bagiannya
         * mengambang di atas latar tanpa batas yang jelas.
         *
         * Garis putus-putus menandai PEMISAH; kartu menandai SATUAN. Untuk
         * yang membaca sekilas, satuan jauh lebih mudah ditangkap: matanya
         * berhenti di tepi kartu tanpa harus mencari garisnya.
         *
         * Nilainya disamakan persis dengan .prof-bagian supaya kedua layar
         * tidak punya dua pengertian tentang "kartu di dalam tab".
         */
        .rin-bagian {
            padding: 15px 16px;
            border: 1px solid #e7ecf5;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        /*
         * Jarak antar kartu lewat pemilih SAUDARA UMUM (~), bukan dipasang di
         * setiap kartu lalu dibatalkan pada yang pertama.
         *
         * Versi `:first-child` TIDAK PERNAH mengenai kartu pertama di dalam
         * borang: anak pertama <form> adalah dua <input type=hidden> yang
         * dipasang direktif token CSRF dan direktif metode, jadi kartunya
         * bukan anak pertama. Margin 14px
         * itu lolos, lalu RUNTUH menembus <form> yang tidak berbantalan dan
         * mendorong seluruh isinya ke bawah. Terukur: jarak badan tab ke
         * kartu pertama 31px di tab Identitas melawan 17px di tab Ringkasan —
         * beda 14px, persis satu margin.
         *
         * Diamnya sempurna: tidak ada galat, dan aturannya TERLIHAT benar
         * saat dibaca. Sama persis dengan jebakan `:first-of-type` di kartu
         * identitas kiri — pemilih berdasar posisi dikalahkan saudara yang
         * tidak terlihat.
         *
         * `~` dipilih, bukan `+`: ia tetap benar walau ada nota atau apa pun
         * menyelip di antara dua kartu.
         */
        .rin-bagian ~ .rin-bagian {
            margin-top: 14px;
        }

        .rin-bagian-judul {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 0 0 13px;
        }

    /* Keterangan bagian: satu tingkat lebih pelan dari judulnya, tetapi tetap
       terbaca — ia menerangkan akibat, bukan hiasan. Dibatasi lebar baca
       supaya kalimat panjang tidak membentang selebar kartu. */
    .rin-bagian-nota {
        margin: -2px 0 12px;
        max-width: 68ch;
        font-size: .78rem;
        line-height: 1.55;
        color: #64748b;
    }

        .rin-bagian-judul span.teks {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--mis-tinta-3);
        }

        .rin-kaki {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 19px;
            padding-top: 16px;
            border-top: 1px solid var(--mis-garis);
        }

        .rin-status-label {
            margin: 0 0 9px;
        }

        /*
         * Deret tombol status.
         *
         * auto-fit dengan lantai 170px, BUKAN flex: enam status harus rata
         * lebarnya supaya tidak ada yang tampak lebih utama dari yang lain —
         * dan dengan flex, "Pendaftaran Dibatalkan" yang panjang akan dua kali
         * lebih lebar daripada "Diproses" tanpa alasan.
         *
         * Lantainya 170px karena tulisan terpanjang di kelima layanan
         * ("Pendaftaran Dibatalkan") menuntut sekitar itu; di bawahnya ia
         * membungkus jadi dua baris, dan tombol setinggi dua baris di antara
         * tombol sebaris membuat deretnya bergerigi.
         */
        .rin-status-pilih {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 170px), 1fr));
            gap: 9px;
        }

        /*
         * Jumlah lajurnya dipatok sesuai jumlah status, lihat catatan di
         * markahnya. Hanya sejak 680px: di bawah itu auto-fit di atas yang
         * berlaku, dan tombolnya menumpuk satu per baris seperti seharusnya
         * di ponsel.
         *
         * 680px, bukan 576px: tombol status menuntut sekitar 170px, jadi tiga
         * lajur baru masuk akal pada kolom selebar itu.
         */
        @media (min-width: 680px) {
            .rin-status-kolom-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rin-status-kolom-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        /*
         * Tiga lajur: ikon, blok teks, penanda amplop.
         *
         * Kisi, BUKAN flex. Dengan flex, anak yang ber-min-width:0 boleh
         * menyusut di bawah isinya dan HURUFNYA yang meluber keluar — kotaknya
         * sendiri tidak pernah bertumpang tindih sehingga kerusakannya luput
         * dari pengukuran kotak. `minmax(0, 1fr)` pada lajur tengah menahan
         * lebarnya di sisa ruang yang sebenarnya.
         */
        .rin-status-tombol {
            display: grid;
            /* Dua lajur: ikon dan blok teks. Lajur ketiga dulu memuat amplop
               di ujung kanan; penandanya sekarang bertingkat di dalam blok
               teks, jadi lajurnya tidak diperlukan lagi. */
            grid-template-columns: 30px minmax(0, 1fr);
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 11px 13px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            font-size: .82rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--mis-tinta-2);
            text-align: left;
            cursor: pointer;
            transition: all .2s ease;
        }

        /*
         * Ikonnya di dalam kotak berwarna keadaannya, dan dipusatkan oleh
         * place-items — bukan oleh bantalan kira-kira yang selalu meleset
         * beberapa piksel pada glif yang tingginya berbeda-beda.
         */
        .rin-status-ikon {
            display: grid;
            place-items: center;
            flex: 0 0 30px;
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: var(--rin-muda);
            color: var(--rin-tua);
        }

        .rin-status-ikon i {
            font-size: 13px !important;
            margin: 0 !important;
        }

        .rin-status-isi {
            display: grid;
            gap: 1px;
            min-width: 0;
        }

        .rin-status-teks {
            min-width: 0;
            /* Nama status yang panjang MEMBUNGKUS, tidak dipotong: "Pendaftaran
               dibatalkan" yang terpotong jadi "Pendaftaran dibat…" menuntut
               orangnya menebak sisanya. */
            overflow-wrap: break-word;
        }

        /*
         * Penanda "kirim email": bertingkat di bawah nama statusnya, sejajar
         * dengan penanda "sekarang", jadi keduanya tidak pernah berebut
         * tempat.
         */
        .rin-status-surat {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: .62rem;
            line-height: 1.35;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #b45309;
        }

        .rin-status-surat i {
            flex: 0 0 auto;
            font-size: 10px !important;
            margin: 0 !important;
        }

        .rin-status-kini {
            font-size: .62rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--rin-tua);
            opacity: .75;
        }

        .rin-status-tombol:not(:disabled):hover {
            border-color: var(--rin-tua);
            background: var(--rin-muda);
            color: var(--rin-tua);
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -12px var(--rin-tua);
        }

        .rin-status-tombol:not(:disabled):focus-visible {
            outline: 2px solid var(--rin-tua);
            outline-offset: 2px;
        }

        /*
         * Status yang sedang berlaku dimatikan, bukan disembunyikan.
         *
         * Menyembunyikannya membuat deretnya berubah panjang tiap kali
         * statusnya pindah, dan orang kehilangan patokan letak. Dimatikan,
         * ia tetap di tempatnya dan sekaligus menjawab "sekarang apa".
         *
         * cursor: default — bukan not-allowed: ini bukan larangan, melainkan
         * keadaan yang memang sudah tercapai.
         */
        .rin-status-tombol:disabled {
            border-color: var(--rin-tua);
            background: var(--rin-muda);
            color: var(--rin-tua);
            cursor: default;
        }

        /* Palet keadaan, disetel sebagai peubah supaya satu aturan tombol di
           atas melayani kelima warnanya. Nilainya menyamai lencana .mis-pil
           yang sudah dipakai di seluruh MIS. */
        .rin-warna-hijau { --rin-muda: #ecfdf5; --rin-tua: #047857; }
        .rin-warna-kuning { --rin-muda: #fffbeb; --rin-tua: #b45309; }
        .rin-warna-biru { --rin-muda: #eff6ff; --rin-tua: #1d4ed8; }
        .rin-warna-ungu { --rin-muda: #f5f3ff; --rin-tua: #6d28d9; }
        .rin-warna-merah { --rin-muda: #fff1f2; --rin-tua: #be123c; }
        .rin-warna-abu { --rin-muda: #f1f5f9; --rin-tua: #475569; }

        /* Kotak pemberitahuan. Kuning untuk yang perlu diketahui, merah untuk
           yang tidak bisa diurungkan — warnanya mengikuti keluarga yang sama
           dengan lencana status, bukan warna sendiri. */
        .rin-nota {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 13px 0 0;
            padding: 11px 14px;
            border: 1px solid #fde68a;
            border-radius: var(--mis-radius-kecil);
            background: #fffbeb;
            font-size: .79rem;
            line-height: 1.5;
            color: #92400e;
        }

        .rin-nota i {
            flex: 0 0 auto;
            margin-top: 2px;
            font-size: inherit;
        }

        .rin-nota-bahaya {
            border-color: #fecdd3;
            background: #fff1f2;
            color: #9f1239;
        }

        .rin-nota-biru {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        /*
         * Jejak & catatan: satu baris satu kejadian, berikon.
         *
         * Dulu seluruh catatannya dicetak sebagai satu paragraf. Kalau
         * isinya lebih dari satu kejadian, keduanya menyatu jadi satu blok
         * teks dan tidak ada yang menandai di mana satu berakhir.
         *
         * Garis penyambung di kiri dibuat dari ::before pada <li>, bukan
         * border pada daftarnya: dengan border, garisnya menerus sampai ke
         * bawah butir terakhir dan menggantung tanpa ujung.
         */
        .rin-jejak {
            display: grid;
            gap: 11px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .rin-jejak li {
            position: relative;
            display: grid;
            grid-template-columns: 25px minmax(0, 1fr);
            align-items: start;
            gap: 10px;
        }

        .rin-jejak li:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 12px;
            top: 27px;
            bottom: -11px;
            width: 1px;
            background: var(--mis-garis);
        }

        /* Waktunya di baris sendiri dan samar: yang dicari mata adalah APA
           yang berubah, bukan jamnya. */
        .rin-jejak-waktu {
            display: block;
            margin-top: 1px;
            font-size: .7rem;
            color: var(--mis-tinta-4);
        }

        .rin-jejak-teks {
            min-width: 0;
            font-size: .84rem;
            /* line-height disebut sendiri: lihat catatan di .rin-baris-label
               soal 28px mutlak yang diwariskan style.css. */
            line-height: 1.45;
            color: var(--mis-tinta);
            overflow-wrap: anywhere;
        }

        /* Daftar peserta rombongan. Bernomor, sebab urutannya berarti: itu
           yang dipakai menerbitkan sertifikat. */
        .rin-peserta {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            counter-reset: peserta;
        }

        .rin-peserta li {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 13px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            font-size: .85rem;
            color: var(--mis-tinta);
        }

        .rin-peserta li::before {
            counter-increment: peserta;
            content: counter(peserta);
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            border-radius: 9px;
            background: #f1f5f9;
            font-size: .72rem;
            font-weight: 800;
            color: var(--mis-tinta-3);
        }

        .rin-peserta-nama {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .rin-peserta-surel {
            display: block;
            font-size: .74rem;
            color: var(--mis-tinta-4);
        }

        /* Nomor peserta: hijau WhatsApp, sebab ia memang tautan ke sana. */
        .rin-peserta-telp {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 2px;
            font-size: .74rem;
            font-weight: 600;
            color: #15803d;
            text-decoration: none;
        }

        .rin-peserta-telp:hover {
            text-decoration: underline;
        }

        @media (max-width: 575.98px) {
            .mis-medali.rin-besar {
                width: 54px;
                height: 54px;
                flex: 0 0 54px;
                font-size: 1.4rem;
            }

            .rin-kaki .mis-tombol {
                flex: 1 1 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
    use App\Support\PesananPelanggan;

    $keadaan = Pendaftaran::keadaanDari($pendaftaran->status);
    $rupa = Pendaftaran::KEADAAN[$keadaan]
        ?? ['label' => 'Belum dikenali', 'warna' => 'abu', 'ikon' => 'fa-circle-notch'];

    $bukti = $baris ? Pendaftaran::buktiBaris($baris) : ['url' => null, 'ada' => false, 'nilai' => false];
    $sesiBaris = $baris ? Pendaftaran::sesiBaris($baris) : null;
    $nomorAngkatan = $baris ? Pendaftaran::nomorAngkatanBaris($baris) : null;
    $waktuBaris = $baris ? Pendaftaran::waktuBaris($baris) : null;

    $nomor = $pendaftaran->id_transaksi ?: ($pendaftaran->id_pemesanan ?: $pendaftaran->getKey());
    $namaOrang = $pendaftaran->nama ?: ($pendaftaran->nama_pemesan ?: 'Tanpa nama');
    $emailOrang = $pendaftaran->email ?: ($pendaftaran->email_pemesan ?: '');
    $telpOrang = $pendaftaran->telp ?: ($pendaftaran->telp_pemesan ?: '');
    $afiliasiOrang = $pendaftaran->affiliasi ?: ($pendaftaran->afiliasi_pemesan ?: '');
    $wa = PesananPelanggan::nomorWa($telpOrang);

    $jumlahOrang = max(1, (int) $pendaftaran->jumlah_pendaftar);
    $totalBayar = (int) ($pendaftaran->total_pembayaran ?: $pendaftaran->total_keseluruhan_pembayaran);
    $kodeUnik = (int) ($pendaftaran->kode_unik ?: $pendaftaran->kode_unik_pembayaran);

    /*
     * Dibaca lewat katalog, bukan dari kolomnya langsung: baris lama dari
     * sebelum kolom `cara_bayar` ada bernilai kosong, dan nilai tak dikenal
     * tetap harus punya label — kolomnya varchar, jadi jalur pendaftaran mana
     * pun bisa menulis nilai baru tanpa migrasi.
     */
    $caraBayar = Pendaftaran::caraBayar($pendaftaran->cara_bayar ?? null);

    /*
     * Peserta lain dalam pendaftaran rombongan. Kelima tabel pendaftaran
     * hanya menyimpan SATU nama, jadi tanpa ini daftar hadir rombongan tidak
     * bisa dibuat dari sistem.
     */
    // Nama kolomnya berbeda tiap tabel (nama vs nama_pemesan), jadi dibaca
    // dari katalog — bukan ditulis lima kali.
    $kolomNama = Pendaftaran::sumber($layanan)['kolom']['nama_orang'];
    $kolomEmail = Pendaftaran::sumber($layanan)['kolom']['email'];
    $kolomTelp = Pendaftaran::sumber($layanan)['kolom']['telp'];

    // Pesanan lembaganya, kalau pendaftaran ini bagian dari satu pesanan.
    $lembaga = \App\PemesananLembaga::untukPendaftaran($layanan, (string) $pendaftaran->getKey());

    $pesertaLain = \App\PendaftaranPeserta::milik($layanan, (string) $pendaftaran->getKey())
        ->terurut()
        ->get(['nama', 'email', 'telp']);

    $diskon = (int) ($pendaftaran->nominal_diskon ?: $pendaftaran->diskon);

    /*
     * Pembayaran bertermin — DP, cicilan, pelunasan.
     *
     * Siapa yang menanggung tagihannya diputuskan SATU aturan di modelnya,
     * bukan di sini: layar, penyimpan, dan ujinya harus sepakat, kalau tidak
     * panelnya muncul sementara penyimpannya menolak dan panitia melihat
     * tombol yang tidak bekerja tanpa penjelasan apa pun.
     */
    $kolomTotal = Pendaftaran::sumber($layanan)['kolom']['total'];
    $totalTagihan = (int) $pendaftaran->{$kolomTotal};

    $indukBayar = \App\PembayaranPendaftaran::indukUntuk(
        $layanan, (string) $pendaftaran->getKey(), $lembaga, $jumlahOrang, $totalTagihan
    );

    $ringkasBayar = $indukBayar === null ? null : \App\PembayaranPendaftaran::ringkas(
        $indukBayar['jenis'], $indukBayar['induk_id'], $indukBayar['tagihan']
    );

    $daftarBayar = $indukBayar === null ? collect() : \App\PembayaranPendaftaran::milik(
        $indukBayar['jenis'], $indukBayar['induk_id']
    )->terurut()->get();

    $berangkatan = Pendaftaran::berangkatan($layanan);
    /*
     * Tambahan kalimat peringatan hapus, dirakit di sini.
     *
     * Di markahnya, @if-nya harus menempel ke kata sebelumnya supaya tidak
     * muncul spasi sebelum koma — dan direktif yang didahului huruf TIDAK
     * dikompilasi Blade, jadi ia lolos ke halaman sebagai teks biasa.
     */
    $hapusTambahan = '';

    if ($berangkatan) {
        $hapusTambahan .= ', dan kursinya dikembalikan ke kuota angkatan';
    }

    if ($layanan === 'clinik_scopus') {
        $hapusTambahan .= ', beserta testimoni yang menempel padanya';
    }

    /*
     * Label dan pengelompokan medan — urusan tampilan, jadi di sini.
     *
     * Medan yang BOLEH disunting datang dari $medan, yaitu daftar putih di
     * UbahDataPendaftaran. Medan tanpa label di sini tidak pernah tergambar,
     * jadi menambah medan di sana menuntut menambah labelnya juga — itu
     * disengaja, supaya tidak ada isian tanpa nama yang terbaca orang.
     */
    $label = [
        'nama' => 'Nama lengkap', 'nama_pemesan' => 'Nama pemesan',
        'email' => 'Email', 'email_pemesan' => 'Email',
        'telp' => 'Nomor WhatsApp', 'telp_pemesan' => 'Nomor WhatsApp',
        'affiliasi' => 'Afiliasi / instansi', 'afiliasi_pemesan' => 'Afiliasi / instansi',
        'note' => 'Catatan panitia',
        'kendala' => 'Kendala', 'desc_kendala' => 'Keterangan kendala',

        'kategori_id' => 'Angkatan', 'jumlah_pendaftar' => 'Jumlah orang',
        'ppn' => 'PPN', 'kode_unik' => 'Kode unik',
        'kode_diskon' => 'Kode diskon', 'nominal_diskon' => 'Nominal potongan',
        'total_pembayaran' => 'Total bayar',
        'total_keseluruhan_pembayaran' => 'Total keseluruhan',

        'tanggal_pemesanan' => 'Tanggal pemesanan',
        'tanggal_reschedule' => 'Tanggal jadwal ulang',
        'group_wa' => 'Tautan grup WhatsApp',
        'sesi' => 'Sesi', 'jam_sesi' => 'Jam sesi',
        'waktu_mulai' => 'Mulai', 'waktu_selesai' => 'Selesai', 'lokasi' => 'Lokasi',
        'biaya' => 'Biaya', 'kode_unik_pembayaran' => 'Kode unik',
        'subtotal_pembayaran' => 'Subtotal',
        'sesi_kedua' => 'Sesi', 'waktu_mulai_kedua' => 'Mulai',
        'waktu_selesai_kedua' => 'Selesai', 'lokasi_kedua' => 'Lokasi',
        'biaya_kedua' => 'Biaya', 'kode_unik_pembayaran_kedua' => 'Kode unik',
        'subtotal_pembayaran_kedua' => 'Subtotal',
        'sesi_ketiga' => 'Sesi', 'waktu_mulai_ketiga' => 'Mulai',
        'waktu_selesai_ketiga' => 'Selesai', 'lokasi_ketiga' => 'Lokasi',
        'biaya_ketiga' => 'Biaya', 'kode_unik_pembayaran_ketiga' => 'Kode unik',
        'subtotal_pembayaran_ketiga' => 'Subtotal',
    ];

    $medanPenuh = ['group_wa', 'note', 'desc_kendala', 'lokasi', 'lokasi_kedua', 'lokasi_ketiga'];
    $medanUang = [
        'ppn', 'kode_unik', 'nominal_diskon', 'total_pembayaran',
        'biaya', 'kode_unik_pembayaran', 'subtotal_pembayaran',
        'biaya_kedua', 'kode_unik_pembayaran_kedua', 'subtotal_pembayaran_kedua',
        'biaya_ketiga', 'kode_unik_pembayaran_ketiga', 'subtotal_pembayaran_ketiga',
        'total_keseluruhan_pembayaran',
    ];

    /*
     * Isi tiap tab, disusun sebagai bagian berjudul.
     *
     * Dipisah jadi tab, bukan ditumpuk jadi satu borang panjang: Scopus Kafe
     * punya 26 isian, dan menyuguhkan semuanya sekaligus menuntut menggulung
     * jauh hanya untuk menemukan satu nominal. Satu tab satu borang, jadi
     * menyimpan satu kelompok tidak menuntut mengirim kelompok lainnya.
     */
    $isiTab = [
        'diri' => [
            ['Identitas pendaftar', 'fa-user', 'mis-ungu',
                ['nama', 'nama_pemesan', 'email', 'email_pemesan', 'telp', 'telp_pemesan', 'affiliasi', 'afiliasi_pemesan']],
            ['Kendala yang disampaikan', 'fa-comment-dots', 'mis-biru', ['kendala', 'desc_kendala']],
            ['Catatan panitia', 'fa-sticky-note', 'mis-kuning', ['note']],
        ],
        'bayar' => [
            /*
             * Nama tab tujuannya DIRAKIT, tidak ditulis mati.
             *
             * Kalimatnya dulu berbunyi "pindah ke tab Jadwal" — dan tab Jadwal
             * tidak ada lagi untuk Scopus Camp sejak isian tunggalnya dilebur.
             * Petunjuk yang menyebut tab yang tidak ada lebih buruk daripada
             * tidak ada petunjuk sama sekali: yang membacanya mencari, tidak
             * ketemu, lalu mengira isiannya memang dihapus.
             */
            ['Jumlah orang', 'fa-users', 'mis-ungu', ['jumlah_pendaftar'],
                'Mau memindahkan peserta ke angkatan lain? Isiannya ada di tab '
                . \App\Support\TabPendaftaran::namaTabUtama($layanan) . '.'],
            ['Nominal', 'fa-money-bill-wave', 'mis-hijau',
                ['ppn', 'kode_unik', 'kode_diskon', 'nominal_diskon', 'total_pembayaran', 'total_keseluruhan_pembayaran']],
        ],
        'sesi' => [
            /*
             * ANGKATAN ADA DI SINI, bukan lagi di tab Pembayaran.
             *
             * Memindahkan peserta ke angkatan berikutnya adalah tindakan
             * JADWAL, dan satu-satunya isian yang benar-benar melakukannya
             * adalah ini — kuotanya ikut berpindah: kursinya dikembalikan ke
             * angkatan lama, diambil dari angkatan baru, dan ditolak kalau
             * yang baru sudah penuh.
             *
             * Dulu ia duduk di tab Pembayaran sementara tab ini cuma berisi
             * "Tanggal jadwal ulang" — sebuah CATATAN yang tidak dibaca apa
             * pun. Admin yang mau memindahkan peserta membuka tab Jadwal,
             * mengisi tanggal, menekan Simpan, dan merasa selesai. Padahal
             * pesertanya masih di angkatan lama dan kursinya masih terpakai
             * di sana.
             */
            ['Angkatan', 'fa-layer-group', 'mis-ungu', ['kategori_id'],
                'Mengganti angkatan memindahkan peserta beserta kursinya: kursi di angkatan '
                . 'lama dikembalikan, kursi di angkatan baru diambil. Kalau angkatan barunya '
                . 'sudah penuh, perpindahannya ditolak.'],
            ['Sesi pertama', 'fa-calendar-check', 'mis-biru',
                ['tanggal_pemesanan', 'sesi', 'jam_sesi', 'waktu_mulai', 'waktu_selesai', 'lokasi', 'biaya', 'kode_unik_pembayaran', 'subtotal_pembayaran']],
            ['Sesi kedua', 'fa-calendar-plus', 'mis-jingga',
                ['sesi_kedua', 'waktu_mulai_kedua', 'waktu_selesai_kedua', 'lokasi_kedua', 'biaya_kedua', 'kode_unik_pembayaran_kedua', 'subtotal_pembayaran_kedua']],
            ['Sesi ketiga', 'fa-calendar-plus', 'mis-kuning',
                ['sesi_ketiga', 'waktu_mulai_ketiga', 'waktu_selesai_ketiga', 'lokasi_ketiga', 'biaya_ketiga', 'kode_unik_pembayaran_ketiga', 'subtotal_pembayaran_ketiga']],
            /*
             * "Penjadwalan ulang" DIBUANG. Isiannya tanggal_reschedule, dan
             * tidak ada satu pun yang membacanya — bahkan surat yang bernama
             * mail_reschedule pun tidak. Satu isian yang harus diisi panitia
             * tanpa akibat apa pun; yang memindahkan jadwal sesungguhnya
             * isian Angkatan di atas.
             *
             * Yang tersisa cuma tautan grup, dan itu pun hanya tergambar untuk
             * Bibliometrik — di sanalah suratnya membacanya dari baris
             * pendaftaran. Untuk Scopus Camp medannya sudah tidak ada di
             * daftar putih, jadi bagian ini tidak ikut tergambar sama sekali.
             */
            ['Grup WhatsApp peserta', 'fab fa-whatsapp', 'mis-hijau', ['group_wa'],
                'Tautan ini ikut terkirim di surat "pendaftaran diterima" peserta.'],
        ],
    ];

    /** Bagian yang benar-benar punya isi untuk layanan ini. */
    $bagianTab = function (string $tab) use ($isiTab, $medan, $label) {
        $hasil = [];

        foreach ($isiTab[$tab] ?? [] as $bagian) {
            [$judul, $ikon, $warna, $daftar] = $bagian;
            $keterangan = $bagian[4] ?? null;
            $ada = [];

            foreach ($daftar as $kolom) {
                if (isset($medan[$kolom], $label[$kolom])) {
                    $ada[] = $kolom;
                }
            }

            if ($ada !== []) {
                $hasil[] = [$judul, $ikon, $warna, $ada, $keterangan];
            }
        }

        return $hasil;
    };

    $bagianDiri = $bagianTab('diri');
    $bagianBayar = $bagianTab('bayar');
    $bagianSesi = $bagianTab('sesi');

    /*
     * Tab yang isinya kurang dari tiga isian DILEBUR ke tab identitas.
     *
     * Aturannya ada di App\Support\TabPendaftaran, bukan di sini: pengendali
     * memakai aturan yang sama untuk mengembalikan panitia ke tab yang baru ia
     * simpan, dan dua salinan pasti berselisih suatu hari.
     *
     * Yang dilebur ditaruh DI DEPAN bagian identitas, bukan di belakangnya.
     * Yang paling sering dicari di antaranya adalah Angkatan — memindahkan
     * peserta — dan menaruhnya di bawah "Catatan panitia" sama saja
     * menyembunyikannya, hanya dengan satu klik lebih sedikit.
     */
    $dilebur = \App\Support\TabPendaftaran::dilebur($layanan);

    $sumberLebur = ['bayar' => &$bagianBayar, 'sesi' => &$bagianSesi];

    foreach ($dilebur as $tabLebur) {
        $bagianDiri = array_merge($sumberLebur[$tabLebur], $bagianDiri);
        $sumberLebur[$tabLebur] = [];
    }

    unset($sumberLebur);

    $namaTabDiri = \App\Support\TabPendaftaran::namaTabUtama($layanan);

    /*
     * Tab peserta untuk SEMUA layanan, bukan webinar saja.
     *
     * Sejak panitia bisa mencatat nama rombongan untuk layanan mana pun,
     * membatasinya ke webinar berarti nama yang sudah tersimpan tidak bisa
     * dilihat di mana pun untuk empat layanan lainnya.
     */
    $adaPeserta = $jumlahOrang > 1 || $pesertaLain->isNotEmpty();

    /* Deret tab, hanya yang memang punya isi. */
    $tab = [['ringkasan', 'Ringkasan', 'fa-clipboard-check', 'mis-ikon-ungu']];

    if ($bagianDiri !== []) {
        $tab[] = ['diri', $namaTabDiri, 'fa-user-edit', 'mis-ikon-biru'];
    }

    if ($bagianBayar !== []) {
        $tab[] = ['bayar', 'Pembayaran', 'fa-wallet', 'mis-ikon-hijau'];
    }

    /*
     * Tab tersendiri, bukan diselipkan ke tab "Pembayaran" di atasnya. Tab itu
     * MENYUNTING medan milik barisnya sendiri dan isinya satu borang utuh;
     * borang bersarang bukan markah yang sah, dan termin memang milik
     * PESANAN, bukan milik satu baris.
     */
    if ($indukBayar !== null) {
        $tab[] = ['termin', 'Termin & DP', 'fa-receipt', 'mis-ikon-kuning'];
    }

    if ($bagianSesi !== []) {
        $tab[] = ['sesi', 'Jadwal', 'fa-calendar-alt', 'mis-ikon-jingga'];
    }

    if ($adaPeserta) {
        $tab[] = ['peserta', 'Peserta', 'fa-users', 'mis-ikon-ungu'];
    }

    /*
     * Tab Hapus SELALU ada, juga bagi yang tidak boleh menghapus.
     *
     * Layar tidak boleh menyuguhkan sesuatu yang kirimannya akan ditolak —
     * tetapi ia juga harus menulis ALASANNYA di tempat tombol itu seharusnya
     * berada. Tabnya dibuang sama sekali, karyawan yang mencari cara
     * membatalkan pendaftaran tidak menemukan apa pun dan tidak tahu harus
     * meminta ke siapa.
     */
    $tab[] = ['hapus', 'Hapus', 'fa-trash-alt', 'mis-ikon-merah'];

    $kunciTab = array_column($tab, 0);
    $tabSekarang = in_array($tabAktif, $kunciTab, true) ? $tabAktif : 'ringkasan';
@endphp
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali {{ $info['warna'] }}" aria-hidden="true">
                <i class="fas {{ $info['ikon'] }}"></i>
            </span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">{{ $namaOrang }}</h1>
                <p class="mis-sub">{{ $info['nama'] }} &middot; {{ $nomor }}</p>
            </div>
            <div class="mis-kepala-aksi">
                {{-- Slip untuk diberikan ke orangnya. Pendaftar yang datang
                     langsung dan membayar tunai sebelumnya pulang tanpa
                     pegangan apa pun: nomor dan kode uniknya hanya ada di
                     layar panitia dan di email — dan sebagian dari mereka
                     tidak punya email. --}}
                @if ($lembaga)
                    {{-- Pesanan lembaga biasanya terpecah ke beberapa angkatan
                         karena kuotanya 20 kursi; fakturnya yang menyatukannya
                         kembali. --}}
                    <a class="mis-tombol mis-tombol-halus" target="_blank" rel="noopener"
                        href="{{ route('account.pendaftaran-layanan.faktur', $lembaga->id) }}">
                        <i class="fas fa-file-invoice" aria-hidden="true"></i> Faktur {{ $lembaga->kode }}
                    </a>
                @endif
                <a class="mis-tombol mis-tombol-halus" target="_blank" rel="noopener"
                    href="{{ route('account.pendaftaran-layanan.slip', [$layanan, $pendaftaran->getKey()]) }}">
                    <i class="fas fa-print" aria-hidden="true"></i> Cetak slip
                </a>
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.pendaftaran-layanan.index', ['layanan' => $layanan]) }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar
                </a>
            </div>
        </div>

        @include('account.pendaftaran_layanan.partials.pesan')

        <div class="rin-kisi">

            {{-- --------------------------------- kartu identitas (kiri) --}}
            <section class="mis-kartu rin-identitas">
                <span class="mis-medali rin-besar {{ $info['warna'] }}" aria-hidden="true">
                    <i class="fas {{ $info['ikon'] }}"></i>
                </span>

                <h2 class="rin-nama">{{ $namaOrang }}</h2>
                {{-- Nomornya DISEBUT namanya, tidak berdiri sendiri.
                     Sebelumnya kode seperti "NMMCG" tercetak tanpa satu kata
                     pun yang menjelaskan ia apa — dan justru inilah yang
                     dicocokkan panitia dengan berita transfer atau dibacakan
                     lewat telepon. Kodenya sendiri tetap paling menonjol. --}}
                {{-- Tombol salinnya MENEMPEL pada nomornya, bukan berdiri
                     sendiri di deret tombol terpisah. Yang disalin orang
                     adalah kode yang sedang dilihatnya; tombol yang jauh dari
                     kodenya menuntut ia memastikan dulu kode mana yang
                     tersalin. --}}
                <p class="rin-nomor">
                    <span class="rin-nomor-label">Nomor pendaftaran</span>
                    <span class="rin-nomor-baris">
                        <span class="rin-nomor-kode">{{ $nomor }}</span>
                        <button type="button" class="rin-salin" data-rin-salin="{{ $nomor }}"
                            title="Salin nomor pendaftaran" aria-label="Salin nomor pendaftaran">
                            <i class="fas fa-copy" aria-hidden="true"></i>
                        </button>
                    </span>
                </p>

                <div class="rin-pil-baris">
                    <span class="mis-pil mis-pil-{{ $rupa['warna'] }}">
                        <i class="fas {{ $rupa['ikon'] }}" aria-hidden="true"></i> {{ $rupa['label'] }}
                    </span>
                    @if ($jumlahOrang > 1)
                        <span class="mis-pil mis-pil-ungu">
                            <i class="fas fa-users" aria-hidden="true"></i> {{ $jumlahOrang }} orang
                        </span>
                    @endif
                </div>

                {{-- Tiap baris keterangan diberi medali mini BERWARNA sesuai
                     kelompoknya, dan warnanya konsisten di seluruh MIS: biru
                     untuk email, hijau untuk WhatsApp, ungu untuk afiliasi,
                     jingga untuk sesi, kuning untuk waktu. Ikonnya dipusatkan
                     oleh place-items, bukan oleh bantalan kira-kira. --}}
                {{-- Tindakannya ADA DI DALAM barisnya, bukan di deret tombol
                     terpisah di atas. Sebelumnya WhatsApp dan Email muncul
                     dua kali — sekali sebagai tombol, sekali lagi sebagai
                     baris keterangan — dan dua tempat untuk satu hal membuat
                     orang menduga keduanya berbeda. --}}
                <div class="rin-baris">
                    <span class="mis-medali mini mis-biru" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                    <div class="rin-baris-isi">
                        <p class="rin-baris-label">Email</p>
                        <p class="rin-baris-nilai">
                            @if ($emailOrang)
                                {{ $emailOrang }}
                            @else
                                <span class="rin-samar">belum diisi</span>
                            @endif
                        </p>
                    </div>
                    @if ($emailOrang)
                        <a class="rin-aksi rin-warna-biru" href="mailto:{{ $emailOrang }}"
                            title="Kirim email ke {{ $emailOrang }}" aria-label="Kirim email">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini mis-hijau" aria-hidden="true"><i class="fab fa-whatsapp"></i></span>
                    <div class="rin-baris-isi">
                        <p class="rin-baris-label">WhatsApp</p>
                        <p class="rin-baris-nilai">
                            @if ($wa)
                                {{ $telpOrang }}
                            @elseif ($telpOrang)
                                {{-- Ditandai, bukan dijadikan tautan yang menuntun ke
                                     halaman galat WhatsApp: nomor di bawah sembilan
                                     angka bukan nomor telepon melainkan sisa isian. --}}
                                <span title="Nomor ini tidak bisa dipakai menghubungi lewat WhatsApp.">
                                    <i class="fas fa-phone-slash mis-ikon-kuning" aria-hidden="true"></i> {{ $telpOrang }}
                                </span>
                            @else
                                <span class="rin-samar">belum diisi</span>
                            @endif
                        </p>
                    </div>
                    @if ($wa)
                        <a class="rin-aksi rin-warna-hijau" href="https://wa.me/{{ $wa }}"
                            target="_blank" rel="noopener" title="Hubungi lewat WhatsApp"
                            aria-label="Hubungi lewat WhatsApp">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini mis-ungu" aria-hidden="true"><i class="fas fa-building"></i></span>
                    <div class="rin-baris-isi">
                        <p class="rin-baris-label">Afiliasi</p>
                        <p class="rin-baris-nilai">
                            {{ $afiliasiOrang ?: '' }}
                            @if (! $afiliasiOrang)
                                <span class="rin-samar">belum diisi</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini mis-jingga" aria-hidden="true"><i class="fas fa-calendar-alt"></i></span>
                    <div class="rin-baris-isi">
                        <p class="rin-baris-label">Sesi / angkatan</p>
                        <p class="rin-baris-nilai">
                            {{ $sesiBaris ?: '' }}
                            @if (! $sesiBaris)
                                <span class="rin-samar">tidak tercatat</span>
                            @endif
                            @if ($nomorAngkatan)
                                {{-- Nomor angkatannya disebut: nama tempat saja tidak
                                     menunjuk satu angkatan — Yogyakarta sudah ke-202
                                     sementara Jakarta baru ke-9. --}}
                                {{-- Tidak boleh terpatah di tengah: terukur, barisnya
                                 berakhir "... Yogyakarta &middot; angkatan" lalu
                                 "ke-199" sendirian di baris berikutnya, dan nomor
                                 angkatan yang terpisah dari katanya terbaca seperti
                                 keterangan lain. Dengan nowrap, yang berpindah baris
                                 seluruh frasanya. --}}
                            <span class="rin-samar rin-takpatah">&middot; angkatan ke-{{ $nomorAngkatan }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini mis-kuning" aria-hidden="true"><i class="fas fa-clock"></i></span>
                    <div class="rin-baris-isi">
                        <p class="rin-baris-label">Mendaftar</p>
                        <p class="rin-baris-nilai">
                            {{ $waktuBaris ? $waktuBaris->translatedFormat('d F Y, H:i') . ' WIB' : '' }}
                            @if (! $waktuBaris)
                                <span class="rin-samar">tidak tercatat</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini {{ $bukti['ada'] ? 'mis-hijau' : 'mis-merah' }}" aria-hidden="true">
                        <i class="fas fa-receipt"></i>
                    </span>
                    <div class="rin-baris-isi">
                        <p class="rin-baris-label">Bukti bayar</p>
                        <p class="rin-baris-nilai">
                            @if (! $bukti['nilai'])
                                {{-- Dulu Webinar Eksklusif berbunyi "tidak memakai
                                     unggahan bukti". Itu benar sampai pesertanya bisa
                                     mengunggah sendiri dari halaman status — sejak itu
                                     kalimatnya menyangkal sesuatu yang justru ada, dan
                                     panitia yang membacanya berhenti mencari. --}}
                                <span class="rin-samar">belum diunggah</span>
                            @elseif ($bukti['ada'])
                                <a href="{{ $bukti['url'] }}" target="_blank" rel="noopener">
                                    Buka ukuran penuh <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                                </a>
                            @else
                                {{-- Dibedakan dari "belum diunggah": terukur 72 dari 183
                                     nilai bukti menunjuk berkas yang sudah tidak ada di
                                     cakram, dan keduanya menuntut tindakan berbeda. --}}
                                <span style="color: #92400e;">berkasnya sudah tidak ada di server</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if ($bukti['ada'])
                    <img class="rin-bukti-gambar" src="{{ $bukti['url'] }}"
                        alt="Bukti bayar {{ $namaOrang }}" loading="lazy">
                @endif

                {{--
                    Panitia bisa mengunggahkan buktinya.

                    Satu-satunya jalur yang ada sebelum ini: peserta Webinar
                    Eksklusif mengunggah sendiri dari halaman statusnya. Empat
                    layanan lain tidak punya jalurnya sama sekali — buktinya
                    sampai lewat WhatsApp dan berhenti di ponsel panitia yang
                    kebetulan menerimanya.

                    Diletakkan di SINI, menempel pada baris "Bukti bayar" dan
                    gambarnya, bukan di tab borang sebelah kanan: yang
                    dikerjakan panitia adalah melihat buktinya kosong lalu
                    mengisinya, dan dua tempat berjauhan untuk satu hal yang
                    sama membuat yang kedua tidak pernah ditemukan.
                --}}
                <form class="rin-bukti-borang" method="POST" enctype="multipart/form-data"
                    action="{{ route('account.pendaftaran-layanan.bukti', [$layanan, $pendaftaran->getKey()]) }}">
                    @csrf

                    <input type="file" class="rin-berkas" id="rin-bukti-berkas" name="bukti"
                        accept="image/jpeg,image/png,image/webp,.heic,.heif"
                        data-mis-berkas="rin-bukti-nama">
                    <label for="rin-bukti-berkas" class="rin-unggah">
                        <span class="mis-medali kecil mis-ungu" aria-hidden="true">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </span>
                        <span class="rin-unggah-teks">
                            <span class="rin-unggah-nama" id="rin-bukti-nama">
                                {{ $bukti['nilai'] ? 'Ganti bukti bayarnya' : 'Unggahkan bukti bayarnya' }}
                            </span>
                            {{-- HEIC disebut: itu format bawaan kamera iPhone, dan bukti
                                 yang diteruskan peserta lewat WhatsApp sering sampai apa
                                 adanya. Panitia yang tidak melihatnya di daftar akan
                                 mengira berkasnya ditolak. --}}
                            <span class="mis-bantuan">JPG, PNG, WebP, atau HEIC — paling besar 8 MB</span>
                        </span>
                    </label>

                    @error('bukti')
                        <p class="mis-bantuan" style="color:#be123c">{{ $message }}</p>
                    @enderror

                    {{-- Tombolnya baru muncul sesudah ada berkas yang dipilih:
                         tombol kirim yang selalu ada pada borang yang masih kosong
                         mengundang ditekan, dan yang didapat cuma galat merah. --}}
                    <button type="submit" class="mis-tombol mis-tombol-ungu rin-bukti-kirim" hidden
                        data-mis-berkas-tombol="rin-bukti-berkas">
                        <i class="fas fa-save" aria-hidden="true"></i>
                        <span>{{ $bukti['nilai'] ? 'Ganti buktinya' : 'Simpan buktinya' }}</span>
                    </button>
                </form>
            </section>

            {{-- -------------------------------------- kartu bertab (kanan) --}}
            <div class="mis-kartu mis-tab-kartu">
                <div class="mis-tab-kepala">
                    <h2 class="mis-kartu-judul">Kelola pendaftaran</h2>
                    <p class="mis-kartu-sub">
                        Pindahkan status, betulkan data, atau pindahkan angkatannya.
                    </p>
                </div>

                {{-- role=tablist + aria-controls: isinya ditukar tanpa memuat
                     ulang halaman, jadi tanpa penanda ini pembaca layar tidak
                     mengumumkan apa pun saat tabnya berganti. --}}
                <ul class="mis-tab nav nav-pills" id="rin-tab" role="tablist">
                    @foreach ($tab as [$kunci, $judul, $ikon, $warnaIkon])
                        <li class="nav-item" role="presentation">
                            {{-- Tab penghapus diberi rupa bahaya: di antara enam tab
                                 yang sekadar berpindah tampilan, satu tab yang
                                 menghapus pendaftaran tampil persis sama. --}}
                            <a class="nav-link {{ $tabSekarang === $kunci ? 'active' : '' }} {{ $kunci === 'hapus' ? 'mis-tab-bahaya' : '' }}"
                                id="rin-tab-{{ $kunci }}" data-toggle="pill" href="#rin-panel-{{ $kunci }}"
                                role="tab" aria-controls="rin-panel-{{ $kunci }}"
                                aria-selected="{{ $tabSekarang === $kunci ? 'true' : 'false' }}">
                                <i class="fas {{ $ikon }} {{ $warnaIkon }}" aria-hidden="true"></i>
                                {{-- Dibungkus <span>, bukan dibiarkan jadi simpul teks
                                     telanjang: di dalam flex, teks telanjang jadi anonymous
                                     flex item yang min-width-nya auto dan TIDAK bisa disetel
                                     CSS mana pun — jadi ia menolak membungkus dan meluber
                                     keluar tabnya. Terukur 12px di 1024px. --}}
                                <span class="rin-tab-teks">{{ $judul }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="mis-tab-isi">

                    {{-- ======================================= ringkasan --}}
                    <div class="tab-pane fade {{ $tabSekarang === 'ringkasan' ? 'show active' : '' }}"
                        id="rin-panel-ringkasan" role="tabpanel" aria-labelledby="rin-tab-ringkasan" tabindex="0">

                        {{-- Tiga angka yang paling sering ditanyakan panitia saat
                             mencocokkan transfer, berdampingan supaya terbaca
                             sekali lihat. --}}
                        {{--
                            Tiap ubin membawa SATU baris keterangan di bawah
                            labelnya.

                            Tanpa itu ubinnya nyaris kosong: terukur, lebarnya
                            253px sementara isinya cuma 99px pada "Kode unik"
                            dan 120px pada "Jumlah orang" — lebih dari separuh
                            ubin jadi petak putih. Dan yang ditaruh di situ
                            bukan hiasan, melainkan jawaban atas pertanyaan
                            yang memang menyusul angkanya: "dibayar lewat
                            apa", "22 ini apa", "15 orangnya siapa saja".
                        --}}
                        {{--
                            Ubin dan notanya dibungkus SATU kartu, sama seperti
                            dua bagian di bawahnya.

                            Sebelum ini iramanya pecah: terukur, tiga blok
                            teratas mengambang tanpa kartu sementara dua di
                            bawahnya berkartu, dengan radius 0/14/14/15/15 dan
                            celah 16/13/14/14. Mata yang membaca sekilas tidak
                            menemukan satuan yang jelas — persis yang membuat
                            layar terasa "belum rapi" walau tiap bagiannya
                            sendiri sudah benar.

                            Sekarang panelnya tiga kartu setara: Pembayaran,
                            Status pembayaran, Jejak & catatan.
                        --}}
                        <div class="rin-bagian">
                            <p class="rin-bagian-judul">
                                <span class="mis-medali kecil mis-hijau" aria-hidden="true">
                                    <i class="fas fa-wallet"></i>
                                </span>
                                <span class="teks">Pembayaran</span>
                            </p>

                            <div class="rin-angka">
                                <div class="mis-ubin">
                                    <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
                                    <div style="min-width: 0;">
                                        <p class="mis-ubin-angka">Rp {{ number_format($totalBayar, 0, ',', '.') }}</p>
                                        <p class="mis-ubin-label">Total bayar</p>
                                        <p class="rin-angka-ket">
                                            <i class="fas {{ $caraBayar['ikon'] }}" aria-hidden="true"></i>
                                            {{ $caraBayar['ringkas'] ?? $caraBayar['label'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mis-ubin">
                                    <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-hashtag"></i></span>
                                    <div style="min-width: 0;">
                                        <p class="mis-ubin-angka">
                                            {{ $kodeUnik > 0 ? number_format($kodeUnik, 0, ',', '.') : '—' }}
                                        </p>
                                        <p class="mis-ubin-label">Kode unik</p>
                                        <p class="rin-angka-ket">
                                            @if ($kodeUnik > 0)
                                                <i class="fas fa-angle-double-right" aria-hidden="true"></i>
                                                angka terakhir nominalnya
                                            @else
                                                <i class="fas fa-minus" aria-hidden="true"></i>
                                                tidak memakai kode unik
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="mis-ubin">
                                    <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-users"></i></span>
                                    <div style="min-width: 0;">
                                        <p class="mis-ubin-angka">{{ $jumlahOrang }}</p>
                                        <p class="mis-ubin-label">Jumlah orang</p>
                                        @php
                                            // Pendaftarnya sendiri ikut dihitung: ia memang
                                            // salah satu peserta, dan panitia yang membaca
                                            // "2 nama" untuk rombongan bertiga akan mengira
                                            // ada satu yang hilang.
                                            $namaTercatat = $pesertaLain->count() + 1;
                                        @endphp
                                        {{-- Disebut "x dari y", bukan "x nama tercatat":
                                             selisihnya yang penting. Dibayar untuk 15
                                             orang tetapi baru 1 nama tercatat berarti 14
                                             sertifikat tidak bisa diterbitkan, dan itu
                                             biasanya baru ketahuan di hari acara. --}}
                                        <p class="rin-angka-ket {{ $namaTercatat >= $jumlahOrang ? '' : 'rin-angka-ket-kurang' }}">
                                            <i class="fas {{ $namaTercatat >= $jumlahOrang ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"
                                                aria-hidden="true"></i>
                                            @if ($namaTercatat >= $jumlahOrang)
                                                semua namanya tercatat
                                            @else
                                                baru {{ $namaTercatat }} dari {{ $jumlahOrang }} nama
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{--
                                SATU nota, bukan dua.

                                Keduanya sama-sama tentang pembayaran, dan sejak
                                ubinnya membawa keterangan sendiri ("Transfer",
                                "angka terakhir nominalnya") sebagian isinya jadi
                                pengulangan. Dua kotak biru bertumpuk juga berat
                                dilihat: terukur 105px untuk keterangan yang muat
                                dalam satu kotak.

                                Cara bayarnya tetap disebut LEBIH DULU di dalam
                                kalimatnya: petunjuk mencocokkan mutasi tidak
                                berlaku bagi yang membayar tunai di tempat, dan
                                panitia yang membacanya tanpa tahu cara bayarnya
                                akan mencari mutasi yang tidak akan pernah ada.
                            --}}
                            <p class="rin-nota rin-nota-biru">
                                <i class="fas {{ $caraBayar['ikon'] }}" aria-hidden="true"></i>
                                <span>
                                    Dibayar lewat <strong>{{ $caraBayar['label'] }}</strong>.
                                    {{ $caraBayar['ket'] }}
                                    @if ($kodeUnik > 0 && $caraBayar['kunci'] !== 'tunai')
                                        Cocokkan mutasinya dengan nominal
                                        <strong>Rp {{ number_format($totalBayar, 0, ',', '.') }}</strong> —
                                        angka {{ number_format($kodeUnik, 0, ',', '.') }} di ujungnya
                                        memang kode unik pendaftaran ini, bukan kelebihan bayar.
                                    @endif
                                </span>
                            </p>

                            @if ($diskon > 0)
                                <p class="rin-nota rin-nota-biru">
                                    <i class="fas fa-tag" aria-hidden="true"></i>
                                    <span>
                                        Dapat potongan <strong>Rp {{ number_format($diskon, 0, ',', '.') }}</strong>@if ($pendaftaran->kode_diskon) dengan kode <strong>{{ $pendaftaran->kode_diskon }}</strong>@endif.
                                    </span>
                                </p>
                            @endif

                        </div>{{-- kartu Pembayaran --}}

                        {{-- Borang status di tab Ringkasan, bukan tab sendiri:
                             inilah tindakan yang paling sering dikerjakan, dan
                             menaruhnya di balik satu tab lagi menambah satu
                             ketukan untuk pekerjaan sehari-hari. --}}
                        <div class="rin-bagian">
                            <p class="rin-bagian-judul">
                                <span class="mis-medali kecil mis-{{ $rupa['warna'] === 'abu' ? 'ungu' : $rupa['warna'] }}" aria-hidden="true">
                                    <i class="fas fa-exchange-alt"></i>
                                </span>
                                <span class="teks">Status pembayaran</span>
                                <span class="mis-pil mis-pil-{{ $rupa['warna'] }}" style="margin-left: auto;">
                                    <i class="fas {{ $rupa['ikon'] }}" aria-hidden="true"></i> {{ $rupa['label'] }}
                                </span>
                            </p>

                            {{--
                                Status dipindahkan dengan SEKALI TEKAN, bukan lewat
                                menu tarik lalu tombol Simpan.

                                Menu tarik menyembunyikan pilihannya: panitia harus
                                membukanya dulu untuk tahu ada status apa saja, lalu
                                menekan tombol kedua di sebelahnya. Dua ketukan, dan
                                yang pertama tidak memberi tahu apa pun. Di sini
                                keenam statusnya terlihat sekaligus, masing-masing
                                berwarna sesuai keadaannya — jadi "mana yang lunas"
                                terjawab tanpa dibaca.

                                Pengamannya PINDAH, tidak hilang: tombol Simpan dulu
                                menjadi jeda sebelum perubahan terkirim, sekarang
                                jedanya berupa penegasan SweetAlert2 yang menyebut
                                akibatnya — termasuk email yang tidak bisa ditarik
                                kembali. Penegasan yang menyebut akibat lebih
                                menolong daripada tombol yang hanya berbunyi
                                "Simpan".
                            --}}
                            <form method="POST" id="rin-borang-status"
                                action="{{ route('account.pendaftaran-layanan.status', [$layanan, $pendaftaran->getKey()]) }}">
                                @csrf
                                <input type="hidden" name="status" id="rin-status-nilai" value="{{ $pendaftaran->status }}">

                                <p class="mis-label rin-status-label">Pindahkan status</p>

                                @php
                                    /*
                                     * Lajurnya dihitung dari JUMLAH statusnya, bukan
                                     * dipatok.
                                     *
                                     * Dengan auto-fit, enam status di kolom selebar ini
                                     * jatuh 4 + 2 — dua petak kanan baris kedua kosong,
                                     * dan itu yang terlihat sebagai "bagian kosong".
                                     *
                                     * Kelima layanan punya jumlah status berbeda:
                                     * 6, 6, 4, 3, 4. Mengambil pembagi terbesar di
                                     * antara 3 dan 2 membuat semuanya genap — 6 jadi
                                     * 3+3, 4 jadi 2+2, 3 jadi satu baris penuh. Yang
                                     * tidak habis dibagi (misal 5) jatuh ke 3 dan
                                     * menyisakan satu petak, dan itu memang tidak bisa
                                     * dihindari.
                                     */
                                    $jumlahStatus = count($pilihanStatus);
                                    $kolomStatus = $jumlahStatus % 3 === 0 ? 3 : ($jumlahStatus % 2 === 0 ? 2 : 3);
                                @endphp
                                <div class="rin-status-pilih rin-status-kolom-{{ $kolomStatus }}"
                                    role="group" aria-label="Pindahkan status pendaftaran">
                                    @foreach ($pilihanStatus as $nilaiStatus => $tulisan)
                                        @php
                                            // Warna dan ikonnya dibaca dari katalog keadaan
                                            // yang sama dengan lencana di seluruh MIS, bukan
                                            // dipilih sendiri di sini: kalau "Pendaftaran
                                            // Diterima" hijau di daftar, ia harus hijau juga
                                            // di sini.
                                            $keadaanPilihan = Pendaftaran::keadaanDari($nilaiStatus);
                                            $rupaPilihan = Pendaftaran::KEADAAN[$keadaanPilihan]
                                                ?? ['warna' => 'abu', 'ikon' => 'fa-circle'];
                                            $iniSekarang = $pendaftaran->status === $nilaiStatus;
                                            $kirimSurat = Pendaftaran::suratUntuk($layanan, $nilaiStatus) !== null;
                                        @endphp
                                        <button type="button"
                                            class="rin-status-tombol rin-warna-{{ $rupaPilihan['warna'] }} {{ $iniSekarang ? 'sekarang' : '' }}"
                                            data-nilai="{{ $nilaiStatus }}"
                                            data-tulisan="{{ $tulisan }}"
                                            data-surat="{{ $kirimSurat ? '1' : '0' }}"
                                            @disabled($iniSekarang)
                                            aria-pressed="{{ $iniSekarang ? 'true' : 'false' }}">
                                            <span class="rin-status-ikon" aria-hidden="true">
                                                <i class="fas {{ $rupaPilihan['ikon'] }}"></i>
                                            </span>
                                            <span class="rin-status-isi">
                                                <span class="rin-status-teks">{{ $tulisan }}</span>
                                                @if (! $iniSekarang && $kirimSurat)
                                                    {{-- Ditulis DENGAN KATA, bukan amplop kecil
                                                         di ujung kanan.

                                                         Versi sebelumnya cuma glif 11px berwarna
                                                         abu dengan keterangan di atribut title —
                                                         dan title hanya muncul kalau kursor
                                                         ditahan di atasnya. Panitia yang hendak
                                                         memindahkan status tidak sedang
                                                         menunggui tooltip; ia menekan.

                                                         Emailnya tidak bisa ditarik kembali,
                                                         jadi tandanya harus terbaca SEBELUM
                                                         ditekan, bukan sesudah. Kuning, sewarna
                                                         penanda lain yang berarti "perhatikan
                                                         dulu". --}}
                                                    <span class="rin-status-surat">
                                                        <i class="fas fa-envelope" aria-hidden="true"></i>
                                                        kirim email
                                                    </span>
                                                @endif
                                                @if ($iniSekarang)
                                                    {{-- BERTINGKAT di bawah namanya, bukan di
                                                         sebelahnya. Sebaris, keduanya berebut
                                                         lebar yang sama dan nama status yang
                                                         pendek justru kalah: terukur huruf
                                                         "Diproses" meluber 24px keluar
                                                         kotaknya dan tercetak menimpa
                                                         lencananya. --}}
                                                    {{-- Satu kata, bukan "status sekarang":
                                                         sesudah bagiannya jadi kartu, bantalan
                                                         kartu memangkas lebar tombolnya dan dua
                                                         kata itu terpatah jadi dua baris —
                                                         terukur, barisnya jadi 68px sementara
                                                         baris tombol di bawahnya 58px. Di dalam
                                                         bagian berjudul "Pindahkan status",
                                                         satu kata ini sudah tidak ambigu. --}}
                                                    <span class="rin-status-kini">sekarang</span>
                                                @endif
                                            </span>

                                        </button>
                                    @endforeach
                                </div>

                                @if ($layanan === 'webinar_eksklusif')
                                    <p class="rin-nota">
                                        <i class="fas fa-chair" aria-hidden="true"></i>
                                        <span>
                                            Khusus layanan ini, <strong>Kedaluwarsa</strong> dan
                                            <strong>Dibatalkan</strong> mengembalikan kursinya ke kuota
                                            angkatan; mengembalikannya ke status aktif mengambil
                                            kursinya lagi.
                                        </span>
                                    </p>
                                @endif
                            </form>
                        </div>

                        {{--
                            Jejak sistem dan catatan panitia DIPISAH jadi dua
                            kartu.

                            Dulu keduanya satu: jejak perpindahan status
                            ditempelkan ke kolom `note` yang sama yang dipakai
                            panitia menulis catatannya sendiri. Panitia yang
                            menyunting catatannya bisa menghapus riwayat audit
                            tanpa sadar, dan kolomnya memanjang tanpa batas.
                            Lihat migrasi jejak_pendaftaran.
                        --}}
                        @if ($jejak->isNotEmpty())
                            <div class="rin-bagian">
                                <p class="rin-bagian-judul">
                                    <span class="mis-medali kecil mis-kuning" aria-hidden="true"><i class="fas fa-history"></i></span>
                                    <span class="teks">Jejak perubahan</span>
                                </p>

                                <ul class="rin-jejak">
                                    @foreach ($jejak as $satu)
                                        @php
                                            /*
                                             * Ikonnya mengikuti KEADAAN TUJUAN
                                             * perpindahannya, bukan tebakan kata kunci
                                             * seperti dulu: sekarang nilainya tersimpan
                                             * utuh di kolomnya sendiri, jadi tidak perlu
                                             * ditebak lagi.
                                             *
                                             * HANYA untuk jejak status. Kolom `ke` pada
                                             * jejak lain memuat nilai bebas — nama orang,
                                             * nominal, tautan grup — dan mewarnainya dengan
                                             * palet status membuat tiap suntingan tampak
                                             * seperti perpindahan status. Untuk nilai yang
                                             * KEBETULAN sama dengan salah satu status yang
                                             * dikenal ("pending", "expired", "completed"
                                             * — misalnya diketik panitia di kolom kendala)
                                             * warnanya bahkan salah arti: kuning jam pasir
                                             * untuk sesuatu yang bukan status.
                                             */
                                            $rupaJejak = null;

                                            if ($satu->aksi === 'status' && $satu->ke) {
                                                $keadaanJejak = Pendaftaran::keadaanDari($satu->ke);
                                                $rupaJejak = Pendaftaran::KEADAAN[$keadaanJejak] ?? null;
                                            }

                                            // Suntingan data: pensil, dan warnanya tenang
                                            // supaya perpindahan status tetap yang paling
                                            // menarik mata di daftar yang sama.
                                            $rupaAksi = [
                                                'ubah' => ['fa-pen', 'ungu'],
                                                'bayar' => ['fa-money-bill-wave', 'hijau'],
                                                'hapus-bayar' => ['fa-undo', 'merah'],
                                                'bukti' => ['fa-receipt', 'biru'],
                                                'ganti-bukti' => ['fa-receipt', 'kuning'],
                                                'peserta' => ['fa-users', 'ungu'],
                                            ];

                                            [$ikonJejak, $warnaJejak] = $rupaAksi[$satu->aksi]
                                                ?? ['fa-exchange-alt', 'biru'];
                                        @endphp
                                        <li>
                                            <span class="mis-medali mini mis-{{ $rupaJejak['warna'] ?? $warnaJejak }}" aria-hidden="true">
                                                <i class="fas {{ $rupaJejak['ikon'] ?? $ikonJejak }}"></i>
                                            </span>
                                            <span class="rin-jejak-teks">
                                                {{ $satu->kalimat }}
                                                <span class="rin-jejak-waktu">
                                                    {{ optional($satu->created_at)->translatedFormat('d M Y, H:i') }}
                                                </span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (trim((string) $pendaftaran->note) !== '')
                            <div class="rin-bagian">
                                <p class="rin-bagian-judul">
                                    <span class="mis-medali kecil mis-jingga" aria-hidden="true"><i class="fas fa-sticky-note"></i></span>
                                    <span class="teks">Catatan panitia</span>
                                </p>
                                <p class="rin-jejak-teks">{{ $pendaftaran->note }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- ============================== borang per kelompok --}}
                    @foreach ([['diri', $bagianDiri], ['bayar', $bagianBayar], ['sesi', $bagianSesi]] as [$kunciTabIsi, $bagian])
                        @if ($bagian !== [])
                            <div class="tab-pane fade {{ $tabSekarang === $kunciTabIsi ? 'show active' : '' }}"
                                id="rin-panel-{{ $kunciTabIsi }}" role="tabpanel"
                                aria-labelledby="rin-tab-{{ $kunciTabIsi }}" tabindex="0">

                                {{-- Satu borang per tab: menyimpan satu kelompok
                                     tidak menuntut mengirim kelompok lainnya, dan
                                     validatornya memakai 'sometimes' supaya medan
                                     di tab lain tidak dianggap dikosongkan. --}}
                                <form method="POST"
                                    action="{{ route('account.pendaftaran-layanan.ubah', [$layanan, $pendaftaran->getKey()]) }}">
                                    @csrf
                                    @method('PUT')

                                    @foreach ($bagian as [$judulBagian, $ikonBagian, $warnaBagian, $daftarMedan, $ketBagian])
                                        <div class="rin-bagian">
                                            <p class="rin-bagian-judul">
                                                <span class="mis-medali kecil {{ $warnaBagian }}" aria-hidden="true">
                                                    <i class="fas {{ $ikonBagian }}"></i>
                                                </span>
                                                <span class="teks">{{ $judulBagian }}</span>
                                            </p>

                                            {{-- Keterangan bagian: menyebut apa yang
                                                 BENAR-BENAR terjadi saat isiannya diubah.
                                                 Yang memakai layar ini bukan orang teknis,
                                                 dan judul bagian saja tidak membedakan
                                                 isian yang memindahkan kursi dari isian
                                                 yang cuma mencatat. --}}
                                            @if ($ketBagian)
                                                <p class="rin-bagian-nota">{{ $ketBagian }}</p>
                                            @endif

                                            @php
                                                /*
                                                 * Lajurnya dihitung dari jumlah isian
                                                 * yang TIDAK merentang penuh — aturan
                                                 * yang sama dengan deret tombol status.
                                                 *
                                                 * Dengan auto-fit, bagian Identitas yang
                                                 * berisi empat isian jatuh 3 + 1: dua
                                                 * petak kanan baris kedua kosong, dan
                                                 * itu yang terlihat sebagai petak
                                                 * menganga di sebelah "Afiliasi".
                                                 *
                                                 * Isian yang merentang penuh tidak ikut
                                                 * dihitung: ia memang memakai sebaris
                                                 * sendiri berapa pun lajurnya.
                                                 */
                                                $medanBiasa = count(array_diff($daftarMedan, $medanPenuh));
                                                $lajurIsian = $medanBiasa > 0 && $medanBiasa % 3 === 0
                                                    ? 3
                                                    : ($medanBiasa > 0 && $medanBiasa % 2 === 0 ? 2 : 3);

                                                /*
                                                 * Sisa petak di baris terakhir DIISI oleh
                                                 * isian terakhir, bukan dibiarkan menganga.
                                                 *
                                                 * Membagi lajur saja tidak cukup: bagian
                                                 * yang berisi 5 atau 1 isian biasa tidak
                                                 * habis dibagi 3 maupun 2, dan terukur
                                                 * menyisakan 1 dan 2 petak kosong. Isian
                                                 * terakhir dilebarkan sebanyak sisanya, jadi
                                                 * barisnya selalu penuh berapa pun jumlah
                                                 * medannya.
                                                 */
                                                $sisaPetak = $medanBiasa > 0 ? $medanBiasa % $lajurIsian : 0;
                                                $rentangTerakhir = $sisaPetak === 0 ? 1 : $lajurIsian - $sisaPetak + 1;
                                                $medanTerakhir = $medanBiasa > 0
                                                    ? array_values(array_diff($daftarMedan, $medanPenuh))[$medanBiasa - 1]
                                                    : null;
                                            @endphp
                                            <div class="rin-isian-kisi rin-isian-kolom-{{ $lajurIsian }}">
                                                @foreach ($daftarMedan as $kolom)
                                                    @include('account.pendaftaran_layanan.partials.isian', [
                                                        'kolom' => $kolom,
                                                        'jenis' => $medan[$kolom],
                                                        'tulisan' => $label[$kolom],
                                                        'nilai' => old($kolom, $pendaftaran->{$kolom}),
                                                        'penuh' => in_array($kolom, $medanPenuh, true),
                                                        'rentang' => $kolom === $medanTerakhir ? $rentangTerakhir : 1,
                                                        /*
                                                         * Label disembunyikan kalau bagiannya
                                                         * HANYA berisi isian ini DAN namanya sama
                                                         * dengan judul bagiannya.
                                                         *
                                                         * Terjadi pada "Catatan panitia":
                                                         * judul kartunya dan label isiannya
                                                         * berbunyi sama persis, berikut ikon
                                                         * kuning yang sama, bertumpuk langsung.
                                                         * Satu hal disebut dua kali membuat orang
                                                         * mencari bedanya — dan tidak ada.
                                                         *
                                                         * Syaratnya dua-duanya, bukan salah satu:
                                                         * bagian berisi banyak medan tetap butuh
                                                         * labelnya walau salah satunya senama.
                                                         */
                                                        'sembunyiLabel' => count($daftarMedan) === 1
                                                            && strcasecmp($label[$kolom] ?? '', $judulBagian) === 0,
                                                        'uang' => in_array($kolom, $medanUang, true),
                                                        'angkatan' => $angkatan,
                                                    ])
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="rin-kaki">
                                        <button type="submit" class="mis-tombol mis-tombol-ungu">
                                            <i class="fas fa-save" aria-hidden="true"></i> Simpan perubahan
                                        </button>
                                        <a class="mis-tombol mis-tombol-halus"
                                            href="{{ route('account.pendaftaran-layanan.rincian', [$layanan, $pendaftaran->getKey()]) }}">
                                            Batalkan
                                        </a>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endforeach

                    {{-- ========================================= termin --}}
                    @if ($indukBayar)
                        <div class="tab-pane fade {{ $tabSekarang === 'termin' ? 'show active' : '' }}"
                            id="rin-panel-termin" role="tabpanel" aria-labelledby="rin-tab-termin" tabindex="0">

                            @php
                                $sudahPersen = $ringkasBayar['tagihan'] > 0
                                    ? min(100, (int) round($ringkasBayar['terbayar'] * 100 / $ringkasBayar['tagihan']))
                                    : 0;
                                $milikLembaga = $indukBayar['jenis'] === \App\PembayaranPendaftaran::LEMBAGA;
                            @endphp

                            {{-- Yang ditanggung SIAPA disebut lebih dulu.

                                 Pesanan lembaga mengikat beberapa pendaftaran
                                 di beberapa angkatan, jadi angka di panel ini
                                 bukan tagihan baris yang sedang dibuka
                                 melainkan tagihan seluruh pesanannya — dan
                                 tanpa kalimat ini, panitia akan mengira
                                 Rp 30.000.000 itu tagihan satu orang. --}}
                            {{-- Ringkasan tagihan DIBUNGKUS kartu, sama seperti
                                 bagian lain di tab Ringkasan: sebelumnya kalimat
                                 pengantar, kotak angka, dan notanya mengambang
                                 bertiga di atas latar tanpa satuan yang jelas. --}}
                            <div class="rin-bagian">
                            <p class="rin-bagian-judul">
                                <span class="mis-medali kecil mis-hijau" aria-hidden="true">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </span>
                                <span class="teks">Ringkasan tagihan</span>
                            </p>

                            <p class="mis-kartu-sub" style="margin-bottom: 13px;">
                                @if ($milikLembaga)
                                    Angka di bawah untuk <strong>seluruh pesanan {{ $lembaga->kode }}</strong>
                                    ({{ $lembaga->nama_lembaga }}), bukan untuk pendaftaran ini saja.
                                @else
                                    Rombongan {{ $jumlahOrang }} orang yang dibayar atas satu nama,
                                    jadi tagihannya dihitung sekaligus.
                                @endif
                            </p>

                            <div class="rin-uang">
                                <div class="rin-uang-baris">
                                    <span>Tagihan</span>
                                    <strong>Rp {{ number_format($ringkasBayar['tagihan'], 0, ',', '.') }}</strong>
                                </div>
                                <div class="rin-uang-baris">
                                    <span>Sudah masuk</span>
                                    <strong class="hijau">Rp {{ number_format($ringkasBayar['terbayar'], 0, ',', '.') }}</strong>
                                </div>
                                <div class="rin-uang-baris akhir">
                                    <span>Sisa tagihan</span>
                                    <strong class="{{ $ringkasBayar['lunas'] ? 'hijau' : 'merah' }}">
                                        @if ($ringkasBayar['lunas'])
                                            Lunas
                                        @else
                                            Rp {{ number_format($ringkasBayar['sisa'], 0, ',', '.') }}
                                        @endif
                                    </strong>
                                </div>

                                {{-- Bilah, bukan hanya angka: "sudah masuk 12 dari
                                     30 juta" butuh dibaca dua kali, sedangkan
                                     seberapa jauh bilahnya terbaca sekali lihat. --}}
                                <div class="rin-uang-bilah" role="img"
                                    aria-label="Terbayar {{ $sudahPersen }} persen dari tagihan">
                                    <span style="width: {{ $sudahPersen }}%"></span>
                                </div>
                            </div>

                            @if ($ringkasBayar['lunas'])
                                {{-- Lunas di sini TIDAK memindahkan status
                                     pendaftarannya, dan itu disebut apa adanya.
                                     Memindahkannya sendiri berarti surat
                                     pemberitahuan ke pesertanya ikut terkirim
                                     tanpa diminta — untuk pesanan lembaga, puluhan
                                     sekaligus. --}}
                                <p class="rin-nota">
                                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                                    <span>
                                        Uangnya sudah penuh. Status pendaftarannya
                                        <strong>belum</strong> ikut berpindah — tandai Lunas dari tab
                                        Ringkasan supaya pemberitahuannya terkirim ke pesertanya.
                                    </span>
                                </p>
                            @endif

                            @if ($daftarBayar->isNotEmpty())
                                <ul class="rin-termin">
                                    @foreach ($daftarBayar as $bayar)
                                        <li>
                                            <span class="rin-termin-pil">
                                                {{ $bayar->sebutan($ringkasBayar['lunas'], $daftarBayar->count()) }}
                                            </span>
                                            <span class="rin-termin-isi">
                                                <strong>Rp {{ number_format((int) $bayar->nominal, 0, ',', '.') }}</strong>
                                                <span class="rin-termin-ket">
                                                    {{ $bayar->tanggal?->locale('id')->translatedFormat('j F Y') }}
                                                    &middot; {{ $bayar->cara_bayar_terbaca }}
                                                    @if ($bayar->dicatat_oleh)
                                                        &middot; dicatat {{ $bayar->dicatat_oleh }}
                                                    @endif
                                                    @if ($bayar->catatan)
                                                        <br>{{ $bayar->catatan }}
                                                    @endif
                                                </span>
                                            </span>

                                            <span class="rin-termin-alat">
                                                @if ($bayar->bukti)
                                                    <a class="mis-tombol mis-tombol-halus"
                                                        href="{{ asset(\App\Http\Controllers\account\PendaftaranLayananController::FOLDER_BUKTI_TERMIN . '/' . basename($bayar->bukti)) }}"
                                                        target="_blank" rel="noopener">
                                                        <i class="fas fa-image" aria-hidden="true"></i> Bukti
                                                    </a>
                                                @endif
                                                <a class="mis-tombol mis-tombol-halus"
                                                    href="{{ route('account.pendaftaran-layanan.kwitansi', $bayar->getKey()) }}"
                                                    target="_blank" rel="noopener">
                                                    <i class="fas fa-receipt" aria-hidden="true"></i> Kwitansi
                                                </a>
                                                @if ($bolehMenghapus)
                                                    <form method="POST" class="rin-termin-hapus"
                                                        action="{{ route('account.pendaftaran-layanan.pembayaran.hapus', $bayar->getKey()) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="mis-tombol mis-tombol-bahaya"
                                                            data-hapus-termin
                                                            data-nominal="Rp {{ number_format((int) $bayar->nominal, 0, ',', '.') }}">
                                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="rin-nota">
                                    <i class="fas fa-wallet" aria-hidden="true"></i>
                                    <span>Belum ada uang yang tercatat masuk untuk pesanan ini.</span>
                                </p>
                            @endif

                            </div>{{-- kartu Ringkasan tagihan --}}

                            <form method="POST" enctype="multipart/form-data" class="rin-termin-borang rin-bagian"
                                action="{{ route('account.pendaftaran-layanan.pembayaran', [$layanan, $pendaftaran->getKey()]) }}">
                                @csrf

                                <p class="rin-bagian-judul">
                                    <span class="mis-medali kecil mis-hijau" aria-hidden="true">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    <span class="teks">Catat uang masuk</span>
                                </p>

                                {{-- Dua lajur, bukan auto-fit: borang ini punya empat
                                     isian biasa (nominal, tanggal, cara bayar, bukti)
                                     dan satu yang merentang penuh. Dengan tiga lajur,
                                     keempatnya jatuh 3 + 1 dan dua petak kanan baris
                                     kedua menganga — aturan yang sama dipakai bagian
                                     berborang lain lewat $lajurIsian. --}}
                                <div class="rin-isian-kisi rin-isian-kolom-2">
                                    <div class="mis-isian">
                                        <label class="mis-label rin-label" for="rin-t-nominal">
                                            <span class="mis-medali mini mis-hijau" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
                                            <span>Nominal</span>
                                        </label>
                                        {{-- Awalan Rp dan pemisah ribuannya sama dengan
                                             kotak nominal di tab Pembayaran; peladen
                                             membuang karakter bukan angka sebelum
                                             menyimpan. --}}
                                        <span class="rin-uang-kotak">
                                            <span class="rin-uang-awalan" aria-hidden="true">Rp</span>
                                            <input type="text" class="form-control-modern rin-uang-isian @error('nominal') is-invalid @enderror"
                                                id="rin-t-nominal" name="nominal" inputmode="numeric" data-mis-rupiah
                                                value="{{ old('nominal') }}"
                                                placeholder="{{ number_format(max(1, $ringkasBayar['sisa']), 0, ',', '.') }}">
                                        </span>
                                        @error('nominal')
                                            <p class="mis-bantuan" style="color:#be123c">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mis-isian">
                                        <label class="mis-label rin-label" for="rin-t-tanggal">
                                            <span class="mis-medali mini mis-jingga" aria-hidden="true"><i class="fas fa-calendar-alt"></i></span>
                                            <span>Tanggal uang masuk</span>
                                        </label>
                                        <input type="date" class="form-control-modern @error('tanggal') is-invalid @enderror"
                                            id="rin-t-tanggal" name="tanggal" max="{{ now()->toDateString() }}"
                                            value="{{ old('tanggal', now()->toDateString()) }}">
                                        @error('tanggal')
                                            <p class="mis-bantuan" style="color:#be123c">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mis-isian">
                                        <label class="mis-label rin-label" for="rin-t-cara">
                                            <span class="mis-medali mini mis-biru" aria-hidden="true"><i class="fas fa-university"></i></span>
                                            <span>Cara bayar</span>
                                        </label>
                                        <select class="form-control-modern" id="rin-t-cara" name="cara_bayar">
                                            @foreach (Pendaftaran::caraBayarPilihan() as $kode => $cara)
                                                <option value="{{ $kode }}" @selected(old('cara_bayar', 'transfer') === $kode)>
                                                    {{ $cara['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mis-isian">
                                        <label class="mis-label rin-label" for="rin-t-bukti">
                                            <span class="mis-medali mini mis-ungu" aria-hidden="true"><i class="fas fa-receipt"></i></span>
                                            <span>Bukti transfer <span style="font-weight:500;text-transform:none;letter-spacing:0;">(boleh menyusul)</span></span>
                                        </label>
                                        {{-- Pola yang sama dengan pengunggah foto di halaman
                                             Profil: kotak bawaan peramban ("Choose file / No
                                             file chosen") tidak bisa diberi gaya, berbahasa
                                             Inggris, dan rupanya berbeda di tiap peramban —
                                             di antara isian lain yang seragam ia terbaca
                                             seperti unsur asing.

                                             Isian aslinya DISEMBUNYIKAN, bukan dibuang: ia
                                             tetap unsur borang yang mengirim berkasnya, dan
                                             labelnya yang jadi sasaran ketukan. --}}
                                        <input type="file" class="rin-berkas @error('bukti') is-invalid @enderror"
                                            id="rin-t-bukti" name="bukti" accept="image/jpeg,image/png,image/webp"
                                            data-mis-berkas="rin-t-bukti-nama">
                                        <label for="rin-t-bukti" class="rin-unggah">
                                            <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="rin-unggah-teks">
                                                <span class="rin-unggah-nama" id="rin-t-bukti-nama">Pilih berkas bukti</span>
                                                <span class="mis-bantuan">JPG, PNG, atau WebP</span>
                                            </span>
                                        </label>
                                        @error('bukti')
                                            <p class="mis-bantuan" style="color:#be123c">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mis-isian rin-isian-penuh">
                                        <label class="mis-label rin-label" for="rin-t-catatan">
                                            <span class="mis-medali mini mis-kuning" aria-hidden="true"><i class="fas fa-sticky-note"></i></span>
                                            <span>Catatan <span style="font-weight:500;text-transform:none;letter-spacing:0;">(boleh dikosongkan)</span></span>
                                        </label>
                                        <input type="text" class="form-control-modern" id="rin-t-catatan"
                                            name="catatan" maxlength="255" value="{{ old('catatan') }}"
                                            placeholder="mis. transfer BNI a.n. Bendahara">
                                    </div>
                                </div>

                                <div class="rin-kaki">
                                    <button type="submit" class="mis-tombol mis-tombol-ungu">
                                        <i class="fas fa-save" aria-hidden="true"></i> Catat pembayaran
                                    </button>
                                    @if ($milikLembaga)
                                        <a class="mis-tombol mis-tombol-halus"
                                            href="{{ route('account.pendaftaran-layanan.faktur', $lembaga->id) }}">
                                            <i class="fas fa-file-invoice" aria-hidden="true"></i>
                                            Faktur {{ $lembaga->kode }}
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- ======================================== peserta --}}
                    @if ($adaPeserta)
                        <div class="tab-pane fade {{ $tabSekarang === 'peserta' ? 'show active' : '' }}"
                            id="rin-panel-peserta" role="tabpanel" aria-labelledby="rin-tab-peserta" tabindex="0">

                            @php
                                // Dirakit di sini, bukan lewat semuaPeserta():
                                // metode itu hanya dipunyai model webinar,
                                // sedangkan tabel pesertanya kini dipakai
                                // kelima layanan.
                                $semuaPeserta = collect([[
                                    'nama' => (string) $pendaftaran->{$kolomNama},
                                    'email' => (string) ($pendaftaran->{$kolomEmail} ?? ''),
                                    'telp' => (string) ($pendaftaran->{$kolomTelp} ?? ''),
                                    'utama' => true,
                                ]])->concat($pesertaLain->map(fn ($p) => [
                                    'nama' => (string) $p->nama,
                                    'email' => (string) ($p->email ?? ''),
                                    'telp' => (string) ($p->telp ?? ''),
                                    'utama' => false,
                                ]))->all();

                                $terdaftar = count($semuaPeserta);
                            @endphp

                            <p class="mis-kartu-sub" style="margin-bottom: 13px;">
                                Nama di daftar ini yang dipakai menerbitkan sertifikat dan
                                memasukkan orang ke grup.
                            </p>

                            {{-- Dibawa dari layar pendaftar webinar yang dibuang:
                                 tanpa daftar ini, nama peserta kedua dan seterusnya
                                 tidak bisa dilihat di mana pun lagi. --}}
                            <ol class="rin-peserta">
                                @foreach ($semuaPeserta as $orangKe)
                                    <li>
                                        <span class="rin-peserta-nama">
                                            {{ $orangKe['nama'] ?: 'Tanpa nama' }}
                                            @if (! empty($orangKe['email']))
                                                <span class="rin-peserta-surel">{{ $orangKe['email'] }}</span>
                                            @endif
                                            {{-- Nomornya jadi TAUTAN WhatsApp, bukan teks
                                                 yang harus disalin: daftar ini dibuka justru
                                                 saat panitia hendak menghubungi orangnya satu
                                                 per satu. --}}
                                            @if (! empty($orangKe['telp']))
                                                <a class="rin-peserta-telp"
                                                    href="https://wa.me/{{ \App\Support\NomorTelepon::rapikan($orangKe['telp']) }}"
                                                    target="_blank" rel="noopener">
                                                    <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                                    {{ \App\Support\DaftarPeserta::bentukLokal(\App\Support\NomorTelepon::rapikan($orangKe['telp'])) }}
                                                </a>
                                            @endif
                                        </span>
                                        @if ($orangKe['utama'])
                                            <span class="mis-pil mis-pil-ungu" style="margin-left: auto;">pendaftar</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>

                            @if ($terdaftar !== $jumlahOrang)
                                {{-- Selisihnya disebut, bukan dibiarkan: pendaftaran
                                     yang dibayar untuk lima orang tetapi hanya memuat
                                     tiga nama berarti dua sertifikat tidak bisa
                                     diterbitkan, dan itu baru ketahuan di hari acara.

                                     Kalimat "Tanyakan sisanya ke pendaftarnya" DIBUANG:
                                     sejak borang di bawah ada, panitia tidak perlu
                                     menunggu siapa pun — ia bisa mengetiknya sendiri. --}}
                                <p class="rin-nota">
                                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                    <span>
                                        Dibayar untuk <strong>{{ $jumlahOrang }}</strong> orang, tetapi baru
                                        <strong>{{ $terdaftar }}</strong> nama yang tercatat.
                                        Lengkapi di bawah.
                                    </span>
                                </p>
                            @endif

                            {{--
                                Panitia mengisikan nama pesertanya sendiri.

                                Sebelum ini daftar di atas hanya bisa DIBACA, sementara
                                peringatan di atasnya menyuruh "tanyakan sisanya ke
                                pendaftarnya" — peringatan tanpa jalan keluar, di layar
                                orang yang justru hendak mengerjakannya. Terukur di
                                produksi 8 Okt 2026: enam pendaftaran rombongan, nol nama
                                peserta tercatat.

                                Satu kotak teks, bukan sederet isian, dan pembacanya
                                DaftarPeserta yang sama dengan borang Tambah: yang
                                mengisi panitia yang sedang menghadapi antrean, dan
                                menempelkan daftar dari WhatsApp atau Excel jauh lebih
                                cepat daripada mengetik ke sepuluh kotak.
                            --}}
                            <form class="rin-peserta-borang" method="POST"
                                action="{{ route('account.pendaftaran-layanan.peserta', [$layanan, $pendaftaran->getKey()]) }}">
                                @csrf
                                @method('PUT')

                                <label class="mis-label rin-label" for="rin-peserta-teks">
                                    <span class="mis-medali mini mis-ungu" aria-hidden="true">
                                        <i class="fas fa-users"></i>
                                    </span>
                                    <span>Nama &amp; nomor peserta selain pendaftarnya</span>
                                </label>

                                {{-- Jalur berkas DI ATAS kotak teksnya, seperti di borang
                                     Tambah: lembaga mengirim daftarnya sebagai lampiran
                                     Excel, dan panitia yang terlanjur melihat kotak kosong
                                     akan mulai mengetik sebelum sempat tahu ada jalan yang
                                     lebih cepat. --}}
                                <input type="file" class="rin-berkas" id="rin-peserta-berkas"
                                    accept=".xlsx,.xls,.csv,text/csv"
                                    data-mis-peserta-berkas="{{ route('account.pendaftaran-layanan.baca-peserta') }}">
                                <label for="rin-peserta-berkas" class="rin-unggah">
                                    <span class="mis-medali kecil mis-hijau" aria-hidden="true">
                                        <i class="fas fa-file-excel"></i>
                                    </span>
                                    <span class="rin-unggah-teks">
                                        <span class="rin-unggah-nama" id="rin-peserta-berkas-nama">
                                            Ambil dari Excel atau CSV
                                        </span>
                                        <span class="mis-bantuan">Nama dan nomornya terisi sendiri ke kotak di bawah</span>
                                    </span>
                                </label>

                                <p class="mis-bantuan rin-peserta-kabar" id="rin-peserta-kabar" hidden></p>

                                <textarea class="form-control-modern @error('peserta') is-invalid @enderror"
                                    id="rin-peserta-teks" name="peserta" rows="5"
                                    data-mis-tumbuh
                                    placeholder="Satu orang per baris — nama, lalu nomornya:&#10;Budi Santoso, 081234567890&#10;Siti Rahma, 085700011122">{{ old('peserta', \App\Support\DaftarPeserta::sebagaiTeks($pesertaLain->map(fn ($p) => ['nama' => (string) $p->nama, 'telp' => (string) ($p->telp ?? '')])->all())) }}</textarea>

                                @error('peserta')
                                    <p class="mis-bantuan" style="color:#be123c">{{ $message }}</p>
                                @enderror

                                {{-- Batasnya disebut DI MUKA. Peladen memang memotong
                                     daftarnya sebanyak kursi yang dibayar, tetapi memotong
                                     diam-diam berarti nama yang hilang baru ketahuan di
                                     hari acara. --}}
                                <p class="mis-bantuan">
                                    Pendaftarnya sudah terhitung satu, jadi di sini paling banyak
                                    <strong>{{ max(0, $jumlahOrang - 1) }}</strong> nama.
                                    Yang lebih dari itu tidak ikut tersimpan.
                                </p>

                                <div class="rin-kaki">
                                    <button type="submit" class="mis-tombol mis-tombol-ungu">
                                        <i class="fas fa-save" aria-hidden="true"></i> Simpan daftar peserta
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- ========================================== hapus --}}
                    <div class="tab-pane fade {{ $tabSekarang === 'hapus' ? 'show active' : '' }}"
                        id="rin-panel-hapus" role="tabpanel" aria-labelledby="rin-tab-hapus" tabindex="0">

                        @if ($bolehMenghapus)
                            <p class="rin-nota rin-nota-bahaya">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                <span>
                                    {{-- Dirakit di PHP, BUKAN dengan @if menempel ke
                                         kata sebelumnya.

                                         Blade tidak mengompilasi direktif yang
                                         didahului huruf: "bayarnya@if" lolos ke
                                         halaman sebagai teks biasa, dan karena
                                         yang pertama gagal, @endif dan @if
                                         sesudahnya ikut gagal berentet. Terukur
                                         di peramban: peringatan hapusnya terbaca
                                         "...bukti bayarnya@if ($berangkatan), dan
                                         kursinya...". Dijaga
                                         DirektifBladeTerkompilasiTest. --}}
                                    Barisnya hilang permanen beserta berkas bukti bayarnya{{ $hapusTambahan }}.
                                    Tidak bisa diurungkan, dan belum ada tong sampah.
                                </span>
                            </p>

                            <p class="rin-nota rin-nota-biru">
                                <i class="fas fa-undo" aria-hidden="true"></i>
                                <span>
                                    Kalau yang Anda maksud <strong>membatalkan</strong>, pindahkan
                                    statusnya di tab Ringkasan — datanya tetap bisa dilihat dan
                                    kursinya tetap terurus.
                                </span>
                            </p>

                            <form method="POST" id="rin-borang-hapus"
                                action="{{ route('account.pendaftaran-layanan.hapus', [$layanan, $pendaftaran->getKey()]) }}">
                                @csrf
                                @method('DELETE')
                                <div class="rin-kaki">
                                    <button type="button" class="mis-tombol mis-tombol-hapus" id="rin-tombol-hapus"
                                        data-nama="{{ $namaOrang }}" data-nomor="{{ $nomor }}">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus pendaftaran ini
                                    </button>
                                </div>
                            </form>
                        @else
                            {{-- Tombolnya tidak disuguhkan, DAN alasannya ditulis di
                                 tempat tombol itu seharusnya berada — beserta jalan
                                 keluar yang memang bisa ia pakai sendiri. --}}
                            <p class="rin-nota">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                                <span>
                                    Hanya administrator yang boleh menghapus pendaftaran.
                                    Penghapusannya tidak bisa diurungkan dan belum ada tong
                                    sampah, jadi haknya dipersempit.
                                </span>
                            </p>

                            <p class="rin-nota rin-nota-biru">
                                <i class="fas fa-undo" aria-hidden="true"></i>
                                <span>
                                    Kalau yang Anda maksud <strong>membatalkan</strong>, itu bisa
                                    Anda lakukan sendiri: pindahkan statusnya di tab
                                    <strong>Ringkasan</strong>.
                                </span>
                            </p>
                        @endif
                    </div>

                </div>{{-- mis-tab-isi --}}
            </div>
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        /*
         * Strip tab di ponsel: satu baris yang bisa digeser, DISERTAI penanda
         * tepi memudar. Tanpa tanda bahwa masih ada isi di samping, tab
         * terakhir praktis tidak ada.
         *
         * Polanya disalin dari layar Data Pelanggan, termasuk satu hal yang
         * mudah salah: ambang "sudah di paling kiri" dihitung dari BANTALAN
         * strip, bukan nol — tiap tab ber-scroll-snap-align: start, jadi
         * posisi diam paling kiri sama dengan bantalannya. Dibandingkan
         * dengan nol, penanda kiri menyala sejak halaman dibuka dan
         * menjanjikan isi yang sebenarnya tidak ada.
         */
        var strip = document.getElementById('rin-tab');

        if (!strip) {
            return;
        }

        var segarkan = function () {
            var bantalan = parseFloat(getComputedStyle(strip).paddingLeft) || 0;

            // 1px toleransi: lebar hasil hitungan peramban kerap berkoma.
            strip.classList.toggle('mis-tab-geser-kiri', strip.scrollLeft > bantalan + 1);
            strip.classList.toggle(
                'mis-tab-geser-kanan',
                strip.scrollLeft + strip.clientWidth < strip.scrollWidth - 1
            );
        };

        strip.addEventListener('scroll', segarkan, { passive: true });
        window.addEventListener('resize', segarkan);
        segarkan();

        var terlihatPenuh = function (el) {
            var a = strip.getBoundingClientRect();
            var b = el.getBoundingClientRect();

            return b.left >= a.left - 1 && b.right <= a.right + 1;
        };

        /*
         * Tab yang terbuka digeser ke dalam pandangan — penting saat halaman
         * dibuka ulang pada tab yang memuat galat validasinya: tanpa ini
         * tandanya aktif tetapi tabnya sendiri di luar layar.
         *
         * HANYA kalau memang belum terlihat utuh: scrollIntoView tanpa syarat
         * menggeser stripnya sebesar bantalannya sendiri, dan penanda kiri
         * menyala tanpa ada isi di sana.
         */
        var aktif = strip.querySelector('.nav-link.active');

        if (aktif && !terlihatPenuh(aktif)) {
            aktif.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }

        strip.addEventListener('click', function (e) {
            var tab = e.target.closest('.nav-link');

            if (!tab) {
                return;
            }

            setTimeout(function () {
                if (!terlihatPenuh(tab)) {
                    tab.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
                }

                segarkan();
            }, 60);
        });
    })();

    /*
     * Konfirmasi hapus lewat misKonfirmasi(), bukan Swal.fire langsung —
     * pembungkus bersama itu yang menjaga rupanya seragam di semua layar.
     *
     * Nama orangnya lewat `sorot`, BUKAN dirangkai ke `pesan`: ia disisipkan
     * sebagai teks, jadi tanda < di dalam nama tidak pernah tertafsir markah.
     *
     * Dialognya bukan pengaman. Peladennya tetap memeriksa peran penghapusnya,
     * jadi melewatinya lewat konsol tidak membuat apa pun bisa terhapus.
     */
    /*
     * Hapus satu catatan pembayaran. Lewat misKonfirmasi() juga — dialognya
     * bukan pengaman, peladennya tetap memeriksa peran penghapusnya, tetapi
     * angka yang dihapus tanpa ditanya adalah angka yang hilang tanpa ada
     * yang sadar sampai tagihannya tidak cocok.
     */
    document.addEventListener('click', function (e) {
        var tombolTermin = e.target.closest('[data-hapus-termin]');

        if (!tombolTermin) {
            return;
        }

        e.preventDefault();

        window.misKonfirmasi({
            judul: 'Hapus catatan pembayaran ini?',
            pesan: 'Sisa tagihannya ikut berubah. Tidak bisa diurungkan.',
            sorot: tombolTermin.dataset.nominal,
            tombol: 'Ya, hapus',
            jenis: 'bahaya',
        }).then(function (setuju) {
            if (setuju) {
                tombolTermin.closest('form').submit();
            }
        });
    });

    /*
     * Nama berkas yang dipilih ditulis di labelnya.
     *
     * Tanpa ini pengunggahnya tetap berbunyi "Pilih berkas bukti" walau
     * berkasnya sudah dipilih — dan orang mengira ketukannya tidak kena, lalu
     * memilih lagi. Isian berkas bawaan menunjukkannya sendiri; begitu ia
     * disembunyikan, tugas itu berpindah ke sini.
     */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type=file][data-mis-berkas]').forEach(function (kotak) {
            var sasaran = document.getElementById(kotak.dataset.misBerkas);

            if (!sasaran) {
                return;
            }

            var semula = sasaran.textContent;

            kotak.addEventListener('change', function () {
                sasaran.textContent = kotak.files && kotak.files.length
                    ? kotak.files[0].name
                    : semula;
            });
        });

        /*
         * Tombol kirim yang menunggu berkasnya dipilih.
         *
         * Dipasang lewat penanda, bukan lewat satu id yang ditulis di skrip:
         * borang kedua yang butuh perilaku yang sama tinggal membawa
         * penandanya sendiri, tanpa cabang kedua di sini.
         */
        document.querySelectorAll('[data-mis-berkas-tombol]').forEach(function (tombol) {
            var kotak = document.getElementById(tombol.dataset.misBerkasTombol);

            if (!kotak) {
                return;
            }

            var gambar = function () {
                tombol.hidden = !(kotak.files && kotak.files.length);
            };

            kotak.addEventListener('change', gambar);
            gambar();
        });
    });

    /*
     * Nominal diberi pemisah ribuan sambil diketik.
     *
     * Peladen membuang seluruh karakter bukan angka sebelum menyimpan, jadi
     * titiknya tidak pernah ikut tersimpan — ia murni alat baca.
     *
     * Letak kursor DIJAGA: tanpa itu, kursor melompat ke ujung kanan tiap
     * kali pemisahnya bertambah, dan menyunting digit di tengah jadi
     * mustahil. Yang dihitung jumlah DIGIT di kiri kursor, bukan posisinya,
     * sebab jumlah titik di kirinya berubah.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var pisah = function (angka) {
            return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        document.querySelectorAll('input[data-mis-rupiah]').forEach(function (kotak) {
            kotak.addEventListener('input', function () {
                var sebelum = kotak.value.slice(0, kotak.selectionStart || 0);
                var digitKiri = (sebelum.match(/\d/g) || []).length;

                var angka = kotak.value.replace(/\D+/g, '');

                kotak.value = angka === '' ? '' : pisah(angka);

                // Kursor dikembalikan sesudah digit ke-N yang sama.
                var pos = 0, hitung = 0;

                while (pos < kotak.value.length && hitung < digitKiri) {
                    if (/\d/.test(kotak.value[pos])) { hitung++; }
                    pos++;
                }

                kotak.setSelectionRange(pos, pos);
            });
        });
    });

    /*
     * Total bayar ikut berubah sambil nominalnya diketik.
     *
     * Dulu keempat kotak ini berdiri sendiri: panitia yang memberi potongan
     * Rp 500.000 harus menghitung sendiri totalnya lalu MENGETIK ULANG angka
     * delapan digit — dan satu digit yang meleset di situ tidak ditolak siapa
     * pun, sebab peladen memang menyimpan total apa adanya.
     *
     * Subtotalnya TIDAK ditebak dari tarif angkatan dikali jumlah orang.
     * Angka tersimpan bisa lahir dari potongan alumni, promo rombongan, atau
     * harga yang dirundingkan, dan menghitung ulang dari tarif akan
     * menimpanya diam-diam begitu halaman dibuka. Yang dipakai selisihnya:
     *
     *     dasar = total - (PPN + kode unik) + potongan
     *
     * diukur sekali dari nilai yang sudah tersimpan, sehingga membuka halaman
     * menghasilkan angka yang sama persis.
     *
     * Yang di sini hanya PRATINJAU. Kotaknya dimatikan dan tidak ikut
     * terkirim; yang benar-benar tersimpan dihitung ulang peladen dengan
     * rumus yang sama (UbahDataPendaftaran::hitungkan()). Jadi kalau skrip ini
     * tidak jalan sama sekali, yang hilang cuma angka yang bergerak di layar —
     * bukan ketepatan angka yang tersimpan.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var angka = function (kotak) {
            return parseInt(String(kotak.value).replace(/\D+/g, ''), 10) || 0;
        };

        var pisah = function (n) {
            return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        document.querySelectorAll('[data-mis-hitung="hasil"]').forEach(function (hasil) {
            var borang = hasil.closest('form');

            if (! borang) { return; }

            var tambah = borang.querySelectorAll('[data-mis-hitung="tambah"]');
            var kurang = borang.querySelectorAll('[data-mis-hitung="kurang"]');

            if (! tambah.length && ! kurang.length) { return; }

            var jumlahkan = function (daftar) {
                var t = 0;
                daftar.forEach(function (k) { t += angka(k); });
                return t;
            };

            var dasar = angka(hasil) - jumlahkan(tambah) + jumlahkan(kurang);

            var hitung = function () {
                // Tidak pernah minus: potongan yang melebihi tagihan menahan
                // totalnya di nol, bukan menampilkan angka negatif.
                var total = Math.max(0, dasar + jumlahkan(tambah) - jumlahkan(kurang));

                hasil.value = pisah(total);

                // Penanda singkat supaya perubahannya tertangkap mata; tanpa
                // ini angkanya berganti tanpa ada yang bergerak di layar.
                // Dipasang di PEMBUNGKUSNYA: kotaknya sendiri disabled dan
                // latarnya sudah bergradien, jadi kedipan di situ tidak
                // terlihat sama sekali.
                var sasaran = hasil.closest('[data-mis-hitung="bungkus"]') || hasil;

                sasaran.classList.remove('rin-hitung-berubah');
                void sasaran.offsetWidth;
                sasaran.classList.add('rin-hitung-berubah');
            };

            tambah.forEach(function (k) { k.addEventListener('input', hitung); });
            kurang.forEach(function (k) { k.addEventListener('input', hitung); });
        });
    });

    /*
     * Daftar peserta diambil dari berkas Excel/CSV.
     *
     * Berkasnya dikirim ke peladen untuk DIBACA, bukan diurai di peramban:
     * pembacanya satu-satunya — App\Support\DaftarPeserta — dan pembaca kedua
     * di sisi peramban akan berbeda perlahan, lalu "Budi, 0812..." terbaca
     * benar saat diketik dan salah saat diunggah tanpa ada yang bisa
     * menjelaskan kenapa.
     *
     * Alamat tujuannya dibawa penanda, bukan ditulis di skrip: rutenya sudah
     * dipakai borang Tambah, dan menuliskannya dua kali berarti satu di
     * antaranya tertinggal saat rutenya berganti nama.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var kotak = document.getElementById('rin-peserta-berkas');
        var teks = document.getElementById('rin-peserta-teks');
        var kabar = document.getElementById('rin-peserta-kabar');
        var namaBerkas = document.getElementById('rin-peserta-berkas-nama');

        if (!kotak || !teks || !kabar) {
            return;
        }

        var semula = namaBerkas ? namaBerkas.textContent : '';

        var beriKabar = function (pesan, jenis) {
            kabar.textContent = pesan;
            kabar.classList.remove('berhasil', 'gagal');

            if (jenis) {
                kabar.classList.add(jenis);
            }

            kabar.hidden = !pesan;
        };

        kotak.addEventListener('change', function () {
            var berkas = kotak.files && kotak.files[0];

            if (!berkas) {
                return;
            }

            if (namaBerkas) {
                namaBerkas.textContent = berkas.name;
            }

            beriKabar('Sedang dibaca…', null);

            var muatan = new FormData();
            muatan.append('berkas', berkas);

            // Token lewat TAJUK dan Accept: application/json, sama persis
            // dengan borang Tambah. Tanpa Accept, isian yang ditolak peladen
            // dibalas pengalihan HTML dan .json() melempar — yang terlihat
            // panitia cuma "gagal dikirim", tanpa alasan yang sebenarnya ada.
            fetch(kotak.dataset.misPesertaBerkas, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: muatan
            })
                .then(function (j) { return j.json().then(function (d) { return { ok: j.ok, d: d }; }); })
                .then(function (h) {
                    if (!h.ok || !h.d.ok) {
                        var sebab = h.d.pesan
                            || (h.d.errors && h.d.errors.berkas && h.d.errors.berkas[0])
                            || 'Berkasnya tidak bisa dibaca.';

                        beriKabar(sebab, 'gagal');

                        if (namaBerkas) { namaBerkas.textContent = semula; }

                        return;
                    }

                    // DITIMPA, bukan ditambahkan: daftar yang diunggah adalah
                    // daftar yang berlaku. Menambahkannya ke isi lama membuat
                    // nama ganda saat panitia mengunggah ulang berkas yang
                    // sudah dibetulkan — dan ia tidak akan menyadarinya.
                    teks.value = h.d.teks;
                    teks.dispatchEvent(new Event('input', { bubbles: true }));

                    beriKabar(
                        h.d.jumlah + ' nama terbaca, ' + h.d.bernomor + ' di antaranya bernomor. '
                            + 'Periksa dulu, lalu tekan Simpan.',
                        'berhasil'
                    );
                })
                .catch(function () {
                    beriKabar('Berkasnya gagal dikirim. Coba lagi.', 'gagal');

                    if (namaBerkas) { namaBerkas.textContent = semula; }
                });
        });
    });

    /*
     * Kotak catatan tumbuh mengikuti isinya.
     *
     * rows="3" dipatok berapa pun panjang isinya, dan jejak status yang
     * menumpuk membuat kalimatnya terpotong di tengah — yang terlihat cuma
     * penggulung kecil di dalam kotak, dan orang tidak tahu masih ada berapa
     * baris lagi di bawahnya.
     *
     * Dihitung dari scrollHeight, BUKAN dari jumlah baris teksnya: isinya
     * satu kalimat panjang tanpa pergantian baris, jadi yang menentukan
     * tingginya adalah pembungkusan kata — dan itu hanya diketahui peramban
     * sesudah dirender.
     *
     * Dibatasi 320px. Tanpa batas, catatan yang sudah puluhan kali berganti
     * status akan membuat satu kotak setinggi layar penuh dan tombol
     * simpannya terdorong jauh ke bawah.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var BATAS = 320;

        function tumbuhkan(kotak) {
            // Dinolkan dulu: tanpa itu, kotak yang sudah tinggi tidak pernah
            // MENYUSUT saat isinya dihapus, sebab scrollHeight-nya tetap
            // sebesar kotaknya sendiri.
            kotak.style.height = 'auto';

            var perlu = kotak.scrollHeight;

            kotak.style.height = Math.min(perlu, BATAS) + 'px';
            kotak.style.overflowY = perlu > BATAS ? 'auto' : 'hidden';
        }

        document.querySelectorAll('textarea[data-mis-tumbuh]').forEach(function (kotak) {
            tumbuhkan(kotak);
            kotak.addEventListener('input', function () { tumbuhkan(kotak); });
        });

        /*
         * Diulang saat tabnya dibuka: kotak di panel yang masih
         * display:none tidak punya scrollHeight yang berarti, jadi
         * perhitungan saat halaman dimuat memulangkan tinggi minimum untuk
         * semua tab kecuali yang sedang aktif.
         *
         * Lewat 'shown.bs.tab', BUKAN 'click' berjeda tetap. Percobaan
         * pertama memakai click + 60ms dan terukur kotaknya tetap 40px
         * padahal isinya menuntut 107px — jedanya menebak kapan panelnya
         * sudah terlihat, dan tebakan itu meleset. Peristiwa bawaan
         * Bootstrap berbunyi SESUDAH panelnya benar-benar tampil.
         *
         * Jeda tetap tetap dipasang sebagai cadangan kalau jQuery atau
         * peristiwanya tidak ada.
         */
        var segarkanSemua = function () {
            document.querySelectorAll('textarea[data-mis-tumbuh]').forEach(tumbuhkan);
        };

        if (window.jQuery) {
            window.jQuery('#rin-tab a[data-toggle="pill"]').on('shown.bs.tab', segarkanSemua);
        }

        document.querySelectorAll('#rin-tab .nav-link').forEach(function (tab) {
            tab.addEventListener('click', function () {
                setTimeout(segarkanSemua, 250);
            });
        });

        // Lebar kotaknya berubah saat jendela diubah, dan pembungkusan katanya
        // ikut berubah — tingginya harus dihitung ulang.
        window.addEventListener('resize', segarkanSemua);
    });

    /*
     * Salin nomor pendaftaran.
     *
     * Lewat Clipboard API bila ada, dengan jalan mundur ke execCommand:
     * Clipboard API hanya tersedia pada konteks aman, dan MIS dijalankan
     * panitia lewat http://127.0.0.1 maupun lewat domainnya. Tanpa jalan
     * mundur, tombolnya diam saja di salah satu dari keduanya — dan tombol
     * yang diam terbaca sebagai rusak.
     *
     * Hasilnya dikabarkan lewat misToast yang sama dengan seluruh MIS,
     * termasuk saat GAGAL: menyalin yang gagal tanpa kabar membuat orang
     * menempelkan isi papan klip yang lama.
     */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-rin-salin]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                var teks = tombol.dataset.rinSalin || '';

                var kabarkan = function (berhasil) {
                    window.misToast(
                        berhasil ? 'berhasil' : 'gagal',
                        berhasil
                            ? 'Nomor ' + teks + ' tersalin.'
                            : 'Nomornya tidak bisa disalin otomatis. Silakan sorot lalu salin sendiri.'
                    );
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(teks)
                        .then(function () { kabarkan(true); })
                        .catch(function () { kabarkan(false); });

                    return;
                }

                var kotak = document.createElement('textarea');
                kotak.value = teks;
                // Di luar layar, BUKAN display:none: unsur yang tidak dirender
                // tidak bisa dipilih, jadi execCommand tidak menyalin apa pun.
                kotak.setAttribute('readonly', '');
                kotak.style.position = 'fixed';
                kotak.style.left = '-9999px';
                document.body.appendChild(kotak);
                kotak.select();

                var berhasil = false;

                try {
                    berhasil = document.execCommand('copy');
                } catch (e) {
                    berhasil = false;
                }

                document.body.removeChild(kotak);
                kabarkan(berhasil);
            });
        });
    });

    /*
     * Pemindahan status: sekali tekan, dengan penegasan yang menyebut akibat.
     *
     * Pengamannya BUKAN tombol Simpan yang dulu ada di sebelah menu tarik,
     * melainkan penegasan ini — dan ia menyebut hal yang tombol Simpan tidak
     * pernah sebut: status mana yang dituju, dan apakah langkah itu mengirim
     * email yang tidak bisa ditarik kembali.
     *
     * Dipasang satu kali di wadahnya, bukan satu penyimak per tombol:
     * jumlahnya berbeda tiap layanan, dan penyimak per tombol berarti enam
     * penutupan yang semuanya menyimpan salinan borang yang sama.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var wadah = document.querySelector('.rin-status-pilih');
        var borang = document.getElementById('rin-borang-status');
        var nilai = document.getElementById('rin-status-nilai');

        if (!wadah || !borang || !nilai) {
            return;
        }

        wadah.addEventListener('click', function (e) {
            var tombol = e.target.closest('.rin-status-tombol');

            if (!tombol || tombol.disabled) {
                return;
            }

            var berkirim = tombol.dataset.surat === '1';

            window.misKonfirmasi({
                judul: 'Pindahkan status ke sini?',
                pesan: berkirim
                    // Emailnya disebut DI MUKA dan dengan kata "tidak bisa
                    // ditarik kembali": sesudah terkirim, tidak ada yang bisa
                    // dikerjakan panitia untuk membatalkannya.
                    ? 'Pendaftarnya langsung dikirimi email pemberitahuan. Email yang sudah terkirim tidak bisa ditarik kembali.'
                    : 'Status di seluruh daftar dan laporan ikut berubah.',
                sorot: tombol.dataset.tulisan,
                tombol: 'Ya, pindahkan',
                jenis: berkirim ? 'peringatan' : 'tanya',
            }).then(function (setuju) {
                if (!setuju) {
                    return;
                }

                nilai.value = tombol.dataset.nilai;
                borang.submit();
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        var tombol = document.getElementById('rin-tombol-hapus');

        if (!tombol) {
            return;
        }

        tombol.addEventListener('click', function () {
            window.misKonfirmasi({
                judul: 'Hapus pendaftaran ini?',
                pesan: 'Barisnya hilang permanen beserta bukti bayarnya. Tidak bisa diurungkan.',
                sorot: tombol.dataset.nomor + ' — ' + tombol.dataset.nama,
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (setuju) {
                if (setuju) {
                    document.getElementById('rin-borang-hapus').submit();
                }
            });
        });
    });
</script>
@endpush
