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
        font-size: .78rem;
        color: var(--mis-tinta-3);
    }

    .pel-baris-aksi {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 14px;
    }

    /* Baris email: nilai di kiri, tombol verifikasi di kanan. */
    .pel-email {
        display: flex;
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

        .pel-pesanan-kanan {
            flex-direction: row;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding-left: 39px;
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
                    @include('partials.avatar', ['orang' => $user, 'ukuran' => 104])
                </div>

                <p class="pel-identitas-nama">{{ $user->full_name ?: $user->username }}</p>
                <p class="pel-identitas-akun">&#64;{{ $user->username }}</p>

                <div class="pel-lencana-deret">
                    @if ($user->status === 'active')
                        <span class="mis-pil mis-pil-hijau"><i class="fas fa-check"></i> Akun aktif</span>
                    @else
                        <span class="mis-pil mis-pil-abu"><i class="fas fa-pause"></i> Nonaktif</span>
                    @endif

                    @if ($user->email_verified_at)
                        <span class="mis-pil mis-pil-biru"><i class="fas fa-envelope-open"></i> Email terverifikasi</span>
                    @else
                        <span class="mis-pil mis-pil-kuning"><i class="fas fa-clock"></i> Email belum terverifikasi</span>
                    @endif

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

                <form class="pel-unggah" method="POST" enctype="multipart/form-data"
                    action="{{ route('account.pengguna.update.updatePhoto', $user) }}">
                    @csrf
                    <div class="mis-isian">
                        <label class="mis-label" for="pel-foto">Ganti foto</label>
                        <input type="file" class="form-control-modern" id="pel-foto" name="gambar"
                            accept="image/jpeg,image/png,image/gif,image/webp">
                        <p class="mis-bantuan">JPG, PNG, GIF, atau WebP &middot; maksimal 3 MB.</p>
                    </div>
                    <button type="submit" class="mis-tombol mis-tombol-ungu" style="width: 100%;">
                        <i class="fas fa-upload"></i> Simpan foto
                    </button>
                </form>
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

                    <form method="POST" action="{{ route('account.pengguna.update', $user) }}">
                        @csrf
                        <div class="mis-kisi-isian">
                            <div class="mis-isian">
                                <label class="mis-label" for="pel-nama">Nama lengkap</label>
                                <input type="text" class="form-control-modern" id="pel-nama" name="full_name"
                                    value="{{ old('full_name', $user->full_name) }}" maxlength="255">
                            </div>

                            <div class="mis-isian">
                                <label class="mis-label" for="pel-username">Username</label>
                                <input type="text" class="form-control-modern" id="pel-username" name="username"
                                    value="{{ old('username', $user->username) }}" maxlength="150">
                                <p class="mis-bantuan">Dipakai untuk masuk, jadi harus unik.</p>
                            </div>

                            <div class="mis-isian">
                                <label class="mis-label" for="pel-status">Status akun</label>
                                <select class="form-control-modern" id="pel-status" name="status">
                                    <option value="active" @selected($user->status === 'active')>Aktif</option>
                                    <option value="non active" @selected($user->status !== 'active')>Nonaktif</option>
                                </select>
                                <p class="mis-bantuan">Akun nonaktif tidak bisa masuk.</p>
                            </div>

                            <div class="mis-isian">
                                <label class="mis-label" for="pel-jenis">Jenis akun</label>
                                <select class="form-control-modern" id="pel-jenis" name="jenis">
                                    <option value="perorangan" @selected($user->jenis === 'perorangan')>Perorangan</option>
                                    <option value="perusahaan" @selected($user->jenis === 'perusahaan')>Perusahaan</option>
                                </select>
                            </div>
                        </div>

                        {{-- Peran TIDAK bisa diubah dari layar ini.

                             Ini halaman data pelanggan; menaikkan seseorang jadi
                             karyawan atau administrator adalah urusan pengelolaan
                             pengguna, dan menaruh kendalinya di sini membuat satu
                             salah klik mengubah hak akses tanpa disengaja. --}}
                        <div class="pel-baris-aksi">
                            <button type="submit" class="mis-tombol mis-tombol-ungu">
                                <i class="fas fa-save"></i> Simpan perubahan
                            </button>
                        </div>
                    </form>
                </section>

                </div>

                {{-- --------------------------------------- kontak --}}
                <div class="tab-pane fade" id="pel-panel-kontak" role="tabpanel"
                    aria-labelledby="pel-tab-kontak" tabindex="0">
                <section class="mis-bagian pel-bagian">

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

                        @unless ($user->email_verified_at)
                            <button type="button" class="mis-tombol mis-tombol-hijau" id="pel-tombol-verifikasi">
                                <i class="fas fa-check-circle"></i> Tandai terverifikasi
                            </button>
                        @endunless
                    </div>

                    <form method="POST" action="{{ route('account.pengguna.update.datadiri', $user) }}" class="mt-3">
                        @csrf
                        <div class="mis-kisi-isian">
                            <div class="mis-isian">
                                <label class="mis-label" for="pel-email-baru">Alamat email</label>
                                <input type="email" class="form-control-modern" id="pel-email-baru" name="email"
                                    value="{{ old('email', $user->email) }}" required>
                                <p class="mis-bantuan">Mengganti email membuat verifikasinya kembali kosong.</p>
                            </div>

                            <div class="mis-isian">
                                <label class="mis-label" for="pel-telp">Nomor WhatsApp</label>
                                <input type="tel" class="form-control-modern" id="pel-telp" name="telp"
                                    value="{{ old('telp', $user->telp) }}" inputmode="numeric"
                                    placeholder="08xxxxxxxxxx">
                            </div>
                        </div>

                        {{-- Satu tombol untuk email dan telepon sekaligus: keduanya
                             dikirim ke alamat yang sama, dan dulu dipecah jadi dua
                             formulir terpisah berisi satu isian masing-masing. --}}
                        <div class="pel-baris-aksi">
                            <button type="submit" class="mis-tombol mis-tombol-ungu">
                                <i class="fas fa-save"></i> Simpan kontak
                            </button>
                        </div>
                    </form>
                </section>

                </div>

                {{-- ------------------------------ riwayat pesanan --}}
                <div class="tab-pane fade" id="pel-panel-pesanan" role="tabpanel"
                    aria-labelledby="pel-tab-pesanan" tabindex="0">
                <section class="mis-bagian pel-bagian">

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
                                        <span class="mis-pil mis-pil-abu">{{ \Illuminate\Support\Str::title($p['status']) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Keterbatasannya disebutkan, tidak disembunyikan: dua dari
                             tiga layanan mencocokkan pemesannya lewat alamat email,
                             jadi jejak sebelum email diganti tidak ikut terbaca. --}}
                        <p class="mis-bantuan" style="margin-top: 10px;">
                            <i class="fas fa-info-circle mis-ikon-biru"></i>
                            Sebagian layanan mencatat pemesannya lewat alamat email, jadi pesanan
                            sebelum emailnya diganti bisa tidak muncul di sini.
                        </p>
                    @endif
                </section>

                </div>

                {{-- ------------------------------ jejak perubahan --}}
                <div class="tab-pane fade" id="pel-panel-jejak" role="tabpanel"
                    aria-labelledby="pel-tab-jejak" tabindex="0">
                <section class="mis-bagian pel-bagian">

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
