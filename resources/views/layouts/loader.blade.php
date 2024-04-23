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
                location.reload();
                // Setelah proses refresh selesai, sembunyikan loader
                hideLoader();
                // Set isRefreshing ke false untuk memungkinkan refresh kembali
                isRefreshing = false;
            }, 1000); // Mengatur delay refresh selama 1 detik (1000 milidetik)
        }
    }

    // Tambahkan event listener untuk mendeteksi gerakan menggeser ke bawah
    window.addEventListener('scroll', handlePullToRefresh, {
        passive: true
    });
</script>
<!--================== END ==================-->