/**
 * Perilaku kecil untuk tampilan back office.
 *
 * 1. Posisi gulir sidebar diingat antar halaman.
 *    Menu di sini panjang (lebih dari 1800px). Tanpa ini, setiap kali sebuah
 *    butir diklik halaman baru dimuat dan menunya kembali ke paling atas,
 *    sehingga pengguna harus menggulir ulang untuk butir berikutnya.
 *
 * 2. Kalau belum ada posisi tersimpan, butir yang sedang aktif digulirkan
 *    sampai terlihat.
 *
 * 3. niceScroll menaruh tabindex="1" di .main-sidebar supaya daerah gulirnya
 *    bisa dipilih papan ketik. Akibatnya Tab pertama justru mendarat di sebuah
 *    kotak kosong — tabindex positif memotong urutan dan melompat ke depan
 *    semua tautan. Daerah itu kita keluarkan dari urutan Tab; tautan menu di
 *    dalamnya tetap bisa dicapai dan fokusnya ikut menggulirkan menu.
 */
(function () {
    'use strict';

    var KUNCI = 'mis-sidebar-gulir';

    function siap(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    siap(function () {
        // Dasbor punya sapaannya sendiri di kepala halaman; tandai supaya
        // sapaan di bilah atas bisa disembunyikan lewat CSS.
        if (document.querySelector('.dsb')) {
            document.body.classList.add('dasbor-terbuka');
        }

        // tabindex="-1" bukan sekadar menghapus: niceScroll hanya memasang
        // nilainya kalau atribut belum ada, jadi nilai ini juga bertahan saat
        // ia memasang ulang dirinya (ganti ukuran jendela, tukar layout).
        var sidebar = document.querySelector('.main-sidebar');

        if (sidebar) {
            sidebar.setAttribute('tabindex', '-1');
        }

        var wadah = document.getElementById('sidebar-wrapper');

        if (!wadah) {
            return;
        }

        // Pulihkan posisi terakhir.
        var tersimpan = null;

        try {
            tersimpan = sessionStorage.getItem(KUNCI);
        } catch (e) {
            // Penyimpanan bisa ditutup (mode penyamaran); bukan hal genting.
        }

        if (tersimpan !== null && !isNaN(parseInt(tersimpan, 10))) {
            wadah.scrollTop = parseInt(tersimpan, 10);
        } else {
            var aktif = wadah.querySelector('.sidebar-menu li.active > a');

            if (aktif && aktif.getBoundingClientRect().bottom > window.innerHeight - 40) {
                aktif.scrollIntoView({ block: 'center' });
            }
        }

        // Simpan setiap kali menu digulir (dibatasi lewat requestAnimationFrame)
        // dan tepat sebelum halaman ditinggalkan.
        var menunggu = false;

        function simpan() {
            try {
                sessionStorage.setItem(KUNCI, String(wadah.scrollTop));
            } catch (e) {
                // diabaikan
            }
        }

        wadah.addEventListener('scroll', function () {
            if (menunggu) {
                return;
            }

            menunggu = true;

            window.requestAnimationFrame(function () {
                simpan();
                menunggu = false;
            });
        }, { passive: true });

        wadah.addEventListener('click', simpan);
        window.addEventListener('pagehide', simpan);
    });
})();

/**
 * Toast bersama (SweetAlert2).
 *
 * Satu pintu untuk semua pemberitahuan singkat di back office, supaya
 * rupanya seragam dan tidak ada lagi campuran alert Bootstrap, modal Swal
 * yang menutupi layar, dan kotak hijau buatan sendiri di tiap fitur.
 *
 *   misToast('berhasil', 'Data tersimpan.')
 *   misToast('gagal', 'Kata sandi lama tidak cocok.')
 *   misToast('info', 'Kode dikirim ke email Anda.')
 *
 * Komponen Livewire memanggilnya lewat:
 *   $this->dispatch('toast', jenis: 'berhasil', pesan: '...')
 */
(function () {
    'use strict';

    /*
     * Ikon bawaan SweetAlert2 tidak dipakai lagi.
     *
     * Ikon itu digambar dari beberapa garis yang posisinya dipatok dalam em
     * terhadap font-size ikonnya sendiri, dan angkanya disetel untuk ukuran
     * bawaan. Begitu ubinnya dikecilkan lewat width/height, garis silangnya
     * tetap di tempat lama sehingga tandanya melenceng dari titik tengah.
     * Diganti ubin bergradien berisi glif Font Awesome — sama seperti
     * .mis-medali di seluruh sistem ini, dan titik tengahnya diurus grid.
     *
     * Nama glifnya harus ada di Font Awesome 5.5; lihat IkonAdaGlifnyaTest.
     */
    var JENIS = {
        berhasil: { judul: 'Berhasil', glif: 'fa-check', warna: 'linear-gradient(135deg, #10b981 0%, #34d399 100%)', pita: '#10b981' },
        gagal: { judul: 'Gagal', glif: 'fa-times', warna: 'linear-gradient(135deg, #f43f5e 0%, #fb7185 100%)', pita: '#f43f5e' },
        peringatan: { judul: 'Perhatian', glif: 'fa-exclamation-triangle', warna: 'linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%)', pita: '#f59e0b' },
        info: { judul: 'Info', glif: 'fa-info-circle', warna: 'linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%)', pita: '#0ea5e9' }
    };

    /* Isi toast dirakit sebagai simpul, bukan untaian HTML: pesannya masuk
       lewat textContent, jadi tanda < atau & di dalamnya tidak pernah
       tertafsir sebagai markah. */
    function rakitIsi(pilih, pesan) {
        var bungkus = document.createElement('div');
        bungkus.className = 'mis-toast-isi';

        var ubin = document.createElement('span');
        ubin.className = 'mis-toast-ubin';
        ubin.setAttribute('aria-hidden', 'true');

        var glif = document.createElement('i');
        glif.className = 'fas ' + pilih.glif;
        ubin.appendChild(glif);

        var teks = document.createElement('div');
        teks.className = 'mis-toast-teks';

        var judul = document.createElement('p');
        judul.className = 'mis-toast-judul';
        judul.textContent = pilih.judul;

        var isi = document.createElement('p');
        isi.className = 'mis-toast-pesan';
        isi.textContent = pesan;

        teks.appendChild(judul);
        teks.appendChild(isi);

        bungkus.appendChild(ubin);
        bungkus.appendChild(teks);

        return bungkus;
    }

    function tampilkan(jenis, pesan, lama) {
        var pilih = JENIS[jenis] || JENIS.info;

        // Halaman yang belum memuat SweetAlert2 tidak boleh ikut rusak;
        // pemberitahuan lebih baik hilang daripada menghentikan skrip lain.
        if (typeof window.Swal === 'undefined') {
            return;
        }

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            html: rakitIsi(pilih, pesan),
            showConfirmButton: false,
            timer: lama || (jenis === 'gagal' ? 5000 : 3000),
            timerProgressBar: true,
            customClass: { popup: 'mis-toast', htmlContainer: 'mis-toast-wadah' },
            didOpen: function (el) {
                el.style.setProperty('--mis-toast-warna', pilih.warna);
                el.style.setProperty('--mis-toast-pita', pilih.pita);
                el.addEventListener('mouseenter', window.Swal.stopTimer);
                el.addEventListener('mouseleave', window.Swal.resumeTimer);
            }
        });
    }

    window.misToast = tampilkan;

    function dariMuatan(m) {
        // Livewire 3 membungkus muatannya di dalam larik; versi lama
        // mengirimnya lewat event.detail. Keduanya diterima di sini.
        var d = Array.isArray(m) ? (m[0] || {}) : (m || {});
        tampilkan(d.jenis || 'info', d.pesan || '', d.lama);
    }

    // Jembatan untuk komponen Livewire 3.
    document.addEventListener('livewire:init', function () {
        if (window.Livewire && typeof window.Livewire.on === 'function') {
            window.Livewire.on('toast', dariMuatan);
        }
    });

    // Jalur cadangan: event peramban biasa, dipakai halaman non-Livewire.
    window.addEventListener('toast', function (e) {
        dariMuatan(e && e.detail);
    });
})();

