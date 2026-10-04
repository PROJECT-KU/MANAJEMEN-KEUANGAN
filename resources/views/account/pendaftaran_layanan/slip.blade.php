{{--
  Slip pendaftaran untuk dicetak dan diberikan ke orangnya.

  Halaman berdiri sendiri, tanpa kerangka account: yang dicetak hanya
  slipnya, dan bilah samping aplikasi ikut tercetak kalau memakai
  kerangkanya. Gayanya sebaris di berkas ini, bukan di mis-ui.css — yang
  ini satu-satunya halaman yang memakainya.
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    $kodeUnik = (int) ($baris->kode_unik ?: 0);
    $total = (int) $baris->total;
    /*
     * Nominalnya memuat kode unik kalau selisihnya kelipatan seribu.
     *
     * Pemeriksaan lama membandingkan tiga angka terakhir dengan kodenya, dan
     * itu rusak sejak rentangnya diseragamkan ke 500-1500: kode 1.188 tidak
     * muat di tiga angka. Pemeriksaan ini berlaku untuk rentang mana pun, dan
     * sengaja berhati-hati — nominal yang diketik tangan dengan ujung bukan
     * nol membuatnya menahan catatannya, bukan menampilkan yang salah.
     */
    $kodeMasuk = $kodeUnik > 0 && $total > $kodeUnik && ($total - $kodeUnik) % 1000 === 0;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip {{ $baris->nomor }} — {{ $nama }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px 14px;
            background: #f1f5f9;
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            color: #0f172a;
        }

        .slip {
            max-width: 420px;
            margin: 0 auto;
            padding: 22px 24px 26px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px -18px rgba(15, 23, 42, .5);
        }

        .slip-kop { text-align: center; }
        .slip-kop img { width: 190px; max-width: 76%; height: auto; }

        .slip-garis {
            height: 3px;
            margin: 14px 0 16px;
            border-radius: 3px;
            background: linear-gradient(90deg, #ea7a2c 0%, #f4a261 100%);
        }

        .slip-judul {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            text-align: center;
        }

        .slip-layanan {
            margin: 3px 0 0;
            font-size: 12px;
            text-align: center;
            color: #64748b;
        }

        .slip-nomor {
            margin: 16px 0 0;
            padding: 11px;
            text-align: center;
            border: 1px dashed #f6c9a3;
            border-radius: 11px;
            background: #fff7ed;
        }

        .slip-nomor span {
            display: block;
            font-size: 10px;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #c2610c;
        }

        .slip-nomor strong {
            display: block;
            margin-top: 2px;
            font-size: 21px;
            letter-spacing: .06em;
        }

        table.slip-rinci { width: 100%; margin-top: 16px; border-collapse: collapse; font-size: 13px; }
        table.slip-rinci td { padding: 6px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.slip-rinci td:first-child { color: #64748b; }
        table.slip-rinci td:last-child { text-align: right; font-weight: 700; }

        .slip-total {
            margin-top: 16px;
            padding: 13px;
            text-align: center;
            border-radius: 11px;
            background: #f8fafc;
        }

        .slip-total span {
            display: block;
            font-size: 10px;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #64748b;
        }

        .slip-total strong { display: block; margin-top: 2px; font-size: 22px; }

        .slip-nota {
            margin-top: 14px;
            padding: 10px 12px;
            border-left: 3px solid #ea7a2c;
            border-radius: 0 9px 9px 0;
            background: #fff7ed;
            font-size: 12px;
            line-height: 1.55;
            color: #9a3412;
        }

        .slip-peserta { margin: 14px 0 0; padding-left: 18px; font-size: 12px; line-height: 1.7; }
        .slip-kaki { margin: 18px 0 0; font-size: 11px; text-align: center; color: #94a3b8; }

        .slip-cetak {
            display: block;
            width: 100%;
            max-width: 420px;
            margin: 16px auto 0;
            padding: 11px;
            border: 0;
            border-radius: 11px;
            background: #4f46e5;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        /* Yang tercetak hanya slipnya: tombol dan latar layar dibuang. */
        @media print {
            body { padding: 0; background: #fff; }
            .slip { max-width: none; box-shadow: none; border-radius: 0; }
            .slip-cetak { display: none; }
        }
    </style>
</head>

<body>
    <div class="slip">
        <div class="slip-kop">
            <img src="{{ asset('assets/img/LogoRSC.png') }}" alt="Rumah Scopus Foundation">
        </div>

        <div class="slip-garis"></div>

        <p class="slip-judul">Bukti Pendaftaran</p>
        <p class="slip-layanan">{{ $nama }}</p>

        <div class="slip-nomor">
            <span>Nomor pendaftaran</span>
            <strong>{{ $baris->nomor }}</strong>
        </div>

        <table class="slip-rinci">
            <tr>
                <td>Nama</td>
                <td>{{ $baris->nama_orang }}</td>
            </tr>
            @if (! empty($angkatan['nama']))
                <tr>
                    <td>Angkatan</td>
                    <td>
                        {{ $angkatan['nama'] }}@if (! empty($angkatan['nomor'])) <br>angkatan ke-{{ $angkatan['nomor'] }}@endif
                    </td>
                </tr>
            @endif
            @if (! empty($angkatan['mulai']))
                <tr>
                    <td>Mulai</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($angkatan['mulai'])->locale('id')->translatedFormat('j F Y') }}</td>
                </tr>
            @endif
            <tr>
                <td>Cara bayar</td>
                <td>{{ $caraBayar['label'] }}</td>
            </tr>
            @if ($keadaan)
                <tr>
                    <td>Keadaan</td>
                    <td>{{ $keadaan['label'] }}</td>
                </tr>
            @endif
        </table>

        <div class="slip-total">
            <span>Total bayar</span>
            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
        </div>

        {{-- Catatan ini HANYA kalau nominalnya memang berakhir dengan kode
             uniknya. Baris lama dari sebelum kodenya dimasukkan ke total
             berakhir 000, dan menyuruh orang "transfer persis sampai angka
             terakhirnya — 000" menyuruhnya mencocokkan sesuatu yang tidak
             ada. --}}
        @if ($kodeMasuk && $caraBayar['kunci'] !== 'tunai')
            {{-- Alasan angka ganjilnya disebut; tanpa itu sebagian orang
                 membulatkannya lalu pembayarannya tidak bisa dicocokkan. --}}
            {{-- Kelebihannya disebut sebagai NOMINAL, bukan "tiga angka
                 terakhir": sejak rentangnya 500-1500, kodenya bisa empat
                 angka dan kalimat lama jadi salah. --}}
            <p class="slip-nota">
                Mohon transfer <strong>persis sampai rupiah terakhirnya</strong>.
                Nominalnya sudah dilebihkan <strong>Rp {{ number_format($kodeUnik, 0, ',', '.') }}</strong>
                sebagai penanda pendaftaran Anda — bukan kelebihan bayar, jadi
                mohon jangan dibulatkan.
            </p>
        @endif

        @if ($peserta->isNotEmpty())
            <p style="margin:16px 0 0;font-size:12px;color:#64748b">Peserta lain:</p>
            <ol class="slip-peserta">
                @foreach ($peserta as $p)
                    {{-- Nomornya ikut dicetak: slip ini yang dipegang panitia
                         di meja daftar ulang, dan peserta yang belum datang
                         dihubungi dari situ juga. --}}
                    <li>{{ $p->nama }}@if (! empty($p->telp)) · {{ \App\Support\DaftarPeserta::bentukLokal($p->telp) }}@endif</li>
                @endforeach
            </ol>
        @endif

        <p class="slip-kaki">
            Dicetak {{ now()->locale('id')->translatedFormat('j F Y, H:i') }} ·
            Rumah Scopus Foundation
        </p>
    </div>

    <button type="button" class="slip-cetak" onclick="window.print()">Cetak slip ini</button>
</body>

</html>
