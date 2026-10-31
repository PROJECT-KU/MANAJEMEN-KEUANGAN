{{--
  Gaya halaman daftar Scopus Kafe.

  Bahasa rupanya sama dengan Scopus Camp — kertas, tinta, jingga, Fraunces +
  Figtree — tetapi SUSUNANNYA sengaja berbeda, dan itu keputusan bukan
  kelalaian.

  Scopus Camp memajang puluhan angkatan: ia butuh lajur kiri untuk keterangan
  dan lajur kanan yang panjang untuk daftarnya. Scopus Kafe cuma punya dua
  sesi tetap. Susunan yang sama di sini meninggalkan separuh layar kosong —
  terukur pada bentuk pertamanya: judul jatuh ke empat baris di lajur sempit
  sementara setengah kanan tidak dipakai apa pun.

  Jadi yang dipakai di sini TERPUSAT: judul di tengah, dua kartu sesi
  berdampingan, empat ubin "yang kamu dapat" sejajar, dan satu pita jalan
  kembali di kaki.

  Gradien merah-jingga #ff3131 → #ff914d dari bentuk lamanya dibuang: palet
  yang sudah ditinggalkan beranda dan blog justru karena pemiliknya
  menyebutnya terlalu mirip situs lain.
--}}
<style>
    .kfd {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --rumput: #0f766e;
        --garis: #e7dcc6;

        background: var(--kertas); color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif; overflow-x: clip;
    }
    .kfd h1, .kfd h2, .kfd h3 {
        font-family: 'Fraunces', Georgia, serif; font-weight: 800;
        color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .kfd p { margin: 0; color: var(--tinta-2); line-height: 1.7; }

    .kfd-kabar {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 14px 18px; margin: 0 0 20px; border-radius: 14px;
        font-size: 15px; line-height: 1.6; border: 1px solid;
    }
    .kfd-kabar i { flex: 0 0 auto; margin-top: 3px; font-size: 18px; }
    .kfd-kabar.sukses { background: #eafaf6; border-color: #a7e3d6; color: #0b5a52; }
    .kfd-kabar.galat  { background: #fdecea; border-color: #f0bdb6; color: #8e2318; }

    /* ========================================================== kepala */
    .kfd-kepala { padding: 124px 0 44px; text-align: center; }

    .kfd-stiker {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 16px; border-radius: 999px; margin-bottom: 20px;
        background: var(--kertas-2); border: 1px solid var(--garis);
        font-size: .85rem; font-weight: 800; color: var(--jingga-tua);
    }

    /* max-width + margin auto, BUKAN hanya text-align: judul yang dipusatkan
       tanpa dibatasi lebarnya tetap merentang selebar layar dan barisnya jadi
       terlalu panjang untuk dibaca sekali tatap. */
    /* BOBOTNYA .kfd .kfd-judul, bukan .kfd-judul saja.

       Aturan reset di atas — `.kfd h1, .kfd h2, .kfd h3 { margin: 0 }` —
       berbobot (0,1,1) karena memuat kelas DAN unsur. `.kfd-judul` cuma
       (0,1,0), jadi `margin: 0 auto`-nya KALAH dan judulnya tidak pernah
       terpusat.

       Terukur: judulnya duduk 345 px di kiri titik tengah layar, dengan
       text-align: center yang memang berlaku — jadi barisnya terpusat satu
       sama lain tetapi seluruh bloknya melenceng. Cacat yang tidak kelihatan
       dari kodenya, cuma dari potretnya. */
    .kfd .kfd-judul {
        font-size: clamp(1.9rem, 4.6vw, 3rem); line-height: 1.18;
        max-width: 18ch; margin: 0 auto;
    }

    /* Stabilo digambar di BELAKANG hurufnya lewat gradien.

       padding-bottom pada judulnya: tanpa itu garis stabilo baris terakhir
       bersentuhan dengan paragraf di bawahnya — terukur pada bentuk
       pertamanya. */
    .kfd-stabilo { background: linear-gradient(180deg, transparent 62%, #fbd9b4 62%); padding: 0 .1em; }
    .kfd .kfd-judul { padding-bottom: .12em; }

    .kfd .kfd-sub { font-size: 1.05rem; max-width: 58ch; margin: 18px auto 0; }

    /* Pita "kapan": dua butir berdampingan dengan pemisah di tengah. */
    .kfd-kapan {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 18px; flex-wrap: wrap; margin-top: 26px;
        padding: 14px 24px; border-radius: 999px;
        background: #fff; border: 1px solid var(--garis);
        box-shadow: 0 8px 22px rgba(22, 35, 63, .06);
        font-size: .97rem; color: var(--tinta-2);
    }
    .kfd-kapan-butir { display: inline-flex; align-items: center; gap: 9px; }
    .kfd-kapan-butir i { color: var(--jingga); font-size: 1.1rem; }
    .kfd-kapan strong { color: var(--tinta); }
    .kfd-kapan-pisah { width: 1px; height: 20px; background: var(--garis); }

    /* ========================================================= bagian */
    .kfd-bagian { padding: 8px 0 80px; text-align: center; }
    .kfd-bagian-judul { font-size: clamp(1.4rem, 3vw, 1.9rem); }
    .kfd .kfd-renggang { margin-top: 72px; }
    .kfd .kfd-bagian-kata { max-width: 50ch; margin: 10px auto 0; font-size: .98rem; }

    /* ==================================================== blok varian */
    /* Satu blok per bentuk layanan. Tanpa pemisah ini dua kelompok sesi
       yang harganya berbeda terbaca sebagai satu deret kartu yang sama. */
    .kfd-varian { margin-top: 42px; }
    .kfd .kfd-varian-kepala { max-width: 860px; margin: 0 auto; }
    .kfd .kfd-varian-nama {
        display: inline-flex; align-items: center; gap: 10px;
        font-size: 1.15rem; margin: 0;
    }
    .kfd .kfd-varian-nama i { color: var(--jingga); }
    .kfd .kfd-varian-ket { margin: 6px 0 0; font-size: .95rem; color: var(--tinta-2); }
    .kfd .kfd-varian-ket strong { color: var(--tinta); }

    /* ====================================================== kartu sesi */
    .kfd-varian .kfd-sesi { margin-top: 18px; }
    .kfd-sesi {
        display: grid; gap: 20px; margin: 30px auto 0; max-width: 860px;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
    }

    .kfd-kartu {
        display: flex; flex-direction: column; align-items: center; gap: 12px;
        padding: 28px 24px; text-align: center;
        background: #fff; border: 2px solid var(--tinta); border-radius: 22px;
        box-shadow: 6px 6px 0 rgba(22, 35, 63, .12);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .kfd-kartu:hover { transform: translateY(-3px); box-shadow: 6px 9px 0 rgba(22, 35, 63, .14); }

    .kfd-jam {
        display: grid; place-items: center; gap: 1px;
        width: 92px; padding: 12px 8px; border-radius: 16px;
        background: linear-gradient(135deg, var(--jingga-muda), var(--jingga-tua));
        color: #fff;
    }
    .kfd-jam-angka { font-family: 'Fraunces', Georgia, serif; font-size: 1.45rem; font-weight: 800; }
    .kfd-jam-label { font-size: .68rem; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }

    .kfd-nama { font-size: 1.3rem; }

    .kfd-ciri { list-style: none; padding: 0; margin: 0; display: grid; gap: 7px; }
    .kfd-ciri li {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        font-size: .92rem; color: var(--tinta-2); line-height: 1.5;
    }
    .kfd-ciri i { color: var(--jingga); }
    .kfd-ciri-ket { opacity: .75; }

    .kfd-harga {
        margin-top: 4px; padding-top: 14px; width: 100%;
        border-top: 2px dashed var(--garis);
    }
    .kfd-harga-angka {
        display: block; font-family: 'Fraunces', Georgia, serif;
        font-size: 1.5rem; font-weight: 800; color: var(--rumput);
    }
    .kfd-harga-ket { font-size: .8rem; color: var(--tinta-2); }

    /* ========================================================== ajakan */
    .kfd-ajak { margin-top: 34px; }
    .kfd-tombol {
        display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        padding: 15px 34px;
        background: var(--jingga); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 5px 5px 0 var(--tinta);
        font-family: 'Fraunces', Georgia, serif; font-size: 1.05rem; font-weight: 800;
        transition: transform .15s ease;
    }
    .kfd-tombol:hover { color: #fff; background: var(--tinta); transform: translateY(-2px); }
    .kfd-tombol.mati { background: var(--tinta-2); box-shadow: none; cursor: not-allowed; }
    .kfd .kfd-ajak-kata { margin-top: 12px; font-size: .92rem; max-width: 46ch; margin-left: auto; margin-right: auto; }

    /* =========================================================== ubin */
    .kfd-ubin {
        display: grid; gap: 18px; margin: 28px auto 0; max-width: 1040px;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
    }
    .kfd-ubin-butir {
        padding: 24px 20px; border-radius: 20px;
        background: #fff; border: 1px solid var(--garis);
        box-shadow: 0 8px 22px rgba(22, 35, 63, .05);
    }
    .kfd-ubin-ikon {
        display: grid; place-items: center; width: 48px; height: 48px;
        margin: 0 auto 14px; border-radius: 15px; font-size: 1.3rem;
        background: linear-gradient(135deg, #fde8d3, #fbd9b4); color: var(--jingga-tua);
    }
    .kfd-ubin-butir h3 { font-size: 1.05rem; margin-bottom: 7px; }
    .kfd-ubin-butir p { font-size: .9rem; }

    /* ================================================== jalan kembali */
    .kfd-lacak {
        display: grid; gap: 14px; justify-items: center;
        margin: 72px auto 0; max-width: 560px; padding: 28px 26px;
        background: #fff; border: 1px solid #efe3cd; border-radius: 22px;
        box-shadow: 0 10px 28px rgba(22, 35, 63, .06);
    }
    .kfd-lacak-ikon {
        display: grid; place-items: center; width: 48px; height: 48px;
        border-radius: 15px; font-size: 1.25rem;
        background: linear-gradient(135deg, #fde8d3, #fbd9b4); color: var(--jingga-tua);
    }
    .kfd-lacak-kata strong { display: block; font-size: 1.08rem; color: var(--tinta); margin-bottom: 4px; }
    .kfd-lacak-kata span { font-size: .93rem; color: var(--tinta-2); line-height: 1.6; }

    /* Kotak dan tombolnya TIDAK pernah terpisah baris: kotak isian yang
       tombolnya terlempar ke baris lain terbaca seperti dua hal yang tidak
       berhubungan. */
    .kfd-lacak-isian { display: flex; gap: 10px; width: 100%; flex-wrap: wrap; }
    .kfd-lacak-isian input {
        flex: 1 1 200px; min-width: 0; padding: 12px 15px; font-size: 15px;
        border: 1px solid #ddd0b8; border-radius: 12px; background: #fdfcf9; color: var(--tinta);
    }
    .kfd-lacak-isian input:focus { outline: 2px solid var(--jingga); outline-offset: 1px; background: #fff; }
    .kfd-lacak-isian button {
        flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; font-size: 15px; font-weight: 700; white-space: nowrap;
        border: 0; border-radius: 12px; cursor: pointer; background: var(--tinta); color: #fff;
    }
    .kfd-lacak-isian button:hover { background: #23365c; }

    .kfd-kosong {
        padding: 56px 24px; margin-top: 24px;
        background: #fff; border: 2px dashed var(--garis); border-radius: 22px;
    }
    .kfd-kosong i { font-size: 2.4rem; color: var(--tinta-2); }
    .kfd-kosong h2 { margin: 14px 0 9px; font-size: 1.3rem; }
    .kfd .kfd-kosong p { max-width: 44ch; margin: 0 auto; }

    @media (max-width: 600px) {
        .kfd-kepala { padding: 104px 0 32px; }
        .kfd-kapan { flex-direction: column; gap: 10px; border-radius: 20px; padding: 16px 20px; }
        .kfd-kapan-pisah { width: 36px; height: 1px; }
        .kfd-lacak { padding: 22px 18px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .kfd *, .kfd *::before, .kfd *::after { transition: none !important; }
    }
</style>
