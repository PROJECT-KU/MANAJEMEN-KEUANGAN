{{--
  Perilaku borang Scopus Kafe.

  Tiga hal, dan ketiganya menolak di sisi peramban HANYA untuk memberi tahu
  lebih cepat — peladennya memeriksa ulang ketiganya. Yang diperiksa di satu
  sisi saja cepat atau lambat akan berselisih dengan sisi yang lain.
--}}
<script>
    (function () {
        var borang = document.getElementById('kfbBorang');

        if (! borang) {
            return;
        }

        var tanggal = document.getElementById('kfbTanggal');
        var kabarTanggal = document.getElementById('kfbKabarTanggal');
        var total = document.getElementById('kfbTotal');
        var kirim = document.getElementById('kfbKirim');
        var kotakSesi = [].slice.call(borang.querySelectorAll('input[name="sesi[]"]'));
        var pilihVarian = [].slice.call(borang.querySelectorAll('input[name="varian"]'));
        var grupSesi = [].slice.call(borang.querySelectorAll('.kfb-sesi[data-varian]'));

        var HARI_BUKA = (tanggal.dataset.hariBuka || '').split(',').map(Number);
        var MAKS = {{ (int) $maksSesi }};

        function rupiah(angka) {
            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        /*
         * Hari ISO dihitung dari bagian tanggalnya, BUKAN dari new Date(nilai).
         *
         * new Date('2026-10-30') dibaca sebagai UTC tengah malam, lalu
         * ditampilkan menurut zona waktu peramban — di WIB ia mundur jadi
         * tanggal sebelumnya, dan hari Jumat terbaca Kamis. Dirakit per
         * bagian, zona waktunya tidak ikut bermain.
         */
        function hariIso(nilai) {
            var bagian = (nilai || '').split('-');

            if (bagian.length !== 3) {
                return 0;
            }

            var d = new Date(+bagian[0], +bagian[1] - 1, +bagian[2]);

            return d.getDay() === 0 ? 7 : d.getDay();
        }

        function periksaTanggal() {
            var hari = hariIso(tanggal.value);
            var boleh = HARI_BUKA.indexOf(hari) !== -1;

            kabarTanggal.classList.toggle('galat', !! tanggal.value && ! boleh);

            kabarTanggal.innerHTML = (!! tanggal.value && ! boleh)
                ? 'Tanggal itu bukan hari penyelenggaraan. Scopus Kafe berjalan setiap '
                    + '<strong>{{ $namaHariBuka }}</strong>.'
                : 'Scopus Kafe berjalan setiap <strong>{{ $namaHariBuka }}</strong>.';

            return boleh;
        }

        /** Varian yang sedang dipilih; radio pertama kalau entah bagaimana tidak ada. */
        function varianAktif() {
            for (var i = 0; i < pilihVarian.length; i++) {
                if (pilihVarian[i].checked) {
                    return pilihVarian[i].value;
                }
            }

            return pilihVarian.length ? pilihVarian[0].value : '';
        }

        /*
         * Kelompok sesi milik varian lain DISEMBUNYIKAN DAN DIMATIKAN.
         *
         * Dimatikan, bukan cuma disembunyikan: kotak centang yang tersembunyi
         * tetapi masih hidup akan ikut terkirim kalau sempat tercentang
         * sebelum variannya diganti — dan peladen melewatinya tanpa ada yang
         * tahu kenapa sesinya hilang dari tagihan. Centangnya juga dilepas,
         * supaya yang terbaca di layar dan yang terkirim selalu sama.
         */
        function pasangVarian() {
            var aktif = varianAktif();

            grupSesi.forEach(function (grup) {
                var punyaDia = grup.dataset.varian === aktif;

                grup.hidden = ! punyaDia;

                [].slice.call(grup.querySelectorAll('input[name="sesi[]"]'))
                    .forEach(function (kotak) {
                        if (! punyaDia) {
                            kotak.checked = false;
                        }

                        kotak.disabled = ! punyaDia;
                    });
            });

            pilihVarian.forEach(function (radio) {
                radio.closest('.kfb-varian-butir')
                    .classList.toggle('dipilih', radio.checked);
            });
        }

        function hitung() {
            pasangVarian();

            var aktif = varianAktif();
            var kotakAktif = kotakSesi.filter(function (kotak) {
                return kotak.closest('.kfb-sesi').dataset.varian === aktif;
            });

            var jumlah = 0;
            var terpilih = 0;

            kotakAktif.forEach(function (kotak) {
                kotak.closest('.kfb-sesi-butir').classList.toggle('dipilih', kotak.checked);

                if (kotak.checked) {
                    terpilih++;
                    jumlah += parseInt(kotak.dataset.biaya || '0', 10);
                }
            });

            /*
             * Yang sudah penuh DIMATIKAN, bukan dibiarkan lalu ditolak saat
             * dikirim: orangnya sudah mencentang sesi keempat dan baru tahu
             * batasnya sesudah menekan tombol.
             *
             * Hanya kelompok yang AKTIF yang disentuh di sini — menyapu
             * seluruh kotak akan menghidupkan kembali milik varian lain yang
             * baru saja dimatikan pasangVarian().
             */
            kotakAktif.forEach(function (kotak) {
                kotak.disabled = ! kotak.checked && terpilih >= MAKS;
                kotak.closest('.kfb-sesi-butir').style.opacity = kotak.disabled ? '.5' : '';
            });

            total.textContent = rupiah(jumlah);

            kirim.disabled = terpilih === 0 || ! periksaTanggal();
        }

        kotakSesi.forEach(function (kotak) { kotak.addEventListener('change', hitung); });
        pilihVarian.forEach(function (radio) { radio.addEventListener('change', hitung); });
        tanggal.addEventListener('change', hitung);
        hitung();

        /*
         * ISI OTOMATIS DARI NOMOR.
         *
         * Memakai alamat yang SAMA dengan Scopus Camp: ia memang mencari di
         * kelima layanan sekaligus, jadi yang pernah memesan Scopus Kafe
         * ditemukan juga lewat sana. Alamat tersendiri untuk layanan ini
         * berarti dua pembatas laju untuk satu fitur, dan dua tempat yang
         * harus sama-sama diperketat saat salah satunya bocor.
         */
        var telp = document.getElementById('kfbTelp');
        var kabarIsi = document.getElementById('kfbKabarIsi');
        var jeda = null;

        telp.addEventListener('input', function () {
            clearTimeout(jeda);

            var nomor = telp.value.replace(/\D+/g, '');

            if (nomor.length < 9) {
                kabarIsi.textContent = '';

                return;
            }

            // Ditunda: tiap ketukan papan ketik yang langsung jadi permintaan
            // akan menghabiskan jatah pembatas lajunya dalam beberapa detik.
            jeda = setTimeout(function () {
                fetch(@json($alamatCari), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            ? document.querySelector('meta[name="csrf-token"]').content
                            : borang.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ telp: telp.value })
                })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (! data || ! data.ditemukan) {
                            kabarIsi.textContent = '';

                            return;
                        }

                        // Yang sudah diketik TIDAK ditimpa: orangnya mungkin
                        // sengaja memesankan untuk orang lain.
                        ['nama', 'email'].forEach(function (medan) {
                            var isian = document.getElementById(
                                'kfb' + medan.charAt(0).toUpperCase() + medan.slice(1)
                            );

                            if (isian && ! isian.value && data[medan]) {
                                isian.value = data[medan];
                            }
                        });

                        kabarIsi.textContent = 'Data Anda kami isikan dari pemesanan sebelumnya.';
                    })
                    .catch(function () { /* jaringan putus sesaat; diabaikan */ });
            }, 600);
        });
    })();
</script>