/* ===================================================== kunci gulir latar ===

   Selagi jendela .custom-popup terbuka, halaman di belakangnya tidak boleh
   ikut tergulir. Tanpa ini, menggulir di atas lapisan gelap justru
   menggerakkan isi halaman di baliknya: jendelanya seolah melayang lepas
   dari halaman, dan di ponsel orang sering kehilangan jendelanya sendiri.

   Dikerjakan lewat pengamat perubahan atribut, bukan dengan menyulih tiap
   tempat yang menyalakan jendela. Jendela .custom-popup tersebar di lima
   layar dan dinyalakan dari puluhan tempat — sebagian di dalam callback
   fetch — jadi menyentuh satu per satu berisiko ada yang terlewat, dan
   jendela baru yang ditulis nanti tidak akan ikut terjaga. Yang diamati
   keadaan akhirnya: apa pun yang mengubah display-nya, hasilnya tertangkap.
   ========================================================================= */
(function () {
    var PILIH = '.custom-popup';
    var KELAS = 'mis-latar-terkunci';

    function adaYangTerbuka() {
        var jendela = document.querySelectorAll(PILIH);

        for (var i = 0; i < jendela.length; i++) {
            if (getComputedStyle(jendela[i]).display !== 'none') {
                return true;
            }
        }

        return false;
    }

    function segarkan() {
        var badan = document.body;

        if (! badan) {
            return;
        }

        var perlu = adaYangTerbuka();

        if (perlu === badan.classList.contains(KELAS)) {
            return;
        }

        if (perlu) {
            // Bilah gulir ikut hilang saat overflow dikunci, dan lebarnya itu
            // membuat seluruh halaman melompat ke kanan sesaat jendela dibuka.
            // Diukur sekarang, bukan dipatok: di macOS bilahnya menumpang di
            // atas isi halaman sehingga nilainya 0 dan tidak ada yang berubah.
            var bilah = window.innerWidth - document.documentElement.clientWidth;

            badan.style.setProperty('--mis-bilah-gulir', Math.max(0, bilah) + 'px');
            badan.classList.add(KELAS);
        } else {
            badan.classList.remove(KELAS);
            badan.style.removeProperty('--mis-bilah-gulir');
        }
    }

    function pasang() {
        var jendela = document.querySelectorAll(PILIH);

        if (! jendela.length || typeof MutationObserver !== 'function') {
            return;
        }

        var pengamat = new MutationObserver(segarkan);

        for (var i = 0; i < jendela.length; i++) {
            pengamat.observe(jendela[i], { attributes: true, attributeFilter: ['style', 'class', 'hidden'] });
        }

        // Halaman bisa saja dimuat dengan jendela sudah terbuka.
        segarkan();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', pasang);
    } else {
        pasang();
    }
})();

