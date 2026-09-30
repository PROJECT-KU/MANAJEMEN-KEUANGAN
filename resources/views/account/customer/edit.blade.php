@extends('layouts.account')
@extends('layouts.loader')

@section('title')
{{ $user->full_name ?: $user->username }} | Data Pelanggan
@stop

{{-- Pemberitahuan lewat toast bersama, bukan kotak alert Bootstrap. --}}
@include('partials.toast-flash')

@push('gaya')
<style>
    /*
     * Sekali lagi memakai bahasa rupa bersama: .mis-bagian, .mis-medali,
     * .mis-pil, .mis-tombol. Yang ditulis di sini hanya yang khas layar ini.
     */

    .pel-tata {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 14px;
        /* stretch, bukan start: kartu identitas ikut setinggi kartu bertab di
           sebelahnya, jadi tidak ada sisi yang berhenti di tengah sementara
           sisi lain masih panjang. */
        align-items: stretch;
    }

    .pel-identitas,
    .pel-kanan {
        /* Tingginya boleh melebihi isinya; isinya sendiri diatur di bawah. */
        min-height: 0;
    }

    .pel-identitas {
        display: flex;
        flex-direction: column;
    }

    /* Pengunggah foto didorong ke dasar kartu, jadi ruang lebih apa pun jatuh
       di antara ringkasan dan pengunggahnya — bukan menggantung di bawah. */
    .pel-unggah {
        margin-top: auto;
    }

    /* ----------------------------------------------------- ringkasan */

    .pel-ringkasan {
        margin: 14px 0 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
        text-align: left;
    }

    .pel-ringkasan > div {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 12px;
        border: 1px solid var(--mis-garis);
        border-radius: 12px;
        background: #f8fafc;
    }

    .pel-ringkasan dt {
        margin: 0;
        line-height: 1.4;
        font-size: .74rem;
        font-weight: 600;
        color: var(--mis-tinta-3);
    }

    .pel-ringkasan dd {
        margin: 0;
        line-height: 1.4;
        font-size: .8rem;
        font-weight: 800;
        color: var(--mis-tinta);
        text-align: right;
        white-space: nowrap;
    }

    /* --------------------------------------------------- kartu identitas */

    .pel-identitas {
        text-align: center;
    }

    /* Bingkai avatar: sudutnya lebih besar daripada avatar daftar karena
       ukurannya jauh lebih besar; radius tetap akan terlihat kaku di 104px. */
    .pel-identitas-avatar > .mis-avatar {
        border-radius: 28px;
        border: 3px solid #fff;
        box-shadow: 0 10px 24px -14px rgba(15, 23, 42, .5);
    }

    /*
     * Keadaan kosong yang ringkas.
     *
     * .mis-kosong bawaan dirancang untuk layar yang SELURUHNYA kosong, jadi
     * bantalannya lebar. Di dalam kartu sekunder seperti Riwayat pesanan dan
     * Jejak perubahan — yang memang kosong pada sebagian besar pelanggan — ia
     * memakan sekitar 400px untuk menyampaikan satu kalimat.
     */
    .pel-kosong-ringkas {
        padding: 18px 12px;
    }

    .pel-kosong-ringkas .mis-kosong-ikon {
        width: 40px;
        height: 40px;
        margin-bottom: 8px;
    }

    .pel-kosong-ringkas .mis-kosong-ikon > .fas {
        font-size: 1rem !important;
    }

    .pel-kosong-ringkas .mis-kosong-judul {
        font-size: .86rem;
    }

    .pel-kosong-ringkas .mis-kosong-teks {
        margin-top: 2px;
        line-height: 1.45;
        font-size: .78rem;
    }

    /* Tombol hubungi di kartu email: selebar isinya, tidak ikut melar. */
    .pel-hubungi {
        flex: 0 0 auto;
    }

    /* ---------------------------------------------- daftar berbaris */

    .pel-pesanan {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .pel-pesanan-baris {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 12px;
        border: 1px solid var(--mis-garis);
        border-radius: 13px;
        background: #f8fafc;
    }

    .pel-pesanan-teks {
        flex: 1 1 auto;
        min-width: 0;
    }

    .pel-pesanan-layanan {
        margin: 0;
        line-height: 1.3;
        font-size: .82rem;
        font-weight: 700;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .pel-pesanan-ket {
        margin: 1px 0 0;
        line-height: 1.45;
        font-size: .73rem;
        color: var(--mis-tinta-3);
        overflow-wrap: anywhere;
    }

    .pel-pesanan-kanan {
        flex: 0 0 auto;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }

    .pel-pesanan-nilai {
        margin: 0;
        line-height: 1.3;
        font-size: .82rem;
        font-weight: 800;
        color: var(--mis-tinta);
        white-space: nowrap;
    }

    .pel-identitas-nama {
        margin: 12px 0 0;
        line-height: 1.25;
        font-size: 1.02rem;
        font-weight: 800;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .pel-identitas-akun {
        margin: 2px 0 0;
        line-height: 1.45;
        font-size: .78rem;
        color: var(--mis-tinta-3);
        overflow-wrap: anywhere;
    }

    .pel-lencana-deret {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
        margin-top: 12px;
    }

    /* Unggah foto: dipisah garis supaya tidak terbaca sebagai bagian nama. */
    .pel-unggah {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed var(--mis-garis);
        text-align: left;
    }

    /* ---------------------------------------------------- bagian isian */

    .pel-bagian + .pel-bagian {
        margin-top: 14px;
    }

    .pel-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px;
        row-gap: 2px;
        align-items: center;
        margin-bottom: 12px;
    }

    .pel-kepala > .mis-medali {
        grid-row: 1 / span 2;
        align-self: center;
    }

    .pel-kepala-judul {
        grid-column: 2;
        grid-row: 1;
        margin: 0;
        line-height: 1.25;
        font-size: .92rem;
        font-weight: 800;
        color: var(--mis-tinta);
    }

    .pel-kepala-sub {
        grid-column: 2;
        grid-row: 2;
        margin: 0;
        /* Angka relatif, bukan warisan line-height 28px mutlak dari layout —
           kalimat yang membungkus dua baris jadi merenggang seperti daftar. */
        line-height: 1.45;
        /* .75rem, menyamai .prof-bagian-sub di halaman profil (12px). */
        font-size: .75rem;
        color: var(--mis-tinta-3);
    }

    /*
     * Baris tombol mengikuti .prof-aksi di halaman profil: keterangan di kiri,
     * tombol di kanan.
     *
     * TANPA garis putus-putus di atasnya. Garis itu dulu memang ada, tetapi
     * dibuang dari profil begitu tiap bagian jadi kartu bersudut sendiri —
     * tepi kartunya sudah memisahkan, dan garis kedua di dalamnya hanya
     * menambah coretan. Terukur di profil: padding-top 0, border none.
     */
    .pel-baris-aksi {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
    }

    .pel-aksi-catatan {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        /* Angka relatif: layout mewariskan line-height 28px MUTLAK, yang pada
           huruf sekecil ini merenggangkan barisnya seperti daftar. */
        line-height: 1.5;
        /* .74rem / 400 / #94a3b8 — menyamai .prof-aksi-catatan di profil. */
        font-size: .74rem;
        font-weight: 400;
        color: #94a3b8;
    }

    .pel-aksi-catatan > .fas {
        font-size: inherit !important;
        line-height: 1;
        flex: 0 0 auto;
    }

    /* Baris email: nilai di kiri, tombol verifikasi di kanan. */
    .pel-email {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        padding: 11px 13px;
        border: 1px solid var(--mis-garis);
        border-radius: 13px;
        background: #f8fafc;
    }

    .pel-email-teks {
        flex: 1 1 auto;
        min-width: 0;
    }

    .pel-email-nilai {
        margin: 0;
        line-height: 1.35;
        font-size: .84rem;
        font-weight: 700;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .pel-email-ket {
        margin: 1px 0 0;
        line-height: 1.45;
        font-size: .74rem;
        color: var(--mis-tinta-3);
    }

    /* ------------------------------------------------------- responsif */

    @media (max-width: 991.98px) {
        .pel-tata {
            grid-template-columns: minmax(0, 1fr);
            align-items: start;
        }

        /* Menumpuk, tidak ada tinggi yang perlu disamakan — jadi pengunggahnya
           kembali menempel pada isinya. */
        .pel-unggah {
            margin-top: 14px;
        }
    }

    @media (max-width: 767.98px) {
        .pel-baris-aksi {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .pel-aksi-catatan {
            justify-content: center;
            text-align: center;
        }

        .pel-baris-aksi .mis-tombol {
            width: 100%;
        }

        .pel-email {
            flex-wrap: wrap;
        }

        .pel-email .mis-tombol {
            width: 100%;
        }

        /* Nilai dan lencana turun ke bawah teksnya: dipertahankan di kanan,
           kolom teksnya tinggal sekitar 120px dan nama layanan pecah tiap
           kata. */
        .pel-pesanan-baris {
            flex-wrap: wrap;
        }

        /*
         * Baris kedua dibaca seperti baris struk: nilainya di kiri, keadaannya
         * di kanan, dipisah garis tipis dari nama layanannya.
         *
         * Sebelumnya keduanya dirapatkan ke kiri di belakang jorokan 39px,
         * sehingga tampak menggantung — tidak sejajar dengan apa pun, dan
         * separuh lebar barisnya kosong.
         */
        .pel-pesanan-kanan {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            width: 100%;
            margin-top: 8px;
            padding-top: 8px;
            padding-left: 39px;
            border-top: 1px dashed var(--mis-garis);

            /*
             * Boleh pecah dua baris.
             *
             * .mis-pil berwatak white-space: nowrap, jadi lencana keadaan tidak
             * pernah menyusut. Terukur di 320px: nilai (95px) + jarak + lencana
             * terpanjang (158px) menuntut 261px, sementara barisnya cuma
             * menyediakan 230px — lencananya meluber 74px keluar kartu. Tanpa
             * wrap tidak ada jalan keluar: yang tersisa hanya memotong
             * tulisannya, dan status yang terpotong justru yang paling perlu
             * dibaca utuh.
             */
            flex-wrap: wrap;
        }

        /* Saat lencananya turun sendirian ke baris kedua, ia tetap rata kanan —
           lurus dengan nilai di atasnya, bukan menggantung di kiri. Pada satu
           baris aturan ini tidak berpengaruh: space-between sudah mendorongnya
           ke kanan. */
        .pel-pesanan-kanan > .mis-pil {
            margin-left: auto;
        }

        /* Jorokan 39px = ubin 27px + jarak 11px, jadi garis dan nilainya lurus
           dengan nama layanan di atasnya, bukan dengan tepi kartunya. */
        .pel-pesanan-nilai {
            font-size: .86rem;
        }
    }

    /* Di bawah 360px jorokan 39px itu tidak lagi terbayar: terukur di 320px
       lencana keadaannya meluber 7px keluar baris. Barisnya dipakai penuh,
       dan kelurusannya dengan nama layanan dikorbankan. */
    @media (max-width: 359.98px) {
        .pel-pesanan-kanan {
            padding-left: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-user-circle"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">{{ $user->full_name ?: $user->username }}</h1>
                <p class="mis-sub">Data pelanggan dan pengaturan akunnya.</p>
            </div>
            <div class="mis-kepala-aksi">
                <a href="{{ route('account.customer.index') }}" class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="pel-tata">

            {{-- ============================================ kolom kiri --}}
            <aside class="mis-bagian pel-identitas">
                <div class="pel-identitas-avatar">
                    @include('partials.avatar', ['orang' => $user, 'ukuran' => 104, 'lencana' => true])
                </div>

                <p class="pel-identitas-nama">{{ $user->full_name ?: $user->username }}</p>
                <p class="pel-identitas-akun">&#64;{{ $user->username }}</p>

                <div class="pel-lencana-deret">
                    {{-- Titik berdenyut, bukan ikon centang: keadaannya terbaca
                         sekilas tanpa membaca tulisannya dulu. Denyutnya hanya
                         untuk akun yang aktif — keadaan yang perlu ditindak
                         dibiarkan diam supaya tidak terasa seperti alarm. --}}
                    @if ($user->status === 'active')
                        <span class="mis-lencana mis-lencana-hijau">
                            <span class="mis-lencana-titik berdenyut" aria-hidden="true"></span>
                            Akun aktif
                        </span>
                    @else
                        <span class="mis-lencana mis-lencana-abu">
                            <span class="mis-lencana-titik" aria-hidden="true"></span>
                            Akun nonaktif
                        </span>
                    @endif

                    {{-- Keadaan verifikasi email tidak diulang sebagai pil:
                         centang bergerigi di sudut foto sudah menyatakannya,
                         hijau kalau sudah dan kuning kalau belum. --}}

                </div>

                {{--
                  Ringkasan tiga angka yang paling sering ditanyakan.

                  Kolom kiri sebelumnya cuma berisi foto dan pengunggahnya,
                  sehingga separuh tingginya kosong sementara kolom kanan penuh.
                  Ketiganya juga menghemat satu klik: jumlah dan tanggal pesanan
                  tidak perlu membuka tab Pesanan dulu.
                --}}
                <dl class="pel-ringkasan">
                    <div>
                        <dt>Bergabung</dt>
                        <dd>{{ optional($user->created_at)->locale('id')->translatedFormat('d M Y') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>Jumlah pesanan</dt>
                        <dd>{{ $pesanan->count() > 0 ? $pesanan->count() . '×' : 'Belum ada' }}</dd>
                    </div>
                    <div>
                        <dt>Terakhir memesan</dt>
                        <dd>
                            @php ($terakhir = $pesanan->first()['waktu'] ?? null)
                            {{ $terakhir ? $terakhir->locale('id')->translatedFormat('d M Y') : '—' }}
                        </dd>
                    </div>
                </dl>

                {{-- Pemilih berkas bergaya, bukan <input type=file> bawaan:
                     yang bawaan bertuliskan "Choose file / No file chosen"
                     dalam bahasa Inggris dan tidak bisa digayakan sama sekali.
                     Tombol simpannya disembunyikan sampai ada berkas dipilih —
                     tombol mati yang selalu terlihat hanya memakan ruang dan
                     tidak menerangkan apa pun. --}}
                @if ($bolehUbah)
                <form class="pel-unggah mis-unggah-bungkus" method="POST" enctype="multipart/form-data"
                    action="{{ route('account.pengguna.update.updatePhoto', $user) }}"
                    data-sibuk data-sibuk-teks="Mengunggah…">
                    @csrf
                    <input type="file" class="mis-berkas" id="pel-foto" name="gambar"
                        accept="image/jpeg,image/png,image/gif,image/webp">
                    <label class="mis-unggah" for="pel-foto">
                        <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-camera"></i></span>
                        <span class="mis-unggah-nama" id="pel-nama-berkas">Pilih foto baru</span>
                        <span class="mis-bantuan">JPG, PNG, GIF, atau WebP &middot; maksimal 3 MB</span>
                    </label>
                    <button type="submit" class="mis-tombol mis-tombol-ungu" id="pel-simpan-foto"
                        style="width: 100%;" hidden disabled>
                        <i class="fas fa-cloud-upload-alt"></i> Simpan foto
                    </button>
                </form>
                @endif
            </aside>

            {{-- =========================================== kolom kanan --}}
            <div class="mis-kartu mis-tab-kartu pel-kanan">
                <div class="mis-tab-kepala">
                    {{-- Satu kepala untuk seluruh kartu, seperti "Pengaturan
                         akun" di halaman profil — bukan kepala berulang di tiap
                         panel, yang membuat judulnya terbaca dua kali. --}}
                    <div class="pel-kepala">
                        <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-user-cog"></i></span>
                        <h2 class="pel-kepala-judul">Pengaturan akun</h2>
                        <p class="pel-kepala-sub">Data diri, kontak, riwayat pesanan, dan jejak perubahannya.</p>
                    </div>

                {{--
                  Empat urusan dipisah jadi tab, tidak ditumpuk.

                  Ditumpuk, kolom kanan setinggi 1105px sementara kolom kiri
                  hanya 409px — 696px petak kosong di kiri, dan halamannya
                  harus digulir 477px hanya untuk melihat kartu terakhir.
                  Halaman profil memecahkan hal yang sama dengan tab, dan ini
                  memakai deret tab bersama yang sama.

                  aria-selected dan aria-controls WAJIB ada: role="tab" saja
                  hanya memberi tahu pembaca layar bahwa ini deretan tab —
                  bukan tab mana yang terbuka, dan bukan panel mana yang
                  dikendalikannya. Kelas .active cuma rupa; ia tidak terbaca.
                --}}
                <ul class="mis-tab nav nav-pills" id="pel-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="pel-tab-akun" data-toggle="pill" href="#pel-panel-akun"
                            role="tab" aria-controls="pel-panel-akun" aria-selected="true">
                            <i class="fas fa-id-card mis-ikon-biru" aria-hidden="true"></i> Data akun
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="pel-tab-kontak" data-toggle="pill" href="#pel-panel-kontak"
                            role="tab" aria-controls="pel-panel-kontak" aria-selected="false">
                            <i class="fas fa-address-book mis-ikon-hijau" aria-hidden="true"></i> Kontak
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="pel-tab-pesanan" data-toggle="pill" href="#pel-panel-pesanan"
                            role="tab" aria-controls="pel-panel-pesanan" aria-selected="false">
                            {{-- Tanpa angka jumlah: ia sudah tertulis dua kali di
                                 layar yang sama — di ringkasan kolom kiri dan di
                                 dalam panelnya — dan lencana di dalam tab membuat
                                 deretnya lebih tinggi daripada deret tab di
                                 halaman profil. --}}
                            <i class="fas fa-receipt mis-ikon-jingga" aria-hidden="true"></i> Pesanan
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="pel-tab-jejak" data-toggle="pill" href="#pel-panel-jejak"
                            role="tab" aria-controls="pel-panel-jejak" aria-selected="false">
                            <i class="fas fa-history mis-ikon-ungu" aria-hidden="true"></i> Jejak
                        </a>
                    </li>
                </ul>

                </div>

                <div class="mis-tab-isi">
                <div class="tab-content">

                {{-- ------------------------------------- data akun --}}
                <div class="tab-pane fade show active" id="pel-panel-akun" role="tabpanel"
                    aria-labelledby="pel-tab-akun" tabindex="0">
                <section class="mis-bagian pel-bagian">
                    {{-- Kepala bagian tetap ada walau kartunya sudah berkepala,
                         sama seperti di halaman profil: kepala kartu menamai
                         seluruh pengaturan, kepala bagian menamai satu urusan
                         di dalamnya. Sempat dibuang karena dikira mengulang —
                         padahal di profil keduanya memang berdampingan. --}}
                    <div class="pel-kepala">
                        <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-id-card"></i></span>
                        <h3 class="pel-kepala-judul">Data akun</h3>
                        <p class="pel-kepala-sub">Nama, username, dan keadaan akunnya.</p>
                    </div>

                    <form method="POST" action="{{ route('account.pengguna.update', $user) }}">
                        @csrf
                        <div class="mis-kisi-isian">
                            <div class="mis-isian">
                                <label class="mis-label" for="pel-nama">Nama lengkap</label>
                                <input type="text" @disabled(! $bolehUbah) class="form-control-modern @error('full_name') is-invalid @enderror"
                                    id="pel-nama" name="full_name"
                                    value="{{ old('full_name', $user->full_name) }}" maxlength="255">
                                @error('full_name')
                                    <p class="mis-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mis-isian">
                                <label class="mis-label" for="pel-username">Username</label>
                                <input type="text" @disabled(! $bolehUbah) class="form-control-modern @error('username') is-invalid @enderror"
                                    id="pel-username" name="username"
                                    value="{{ old('username', $user->username) }}" maxlength="150">
                                @error('username')
                                    <p class="mis-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @else
                                    <p class="mis-bantuan">Dipakai untuk masuk, jadi harus unik.</p>
                                @enderror
                            </div>

                            <div class="mis-isian">
                                <label class="mis-label" for="pel-status">Status akun</label>
                                <select @disabled(! $bolehUbah) class="form-control-modern" id="pel-status" name="status">
                                    <option value="active" @selected($user->status === 'active')>Aktif</option>
                                    <option value="non active" @selected($user->status !== 'active')>Nonaktif</option>
                                </select>
                                <p class="mis-bantuan">Akun nonaktif tidak bisa masuk.</p>
                            </div>

                            <div class="mis-isian">
                                <label class="mis-label" for="pel-jenis">Jenis akun</label>
                                <select @disabled(! $bolehUbah) class="form-control-modern" id="pel-jenis" name="jenis">
                                    <option value="perorangan" @selected($user->jenis === 'perorangan')>Perorangan</option>
                                    <option value="perusahaan" @selected($user->jenis === 'perusahaan')>Perusahaan</option>
                                </select>
                            </div>

                            {{--
                              Peran ditampilkan, bukan diketik.

                              Ia memang tidak boleh diubah dari layar data pelanggan —
                              menaikkan seseorang jadi karyawan atau administrator itu
                              urusan pengelolaan pengguna. Ditampilkan begini, alasannya
                              terbaca langsung; disembunyikan, baris kedua menyisakan
                              dua sel kosong di samping Jenis akun dan orang tetap tidak
                              tahu peran akun yang sedang dibukanya. Polanya sama dengan
                              Nama bank di halaman profil.
                            --}}
                            <div class="mis-isian">
                                <label class="mis-label"><i class="fas fa-lock"></i> Peran</label>
                                <div class="mis-statis">
                                    <span class="mis-medali mini mis-ungu" aria-hidden="true"><i class="fas fa-user-shield"></i></span>
                                    <div>
                                        <p class="mis-statis-label">Ditetapkan pengelola</p>
                                        <p class="mis-statis-nilai">{{ $user->peranTerbaca() }}</p>
                                    </div>
                                </div>
                                {{-- Petunjuknya dulu berbunyi "Diubah lewat halaman
                                     pengelolaan pengguna" — dan itu jalan buntu:
                                     halaman itu menyaring level IN (staff, karyawan,
                                     trainer, manager, ceo), sedangkan pelanggan
                                     ber-level user, jadi ia tidak pernah muncul di
                                     sana. Menunjuk tempat yang tidak memuat orangnya
                                     lebih buruk daripada tidak menunjuk apa-apa. --}}
                                <p class="mis-bantuan">Peran tidak bisa diubah dari layar ini.</p>
                            </div>

                            {{-- Sel ketiga baris kedua. Diisi tanggal perubahan
                                 terakhir, bukan diulang dari kolom kiri: ia
                                 satu-satunya keterangan di kartu ini yang belum
                                 ada di mana pun, dan sejalan dengan tab Jejak di
                                 sebelahnya. --}}
                            <div class="mis-isian">
                                <label class="mis-label"><i class="fas fa-lock"></i> Terakhir diubah</label>
                                <div class="mis-statis">
                                    <span class="mis-medali mini mis-biru" aria-hidden="true"><i class="fas fa-clock"></i></span>
                                    <div>
                                        <p class="mis-statis-label">Perubahan terakhir</p>
                                        <p class="mis-statis-nilai">
                                            {{ optional($user->updated_at)->locale('id')->translatedFormat('d M Y, H:i') ?: 'Belum pernah' }}
                                        </p>
                                    </div>
                                </div>
                                <p class="mis-bantuan">Rinciannya ada di tab Jejak.</p>
                            </div>
                        </div>

                        {{-- Peran TIDAK bisa diubah dari layar ini.

                             Ini halaman data pelanggan; menaikkan seseorang jadi
                             karyawan atau administrator adalah urusan pengelolaan
                             pengguna, dan menaruh kendalinya di sini membuat satu
                             salah klik mengubah hak akses tanpa disengaja. --}}
                        <div class="pel-baris-aksi">
                            {{-- Kenapa isiannya mati disebutkan di tempat tombol simpan
                                 seharusnya berada. Sebelum ini karyawan melihat borang
                                 yang tampak bisa diisi, mengetik, menekan Simpan, lalu
                                 ditolak 403 — pemberitahuan sesudah orang bekerja. --}}
                            <p class="pel-aksi-catatan">
                                @if ($bolehUbah)
                                    <i class="fas fa-info-circle mis-ikon-ungu"></i>
                                    Perubahan tercatat di tab Jejak beserta nama Anda.
                                @else
                                    <i class="fas fa-lock mis-ikon-kuning"></i>
                                    Hanya administrator yang boleh mengubah data pelanggan.
                                @endif
                            </p>
                            @if ($bolehUbah)
                                <button type="submit" class="mis-tombol mis-tombol-ungu">
                                    <i class="fas fa-save"></i> Simpan perubahan
                                </button>
                            @endif
                        </div>
                    </form>
                </section>

                </div>

                {{-- --------------------------------------- kontak --}}
                <div class="tab-pane fade" id="pel-panel-kontak" role="tabpanel"
                    aria-labelledby="pel-tab-kontak" tabindex="0">
                <section class="mis-bagian pel-bagian">
                    <div class="pel-kepala">
                        <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-address-book"></i></span>
                        <h3 class="pel-kepala-judul">Kontak</h3>
                        <p class="pel-kepala-sub">Cara menghubungi pelanggan ini.</p>
                    </div>

                    <div class="pel-email">
                        <span class="mis-medali kecil {{ $user->email_verified_at ? 'mis-hijau' : 'mis-kuning' }}" aria-hidden="true">
                            <i class="fas {{ $user->email_verified_at ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                        </span>
                        <div class="pel-email-teks">
                            <p class="pel-email-nilai">{{ $user->email }}</p>
                            <p class="pel-email-ket">
                                {{ $user->email_verified_at
                                    ? 'Terverifikasi ' . $user->email_verified_at->locale('id')->translatedFormat('d M Y')
                                    : 'Belum diverifikasi.' }}
                            </p>
                        </div>

                        {{-- Kartu ini judulnya "Cara menghubungi pelanggan ini",
                             jadi ia menyediakan caranya — bukan hanya
                             menampilkan alamatnya untuk disalin manual. --}}
                        <a class="mis-tombol mis-tombol-halus pel-hubungi" href="mailto:{{ $user->email }}"
                            title="Kirim email ke {{ $user->email }}">
                            <i class="fas fa-paper-plane mis-ikon-biru"></i> Email
                        </a>

                        @php ($wa = \App\Support\PesananPelanggan::nomorWa($user->telp))
                        @if ($wa)
                            <a class="mis-tombol mis-tombol-halus pel-hubungi" target="_blank" rel="noopener"
                                href="https://wa.me/{{ $wa }}" title="Hubungi {{ $user->telp }} lewat WhatsApp">
                                <i class="fab fa-whatsapp mis-ikon-hijau"></i> WhatsApp
                            </a>
                        @endif

                        @if ($bolehUbah && ! $user->email_verified_at)
                            <button type="button" class="mis-tombol mis-tombol-hijau" id="pel-tombol-verifikasi">
                                <i class="fas fa-check-circle"></i> Tandai terverifikasi
                            </button>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('account.pengguna.update.datadiri', $user) }}" class="mt-3">
                        @csrf
                        <div class="mis-kisi-isian">
                            <div class="mis-isian">
                                <label class="mis-label" for="pel-email-baru">Alamat email</label>
                                <input type="email" @disabled(! $bolehUbah) class="form-control-modern @error('email') is-invalid @enderror"
                                    id="pel-email-baru" name="email"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <p class="mis-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mis-isian">
                                <label class="mis-label" for="pel-telp">Nomor WhatsApp</label>
                                <input type="tel" @disabled(! $bolehUbah) class="form-control-modern @error('telp') is-invalid @enderror"
                                    id="pel-telp" name="telp"
                                    value="{{ old('telp', $user->telp) }}" inputmode="numeric"
                                    placeholder="08xxxxxxxxxx">
                                @error('telp')
                                    <p class="mis-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Satu tombol untuk email dan telepon sekaligus: keduanya
                             dikirim ke alamat yang sama, dan dulu dipecah jadi dua
                             formulir terpisah berisi satu isian masing-masing. --}}
                        <div class="pel-baris-aksi">
                            <p class="pel-aksi-catatan">
                                @if ($bolehUbah)
                                    <i class="fas fa-shield-alt mis-ikon-hijau"></i>
                                    Mengganti email membuat verifikasinya kembali kosong.
                                @else
                                    <i class="fas fa-lock mis-ikon-kuning"></i>
                                    Hanya administrator yang boleh mengubah data pelanggan.
                                @endif
                            </p>
                            @if ($bolehUbah)
                                <button type="submit" class="mis-tombol mis-tombol-ungu">
                                    <i class="fas fa-save"></i> Simpan kontak
                                </button>
                            @endif
                        </div>
                    </form>
                </section>

                </div>

                {{-- ------------------------------ riwayat pesanan --}}
                <div class="tab-pane fade" id="pel-panel-pesanan" role="tabpanel"
                    aria-labelledby="pel-tab-pesanan" tabindex="0">
                <section class="mis-bagian pel-bagian">
                    <div class="pel-kepala">
                        <span class="mis-medali kecil mis-jingga" aria-hidden="true"><i class="fas fa-receipt"></i></span>
                        <h3 class="pel-kepala-judul">Riwayat pesanan</h3>
                        <p class="pel-kepala-sub">Layanan yang pernah dipesan orang ini.</p>
                    </div>

                    @if ($pesanan->isEmpty())
                        <div class="mis-kosong pel-kosong-ringkas">
                            <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-receipt"></i></span>
                            <p class="mis-kosong-judul">Belum ada pesanan</p>
                            <p class="mis-kosong-teks">Pesanannya muncul di sini setelah ia memesan layanan.</p>
                        </div>
                    @else
                        <div class="pel-pesanan">
                            @foreach ($pesanan as $p)
                                <div class="pel-pesanan-baris">
                                    <span class="mis-medali mini {{ $p['warna'] }}" aria-hidden="true">
                                        <i class="fas {{ $p['ikon'] }}"></i>
                                    </span>
                                    <div class="pel-pesanan-teks">
                                        <p class="pel-pesanan-layanan">{{ $p['layanan'] }}</p>
                                        <p class="pel-pesanan-ket">
                                            {{ $p['nomor'] ?: 'Tanpa nomor' }}
                                            @if ($p['waktu'])
                                                &middot; {{ $p['waktu']->locale('id')->translatedFormat('d M Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="pel-pesanan-kanan">
                                        @if ($p['nilai'] > 0)
                                            <p class="pel-pesanan-nilai">Rp {{ number_format($p['nilai'], 0, ',', '.') }}</p>
                                        @endif
                                        {{-- Warna, label, dan ikonnya datang dari
                                             PesananPelanggan::rupaStatus(): empat layanan
                                             memakai kosakata berbeda, jadi pemetaannya
                                             ditaruh satu tempat, bukan disebar di Blade. --}}
                                        <span class="mis-pil mis-pil-{{ $p['rupa']['warna'] }}">
                                            <i class="fas {{ $p['rupa']['ikon'] }}"></i> {{ $p['rupa']['label'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Keterbatasannya disebutkan, tidak disembunyikan.
                             Tiga dari empat layanan tidak menyimpan rujukan ke
                             akun pemesannya sama sekali, jadi penautannya
                             menebak dari nomor telepon, email, lalu nama — dan
                             sengaja menahan diri kalau pengenalnya dipakai lebih
                             dari satu akun. Online Training tidak bisa ikut sebab
                             layanannya berjalan di luar sistem ini. --}}
                        <p class="mis-bantuan" style="margin-top: 10px;">
                            <i class="fas fa-info-circle mis-ikon-biru"></i>
                            Pesanan dicocokkan lewat nomor telepon, lalu email, lalu nama. Pesanan
                            yang datanya berbeda dari data akun ini bisa tidak muncul, dan pesanan
                            Online Training tidak tercatat di sini.
                        </p>
                    @endif
                </section>

                </div>

                {{-- ------------------------------ jejak perubahan --}}
                <div class="tab-pane fade" id="pel-panel-jejak" role="tabpanel"
                    aria-labelledby="pel-tab-jejak" tabindex="0">
                <section class="mis-bagian pel-bagian">
                    <div class="pel-kepala">
                        <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-history"></i></span>
                        <h3 class="pel-kepala-judul">Jejak perubahan</h3>
                        <p class="pel-kepala-sub">Siapa mengubah apa pada akun ini.</p>
                    </div>

                    @if ($jejak->isEmpty())
                        <div class="mis-kosong pel-kosong-ringkas">
                            <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-history"></i></span>
                            <p class="mis-kosong-judul">Belum ada catatan</p>
                            <p class="mis-kosong-teks">Perubahan pada akun ini akan tercatat di sini.</p>
                        </div>
                    @else
                        <div class="pel-pesanan">
                            @foreach ($jejak as $baris)
                                <div class="pel-pesanan-baris">
                                    <span class="mis-medali mini {{ $baris->berhasil ? 'mis-hijau' : 'mis-merah' }}" aria-hidden="true">
                                        <i class="fas {{ $baris->berhasil ? 'fa-check' : 'fa-times' }}"></i>
                                    </span>
                                    <div class="pel-pesanan-teks">
                                        <p class="pel-pesanan-layanan">{{ $baris->alasan ?: 'Perubahan tidak dijelaskan' }}</p>
                                        <p class="pel-pesanan-ket">
                                            {{ optional($baris->created_at)->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                            @if ($baris->ip) &middot; {{ $baris->ip }} @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
                </div>

                </div>{{-- tab-content --}}
                </div>{{-- mis-tab-isi --}}

            </div>
        </div>

        {{-- Formulir verifikasi disembunyikan; dijalankan setelah dikonfirmasi. --}}
        @unless ($user->email_verified_at)
            <form id="pel-form-verifikasi" method="POST" class="d-none"
                action="{{ route('account.pengguna.update.vertifikasiemail', $user) }}">
                @csrf
            </form>
        @endunless

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Pemilih berkas: namanya ditampilkan, tombol simpan baru muncul setelah
     * ada yang dipilih, dan ukuran serta jenisnya diperiksa di peramban.
     *
     * Pemeriksaan di peramban ini BUKAN pengaman — peladen tetap memeriksa
     * ulang lewat FotoProfil. Gunanya cuma memberi tahu lebih cepat, sebelum
     * orang menunggu 3 MB terkirim untuk ditolak.
     */
    (function () {
        const isian = document.getElementById('pel-foto');
        const nama = document.getElementById('pel-nama-berkas');
        const simpan = document.getElementById('pel-simpan-foto');
        const bingkai = document.querySelector('.pel-identitas-avatar .mis-foto-bingkai')
            || document.querySelector('.pel-identitas-avatar');
        if (!isian || !nama || !simpan) return;

        const BATAS = 3 * 1024 * 1024;
        const BOLEH = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        /*
         * Pratinjau harus menangani DUA bentuk avatar.
         *
         * Kalau pelanggannya sudah berfoto, avatarnya <img> dan cukup ditukar
         * src-nya. Kalau belum, avatarnya <span> berisi inisial — tidak ada
         * gambar untuk ditukar sama sekali. Karena itu simpulnya disimpan
         * dulu, diganti <img> saat memilih, lalu dikembalikan apa adanya kalau
         * pilihannya dibatalkan.
         */
        const avatarAsli = bingkai ? bingkai.querySelector('.mis-avatar') : null;
        const salinanAsli = avatarAsli ? avatarAsli.cloneNode(true) : null;
        let alamatObjek = null;

        const lepasAlamat = function () {
            if (alamatObjek) {
                URL.revokeObjectURL(alamatObjek);
                alamatObjek = null;
            }
        };

        const kembalikanAvatar = function () {
            lepasAlamat();
            if (!bingkai || !salinanAsli) return;
            const sekarang = bingkai.querySelector('.mis-avatar');
            if (sekarang) sekarang.replaceWith(salinanAsli.cloneNode(true));
        };

        const tampilkanPratinjau = function (berkas) {
            if (!bingkai || !avatarAsli) return;

            lepasAlamat();
            alamatObjek = URL.createObjectURL(berkas);

            const sekarang = bingkai.querySelector('.mis-avatar');
            const ukuran = avatarAsli.style.width || '104px';

            // Selalu <img> baru, bukan menyulap <span> jadi gambar: ukuran dan
            // warnanya menempel pada style sebaris milik simpul inisial.
            const gambar = document.createElement('img');
            gambar.className = 'mis-avatar';
            gambar.alt = 'Pratinjau foto yang dipilih';
            gambar.style.width = ukuran;
            gambar.style.height = avatarAsli.style.height || ukuran;
            gambar.src = alamatObjek;

            if (sekarang) sekarang.replaceWith(gambar);
            else bingkai.prepend(gambar);
        };

        const kosongkan = function (pesan) {
            isian.value = '';
            nama.textContent = 'Pilih foto baru';
            simpan.hidden = true;
            simpan.disabled = true;
            kembalikanAvatar();
            if (pesan) window.misToast('gagal', pesan);
        };

        isian.addEventListener('change', function () {
            const berkas = isian.files && isian.files[0];
            if (!berkas) return kosongkan(null);

            const ext = (berkas.name.split('.').pop() || '').toLowerCase();
            if (BOLEH.indexOf(ext) === -1) {
                return kosongkan('Hanya JPG, PNG, GIF, atau WebP yang bisa diunggah.');
            }
            if (berkas.size > BATAS) {
                return kosongkan('Berkasnya lebih dari 3 MB.');
            }

            nama.textContent = berkas.name;
            simpan.hidden = false;
            simpan.disabled = false;
            tampilkanPratinjau(berkas);
        });
    })();

    /*
     * Menandai email terverifikasi berarti menyatakan alamatnya benar tanpa
     * bukti apa pun dari pemiliknya, jadi ia dikonfirmasi dulu — bukan
     * langsung jalan saat tombolnya tersentuh.
     */
    (function () {
        const tombol = document.getElementById('pel-tombol-verifikasi');
        const borang = document.getElementById('pel-form-verifikasi');
        if (!tombol || !borang) return;

        tombol.addEventListener('click', function () {
            Swal.fire({
                title: 'Tandai email terverifikasi?',
                text: 'Anda menyatakan alamat ini benar tanpa menunggu pelanggannya mengklik tautan verifikasi.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, tandai',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'mis-tombol mis-tombol-hijau',
                    cancelButton: 'mis-tombol mis-tombol-halus',
                },
            }).then(function (hasil) {
                if (hasil.isConfirmed) borang.submit();
            });
        });
    })();
</script>
@endpush
