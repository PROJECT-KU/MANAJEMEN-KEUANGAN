{{--
  Kwitansi satu kali uang masuk — DP, cicilan, atau pelunasan.

  Halaman berdiri sendiri tanpa kerangka account, sama seperti faktur dan
  slip: yang dicetak hanya kwitansinya, dan bilah samping aplikasi ikut
  tercetak kalau memakai kerangkanya.

  Bergaya cetak, bukan PDF. Membuat PDF di peladen menambah satu kemungkinan
  gagal, sedangkan Ctrl+P sudah menghasilkan berkas yang bisa dilampirkan ke
  email lembaganya.
--}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kwitansi {{ $bayar->getKey() }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 26px 14px;
            background: #f1f5f9;
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 13px;
        }

        .kwi {
            max-width: 620px;
            margin: 0 auto;
            padding: 28px 30px 30px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px -18px rgba(15, 23, 42, .5);
        }

        .kwi-kop { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }
        .kwi-kop img { width: 190px; max-width: 48%; height: auto; }
        .kwi-kop-kanan { text-align: right; }
        .kwi-kop-kanan p { margin: 0; }
        .kwi-judul { font-size: 19px; font-weight: 800; letter-spacing: .02em; }
        .kwi-termin { margin-top: 3px !important; font-size: 13px; color: #c2610c; font-weight: 700; }
        .kwi-tanggal { margin-top: 3px !important; font-size: 12px; color: #64748b; }

        .kwi-garis { height: 3px; margin: 16px 0 18px; border-radius: 3px;
            background: linear-gradient(90deg, #ea7a2c 0%, #f4a261 100%); }

        .kwi-label { margin: 0 0 4px; font-size: 10px; letter-spacing: .09em;
            text-transform: uppercase; color: #94a3b8; }
        .kwi-nilai { margin: 0 0 16px; line-height: 1.55; }
        .kwi-nilai strong { font-size: 15px; }

        /* Nominalnya besar dan sendirian: ini satu-satunya angka yang dicari
           orang saat kwitansi dibuka. */
        .kwi-nominal {
            margin: 18px 0;
            padding: 16px 18px;
            border-radius: 11px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .kwi-nominal strong { display: block; font-size: 26px; letter-spacing: -.01em; }
        .kwi-nominal span { font-size: 11px; color: #64748b; }

        table.kwi-rinci { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.kwi-rinci td { padding: 6px 0; vertical-align: top; }
        table.kwi-rinci td:first-child { color: #64748b; width: 42%; }
        table.kwi-rinci td:last-child { text-align: right; font-weight: 600; }
        table.kwi-rinci tr.akhir td {
            border-top: 1px solid #e2e8f0; padding-top: 9px; font-size: 15px; font-weight: 800;
        }

        .kwi-nota {
            margin-top: 18px; padding: 11px 13px; border-radius: 9px;
            background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412;
            font-size: 11.5px; line-height: 1.6;
        }

        .kwi-ttd { margin-top: 26px; display: flex; justify-content: flex-end; }
        .kwi-ttd div { text-align: center; min-width: 190px; }
        .kwi-ttd p { margin: 0; font-size: 11.5px; color: #64748b; }
        .kwi-ttd .nama { margin-top: 46px; font-weight: 700; color: #0f172a;
            border-top: 1px solid #cbd5e1; padding-top: 6px; }

        .kwi-kaki { margin-top: 20px; font-size: 10.5px; color: #94a3b8; text-align: center; }

        @media print {
            body { background: #fff; padding: 0; }
            .kwi { box-shadow: none; border-radius: 0; max-width: none; padding: 0; }
        }

        @media (max-width: 575.98px) {
            .kwi { padding: 20px 17px 22px; }
            .kwi-kop { flex-direction: column; }
            .kwi-kop-kanan { text-align: left; }
        }
    </style>
</head>

<body>
    <div class="kwi">
        <div class="kwi-kop">
            <img src="{{ asset('assets/img/LogoRSC.png') }}" alt="Rumah Scopus Foundation">
            <div class="kwi-kop-kanan">
                <p class="kwi-judul">KWITANSI</p>
                <p class="kwi-termin">
                    {{ $bayar->sebutan($ringkas['lunas'], $semua->count()) }}
                </p>
                <p class="kwi-tanggal">
                    {{ $bayar->tanggal?->locale('id')->translatedFormat('j F Y') }}
                </p>
            </div>
        </div>

        <div class="kwi-garis"></div>

        <p class="kwi-label">Telah diterima dari</p>
        <p class="kwi-nilai">
            <strong>{{ $untuk }}</strong>
            @if ($lembaga)
                @if ($lembaga->alamat)
                    <br>{{ $lembaga->alamat }}
                @endif
                @if ($lembaga->npwp)
                    <br>NPWP: {{ $lembaga->npwp }}
                @endif
                <br>Pesanan: {{ $lembaga->kode }}
            @endif
        </p>

        <div class="kwi-nominal">
            <strong>Rp {{ number_format((int) $bayar->nominal, 0, ',', '.') }}</strong>
            <span>
                {{ $bayar->cara_bayar_terbaca }}@if ($bayar->catatan) &middot; {{ $bayar->catatan }}@endif
            </span>
        </div>

        {{-- Tagihan dan sisanya ikut tercetak, bukan nominal terminnya saja.

             Kwitansi yang hanya menyebut "Rp 10.000.000 diterima" tidak
             menjawab pertanyaan yang dibawa orang keuangan lembaga saat
             memegangnya: tinggal berapa. --}}
        <table class="kwi-rinci">
            <tr>
                <td>Total tagihan</td>
                <td>Rp {{ number_format($ringkas['tagihan'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Sudah dibayar ({{ $ringkas['jumlah'] }}&times;)</td>
                <td>Rp {{ number_format($ringkas['terbayar'], 0, ',', '.') }}</td>
            </tr>
            <tr class="akhir">
                <td>Sisa tagihan</td>
                <td>
                    @if ($ringkas['lunas'])
                        LUNAS
                    @else
                        Rp {{ number_format($ringkas['sisa'], 0, ',', '.') }}
                    @endif
                </td>
            </tr>
        </table>

        @if (! $ringkas['lunas'])
            <p class="kwi-nota">
                Kwitansi ini untuk pembayaran <strong>sebagian</strong>. Sisanya
                Rp {{ number_format($ringkas['sisa'], 0, ',', '.') }} masih terutang, dan
                kursinya sudah ditahan sejak pendaftarannya dicatat.
            </p>
        @endif

        <div class="kwi-ttd">
            <div>
                <p>Diterima oleh,</p>
                <p class="nama">{{ $bayar->dicatat_oleh ?: 'Panitia Rumah Scopus' }}</p>
            </div>
        </div>

        <p class="kwi-kaki">
            Dicetak {{ now()->locale('id')->translatedFormat('j F Y, H:i') }} &middot;
            Rumah Scopus Foundation
        </p>
    </div>
</body>

</html>