/* ================================================== tombol sedang sibuk ===

   Formulir biasa (bukan Livewire, bukan fetch) tidak memberi tanda apa pun
   setelah tombolnya ditekan: halaman diam sampai peladen menjawab. Di
   sambungan lambat orang mengira tombolnya tidak kena lalu menekannya lagi,
   dan kiriman kedua itulah yang menimpa yang pertama.

   Dipasang lewat atribut data-sibuk pada <form>, bukan otomatis ke semua
   formulir: sebagian formulir membatalkan kirimannya lalu memakai fetch
   sendiri, dan tombol yang dikunci di situ tidak akan pernah pulih.
   ========================================================================= */
(function () {
    var ASLI = 'data-tombol-asli';

    function tombolnya(form) {
        return Array.prototype.filter.call(
            form.querySelectorAll('button'),
            function (b) { return ! b.type || b.type === 'submit'; }
        );
    }

    function kunci(form) {
        tombolnya(form).forEach(function (b) {
            if (b.hasAttribute(ASLI)) {
                return;
            }

            b.setAttribute(ASLI, b.innerHTML);
            b.innerHTML = '<span class="mis-putar" aria-hidden="true"></span> ' + (b.getAttribute('data-sibuk-teks') || 'Menyimpan…');
            b.disabled = true;
        });
    }

    function pulih(form) {
        tombolnya(form).forEach(function (b) {
            if (! b.hasAttribute(ASLI)) {
                return;
            }

            b.innerHTML = b.getAttribute(ASLI);
            b.removeAttribute(ASLI);
            b.disabled = false;
        });
    }

    // Fase gelembung, bukan tangkap: penangan milik halaman harus sempat
    // berjalan dulu, supaya yang memanggil preventDefault() ketahuan di sini.
    document.addEventListener('submit', function (e) {
        var form = e.target;

        if (! (form instanceof HTMLFormElement) || ! form.hasAttribute('data-sibuk') || e.defaultPrevented) {
            return;
        }

        // Ditunda satu putaran: tombol yang dinonaktifkan pada detik yang sama
        // dengan kirimannya tidak ikut terkirim nilainya.
        setTimeout(function () { kunci(form); }, 0);
    });

    // Kembali lewat tombol "back" memakai halaman dari cache, lengkap dengan
    // tombol yang masih terkunci dari kiriman sebelumnya.
    window.addEventListener('pageshow', function (e) {
        if (! e.persisted) {
            return;
        }

        Array.prototype.forEach.call(document.querySelectorAll('form[data-sibuk]'), pulih);
    });
})();

