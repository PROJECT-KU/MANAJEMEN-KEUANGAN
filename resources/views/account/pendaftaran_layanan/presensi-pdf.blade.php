{{--
  Lembar presensi peserta satu angkatan, dicetak lewat Dompdf.

  DITULIS DENGAN TABEL DAN GAYA SEBARIS seperti berkas ekspor-pdf di
  sebelahnya: Dompdf tidak mengenal flexbox maupun grid, dan tata letak
  yang memakainya runtuh jadi satu lajur tanpa galat apa pun.

  Logonya LOGO YAYASAN, bukan logo MIS. Lembar ini dipegang peserta di meja
  registrasi — bukan orang dalam — jadi yang pantas tampil merek
  yayasannya. Berkas ukuran situs (1906 px) yang dipakai, bukan ukuran
  surat: ini cetakan, bukan email, dan di kertas kerapatan piksel justru
  yang menentukan.

  Disisipkan sebagai data URI, bukan ditautkan: Dompdf dijalankan dengan
  isRemoteEnabled = false, jadi alamat http apa pun tidak akan diambil dan
  logonya hilang tanpa pesan.
--}}
@php
    $berkasLogo = public_path('assets/img/LogoRSC.png');
    $logo = is_file($berkasLogo)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($berkasLogo))
        : null;

    $belum = count(array_filter($orang, fn ($o) => ! $o['terisi']));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Presensi Peserta</title>
    <style>
        @page { margin: 26mm 14mm 20mm 14mm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            margin: 0;
        }

        /* Kepala dan kaki diulang di SETIAP halaman lewat posisi tetap.
           Lembar presensi hampir selalu lebih dari satu halaman, dan
           halaman kedua tanpa kepala tidak menunjuk angkatan mana pun. */
        .kepala { position: fixed; top: -20mm; left: 0; right: 0; }
        .kaki   { position: fixed; bottom: -14mm; left: 0; right: 0; }

        .judul {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1.2px;
            color: #0f172a;
            margin: 0;
        }

        .anak-judul { font-size: 10.5px; color: #475569; margin: 3px 0 0 0; }

        .garis-merek { height: 3px; background: #ea580c; margin-top: 7px; }

        .pita {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 7px 10px;
            margin: 0 0 9px 0;
        }

        .pita td { font-size: 9px; color: #475569; padding: 1px 0; }
        .pita .label { color: #94a3b8; }
        .pita .nilai { font-weight: bold; color: #0f172a; }

        table.daftar { width: 100%; border-collapse: collapse; }

        table.daftar th {
            background: #0f172a;
            color: #ffffff;
            font-size: 8.5px;
            letter-spacing: .4px;
            padding: 6px 5px;
            border: 1px solid #0f172a;
        }

        table.daftar td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            vertical-align: middle;
        }

        /* Baris berselang-seling: di lembar yang dibaca sambil berdiri,
           mata kehilangan barisnya tanpa pembeda apa pun. */
        table.daftar tr.selang td { background: #f8fafc; }

        .tengah { text-align: center; }
        .kanan  { text-align: right; }

        .nama { font-weight: bold; color: #0f172a; }
        .samar { color: #94a3b8; font-style: italic; font-weight: normal; }
        .kecil { font-size: 8px; color: #64748b; }

        /* Penanda rombongan, HANYA untuk yang bukan pendaftarnya.
           Dicetak pada setiap baris, lencananya muncul di hampir semua
           baris — terukur pada angkatan nyata: 11 dari 11 — lalu memecah
           nama panjang ke baris kedua tanpa menerangkan apa pun. Yang
           perlu dibedakan justru yang sedikit. */
        .rombongan {
            display: block;
            font-size: 7.5px;
            color: #6366f1;
            font-weight: normal;
        }

        /* Kotak tanda tangan: tingginya yang membuat lembar ini bisa
           ditandatangani di atas meja, bukan lebarnya. */
        /* 34px, bukan 26px. Dicoba di atas kertas, tanda tangan dewasa
           tidak muat di 26px dan meluber ke baris tetangganya. */
        .ttd { height: 34px; }

        .tandatangan-panitia { margin-top: 18px; }
        .tandatangan-panitia td { font-size: 9px; color: #475569; padding: 0; }
    </style>
</head>
<body>

<div class="kepala">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            @if ($logo)
                <td width="150" valign="middle">
                    <img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="140">
                </td>
            @endif
            <td valign="middle" class="tengah">
                <p class="judul">DAFTAR PRESENSI PESERTA</p>
                <p class="anak-judul">
                    {{ $angkatan['layanan'] ?? 'Layanan Rumah Scopus' }}
                    @if (($angkatan['nomor'] ?? null) !== null)
                        &middot; Angkatan ke-{{ $angkatan['nomor'] }}
                    @endif
                </p>
            </td>
            {{-- Lajur kosong selebar logonya: tanpa ini judulnya terpusat
                 pada sisa lebar, bukan pada lebar kertasnya. --}}
            @if ($logo)
                <td width="150"></td>
            @endif
        </tr>
    </table>
    <div class="garis-merek"></div>
</div>

<div class="kaki">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="kecil">Rumah Scopus Foundation &middot; dicetak {{ $dicetak }} WIB</td>
            {{-- Nomor halaman TIDAK dicetak di sini melainkan lewat
                 page_text() di pengendalinya. counter(pages) milik CSS
                 dijawab 0 oleh Dompdf — terukur: kakinya berbunyi "Halaman
                 1 dari 0". --}}
            <td></td>
        </tr>
    </table>
</div>

<table class="pita" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="50%">
            <span class="label">Angkatan</span>
            <span class="nilai">{{ $angkatan['nama'] ?? '—' }}</span>
        </td>
        <td width="25%">
            <span class="label">Mulai</span>
            <span class="nilai">{{ $angkatan['mulai'] ?? '—' }}</span>
        </td>
        <td width="25%" class="kanan">
            <span class="label">Jumlah peserta</span>
            <span class="nilai">{{ count($orang) }} orang</span>
        </td>
    </tr>
    @if ($belum > 0 || $saringan !== [])
        <tr>
            <td colspan="3" style="padding-top:4px">
                @if ($belum > 0)
                    {{-- Disebut di MUKA, bukan disembunyikan: yang memegang
                         lembar ini perlu tahu ada kursi yang namanya memang
                         belum pernah ditanyakan, bukan salah cetak. --}}
                    <span class="label">Belum berisi nama</span>
                    <span class="nilai">{{ $belum }} kursi</span>
                    @if ($saringan !== []) <span class="label">&middot;</span> @endif
                @endif
                @if ($saringan !== [])
                    <span class="label">Saringan</span>
                    <span>{{ implode(' · ', array_map(
                        fn ($nama, $nilai) => $nama . ': ' . $nilai,
                        array_keys($saringan), $saringan)) }}</span>
                @endif
            </td>
        </tr>
    @endif
</table>

<table class="daftar">
    <thead>
        <tr>
            <th width="4%">NO</th>
            <th width="26%" style="text-align:left">NAMA PESERTA</th>
            <th width="20%" style="text-align:left">ASAL / AFILIASI</th>
            <th width="13%">NOMOR DAFTAR</th>
            <th width="14%">TELEPON</th>
            <th width="23%">TANDA TANGAN</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($orang as $i => $o)
            <tr class="{{ $i % 2 === 1 ? 'selang' : '' }}">
                <td class="tengah">{{ $i + 1 }}</td>
                <td>
                    <span class="nama {{ $o['terisi'] ? '' : 'samar' }}">{{ $o['nama'] }}</span>
                    @if ($o['peran'] !== 'Pendaftar')
                        <span class="rombongan">peserta rombongan</span>
                    @endif
                </td>
                <td>{{ $o['afiliasi'] !== '' ? $o['afiliasi'] : '—' }}</td>
                <td class="tengah kecil">{{ strtoupper($o['nomor']) }}</td>
                <td class="tengah">{{ $o['telp'] !== '' ? $o['telp'] : '—' }}</td>
                {{-- Nomor barisnya diulang di kotak tanda tangan. Lembar
                     presensi dibaca menyamping sambil berdiri, dan tanpa
                     penanda di ujung kanan orang menandatangani baris
                     tetangganya. --}}
                <td class="ttd kanan kecil">{{ $i + 1 }}.</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="tengah samar" style="padding:22px">
                    Tidak ada peserta pada angkatan ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<table class="tandatangan-panitia" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="60%"></td>
        <td width="40%" class="tengah">
            <p style="margin:0 0 42px 0">Panitia Rumah Scopus Foundation</p>
            <p style="margin:0; border-top:1px solid #94a3b8; padding-top:4px">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</p>
        </td>
    </tr>
</table>

</body>
</html>
