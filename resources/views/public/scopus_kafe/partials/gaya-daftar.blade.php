{{--
  Gaya halaman daftar Scopus Kafe.

  Bahasa rupanya sama dengan Scopus Camp — kertas, tinta, jingga, Fraunces +
  Figtree. Bentuk lamanya memakai gradien merah-jingga #ff3131 → #ff914d,
  palet yang sudah ditinggalkan beranda dan blog justru karena pemiliknya
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

    .kfd-kepala { padding: 120px 0 36px; }
    .kfd-stiker {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 15px; border-radius: 999px; margin-bottom: 18px;
        background: var(--kertas-2); border: 1px solid var(--garis);
        font-size: .85rem; font-weight: 800; color: var(--jingga-tua);
    }
    .kfd-judul { font-size: clamp(1.9rem, 5vw, 3rem); line-height: 1.15; max-width: 16ch; }

    /* Stabilo digambar di BELAKANG hurufnya lewat gradien, bukan dengan
       latar kotak: kotak yang menempel membuat barisnya terlihat seperti
       tombol. */
    .kfd-stabilo {
        background: linear-gradient(180deg, transparent 62%, #fbd9b4 62%);
        padding: 0 .1em;
    }
    .kfd-sub { font-size: 1.05rem; max-width: 56ch; margin-top: 14px; }

    .kfd-kapan {
        display: flex; align-items: flex-start; gap: 12px;
        margin-top: 22px; padding: 15px 18px; max-width: 62ch;
        background: #fff; border: 1px solid var(--garis); border-radius: 16px;
        font-size: .97rem; line-height: 1.65; color: var(--tinta-2);
    }
    .kfd-kapan i { flex: 0 0 auto; margin-top: 3px; font-size: 1.15rem; color: var(--jingga); }
    .kfd-kapan strong { color: var(--tinta); }

    .kfd-bagian { padding: 8px 0 72px; }

    /* Dua lajur tak sama: keterangan di kiri, sesi di kanan — bentuk yang
       sama dengan halaman daftar Scopus Camp. */
    .kfd-kisi { display: grid; grid-template-columns: 330px minmax(0, 1fr); gap: 48px; }

    .kfd-panel-kartu {
        background: #fff; border: 1px solid var(--garis); border-radius: 22px;
        padding: 24px 22px; box-shadow: 0 10px 28px rgba(22, 35, 63, .06);
    }
    .kfd-panel-judul { font-size: 1.15rem; margin-bottom: 14px; }
    .kfd-fasilitas { list-style: none; padding: 0; margin: 0 0 14px; display: grid; gap: 10px; }
    .kfd-fasilitas li { display: flex; gap: 10px; align-items: flex-start; font-size: .95rem; line-height: 1.55; }
    .kfd-fasilitas i { flex: 0 0 auto; margin-top: 3px; color: var(--rumput); }
    .kfd-panel-kata { font-size: .9rem; }

    /* ================================================= jalan kembali */
    .kfd-lacak {
        margin-top: 14px; padding: 18px 20px; border-radius: 18px;
        background: #fff; border: 1px solid #efe3cd;
        box-shadow: 0 6px 20px rgba(22, 35, 63, .05);
    }
    .kfd-lacak-kata { display: flex; align-items: flex-start; gap: 12px; font-size: 14px; line-height: 1.55; color: var(--tinta-2); }
    .kfd-lacak-kata strong { display: block; color: var(--tinta); font-size: 15.5px; margin-bottom: 2px; }
    .kfd-lacak-ikon {
        flex: 0 0 auto; display: grid; place-items: center; width: 40px; height: 40px;
        border-radius: 12px; font-size: 18px;
        background: linear-gradient(135deg, #fde8d3, #fbd9b4); color: var(--jingga-tua);
    }
    .kfd-lacak-isian { display: grid; gap: 9px; margin-top: 14px; }
    .kfd-lacak-isian input {
        width: 100%; min-width: 0; padding: 12px 14px; font-size: 15px;
        border: 1px solid #ddd0b8; border-radius: 12px; background: #fdfcf9; color: var(--tinta);
    }
    .kfd-lacak-isian input:focus { outline: 2px solid var(--jingga); outline-offset: 1px; background: #fff; }
    .kfd-lacak-isian button {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 12px 16px; font-size: 15px; font-weight: 700;
        border: 0; border-radius: 12px; cursor: pointer; background: var(--tinta); color: #fff;
    }
    .kfd-lacak-isian button:hover { background: #23365c; }

    /* ========================================================= sesi */
    .kfd-sesi { display: grid; gap: 14px; align-content: start; }

    .kfd-baris {
        display: grid; grid-template-columns: 76px minmax(0, 1fr) auto; gap: 18px;
        align-items: center; padding: 18px 20px;
        background: #fff; border: 2px solid var(--tinta); border-radius: 18px;
        box-shadow: 5px 5px 0 rgba(22, 35, 63, .12);
    }
    .kfd-jam {
        display: grid; place-items: center; gap: 2px; padding: 10px 6px;
        background: var(--jingga); color: #fff; border-radius: 12px;
    }
    .kfd-jam-angka { font-family: 'Fraunces', Georgia, serif; font-size: 1.25rem; font-weight: 800; }
    .kfd-jam-label { font-size: .7rem; letter-spacing: .06em; text-transform: uppercase; opacity: .9; }

    .kfd-nama { font-size: 1.12rem; margin-bottom: 5px; }
    .kfd-ket { font-size: .9rem; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .kfd-pisah { opacity: .5; }

    .kfd-kanan { display: grid; justify-items: end; gap: 2px; }
    .kfd-harga { font-family: 'Fraunces', Georgia, serif; font-size: 1.2rem; font-weight: 800; color: var(--rumput); }
    .kfd-harga-ket { font-size: .78rem; color: var(--tinta-2); }

    .kfd-tombol {
        display: inline-flex; align-items: center; justify-content: center; gap: 9px;
        margin-top: 8px; padding: 14px 24px;
        background: var(--jingga); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 4px 4px 0 var(--tinta); font-weight: 800;
    }
    .kfd-tombol:hover { color: #fff; background: var(--tinta); }
    .kfd-tombol.mati { background: var(--tinta-2); box-shadow: none; cursor: not-allowed; }

    .kfd-kosong {
        padding: 48px 24px; text-align: center;
        background: #fff; border: 2px dashed var(--garis); border-radius: 22px;
    }
    .kfd-kosong i { font-size: 2.4rem; color: var(--tinta-2); }
    .kfd-kosong h2 { margin: 14px 0 9px; font-size: 1.3rem; }

    @media (max-width: 992px) {
        .kfd-kisi { grid-template-columns: 1fr; gap: 30px; }
    }
    @media (max-width: 600px) {
        .kfd-kepala { padding: 104px 0 28px; }
        .kfd-baris { grid-template-columns: 62px minmax(0, 1fr); gap: 14px; padding: 15px 16px; }
        .kfd-kanan { grid-column: 1 / -1; justify-items: flex-start; padding-top: 11px; border-top: 2px dashed rgba(22, 35, 63, .14); }
    }
    @media (min-width: 576px) and (max-width: 992px) {
        .kfd-lacak-isian { grid-template-columns: minmax(0, 1fr) auto; }
        .kfd-lacak-isian button { width: auto; white-space: nowrap; }
    }
</style>