/* =================================================== tab: aria-selected ===

   Bootstrap 4 memindahkan kelas .active saat tab diganti, tetapi TIDAK
   memperbarui aria-selected. Akibatnya pembaca layar terus mengumumkan tab
   pertama sebagai yang terpilih, berapa kali pun orang berpindah — kelas
   .active hanya rupa, ia tidak terbaca sama sekali.

   Dipasang di sini, bukan di satu halaman, supaya tiap deretan tab di sistem
   ini ikut benar tanpa perlu diingat satu per satu.
   ========================================================================= */
(function () {
    document.addEventListener('click', function (e) {
        var tab = e.target.closest ? e.target.closest('[role="tab"]') : null;

        if (! tab) {
            return;
        }

        var daftar = tab.closest('[role="tablist"]');

        if (! daftar) {
            return;
        }

        var semua = daftar.querySelectorAll('[role="tab"]');

        for (var i = 0; i < semua.length; i++) {
            semua[i].setAttribute('aria-selected', semua[i] === tab ? 'true' : 'false');
        }
    });
})();

/**
 * Dialog konfirmasi bersama (SweetAlert2).
 *
 * Sebelum ini tiap layar merakit Swal.fire-nya sendiri — delapan puluh empat
 * berkas tampilan memanggilnya langsung — sehingga rupanya berbeda-beda dan
 * tiap layar mengulangi pilihan yang sama: tombol mana yang merah, mana yang
 * di kiri, ikon apa yang dipakai. Yang paling sering keliru: tombol
 * konfirmasinya diberi kelas .mis-tombol-bahaya, yang sebenarnya kotak 34x34
 * untuk ikon saja, sehingga tulisannya menembus keluar kotak.
 *
 *   misKonfirmasi({
 *       judul: 'Hapus pelanggan ini?',
 *       pesan: 'Data %s dan riwayatnya ikut terhapus.',
 *       sorot: 'Sugeng',
 *       tombol: 'Ya, hapus',
 *       jenis: 'bahaya',
 *   }).then(function (ya) { if (ya) { ... } });
 */
