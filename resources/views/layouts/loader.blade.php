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
    let startY = 0; // Simpan posisi awal sentuhan

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
    function handlePullToRefresh(event) {
        // Cek apakah scroll berada di paling atas dan tidak sedang dalam proses refresh
        if (window.scrollY === 0 && !isRefreshing) {
            // Periksa apakah sedang melakukan gerakan tarik ke atas
            if (event.touches && event.touches.length > 0) {
                startY = event.touches[0].clientY; // Simpan posisi awal sentuhan
            }
        }
    }

    // Fungsi untuk menangani selesai gerakan tarik
    function handleTouchEnd(event) {
        // Periksa apakah selesai gerakan tarik dan sudah cukup jauh
        if (startY > 0 && event.changedTouches && event.changedTouches.length > 0) {
            var endY = event.changedTouches[0].clientY; // Simpan posisi akhir sentuhan
            var distance = startY - endY; // Hitung jarak geser

            if (distance > 300) { // Jika jarak geser lebih dari 300px, lakukan refresh
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

            // Reset posisi awal sentuhan
            startY = 0;
        }
    }

    // Tambahkan event listener untuk mendeteksi gerakan menggeser ke bawah
    window.addEventListener('touchstart', handlePullToRefresh, {
        passive: true
    });
    window.addEventListener('touchend', handleTouchEnd, {
        passive: true
    });
</script>
<!--================== END ==================-->