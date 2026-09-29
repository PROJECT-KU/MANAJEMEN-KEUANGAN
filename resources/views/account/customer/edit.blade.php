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
     * Sekali lagi memakai bahasa rupa bersama: .mis-kartu, .mis-medali,
     * .mis-pil, .mis-tombol. Yang ditulis di sini hanya yang khas layar ini.
     */

    .pel-tata {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 14px;
        align-items: start;
    }

    /* --------------------------------------------------- kartu identitas */

    .pel-identitas {
        text-align: center;
    }

    .pel-identitas-foto {
        width: 104px;
        height: 104px;
        border-radius: 28px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 10px 24px -14px rgba(15, 23, 42, .5);
        background: #f1f5f9;
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
            <aside class="mis-kartu pel-identitas">
                <img class="pel-identitas-foto" alt="Foto {{ $user->full_name ?: $user->username }}"
                    src="{{ \App\Support\FotoProfil::url($user->gambar) }}">

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

                    <span class="mis-pil mis-pil-ungu"><i class="fas fa-calendar-alt"></i>
                        Bergabung {{ optional($user->created_at)->locale('id')->translatedFormat('d M Y') ?: '-' }}
                    </span>
                </div>

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
            <div>

                {{-- ------------------------------------- data akun --}}
                <section class="mis-kartu pel-bagian">
                    <div class="pel-kepala">
                        <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-id-card"></i></span>
                        <h2 class="pel-kepala-judul">Data akun</h2>
                        <p class="pel-kepala-sub">Nama, username, dan keadaan akunnya.</p>
                    </div>

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

                {{-- --------------------------------------- kontak --}}
                <section class="mis-kartu pel-bagian">
                    <div class="pel-kepala">
                        <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-address-book"></i></span>
                        <h2 class="pel-kepala-judul">Kontak</h2>
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
