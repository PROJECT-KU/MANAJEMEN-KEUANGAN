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
    let startY = 0;

    // Fungsi untuk menangani gerakan awal ketika pengguna mulai menggeser
    function handleTouchStart(event) {
        // Simpan posisi awal sentuhan
        startY = event.touches[0].clientY;
    }

    // Fungsi untuk menangani gerakan saat pengguna sedang menggeser
    function handleTouchMove(event) {
        // Hitung jarak yang telah digeser
        let deltaY = event.touches[0].clientY - startY;

        // Cek apakah pengguna menggeser ke bawah lebih dari 200 piksel
        if (deltaY > 200) {
            // Lakukan reload halaman
            location.reload();
        }
    }

    // Tambahkan event listener untuk mendeteksi sentuhan awal
    window.addEventListener('touchstart', handleTouchStart, false);

    // Tambahkan event listener untuk mendeteksi gerakan menggeser
    window.addEventListener('touchmove', handleTouchMove, false);
</script>

<!--================== END ==================-->