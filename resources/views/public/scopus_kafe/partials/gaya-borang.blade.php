{{--
  Gaya borang Scopus Kafe.

  Bahasa rupanya SAMA dengan Scopus Camp — kertas, tinta, jingga, Fraunces +
  Figtree — supaya orang yang memakai dua layanan tidak mengira salah satunya
  bukan milik Rumah Scopus. Yang berbeda cuma isinya: di sini tidak ada
  angkatan, yang dipilih tanggal dan sesi.
--}}
<style>
    .kfb {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --rumput: #0f766e;
        --garis: #e7dcc6;   --merah: #a3281c;

        background: var(--kertas); color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        padding: 108px 0 72px; min-height: 86vh; overflow-x: clip;
    }
    .kfb h1, .kfb h2 {
        font-family: 'Fraunces', Georgia, serif; font-weight: 800;
        color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .kfb p { margin: 0; color: var(--tinta-2); line-height: 1.7; }

    .kfb-kabar {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 14px 18px; margin: 0 0 20px; border-radius: 14px;
        font-size: 15px; line-height: 1.6; border: 1px solid;
    }
    .kfb-kabar i { flex: 0 0 auto; margin-top: 3px; font-size: 18px; }
    .kfb-kabar.sukses { background: #eafaf6; border-color: #a7e3d6; color: #0b5a52; }
    .kfb-kabar.galat  { background: #fdecea; border-color: #f0bdb6; color: #8e2318; }

    /* ===================================== bobot pemilihnya, bukan isinya

       Aturan reset di atas — `.kfb h1, .kfb h2 { margin: 0 }` dan
       `.kfb p { margin: 0 }` — berbobot (0,1,1) karena memuat kelas DAN
       unsur. Pemilih satu kelas seperti `.kfb-judul` cuma (0,1,0), jadi
       jarak yang ditulis di sana KALAH dan tidak pernah berlaku.

       Terukur dari potretnya: tiap teks bantu menempel ke isian di atasnya,
       dan judul halaman bersentuhan dengan paragrafnya. Tidak ada galat, dan
       dari kodenya pun tidak kelihatan — jaraknya memang tertulis. */
    .kfb .kfb-kepala { max-width: 680px; margin: 0 auto 30px; text-align: center; }
    .kfb .kfb-judul { font-size: clamp(1.7rem, 4vw, 2.4rem); margin-bottom: 12px; }
    .kfb .kfb-sub { font-size: 1.02rem; max-width: 60ch; margin: 0 auto; }

    /* Dua kartu berdampingan di layar lebar, menumpuk di layar sempit.
       minmax(0, 1fr): tanpa nol, isian tanggal yang punya lebar bawaan
       sendiri memaksa lajurnya melebar dan kisinya meluber. */
    .kfb-kisi {
        display: grid; gap: 20px; margin-bottom: 20px;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 330px), 1fr));
    }

    .kfb-kartu {
        background: #fff; border: 1px solid var(--garis); border-radius: 22px;
        padding: 24px 22px; box-shadow: 0 10px 28px rgba(22, 35, 63, .06);
    }
    .kfb-kartu-judul {
        display: flex; align-items: center; gap: 10px;
        font-size: 1.12rem; margin-bottom: 18px;
    }
    .kfb-kartu-judul i { color: var(--jingga); }

    .kfb-label {
        display: block; font-size: .82rem; font-weight: 800;
        letter-spacing: .03em; text-transform: uppercase;
        color: var(--tinta-2); margin: 20px 0 7px;
    }
    /* Label PERTAMA di tiap kartu, bukan ":first-of-type".

       :first-of-type menghitung per NAMA UNSUR. Label-label di sini <label>,
       kecuali "Pilih sesi" yang <span> karena ia menamai sekelompok kotak
       centang, bukan satu isian — jadi ia satu-satunya span dan otomatis
       jadi "yang pertama", lalu kehilangan jarak atasnya dan menempel ke
       teks bantu di atasnya. Terukur dari potretnya. */
    .kfb .kfb-kartu-judul + .kfb-label { margin-top: 0; }

    /* Bintang merah HANYA untuk yang validatornya memang required; dipasang
       di medan yang sebenarnya boleh kosong, ia mengajari orang mengabaikan
       bintangnya. */
    .kfb-wajib { color: var(--merah); }

    .kfb-isian {
        width: 100%; padding: 12px 14px; font-size: 1rem;
        border: 1px solid #ddd0b8; border-radius: 12px;
        background: #fdfcf9; color: var(--tinta);
    }
    .kfb-isian:focus { outline: 2px solid var(--jingga); outline-offset: 1px; background: #fff; }
    .kfb .kfb-bantu { font-size: .86rem; margin-top: 8px; }
    .kfb .kfb-bantu.galat { color: var(--merah); font-weight: 700; }

    /* ============================================================ sesi */
    .kfb-sesi { display: grid; gap: 10px; }

    .kfb-sesi-butir {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 16px; border: 2px solid var(--garis); border-radius: 14px;
        background: #fdfcf9; cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }
    .kfb-sesi-butir:hover { border-color: #d9c9a8; }
    .kfb-sesi-butir.dipilih { border-color: var(--jingga); background: #fff7ef; }
    .kfb-sesi-butir input { flex: 0 0 auto; width: 20px; height: 20px; accent-color: var(--jingga); }

    .kfb-sesi-isi { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
    .kfb-sesi-isi strong { font-size: 1rem; }
    .kfb-sesi-jam { font-size: .85rem; color: var(--tinta-2); }
    .kfb-sesi-harga { flex: 0 0 auto; font-weight: 800; font-size: .95rem; }

    /* ======================================================== ringkasan */
    .kfb-ringkas {
        background: #fff; border: 1px solid var(--garis); border-radius: 22px;
        padding: 22px; box-shadow: 0 10px 28px rgba(22, 35, 63, .06);
    }
    .kfb-ringkas-baris {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 16px; padding-bottom: 12px; border-bottom: 2px dashed var(--garis);
    }
    .kfb-ringkas-baris span { font-weight: 700; }
    .kfb-ringkas-baris strong {
        font-family: 'Fraunces', Georgia, serif; font-size: 1.6rem;
        color: var(--rumput);
    }

    .kfb-setuju {
        display: flex; gap: 11px; align-items: flex-start;
        margin: 18px 0 16px; font-size: .95rem; line-height: 1.6; cursor: pointer;
    }
    .kfb-setuju input { flex: 0 0 auto; margin-top: 3px; width: 18px; height: 18px; accent-color: var(--jingga); }

    .kfb-kirim {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; min-height: 58px; border: 0; border-radius: 16px; cursor: pointer;
        background: linear-gradient(135deg, var(--jingga-muda), var(--jingga-tua));
        color: #fff; font-family: 'Fraunces', Georgia, serif;
        font-size: 1.08rem; font-weight: 800;
        box-shadow: 0 10px 24px rgba(231, 109, 32, .3);
        transition: transform .18s ease;
    }
    .kfb-kirim:hover { transform: translateY(-2px); color: #fff; }
    .kfb-kirim[disabled] { opacity: .55; cursor: not-allowed; transform: none; }

    .kfb-aman {
        display: flex; align-items: flex-start; gap: 10px;
        margin-top: 14px; font-size: .9rem; line-height: 1.6; color: var(--tinta-2);
    }
    .kfb-aman i { flex: 0 0 auto; margin-top: 3px; color: var(--jingga); font-size: 1rem; }

    @media (max-width: 575.98px) {
        .kfb { padding-top: 96px; }
        .kfb-kartu, .kfb-ringkas { padding: 18px 16px; border-radius: 18px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .kfb *, .kfb *::before, .kfb *::after { transition: none !important; }
    }
</style>
