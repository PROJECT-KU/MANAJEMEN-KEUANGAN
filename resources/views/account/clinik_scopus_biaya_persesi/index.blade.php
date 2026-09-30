@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Biaya per Sesi | MIS
@stop

{{-- Pemberitahuan lewat toast bersama, bukan kotak alert Bootstrap. --}}
@include('partials.toast-flash')

@push('gaya')
<style>
    /*
     * Layar ini memakai bahasa rupa yang sama dengan Profil dan Data
     * Pelanggan: kartu .mis-bagian, ubin ikon .mis-medali, lencana .mis-pil,
     * tombol .mis-tombol. Yang ditulis di sini hanya yang khas layar ini.
     *
     * Acuannya docs/panduan-ui-mis.md.
     */

    /*
     * Kepala kartu bagian, disalin apa adanya dari .pel-kepala di Data
     * Pelanggan supaya kedua layar tampil serupa.
     *
     * .mis-kartu-kepala TIDAK dipakai di sini: ia ber-justify-content
     * space-between, yang benar untuk "judul di kiri, tombol di kanan" tetapi
     * melempar judulnya ke tepi kanan begitu anaknya cuma ubin ikon dan blok
     * teks.
     */
    .tar-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px;
        row-gap: 2px;
        align-items: center;
        margin-bottom: 12px;
    }

    .tar-kepala > .mis-medali {
        grid-row: 1 / span 2;
        align-self: center;
    }

    .tar-kepala-judul {
        grid-column: 2;
        grid-row: 1;
        margin: 0;
        line-height: 1.25;
        font-size: .92rem;
        font-weight: 800;
        color: var(--mis-tinta);
    }

    .tar-kepala-sub {
        grid-column: 2;
        grid-row: 2;
        margin: 0;
        /* Angka relatif, bukan warisan line-height 28px mutlak dari layout. */
        line-height: 1.45;
        font-size: .75rem;
        color: var(--mis-tinta-3);
    }

    /* --------------------------------------------- kartu tarif berlaku */

    /*
     * Satu kartu besar untuk SATU angka.
     *
     * Yang dicari orang di layar ini cuma satu: berapa harga sesi sekarang.
     * Bentuk lamanya menyembunyikannya sebagai satu baris di dalam tabel
     * lima kolom, sejajar dengan baris-baris lama yang sudah tidak berlaku —
     * jadi pertanyaan paling dasar itu justru paling lama dijawab.
     */
    .tar-utama {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        gap: var(--mis-jarak);
        align-items: start;
        margin-bottom: var(--mis-jarak);
    }

    .tar-angka {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 22px;
        border: 1px solid var(--mis-garis);
        border-radius: var(--mis-radius);
        background:
            radial-gradient(120% 140% at 0% 0%, #ecfdf5 0%, rgba(236, 253, 245, 0) 60%),
            #fff;
        box-shadow: var(--mis-bayang);
    }

    .tar-angka-teks {
        min-width: 0;
    }

    .tar-label {
        margin: 0;
        line-height: 1.3;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--mis-tinta-4);
    }

    .tar-nilai {
        margin: 3px 0 0;
        line-height: 1.1;
        font-size: 1.9rem;
        font-weight: 800;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .tar-ket {
        margin: 6px 0 0;
        line-height: 1.5;
        font-size: .78rem;
        color: var(--mis-tinta-3);
    }

    /* Rincian angka: dasar, PPN, dan yang benar-benar dibayar pelanggan. */
    .tar-rincian {
        display: grid;
        gap: 8px;
        padding: 16px 18px;
        border: 1px solid var(--mis-garis);
        border-radius: var(--mis-radius);
        background: #fff;
        box-shadow: var(--mis-bayang);
    }

    .tar-baris {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        font-size: .82rem;
        color: var(--mis-tinta-2);
    }

    .tar-baris > span:last-child {
        font-weight: 700;
        color: var(--mis-tinta);
        white-space: nowrap;
    }

    /* Baris jumlah dipisah garis dan ditebalkan: itu angka yang dilihat
       pelanggan, bukan angka kerja orang dalam. */
    .tar-baris.jumlah {
        margin-top: 4px;
        padding-top: 10px;
        border-top: 1px dashed var(--mis-garis);
        font-size: .88rem;
    }

    .tar-baris.jumlah > span:last-child {
        font-size: 1.05rem;
        font-weight: 800;
        color: #047857;
    }

    /* ------------------------------------------------------ borang tarif */

    .tar-borang {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) auto;
        gap: 12px;
        align-items: end;
    }

    /* Awalan "Rp" dan akhiran "%" menempel di dalam kotaknya, bukan jadi
       label terpisah: keduanya satuan, bukan keterangan. */
    .tar-isian {
        position: relative;
    }

    .tar-isian .form-control-modern {
        padding-left: 40px;
    }

    .tar-isian.persen .form-control-modern {
        padding-left: 14px;
        padding-right: 36px;
    }

    .tar-satuan {
        position: absolute;
        bottom: 0;
        display: grid;
        place-items: center;
        width: 30px;
        height: 42px;
        font-size: .82rem;
        font-weight: 700;
        color: var(--mis-tinta-4);
        pointer-events: none;
    }

    .tar-satuan.kiri { left: 8px; }
    .tar-satuan.kanan { right: 6px; }

    /* Pratinjau hitungan, berubah saat diketik. */
    .tar-pratinjau {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        font-size: .78rem;
        color: var(--mis-tinta-3);
    }

    /* ----------------------------------------------------------- riwayat */

    .tar-riwayat-nilai {
        margin: 0;
        line-height: 1.25;
        font-size: .86rem;
        font-weight: 700;
        color: var(--mis-tinta);
    }

    .tar-riwayat-ket {
        margin: 1px 0 0;
        line-height: 1.4;
        font-size: .73rem;
        color: var(--mis-tinta-3);
    }

    .tar-aksi {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    /* ---------------------------------------------------------- responsif */

    @media (max-width: 991.98px) {
        .tar-utama {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 767.98px) {
        .tar-borang {
            grid-template-columns: minmax(0, 1fr);
        }

        .tar-borang .mis-tombol {
            width: 100%;
        }

        .tar-nilai {
            font-size: 1.6rem;
        }

        /* Tiap baris riwayat jadi kartu tersendiri, seperti daftar pelanggan:
           garis 1px terlalu sepi untuk memisahkan empat keterangan berlabel. */
        .mis-tabel-kartu.tar-tabel tbody {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .mis-tabel-kartu.tar-tabel tbody tr {
            border: 1px solid var(--mis-garis);
            border-radius: 13px;
            background: #f8fafc;
        }

        .tar-aksi {
            justify-content: flex-end;
        }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-hijau" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Biaya per sesi</h1>
                <p class="mis-sub">Harga satu sesi Clinik Scopus yang dipakai saat pelanggan memesan.</p>
            </div>
            <div class="mis-kepala-aksi">
                <span class="mis-pil {{ $berlaku ? 'mis-pil-hijau' : 'mis-pil-merah' }}">
                    <i class="fas {{ $berlaku ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                    {{ $berlaku ? 'Tarif sudah disetel' : 'Belum ada tarif berlaku' }}
                </span>
            </div>
        </div>

        {{-- --------------------------------- tarif yang sedang berlaku --}}
        @php
            $tarifAngka = $berlaku ? (int) $berlaku->biaya_persesi : 0;
            $ppnPersen = $berlaku ? $berlaku->ppn_persen : 0;
            $ppnRupiah = (int) round($tarifAngka * $ppnPersen / 100);
        @endphp

        <div class="tar-utama">
            <div class="tar-angka">
                <span class="mis-medali mis-hijau" aria-hidden="true"><i class="fas fa-tags"></i></span>
                <div class="tar-angka-teks">
                    <p class="tar-label">Berlaku sekarang</p>
                    <p class="tar-nilai">{{ $berlaku ? $berlaku->tarif_terbaca : 'Belum disetel' }}</p>
                    <p class="tar-ket">
                        @if ($berlaku)
                            per satu sesi &middot; disetel
                            {{ optional($berlaku->updated_at)->locale('id')->translatedFormat('d M Y') }}
                            @if ($sesiMemakai > 0)
                                &middot; jadi acuan {{ number_format($sesiMemakai) }} sesi
                            @endif
                        @else
                            Borang pemesanan menampilkan harga kosong sampai tarifnya disetel.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Rincian yang dilihat pelanggan, dihitung di sini supaya tidak
                 ada yang perlu mengalikan persen di kepalanya sendiri. --}}
            <div class="tar-rincian">
                <div class="tar-baris">
                    <span><i class="fas fa-receipt mis-ikon-biru"></i> Tarif dasar</span>
                    <span>{{ $berlaku ? $berlaku->tarif_terbaca : '—' }}</span>
                </div>
                <div class="tar-baris">
                    <span><i class="fas fa-percent mis-ikon-jingga"></i> PPN {{ $ppnPersen }}%</span>
                    <span>{{ $ppnPersen > 0 ? 'Rp ' . number_format($ppnRupiah, 0, ',', '.') : 'Tidak dikenakan' }}</span>
                </div>
                <div class="tar-baris jumlah">
                    <span>Dibayar pelanggan</span>
                    <span>{{ $berlaku ? 'Rp ' . number_format($tarifAngka + $ppnRupiah, 0, ',', '.') : '—' }}</span>
                </div>
                <p class="mis-bantuan" style="margin-top: 6px;">
                    <i class="fas fa-info-circle mis-ikon-biru"></i>
                    Belum termasuk kode unik dan potongan promo, yang dihitung saat memesan.
                </p>
            </div>
        </div>

        {{-- ------------------------------------------- setel tarif --}}
        <div class="mis-bagian" style="margin-bottom: var(--mis-jarak);">
            <div class="tar-kepala">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-edit"></i></span>
                <h2 class="tar-kepala-judul">Setel tarif</h2>
                <p class="tar-kepala-sub">Dua isian saja. Tarif baru langsung berlaku untuk pemesanan berikutnya.</p>
            </div>

            @if ($bolehUbah)
                <form method="POST" action="{{ route('account.Clinik-Scopus-Biaya-Persesi.simpan') }}" id="tar-borang">
                    @csrf

                    {{-- Kalau dicentang, baris yang berlaku DIPERBAIKI, bukan
                         ditambah baris baru. Dibedakan supaya salah ketik tidak
                         meninggalkan jejak seolah harganya pernah berubah. --}}
                    <input type="hidden" name="perbaiki" id="tar-perbaiki" value="">

                    <div class="tar-borang">
                        <div class="mis-isian tar-isian">
                            <label class="mis-label" for="tar-tarif">Tarif per sesi</label>
                            <span class="tar-satuan kiri" aria-hidden="true">Rp</span>
                            <input type="text" class="form-control-modern @error('biaya_persesi') is-invalid @enderror"
                                id="tar-tarif" name="biaya_persesi" inputmode="numeric" autocomplete="off"
                                value="{{ old('biaya_persesi', $berlaku ? number_format((int) $berlaku->biaya_persesi, 0, ',', '.') : '') }}"
                                placeholder="125.000" required>
                            @error('biaya_persesi')
                                <p class="mis-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mis-isian tar-isian persen">
                            <label class="mis-label" for="tar-ppn">PPN</label>
                            <span class="tar-satuan kanan" aria-hidden="true">%</span>
                            <input type="number" class="form-control-modern @error('ppn') is-invalid @enderror"
                                id="tar-ppn" name="ppn" min="0" max="100" inputmode="numeric"
                                value="{{ old('ppn', $berlaku && $berlaku->ppn !== null ? $berlaku->ppn_persen : '') }}"
                                placeholder="0">
                            @error('ppn')
                                <p class="mis-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="mis-tombol mis-tombol-ungu" id="tar-simpan">
                            <i class="fas fa-save"></i> Berlakukan
                        </button>
                    </div>

                    <div class="tar-pratinjau" id="tar-pratinjau" aria-live="polite">
                        <i class="fas fa-calculator mis-ikon-ungu" aria-hidden="true"></i>
                        <span id="tar-pratinjau-teks">Pelanggan membayar <strong>—</strong></span>
                    </div>
                </form>
            @else
                <p class="mis-bantuan">
                    <i class="fas fa-lock mis-ikon-kuning"></i>
                    Hanya administrator yang boleh mengubah tarif.
                </p>
            @endif
        </div>

        {{-- ----------------------------------------------- riwayat --}}
        <div class="mis-bagian">
            <div class="tar-kepala">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-history"></i></span>
                <h2 class="tar-kepala-judul">Tarif sebelumnya</h2>
                <p class="tar-kepala-sub">Disimpan karena sesi yang sudah dipesan memakai harga yang berlaku saat itu.</p>
            </div>

            @if ($riwayat->isEmpty())
                <div class="mis-kosong">
                    <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-history"></i></span>
                    <p class="mis-kosong-judul">Belum ada tarif lain</p>
                    <p class="mis-kosong-teks">Tarif yang diganti akan tersimpan di sini, lengkap dengan tanggalnya.</p>
                </div>
            @else
                <div class="mis-tabel-bungkus">
                    <table class="mis-tabel mis-tabel-kartu tar-tabel">
                        <thead>
                            <tr>
                                <th>Tarif</th>
                                <th>PPN</th>
                                <th>Dipakai</th>
                                <th>Disetel</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayat as $item)
                                <tr>
                                    <td class="mis-td-utama">
                                        <span class="mis-sel-utama">
                                            <span class="mis-medali kecil mis-abu" aria-hidden="true">
                                                <i class="fas fa-tag"></i>
                                            </span>
                                            <span class="mis-sel-teks">
                                                <p class="tar-riwayat-nilai">{{ $item->tarif_terbaca }}</p>
                                                <p class="tar-riwayat-ket">per satu sesi</p>
                                            </span>
                                        </span>
                                    </td>

                                    <td data-judul="PPN">
                                        @if ($item->ppn_persen > 0)
                                            <span class="mis-pil mis-pil-kuning">
                                                <i class="fas fa-percent"></i> {{ $item->ppn_persen }}%
                                            </span>
                                        @else
                                            <span class="tar-riwayat-ket">Tanpa PPN</span>
                                        @endif
                                    </td>

                                    <td data-judul="Dipakai">
                                        @if ($item->clinik_scopus_count > 0)
                                            <span class="mis-pil mis-pil-biru" title="Jadi acuan harga sesi sebanyak ini">
                                                <i class="fas fa-link"></i> {{ $item->clinik_scopus_count }} sesi
                                            </span>
                                        @else
                                            <span class="tar-riwayat-ket">Belum dipakai</span>
                                        @endif
                                    </td>

                                    <td data-judul="Disetel">
                                        <span class="tar-riwayat-ket">
                                            {{ optional($item->updated_at)->locale('id')->translatedFormat('d M Y') ?: '—' }}
                                        </span>
                                    </td>

                                    <td data-judul="Aksi">
                                        <div class="tar-aksi">
                                            @if ($bolehUbah)
                                                <button type="button" class="mis-tombol mis-tombol-garis"
                                                    title="Berlakukan lagi tarif ini"
                                                    data-berlaku="{{ route('account.Clinik-Scopus-Biaya-Persesi.berlakukan', $item) }}"
                                                    data-nilai="{{ $item->tarif_terbaca }}">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button type="button" class="mis-tombol mis-tombol-bahaya"
                                                    title="Hapus dari riwayat"
                                                    data-hapus="{{ route('account.Clinik-Scopus-Biaya-Persesi.destroy', $item) }}"
                                                    data-nilai="{{ $item->tarif_terbaca }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <span class="tar-riwayat-ket">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $riwayat->links('vendor.pagination.bootstrap-4') }}
            @endif
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Isian tarif dipoles jadi berformat ribuan saat diketik, dan pratinjau
     * di bawahnya ikut berubah.
     *
     * Angkanya tetap dikirim apa adanya; peladen membuang tanda bacanya
     * sendiri. Tanpa pratinjau, orang harus mengalikan persen di kepalanya
     * untuk tahu berapa yang sebenarnya dibayar pelanggan — dan itu justru
     * angka yang paling ingin dipastikan sebelum menekan Berlakukan.
     */
    (function () {
        const tarif = document.getElementById('tar-tarif');
        const ppn = document.getElementById('tar-ppn');
        const teks = document.getElementById('tar-pratinjau-teks');
        const perbaiki = document.getElementById('tar-perbaiki');
        const simpan = document.getElementById('tar-simpan');
        if (!tarif || !teks) return;

        const awalTarif = tarif.value;
        const awalPpn = ppn ? ppn.value : '';

        const rupiah = function (n) {
            return 'Rp ' + n.toLocaleString('id-ID');
        };

        const angka = function (nilai) {
            return parseInt(String(nilai).replace(/\D+/g, ''), 10) || 0;
        };

        const segarkan = function () {
            const dasar = angka(tarif.value);
            const persen = Math.min(100, Math.max(0, parseInt(ppn && ppn.value, 10) || 0));
            const pajak = Math.round(dasar * persen / 100);

            if (dasar < 1) {
                teks.innerHTML = 'Pelanggan membayar <strong>—</strong>';
            } else if (persen > 0) {
                teks.innerHTML = 'Pelanggan membayar <strong>' + rupiah(dasar + pajak)
                    + '</strong> — ' + rupiah(dasar) + ' + PPN ' + persen + '% (' + rupiah(pajak) + ')';
            } else {
                teks.innerHTML = 'Pelanggan membayar <strong>' + rupiah(dasar) + '</strong> — tanpa PPN';
            }

            /*
             * Menekan tombol berarti hal yang berbeda tergantung apa yang
             * berubah, jadi tulisannya ikut berubah. Tarif yang sama persis
             * dengan yang berlaku tetapi PPN-nya berbeda tetap dihitung
             * perbaikan, bukan kenaikan harga.
             */
            if (perbaiki && simpan) {
                const samaTarif = angka(tarif.value) === angka(awalTarif);
                const adaAcuan = awalTarif !== '';

                perbaiki.value = (adaAcuan && samaTarif) ? @json($berlaku?->getKey()) ?? '' : '';
                simpan.innerHTML = (adaAcuan && samaTarif)
                    ? '<i class="fas fa-save"></i> Perbaiki'
                    : '<i class="fas fa-save"></i> Berlakukan';
            }
        };

        tarif.addEventListener('input', function () {
            const n = angka(tarif.value);
            tarif.value = n > 0 ? n.toLocaleString('id-ID') : '';
            segarkan();
        });

        if (ppn) ppn.addEventListener('input', segarkan);

        segarkan();
    })();

    /*
     * Memberlakukan tarif lama dan menghapus baris riwayat.
     *
     * Satu penangan untuk seluruh tabel, bukan onclick di tiap baris: dengan
     * begitu alamatnya datang dari route() di markah, bukan dirangkai di
     * JavaScript — perubahan rute tidak diam-diam merusak tombolnya.
     */
    (function () {
        const kirim = function (alamat, metode) {
            return fetch(alamat, {
                method: metode,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (j) {
                    if (!j.ok || !j.d.success) {
                        window.misToast('gagal', j.d.message || 'Tindakannya gagal.');
                        return false;
                    }
                    window.misToast('berhasil', j.d.message);
                    return true;
                })
                .catch(function () {
                    window.misToast('gagal', 'Tidak bisa menghubungi peladen.');
                    return false;
                });
        };

        document.addEventListener('click', function (e) {
            const pulih = e.target.closest('[data-berlaku]');

            if (pulih) {
                window.misKonfirmasi({
                    judul: 'Berlakukan lagi tarif ini?',
                    pesan: '%s per sesi akan dipakai untuk pemesanan berikutnya. Tarif yang sekarang berhenti berlaku.',
                    sorot: pulih.dataset.nilai,
                    tombol: 'Ya, berlakukan',
                    jenis: 'tanya',
                    glif: 'fa-undo',
                }).then(function (ya) {
                    if (ya) kirim(pulih.dataset.berlaku, 'POST').then(function (baik) {
                        if (baik) setTimeout(function () { window.location.reload(); }, 900);
                    });
                });

                return;
            }

            const hapus = e.target.closest('[data-hapus]');
            if (!hapus) return;

            window.misKonfirmasi({
                judul: 'Hapus tarif ini dari riwayat?',
                pesan: 'Baris %s dihapus permanen. Tarif yang masih jadi acuan harga sesi tidak bisa dihapus.',
                sorot: hapus.dataset.nilai,
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (ya) {
                if (ya) kirim(hapus.dataset.hapus, 'DELETE').then(function (baik) {
                    if (baik) hapus.closest('tr').remove();
                });
            });
        });
    })();
</script>
@endpush
