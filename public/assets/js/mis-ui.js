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
