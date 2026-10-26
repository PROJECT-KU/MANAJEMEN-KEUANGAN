{{--
  Penanya keadaan pembayaran, disalin dari Scopus Camp.

  Yang ditanyakan berkala BUKAN seluruh halaman melainkan satu alamat yang
  cuma memulangkan keadaannya. Memuat ulang seluruh halaman tiap beberapa
  detik di hosting ini pernah berakhir 503 — kuota prosesnya habis oleh
  poller, bukan oleh pengunjung.
--}}
<script>
    (function () {
        var wadah = document.querySelector('.sts');

        // Sudah selesai: tidak ada yang perlu ditunggu, jadi tidak ada yang
        // perlu ditanyakan.
        if (! wadah || wadah.dataset.selesai === '1') {
            return;
        }

        var alamat = wadah.dataset.keadaan;
        var sisa = 40;   // 40 x 5 detik = sekitar 3 menit, lalu berhenti sendiri

        /*
         * Berhenti sendiri, dan berhenti saat tabnya tidak terlihat.
         *
         * Poller yang jalan selamanya di tiap tab yang lupa ditutup adalah
         * sebab 503 di hosting ini: kuota prosesnya habis oleh permintaan
         * yang tidak ada yang membaca jawabannya.
         */
        var jam = setInterval(function () {
            if (document.hidden) {
                return;
            }

            if (--sisa <= 0) {
                clearInterval(jam);
                return;
            }

            fetch(alamat, { headers: { 'Accept': 'application/json' } })
                .then(function (jawab) { return jawab.ok ? jawab.json() : null; })
                .then(function (data) {
                    if (data && data.selesai) {
                        clearInterval(jam);
                        window.location.reload();
                    }
                })
                .catch(function () { /* jaringan putus sesaat; dicoba lagi nanti */ });
        }, 5000);
    })();
</script>
