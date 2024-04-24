<!-- MEREFRESH PWA DI HP -->
<style>
    .loader {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 2s linear infinite;
        z-index: 9999;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>
<!-- END -->

<!--================== MEREFRESH PWA DI HP ==================-->
<script>
    // Fungsi untuk menyembunyikan sidebar saat proses refresh dimulai
    function hideSidebarOnRefresh() {
        var SidebarPwa = document.getElementById('SidebarPwa');
        SidebarPwa.style.display = 'none'; // Sembunyikan sidebar
    }

    // Panggil fungsi saat halaman dimuat untuk menyembunyikan sidebar awal
    window.addEventListener('load', hideSidebarOnRefresh);

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').then(registration => {
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }).catch(err => {
                console.log('ServiceWorker registration failed: ', err);
            });
        });
    }

    let isRefreshing = false;

    // Fungsi untuk menampilkan loader
    function showLoader() {
        // Tambahkan elemen loader ke dalam body
        var loader = document.createElement('div');
        loader.className = 'loader';
        document.body.appendChild(loader);
    }

    // Fungsi untuk menyembunyikan loader
    function hideLoader() {
        // Hapus elemen loader dari body jika ada
        var loader = document.querySelector('.loader');
        if (loader) {
            loader.parentNode.removeChild(loader);
        }
    }

    // Fungsi untuk menangani refresh saat menggeser ke bawah
    function handlePullToRefresh() {
        // Cek apakah scroll berada di paling atas dan tidak sedang dalam proses refresh
        if (window.scrollY === 0 && !isRefreshing) {
            isRefreshing = true;
            // Tampilkan loader
            showLoader();

            // Lakukan refresh halaman setelah beberapa saat
            setTimeout(() => {
                // Panggil fungsi untuk menyembunyikan sidebar saat proses refresh dimulai
                hideSidebarOnRefresh();
                // Lakukan refresh halaman
                location.reload();
                // Setelah proses refresh selesai, sembunyikan loader
                hideLoader();
                // Tampilkan kembali sidebar setelah proses refresh selesai
                var SidebarPwa = document.getElementById('SidebarPwa');
                SidebarPwa.style.display = 'block';
                // Set isRefreshing ke false untuk memungkinkan refresh kembali
                isRefreshing = false;
            }, 1000); // Mengatur delay refresh selama 1 detik (1000 milidetik)
        }
    }

    // Tambahkan event listener untuk mendeteksi gerakan menggeser ke bawah
    window.addEventListener('scroll', handlePullToRefresh, {
        passive: true
    });

    // Fungsi untuk mendeteksi apakah perangkat adalah ponsel atau browser
    function isMobileDevice() {
        return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
    }

    // Fungsi untuk menyembunyikan atau menampilkan elemen berdasarkan tipe perangkat
    function toggleElementBasedOnDevice() {
        var SidebarPwa = document.getElementById('SidebarPwa');

        if (isMobileDevice()) {
            // Jika aplikasi berjalan di perangkat seluler (PWA)
            SidebarPwa.style.display = 'none';
        } else {
            // Jika aplikasi berjalan di browser
            SidebarPwa.style.display = 'block';
        }
    }

    // Panggil fungsi ketika halaman dimuat
    window.addEventListener('load', toggleElementBasedOnDevice);
</script>
<!--================== END ==================-->