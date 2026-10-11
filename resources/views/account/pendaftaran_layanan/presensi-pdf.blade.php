{{--
  Lembar presensi peserta satu angkatan, dicetak lewat Dompdf.

  DITULIS DENGAN TABEL DAN GAYA SEBARIS seperti berkas ekspor-pdf di
  sebelahnya: Dompdf tidak mengenal flexbox maupun grid, dan tata letak yang
  memakainya runtuh jadi satu lajur tanpa galat apa pun. Jarak antar kotak
  karena itu dibuat dengan sel pemisah berlebar tetap, bukan dengan gap.

  Logonya LOGO YAYASAN, bukan logo MIS. Lembar ini dipegang peserta di meja
  registrasi — bukan orang dalam — jadi yang pantas tampil merek yayasannya.
  Berkas ukuran situs (1906 px) yang dipakai, bukan ukuran surat: ini
  cetakan, dan di kertas kerapatan piksel justru yang menentukan.

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

    // Rentang tanggal dirakit di sini supaya markahnya tidak bercabang tiga.
    $rentang = $angkatan['mulai'] ?? null;

    if ($rentang !== null && ($angkatan['selesai'] ?? null) !== null
        && $angkatan['selesai'] !== $angkatan['mulai']) {
        $rentang .= ' – ' . $angkatan['selesai'];
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Presensi Peserta</title>
    <style>
        @page { margin: 30mm 13mm 18mm 13mm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            margin: 0;
        }

        /* Kepala dan kaki diulang di SETIAP halaman lewat posisi tetap.
           Lembar presensi hampir selalu lebih dari satu halaman, dan halaman
           kedua tanpa kepala tidak menunjuk angkatan mana pun. */
        .kepala { position: fixed; top: -24mm; left: 0; right: 0; }
        .kaki   { position: fixed; bottom: -12mm; left: 0; right: 0; }

        .judul {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1.4px;
            color: #0f172a;
            margin: 0;
            /* Dicoba lebih dulu pada 16px/2px dengan logo 142px: judulnya
               terpatah jadi "DAFTAR PRESENSI" / "PESERTA" dua baris, dan
               kepalanya tidak lagi terbaca sebagai satu judul. */
            white-space: nowrap;
        }

        .anak-judul {
            font-size: 10px;
            color: #ea580c;
            letter-spacing: .6px;
            margin: 4px 0 0 0;
        }

        /* Dua garis beda tebal: yang tebal pendek berwarna merek, yang tipis
           penuh berwarna muda. Satu garis saja terbaca seperti pembatas
           seadanya; dua membuat kepalanya terbaca sebagai kop. */
        .garis-tebal { height: 2.5px; background: #ea580c; width: 72px; }
        .garis-tipis { height: 1px; background: #e2e8f0; }

        /* ---------------------------------------------- pita keterangan */
        .kotak {
            border: 1px solid #e2e8f0;
            border-left: 3px solid #cbd5e1;
            background: #f8fafc;
            padding: 7px 9px;
        }

        .kotak-utama { border-left-color: #ea580c; background: #fff7ed; }
        .kotak-angka { border-left-color: #6366f1; background: #eef2ff; }

        .kotak .label {
            display: block;
            font-size: 7px;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .kotak .nilai { font-size: 10px; font-weight: bold; color: #0f172a; }

        .catatan-kurang {
            margin: 7px 0 0 0;
            padding: 5px 9px;
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
            font-size: 8.5px;
        }

        /* ------------------------------------------------------- tabel */
        table.daftar { width: 100%; border-collapse: collapse; margin-top: 11px; }

        table.daftar th {
            background: #1e293b;
            color: #ffffff;
            font-size: 8px;
            letter-spacing: .8px;
            padding: 7px 5px;
            border: 1px solid #1e293b;
        }

        table.daftar td {
            border: 1px solid #e2e8f0;
            padding: 6px 5px;
            vertical-align: middle;
        }

        /* Baris berselang-seling: di lembar yang dibaca sambil berdiri, mata
           kehilangan barisnya tanpa pembeda apa pun. */
        table.daftar tr.selang td { background: #f8fafc; }

        /* Lajur nomor diberi warna sendiri supaya ujung kiri tabel punya
           jangkar — tanpa itu barisnya terbaca melayang. */
        td.no {
            background: #f1f5f9 !important;
            text-align: center;
            font-weight: bold;
            color: #475569;
            font-size: 9px;
        }

        .tengah { text-align: center; }
        .kanan  { text-align: right; }

        .nama { font-weight: bold; color: #0f172a; font-size: 10px; }
        .samar { color: #94a3b8; font-style: italic; font-weight: normal; }
        .kecil { font-size: 8px; color: #64748b; }

        .nomor-daftar {
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 8px;
            color: #475569;
            letter-spacing: .5px;
        }

        /* Penanda rombongan, HANYA untuk yang bukan pendaftarnya. Dicetak di
           setiap baris, lencananya muncul di hampir semua baris — terukur
           pada angkatan nyata: 11 dari 11 — lalu memecah nama panjang ke
           baris kedua tanpa menerangkan apa pun. */
        .rombongan {
            display: block;
            font-size: 7.5px;
            color: #6366f1;
            font-weight: normal;
        }

        /* Kotak tanda tangan: tingginya yang membuat lembar ini bisa
           ditandatangani di atas meja, bukan lebarnya. Dicoba di atas
           kertas, tanda tangan dewasa tidak muat di 26px. */
        .ttd { height: 34px; }

        /* --------------------------------------------------- tanda tangan */
        .blok-ttd { margin-top: 20px; }
        .blok-ttd td { font-size: 9px; color: #475569; }

        .ttd-peran {
            font-size: 8px;
            letter-spacing: 1px;
            color: #94a3b8;
            margin: 0 0 2px 0;
        }

        .ttd-lembaga { font-weight: bold; color: #0f172a; margin: 0 0 46px 0; }

        .ttd-garis {
            border-top: 1px solid #94a3b8;
            padding-top: 5px;
            margin: 0;
            color: #94a3b8;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

<div class="kepala">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            @if ($logo)
                <td width="132" valign="middle">
                    <img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="124">
                </td>
            @endif
            <td valign="middle" class="tengah">
                <p class="judul">DAFTAR PRESENSI PESERTA</p>
                <p class="anak-judul">
                    {{ strtoupper($angkatan['layanan'] ?? 'LAYANAN RUMAH SCOPUS') }}
                    @if (($angkatan['nomor'] ?? null) !== null)
                        &middot; ANGKATAN KE-{{ $angkatan['nomor'] }}
                    @endif
                </p>
            </td>
            {{-- Lajur kosong selebar logonya: tanpa ini judulnya terpusat pada
                 sisa lebar, bukan pada lebar kertasnya. --}}
            @if ($logo)
                <td width="132"></td>
            @endif
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:8px">
        <tr>
            <td width="72"><div class="garis-tebal"></div></td>
            <td><div class="garis-tipis"></div></td>
        </tr>
    </table>
</div>

<div class="kaki">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="kecil">Rumah Scopus Foundation &middot; dicetak {{ $dicetak }} WIB</td>
            {{-- Nomor halaman TIDAK dicetak di sini melainkan lewat page_text()
                 di pengendalinya. counter(pages) milik CSS dijawab 0 oleh
                 Dompdf — terukur: kakinya berbunyi "Halaman 1 dari 0". --}}
            <td></td>
        </tr>
    </table>
</div>

{{-- Pita keterangan: kotak terpisah, bukan satu baris panjang. Satu baris
     membuat empat keterangan berbeda terbaca seperti satu kalimat, dan yang
     paling dicari di meja registrasi — nama angkatannya — tidak menonjol
     dari sisanya. --}}
<table width="100%" cellpadding="0" cellspacing="0">
    <tr valign="top">
        <td width="40%" class="kotak kotak-utama">
            <span class="label">ANGKATAN</span>
            <span class="nilai">{{ $angkatan['nama'] ?? '—' }}</span>
        </td>
        <td width="7"></td>
        <td class="kotak">
            <span class="label">PELAKSANAAN</span>
            <span class="nilai">{{ $rentang ?? '—' }}</span>
        </td>
        <td width="7"></td>
        <td class="kotak">
            <span class="label">LOKASI</span>
            <span class="nilai">{{ ($angkatan['lokasi'] ?? '') !== '' ? $angkatan['lokasi'] : '—' }}</span>
        </td>
        <td width="7"></td>
        <td width="15%" class="kotak kotak-angka tengah">
            <span class="label">PESERTA</span>
            <span class="nilai">{{ count($orang) }} orang</span>
        </td>
    </tr>
</table>

@if ($belum > 0 || $saringan !== [])
    <p class="catatan-kurang">
        @if ($belum > 0)
            {{-- Disebut di MUKA, bukan disembunyikan: yang memegang lembar ini
                 perlu tahu ada kursi yang namanya memang belum pernah
                 ditanyakan, bukan salah cetak. --}}
            <strong>{{ $belum }} kursi</strong> sudah dibayar tetapi namanya belum diisi.
        @endif
        @if ($saringan !== [])
            Saringan: {{ implode(' · ', array_map(
                fn ($nama, $nilai) => $nama . ': ' . $nilai,
                array_keys($saringan), $saringan)) }}.
        @endif
    </p>
@endif

<table class="daftar">
    <thead>
        <tr>
            <th width="5%">NO</th>
            <th width="27%" style="text-align:left">NAMA PESERTA</th>
            <th width="21%" style="text-align:left">ASAL / AFILIASI</th>
            <th width="12%">NO. DAFTAR</th>
            <th width="14%">TELEPON</th>
            <th width="21%">TANDA TANGAN</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($orang as $i => $o)
            <tr class="{{ $i % 2 === 1 ? 'selang' : '' }}">
                <td class="no">{{ $i + 1 }}</td>
                <td>
                    <span class="nama {{ $o['terisi'] ? '' : 'samar' }}">{{ $o['nama'] }}</span>
                    @if ($o['peran'] !== 'Pendaftar')
                        <span class="rombongan">peserta rombongan</span>
                    @endif
                </td>
                <td>{{ $o['afiliasi'] !== '' ? $o['afiliasi'] : '—' }}</td>
                <td class="tengah nomor-daftar">{{ strtoupper($o['nomor']) }}</td>
                <td class="tengah">{{ $o['telp'] !== '' ? $o['telp'] : '—' }}</td>
                {{-- Nomor barisnya diulang di kotak tanda tangan. Lembar
                     presensi dibaca menyamping sambil berdiri, dan tanpa
                     penanda di ujung kanan orang menandatangani baris
                     tetangganya. --}}
                <td class="ttd kanan kecil">{{ $i + 1 }}.</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="tengah samar" style="padding:24px">
                    Tidak ada peserta pada angkatan ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<table class="blok-ttd" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="58%"></td>
        <td width="42%" class="tengah">
            {{-- "Penanggung jawab angkatan", bukan "Panitia Rumah Scopus
                 Foundation". Nama lembaga tidak menunjuk siapa pun, sehingga
                 tidak ada yang bisa dituju kalau lembarnya dipersoalkan
                 kemudian. Namanya ditulis tangan di atas garis. --}}
            <p class="ttd-peran">PENANGGUNG JAWAB ANGKATAN</p>
            <p class="ttd-lembaga">Rumah Scopus Foundation</p>
            <p class="ttd-garis">nama terang &amp; tanda tangan</p>
        </td>
    </tr>
</table>

</body>
</html>
