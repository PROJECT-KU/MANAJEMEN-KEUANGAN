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
