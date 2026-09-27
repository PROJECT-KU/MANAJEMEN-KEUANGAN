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

    var JENIS = {
        berhasil: { icon: 'success', warna: '#10b981' },
        gagal: { icon: 'error', warna: '#f43f5e' },
        peringatan: { icon: 'warning', warna: '#f59e0b' },
        info: { icon: 'info', warna: '#0ea5e9' }
    };

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
            icon: pilih.icon,
            title: pesan,
            showConfirmButton: false,
            timer: lama || (jenis === 'gagal' ? 5000 : 3000),
            timerProgressBar: true,
            customClass: { popup: 'mis-toast' },
            didOpen: function (el) {
                el.style.setProperty('--mis-toast-warna', pilih.warna);
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
