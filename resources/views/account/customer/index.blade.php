@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Pelanggan | MIS
@stop

{{-- Pemberitahuan lewat toast bersama, bukan kotak alert Bootstrap. --}}
@include('partials.toast-flash')

@push('gaya')
<style>
    /*
     * Layar ini memakai bahasa rupa yang sama dengan halaman profil: kartu
     * .mis-kartu, ubin ikon .mis-medali, lencana .mis-pil, tombol .mis-tombol.
     * Yang ditulis di sini hanya yang khas layar ini.
     *
     * Versi sebelumnya menaruh 265 baris <style> beserta puluhan style sebaris
     * di dalam markahnya, dengan nama kelas sendiri (hero-glass, customer-card)
     * yang tidak dipakai layar lain. Akibatnya dua layar yang sama-sama daftar
     * data tampil berbeda, dan setiap perbaikan rupa harus dikerjakan dua kali.
     */

    /* ------------------------------------------------------- ringkasan */

    .pel-ringkas {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .pel-ubin {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid #e7ecf5;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .pel-ubin-angka {
        margin: 0;
        line-height: 1.1;
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--mis-tinta);
    }

    .pel-ubin-label {
        margin: 2px 0 0;
        line-height: 1.4;
        font-size: .74rem;
        font-weight: 600;
        color: var(--mis-tinta-3);
    }

    /* ------------------------------------------------------- penyaring */

    .pel-saring {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px;
        margin-bottom: 14px;
    }

    .pel-saring-cari {
        flex: 1 1 240px;
        min-width: 0;
    }

    .pel-saring-pilih {
        flex: 0 1 170px;
        min-width: 0;
    }

    /* Ringkasan pelipat hanya berguna di ponsel; di layar lebar penyaringnya
       memang selalu terbuka, jadi ringkasannya tidak perlu ada. */
    .pel-lipat > summary {
        display: none;
    }

    /* ---------------------------------------------------------- daftar */

    /* Sel nama: foto + nama + username dalam satu sel supaya barisnya tidak
       melebar oleh kolom yang isinya cuma satu kata. */
    .pel-orang {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .pel-foto {
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid var(--mis-garis);
        background: #f1f5f9;
    }

    .pel-nama {
        margin: 0;
        line-height: 1.25;
        font-size: .84rem;
        font-weight: 700;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .pel-akun {
        margin: 1px 0 0;
        line-height: 1.4;
        font-size: .72rem;
        color: var(--mis-tinta-3);
        overflow-wrap: anywhere;
    }

    .pel-kontak {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: .76rem;
        color: var(--mis-tinta-2);
    }

    .pel-kontak span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        overflow-wrap: anywhere;
    }

    /* Ikon sebaris ikut ukuran teksnya; aturan global layout mengunci .fas
       ke 20px dengan bobot yang sama, jadi di sini perlu lebih spesifik. */
    .pel-kontak span > .fas {
        width: 13px;
        font-size: .72rem !important;
        text-align: center;
    }

    .pel-aksi {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    /* ------------------------------------------------------- responsif */

    @media (max-width: 1100px) {
        .pel-ringkas {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .pel-ringkas {
            gap: 10px;
        }

        .pel-ubin {
            padding: 12px 13px;
            border-radius: 14px;
        }

        .pel-ubin-angka {
            font-size: 1.15rem;
        }

        /*
         * Penyaring dilipat di ponsel.
         *
         * Tiga kendali yang selalu terbuka memakan satu layar penuh sebelum
         * baris pertama data kelihatan, padahal yang dicari orang justru
         * datanya. <details>/<summary> dipakai supaya tidak perlu JavaScript
         * dan tetap bisa dibuka pembaca layar.
         */
        .pel-lipat > summary {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 13px;
            border: 1px solid var(--mis-garis);
            border-radius: 12px;
            background: #fff;
            font-size: .8rem;
            font-weight: 700;
            color: var(--mis-tinta-2);
            cursor: pointer;
            list-style: none;
        }

        .pel-lipat > summary::-webkit-details-marker {
            display: none;
        }

        .pel-lipat[open] > summary {
            margin-bottom: 10px;
        }

        .pel-saring {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .pel-saring-cari,
        .pel-saring-pilih {
            flex: 1 1 auto;
        }

        .pel-saring .mis-tombol {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        /* Di mode kartu, sel nilai berada di kanan; deretan tombol ikut rata
           kanan supaya tepinya lurus dengan nilai baris lainnya. */
        .pel-aksi {
            justify-content: flex-end;
        }

        .pel-kontak {
            align-items: flex-end;
        }
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-users"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Data Pelanggan</h1>
                <p class="mis-sub">Orang luar yang memakai layanan jasa Rumah Scopus.</p>
            </div>
        </div>

        {{-- ---------------------------------------------- ringkasan --}}
        {{-- Empat angka yang paling sering ditanyakan, dihitung dari seluruh
             pelanggan — bukan dari halaman yang sedang tampil. --}}
        <div class="pel-ringkas">
            <div class="pel-ubin">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-users"></i></span>
                <div>
                    <p class="pel-ubin-angka">{{ number_format($ringkasan['total']) }}</p>
                    <p class="pel-ubin-label">Seluruh pelanggan</p>
                </div>
            </div>
            <div class="pel-ubin">
                <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-user-check"></i></span>
                <div>
                    <p class="pel-ubin-angka">{{ number_format($ringkasan['aktif']) }}</p>
                    <p class="pel-ubin-label">Akun aktif</p>
                </div>
            </div>
            <div class="pel-ubin">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-envelope-open-text"></i></span>
                <div>
                    <p class="pel-ubin-angka">{{ number_format($ringkasan['terverifikasi']) }}</p>
                    <p class="pel-ubin-label">Email terverifikasi</p>
                </div>
            </div>
            <div class="pel-ubin">
                <span class="mis-medali kecil mis-jingga" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
                <div>
                    <p class="pel-ubin-angka">{{ number_format($ringkasan['baru']) }}</p>
                    <p class="pel-ubin-label">Bergabung 30 hari terakhir</p>
                </div>
            </div>
        </div>

        {{-- ---------------------------------------------- penyaring --}}
        {{-- <details> membungkus penyaringnya, bukan berdiri sendiri: di
             ponsel tiga kendali yang selalu terbuka memakan satu layar penuh
             sebelum baris pertama data kelihatan. Di layar lebar ia dipaksa
             terbuka oleh skrip di bawah dan ringkasannya disembunyikan, jadi
             tampak seperti baris penyaring biasa. --}}
        <details class="pel-lipat" id="pel-penyaring">
            <summary>
                <i class="fas fa-sliders-h mis-ikon-ungu" aria-hidden="true"></i>
                Cari &amp; saring
                @if ($cari !== '' || $status || $verifikasi)
                    <span class="mis-pil mis-pil-ungu">aktif</span>
                @endif
            </summary>

        <form method="GET" action="{{ route('account.customer.index') }}" class="pel-saring">
            <div class="mis-isian pel-saring-cari">
                <label class="mis-label" for="pel-cari">Cari</label>
                <input type="search" class="form-control-modern" id="pel-cari" name="cari"
                    value="{{ $cari }}" placeholder="Nama, username, email, atau telepon">
            </div>

            <div class="mis-isian pel-saring-pilih">
                <label class="mis-label" for="pel-status">Status akun</label>
                <select class="form-control-modern" id="pel-status" name="status">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                </select>
            </div>

            <div class="mis-isian pel-saring-pilih">
                <label class="mis-label" for="pel-verifikasi">Email</label>
                <select class="form-control-modern" id="pel-verifikasi" name="verifikasi">
                    <option value="">Semua email</option>
                    <option value="sudah" @selected($verifikasi === 'sudah')>Sudah diverifikasi</option>
                    <option value="belum" @selected($verifikasi === 'belum')>Belum diverifikasi</option>
                </select>
            </div>

            <button type="submit" class="mis-tombol mis-tombol-ungu">
                <i class="fas fa-search"></i> Terapkan
            </button>

            @if ($cari !== '' || $status || $verifikasi)
                <a href="{{ route('account.customer.index') }}" class="mis-tombol mis-tombol-halus" title="Hapus semua saringan">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
        </details>

        {{-- -------------------------------------------------- daftar --}}
        @if ($pelanggan->isEmpty())
            <div class="mis-kartu">
                <div class="mis-kosong">
                    <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-users"></i></span>
                    <p class="mis-kosong-judul">
                        {{ $cari !== '' || $status || $verifikasi ? 'Tidak ada yang cocok' : 'Belum ada pelanggan' }}
                    </p>
                    <p class="mis-kosong-teks">
                        {{ $cari !== '' || $status || $verifikasi
                            ? 'Coba ganti kata kuncinya, atau hapus saringannya.'
                            : 'Pelanggan muncul di sini setelah mendaftar di layanan.' }}
                    </p>
                </div>
            </div>
        @else
            {{-- mis-tabel-kartu: di bawah 576px tabelnya berubah jadi tumpukan
                 kartu, jadi tidak perlu digeser ke samping di ponsel. --}}
            <div class="mis-tabel-bungkus">
                <table class="mis-tabel mis-tabel-kartu">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>Bergabung</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pelanggan as $orang)
                            <tr>
                                <td class="mis-td-utama">
                                    <div class="pel-orang">
                                        <img class="pel-foto" alt=""
                                            src="{{ \App\Support\FotoProfil::url($orang->gambar) }}">
                                        <div style="min-width: 0;">
                                            <p class="pel-nama">{{ $orang->full_name ?: $orang->username }}</p>
                                            <p class="pel-akun">&#64;{{ $orang->username }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td data-judul="Kontak">
                                    <div class="pel-kontak">
                                        <span><i class="fas fa-envelope mis-ikon-biru"></i> {{ $orang->email }}</span>
                                        <span><i class="fas fa-phone mis-ikon-hijau"></i> {{ $orang->telp ?: 'Nomor belum diisi' }}</span>
                                    </div>
                                </td>

                                <td data-judul="Status">
                                    {{-- Dua keadaan yang berbeda, jadi dua lencana: akunnya
                                         aktif atau tidak, dan emailnya terverifikasi atau
                                         belum. Digabung jadi satu lencana, salah satunya
                                         selalu tersembunyi. --}}
                                    <div class="mis-baris-ikon" style="flex-wrap: wrap; gap: 5px;">
                                        @if ($orang->status === 'active')
                                            <span class="mis-pil mis-pil-hijau"><i class="fas fa-check"></i> Aktif</span>
                                        @else
                                            <span class="mis-pil mis-pil-abu"><i class="fas fa-pause"></i> Nonaktif</span>
                                        @endif

                                        @if ($orang->email_verified_at)
                                            <span class="mis-pil mis-pil-biru"><i class="fas fa-envelope-open"></i> Terverifikasi</span>
                                        @else
                                            <span class="mis-pil mis-pil-kuning"><i class="fas fa-clock"></i> Belum verifikasi</span>
                                        @endif
                                    </div>
                                </td>

                                <td data-judul="Bergabung">
                                    <span class="pel-akun" style="font-size: .78rem;">
                                        {{ optional($orang->created_at)->locale('id')->translatedFormat('d M Y') ?: '-' }}
                                    </span>
                                </td>

                                <td data-judul="Aksi">
                                    <div class="pel-aksi">
                                        <a href="{{ route('account.customer.edit', $orang) }}"
                                            class="mis-tombol mis-tombol-garis" title="Lihat &amp; ubah">
                                            <i class="fas fa-user-edit"></i>
                                        </a>
                                        @if (auth()->user()->adalahAdministrator())
                                            <button type="button" class="mis-tombol mis-tombol-bahaya"
                                                title="Hapus pelanggan"
                                                data-hapus="{{ route('account.customer.destroy', $orang) }}"
                                                data-nama="{{ $orang->full_name ?: $orang->username }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mis-aksi-baris" style="justify-content: center; margin-top: 16px;">
                {{ $pelanggan->links('vendor.pagination.bootstrap-4') }}
            </div>
        @endif

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Penyaring terbuka sendiri mulai 768px dan terlipat di bawah itu.
     *
     * Dikerjakan skrip, bukan CSS: isi <details> yang tertutup disembunyikan
     * oleh gaya bawaan peramban, dan menimpanya dari CSS tidak bisa diandalkan
     * antar peramban.
     */
    (function () {
        const lipat = document.getElementById('pel-penyaring');
        if (!lipat) return;
        const lebar = window.matchMedia('(min-width: 768px)');
        const setel = function () { if (lebar.matches) lipat.setAttribute('open', ''); };
        setel();
        lebar.addEventListener('change', setel);
    })();

    /*
     * Penghapusan: satu penangan untuk seluruh tabel, bukan onclick di tiap
     * baris. Dengan begitu tidak ada nama fungsi global yang harus dijaga, dan
     * alamatnya datang dari route() di markah — bukan dirangkai dari id di
     * JavaScript, yang membuat perubahan rute diam-diam merusak tombolnya.
     */
    document.addEventListener('click', function (e) {
        const tombol = e.target.closest('[data-hapus]');
        if (!tombol) return;

        Swal.fire({
            title: 'Hapus pelanggan ini?',
            html: 'Data <strong></strong> dan riwayatnya ikut terhapus. Tindakan ini tidak bisa dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: { confirmButton: 'mis-tombol mis-tombol-bahaya', cancelButton: 'mis-tombol mis-tombol-halus' },
            buttonsStyling: false,
            didOpen: function (el) {
                // Nama disisipkan sebagai teks, bukan dirangkai ke HTML:
                // nama pelanggan datang dari isian orang.
                const kuat = el.querySelector('strong');
                if (kuat) kuat.textContent = tombol.dataset.nama || 'ini';
            },
        }).then(function (hasil) {
            if (!hasil.isConfirmed) return;

            fetch(tombol.dataset.hapus, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (j) {
                    if (!j.ok || !j.d.success) {
                        window.misToast('gagal', j.d.message || 'Gagal menghapus pelanggan.');
                        return;
                    }
                    window.misToast('berhasil', j.d.message);
                    tombol.closest('tr').remove();
                })
                .catch(function () {
                    window.misToast('gagal', 'Tidak bisa menghubungi peladen.');
                });
        });
    });
</script>
@endpush
