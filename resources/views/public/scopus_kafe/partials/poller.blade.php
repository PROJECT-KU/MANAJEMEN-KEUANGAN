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
        /*
         * Satu pita, sekali saja, dengan tombol yang benar-benar menolong.
         *
         * Disisipkan tepat di atas wadah statusnya supaya terbaca sebagai
         * jawaban atas layar yang tidak kunjung berubah — bukan sebagai
         * peringatan milik bagian lain.
         */
        function kabarkanTertahan() {
            if (document.getElementById('sts-poller-tertahan')) {
                return;
            }

            var pita = document.createElement('div');
            pita.id = 'sts-poller-tertahan';
            pita.setAttribute('role', 'status');
            pita.style.cssText = 'margin:0 0 14px;padding:12px 15px;border-radius:13px;'
                + 'background:#fdf5e4;border:1px solid #f0d9a8;color:#7a5312;'
                + 'font-size:.92rem;line-height:1.55;display:flex;gap:9px;'
                + 'align-items:flex-start;flex-wrap:wrap;';

            var teks = document.createElement('span');
            teks.style.flex = '1 1 180px';
            teks.textContent = 'Halaman ini berhenti memeriksa sendiri karena sedang '
                + 'ramai. Kalau Anda sudah membayar, muat ulang halamannya untuk '
                + 'melihat keadaan terbaru.';

            var tombol = document.createElement('button');
            tombol.type = 'button';
            tombol.textContent = 'Muat ulang';
            tombol.style.cssText = 'flex:0 0 auto;border-radius:999px;padding:6px 15px;'
                + 'background:#fff;border:1px solid #e0c188;color:#7a5312;'
                + 'font-family:inherit;font-size:.86rem;font-weight:800;cursor:pointer;';
            tombol.addEventListener('click', function () { window.location.reload(); });

            pita.appendChild(teks);
            pita.appendChild(tombol);
            wadah.parentNode.insertBefore(pita, wadah);
        }

        var jam = setInterval(function () {
            if (document.hidden) {
                return;
            }

            if (--sisa <= 0) {
                clearInterval(jam);
                return;
            }

            fetch(alamat, { headers: { 'Accept': 'application/json' } })
                .then(function (jawab) {
                    /*
                     * DITOLAK itu kabar, bukan diam.
                     *
                     * Sampai 3 Nov 2026 baris ini berbunyi
                     * `jawab.ok ? jawab.json() : null` lalu berhenti. Jadi
                     * 429 dari pembatas laju — yang bisa datang dari orang
                     * LAIN di jaringan yang sama, sebab jatahnya dulu
                     * dihitung per IP — membuat halaman ini berkata
                     * "menunggu pembayaran" SELAMANYA walau uangnya sudah
                     * masuk. Orangnya menghubungi panitia menanyakan uang
                     * yang sebenarnya sudah tercatat.
                     *
                     * Sekarang penolakannya menghentikan penanyaan dan
                     * memunculkan satu tombol: muat ulang halamannya.
                     */
                    if (jawab.status === 429) {
                        clearInterval(jam);
                        kabarkanTertahan();

                        return null;
                    }

                    return jawab.ok ? jawab.json() : null;
                })
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