(function () {
    'use strict';

    /*
     * Ikon bawaan SweetAlert2 tidak dipakai, dengan alasan yang sama seperti
     * pada toast di atas: garisnya dipatok dalam em terhadap ukuran bawaannya,
     * jadi begitu ubinnya diubah ukuran tandanya melenceng dari titik tengah.
     * Diganti ubin bergradien berisi glif Font Awesome.
     *
     * Nama glifnya harus ada di Font Awesome 5.5; lihat IkonAdaGlifnyaTest.
     */
    var JENIS = {
        bahaya: {
            glif: 'fa-trash-alt',
            warna: 'linear-gradient(135deg, #e11d48 0%, #fb7185 100%)',
            bayang: 'rgba(225, 29, 72, .9)',
            kelasTombol: 'mis-tombol mis-tombol-hapus'
        },
        peringatan: {
            glif: 'fa-exclamation-triangle',
            warna: 'linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%)',
            bayang: 'rgba(245, 158, 11, .9)',
            kelasTombol: 'mis-tombol mis-tombol-ungu'
        },
        tanya: {
            glif: 'fa-question',
            warna: 'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)',
            bayang: 'rgba(99, 102, 241, .9)',
            kelasTombol: 'mis-tombol mis-tombol-ungu'
        }
    };

    /*
     * Isinya dirakit sebagai simpul, bukan untaian HTML.
     *
     * Yang disisipkan di sini nama orang — datang dari isian yang diketik
     * orang lain. Lewat textContent, tanda < atau & di dalamnya tidak pernah
     * tertafsir sebagai markah.
     */
    function rakitIsi(pilih, opsi) {
        var bungkus = document.createElement('div');

        var pesan = document.createElement('p');
        pesan.style.margin = '0';

        // %s pada pesannya diganti nama yang disorot; tanpa penanda itu,
        // namanya ditempel di depan.
        var teks = String(opsi.pesan || '');
        var sorot = opsi.sorot ? String(opsi.sorot) : '';

        if (sorot === '') {
            pesan.textContent = teks;
        } else {
            var belah = teks.split('%s');
            pesan.appendChild(document.createTextNode(belah[0]));

            var kuat = document.createElement('strong');
            kuat.className = 'mis-konfirm-sorot';
            kuat.textContent = sorot;
            pesan.appendChild(kuat);

            pesan.appendChild(document.createTextNode(belah.length > 1 ? belah.slice(1).join('%s') : ''));
        }

        bungkus.appendChild(pesan);

        return bungkus;
    }

    function tanya(opsi) {
        opsi = opsi || {};

        // Halaman yang belum memuat SweetAlert2 tidak boleh kehilangan
        // aksinya sama sekali; confirm() bawaan peramban jadi cadangannya.
        if (typeof window.Swal === 'undefined') {
            var jawab = window.confirm(
                (opsi.judul || 'Lanjutkan?') + '\n\n' +
                String(opsi.pesan || '').replace('%s', opsi.sorot || '')
            );

            return Promise.resolve(jawab);
        }

        var pilih = JENIS[opsi.jenis] || JENIS.bahaya;

        return window.Swal.fire({
            // Judulnya selalu kalimat tetap dari kode, bukan isian orang.
            title: opsi.judul || 'Lanjutkan?',
            html: rakitIsi(pilih, opsi),
            /*
             * Ikonnya lewat slot ikon milik SweetAlert, bukan diselipkan ke
             * dalam isi. Slot itulah yang dirender DI ATAS judul; ditaruh di
             * dalam isi, ubinnya muncul di bawah judul dan urutan bacanya jadi
             * judul - gambar - kalimat.
             *
             * Rupa bawaan slot itu ditimpa habis di mis-ui.css; yang dipakai
             * cuma tempatnya.
             */
            icon: 'warning',
            iconHtml: '<i class="fas ' + (opsi.glif || pilih.glif) + '" aria-hidden="true"></i>',
            showCancelButton: true,
            confirmButtonText: opsi.tombol || 'Ya, lanjutkan',
            cancelButtonText: opsi.batal || 'Batal',
            // Batal di kiri dan jadi tumpuan fokus: tombol yang menghapus
            // untuk selamanya tidak boleh jadi yang paling gampang tertekan.
            reverseButtons: true,
            focusCancel: true,
            buttonsStyling: false,
            customClass: {
                icon: 'mis-konfirm-ubin',
                confirmButton: pilih.kelasTombol,
                cancelButton: 'mis-tombol mis-tombol-halus'
            },
            didOpen: function (el) {
                var ubin = el.querySelector('.mis-konfirm-ubin');

                if (ubin) {
                    ubin.style.setProperty('--mis-konfirm-warna', pilih.warna);
                    ubin.style.setProperty('--mis-konfirm-bayang', pilih.bayang);
                }
            }
        }).then(function (hasil) {
            return !! hasil.isConfirmed;
        });
    }

    window.misKonfirmasi = tanya;
})();
