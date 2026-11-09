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
         * keenam layanan sekaligus, jadi yang pernah memesan Scopus Kafe
         * ditemukan juga lewat sana. Alamat tersendiri untuk layanan ini
         * berarti dua pembatas laju untuk satu fitur, dan dua tempat yang
         * harus sama-sama diperketat saat salah satunya bocor.
         *
         * -------------------------------------------------------------------
         * Yang diukur rusak pada bentuk pertamanya, 9 Nov 2026
         * -------------------------------------------------------------------
         *
         * Bentuk pertamanya memang berhasil pada satu jalur — mengetik nomor
         * angka demi angka tanpa ragu, dengan jatah pembatas laju yang masih
         * utuh. Tiga jalur lain, yang justru lebih sering terjadi, diam:
         *
         *   1. TEMPEL dari papan klip, dan isian otomatis peramban. Nol
         *      permintaan terkirim: hanya 'input' yang didengarkan, dan
         *      sebagian peramban tidak melepasnya untuk tempel dari menu
         *      konteks maupun untuk isiannya sendiri. Orang yang menyalin
         *      nomornya dari WhatsApp adalah jalur yang paling wajar di
         *      layanan ini, dan ia yang paling pasti tidak kebagian.
         *
         *   2. MENGETIK DENGAN RAGU. Terukur: mengetik sembilan angka,
         *      berhenti memeriksa, lanjut, menghapus satu angka yang salah,
         *      mengetik ulang — LIMA permintaan untuk satu nomor, dan yang
         *      kelima dijawab 429. Nomor separuh ikut dicari, dan nomor yang
         *      sama dicari berulang kali. Jatahnya 4 per menit per IP.
         *
         *   3. 429 SAMA SEKALI TIDAK DIKATAKAN. Terukur: dengan jatah per
         *      menit yang sudah habis, mengetik nomor lengkap yang jelas ada
         *      di basis data menyisakan nama dan email kosong, pita kabar
         *      kosong, konsol tanpa galat. Dari layar, "pernah memesan" dan
         *      "sedang dibatasi" tidak bisa dibedakan — dan inilah bentuk
         *      yang dilaporkan sebagai "kenapa belum otomatis".
         *
         * Keempat aturan di bawah menjawab ketiganya.
         */

        /*
         * Yang sudah masuk akun TIDAK lewat sini sama sekali.
         *
         * Borangnya sudah dirender terisi oleh peladen, dari pesanan
         * terakhirnya. Menjalankan pencarian lagi di sini cuma menghabiskan
         * jatah yang disediakan untuk tamu, demi jawaban yang sudah dipegang.
         */
        @if (! $sudahTerisi)
        var telp = document.getElementById('kfbTelp');
        var kabarIsi = document.getElementById('kfbKabarIsi');

        if (telp && kabarIsi && window.fetch) {
            var ALAMAT = @json($alamatCari);
            var NOMOR_MIN = {{ (int) $nomorMin }};
            var NOMOR_MAKS = {{ (int) $nomorMaks }};

            var medan = {
                nama: document.getElementById('kfbNama'),
                email: document.getElementById('kfbEmail')
            };

            var nomorTerakhir = '';
            var diisiSkrip = [];
            var jeda = null;

            /*
             * Dinormalkan SEPERTI PELADEN: angka saja, awalan 0 jadi 62.
             *
             * Bukan sekadar membuang tanda baca. Peladen mengukur panjangnya
             * SESUDAH 0 di depan diganti 62, jadi "087880000914" dihitung 13
             * angka di sana dan 12 di sini. Mengukur dua bentuk yang berbeda
             * membuat batas yang katanya sama tetap berselisih satu angka —
             * dan selisih satu angka itu tepat berupa satu permintaan yang
             * terkirim lalu ditolak.
             */
            function nomorRapi(nilai) {
                var angka = (nilai || '').replace(/\D+/g, '');

                if (angka === '') {
                    return '';
                }

                return angka.charAt(0) === '0' ? '62' + angka.slice(1) : angka;
            }

            function bersihkanTanda() {
                Object.keys(medan).forEach(function (kunci) {
                    if (medan[kunci]) {
                        medan[kunci].classList.remove('kfb-baru-diisi');
                    }
                });
            }

            function sembunyikanKabar() {
                kabarIsi.textContent = '';
                kabarIsi.hidden = true;
                kabarIsi.classList.remove('kfb-kabar-tertahan');
            }

            /*
             * Pita kabarnya dirakit lewat createElement dan textContent, tidak
             * lewat innerHTML. Isinya memuat NAMA DAN EMAIL dari basis data —
             * keduanya diketik orang ke borang umum, jadi keduanya bisa memuat
             * markah.
             */
            function gambarKabar(ikon, kalimat, tombolBatal) {
                kabarIsi.textContent = '';

                var i = document.createElement('i');
                // Bootstrap Icons: ini yang dimuat tata letak publik. Nama
                // ikon Font Awesome tetap menyisakan kotak kosong tanpa galat.
                i.className = 'bi ' + ikon;
                i.setAttribute('aria-hidden', 'true');
                kabarIsi.appendChild(i);

                var teks = document.createElement('span');
                teks.textContent = kalimat;
                kabarIsi.appendChild(teks);

                if (tombolBatal) {
                    var batal = document.createElement('button');
                    batal.type = 'button';
                    batal.className = 'kfb-kabar-batal';
                    batal.textContent = 'Kosongkan';
                    batal.addEventListener('click', kosongkan);
                    kabarIsi.appendChild(batal);
                }

                kabarIsi.hidden = false;
            }

            /*
             * HANYA yang diisikan skrip yang dikosongkan.
             *
             * Mengosongkan semuanya akan membuang nama yang mungkin sudah
             * diketik orangnya sendiri sebelum nomornya terisi — dan tombol
             * yang menghapus pekerjaan orang adalah tombol yang tidak akan
             * ditekan siapa pun lagi.
             */
            function kosongkan() {
                diisiSkrip.forEach(function (kunci) {
                    if (medan[kunci]) {
                        medan[kunci].value = '';
                    }
                });

                diisiSkrip = [];
                bersihkanTanda();
                sembunyikanKabar();

                if (medan.nama) {
                    medan.nama.focus();
                }
            }

            function isikan(data) {
                diisiSkrip = [];
                bersihkanTanda();

                Object.keys(medan).forEach(function (kunci) {
                    var isian = medan[kunci];

                    // Yang sudah ada isinya DIBIARKAN: orangnya mungkin
                    // sengaja memesankan untuk orang lain.
                    if (! isian || ! data[kunci] || isian.value.trim() !== '') {
                        return;
                    }

                    isian.value = data[kunci];
                    isian.classList.add('kfb-baru-diisi');
                    diisiSkrip.push(kunci);
                });

                if (diisiSkrip.length === 0) {
                    sembunyikanKabar();

                    return;
                }

                kabarIsi.classList.remove('kfb-kabar-tertahan');
                gambarKabar('bi-stars', diisiSkrip.length + ' isian kami isikan dari '
                    + 'pemesanan Anda sebelumnya. Periksa dulu, ubah kalau ada yang '
                    + 'berbeda.', true);
            }

            function tampilkanTertahan() {
                diisiSkrip = [];
                bersihkanTanda();
                kabarIsi.classList.add('kfb-kabar-tertahan');

                /*
                 * Kalimatnya SENGAJA tidak menyebut apakah nomornya terdaftar.
                 *
                 * 429 terbit untuk nomor yang ada di basis data maupun yang
                 * tidak, dan menyebutkannya akan mengubah pita ini jadi alat
                 * memeriksa keanggotaan — persis yang pembatas lajunya dipasang
                 * untuk mencegah.
                 */
                gambarKabar('bi-hourglass-split', 'Pengisian otomatis sedang dibatasi, '
                    + 'jadi isian di bawah tidak kami isikan kali ini. Silakan isi '
                    + 'sendiri — pemesanannya tetap berjalan seperti biasa.', false);
            }

            function cari() {
                var nomor = nomorRapi(telp.value);

                /*
                 * Nomor SEPARUH tidak dicari.
                 *
                 * Batasnya diteruskan peladen, bukan ditulis ulang di sini.
                 * Yang di bawah batas memang tidak akan pernah ketemu, jadi
                 * mencarinya hanya menghabiskan jatah 4-per-menit yang
                 * dibutuhkan nomor lengkapnya beberapa ketukan kemudian.
                 */
                if (nomor.length < NOMOR_MIN || nomor.length > NOMOR_MAKS) {
                    nomorTerakhir = '';
                    sembunyikanKabar();

                    return;
                }

                // Nomor yang SAMA tidak dicari dua kali. Orang keluar-masuk
                // isian ini saat memeriksa kembali isiannya, dan tiap
                // kembalinya melepas 'blur' lagi.
                if (nomor === nomorTerakhir) {
                    return;
                }

                nomorTerakhir = nomor;

                fetch(ALAMAT, {
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
                    .then(function (r) {
                        if (r.status === 429) {
                            // Dibuka lagi untuk dicoba ulang: batas per menit
                            // pulih sendiri, dan tanpa ini halamannya tidak
                            // akan mencoba lagi sampai dimuat ulang.
                            nomorTerakhir = '';
                            tampilkanTertahan();

                            return null;
                        }

                        if (! r.ok) {
                            nomorTerakhir = '';

                            return null;
                        }

                        return r.json();
                    })
                    .then(function (data) {
                        if (! data) {
                            return;
                        }

                        if (! data.ditemukan) {
                            sembunyikanKabar();

                            return;
                        }

                        isikan(data);
                    })
                    .catch(function () {
                        /*
                         * Jaringan yang putus didiamkan — ini kemudahan, bukan
                         * syarat, dan borangnya masih bisa diisi tangan. Yang
                         * TIDAK boleh didiamkan penolakan dari peladen di atas:
                         * itu bukan gangguan sesaat, melainkan jawaban.
                         */
                        nomorTerakhir = '';
                    });
            }

            /*
             * 1.500 ms, BUKAN 600.
             *
             * Enam ratus adalah jeda yang masih terjadi DI TENGAH mengetik
             * nomor: orang berhenti sebentar untuk mencocokkan angka yang
             * sudah masuk dengan yang ada di ponselnya. Terukur dengan 600 ms:
             * satu orang yang mengetik satu nomor dengan ragu — berhenti
             * memeriksa, salah satu angka, menghapus, mengetik ulang —
             * melepas ENAM permintaan, dan jatahnya 4 per menit per IP. Yang
             * keempat dan seterusnya dijawab 429, termasuk yang membawa nomor
             * lengkapnya.
             *
             * Menaikkan batas panjangnya BUKAN jalan keluarnya, dan itu
             * diukur: dari 347 nomor yang tersimpan di tujuh tabel, 16 (4,6%)
             * panjangnya cuma 9-10 angka. Batas yang cukup tinggi untuk
             * menahan nomor separuh juga akan membuang satu dari dua puluh
             * dua orang sungguhan — dan membuangnya DIAM-DIAM, sebab layarnya
             * tidak punya cara membedakan "tidak pernah dicari" dari "tidak
             * ketemu".
             *
             * Yang benar: jangan mencari sampai ia SELESAI dengan isiannya.
             * Dengan 'change' dan 'blur' di bawah, memperpanjang jeda ini
             * tidak membuat siapa pun menunggu lebih lama — yang berpindah ke
             * isian berikutnya dicari saat itu juga, dan yang tetap diam di
             * isian nomor memang sudah selesai mengetik.
             */
            telp.addEventListener('input', function () {
                clearTimeout(jeda);
                jeda = setTimeout(cari, 1500);
            });

            /*
             * 'change' dan 'blur' JUGA, bukan hanya 'input'.
             *
             * Tempel dari papan klip dan isian otomatis peramban tidak selalu
             * melepas 'input'. Terukur tanpa keduanya: menempel nomor yang ada
             * di basis data lalu berpindah isian mengirim NOL permintaan, dan
             * borangnya tetap kosong tanpa sepatah kata.
             *
             * Keduanya tidak menggandakan permintaan: jeda di atas dibatalkan
             * lebih dulu, dan penjaga nomorTerakhir memulangkan lebih awal
             * untuk nomor yang sama.
             */
            function cariSekarang() {
                clearTimeout(jeda);
                cari();
            }

            telp.addEventListener('change', cariSekarang);
            telp.addEventListener('blur', cariSekarang);
        }
        @endif
    })();
</script>
