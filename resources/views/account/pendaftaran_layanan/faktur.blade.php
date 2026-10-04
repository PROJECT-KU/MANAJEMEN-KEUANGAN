{{--
  Faktur satu pesanan lembaga.

  Halaman berdiri sendiri tanpa kerangka account: yang dicetak hanya
  fakturnya, dan bilah samping aplikasi ikut tercetak kalau memakai
  kerangkanya.
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faktur {{ $lembaga->kode }} — {{ $lembaga->nama_lembaga }}</title>
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

        .fak {
            max-width: 760px;
            margin: 0 auto;
            padding: 28px 30px 30px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px -18px rgba(15, 23, 42, .5);
        }

        .fak-kop { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }
        .fak-kop img { width: 210px; max-width: 48%; height: auto; }
        .fak-kop-kanan { text-align: right; }
        .fak-kop-kanan p { margin: 0; }
        .fak-judul { font-size: 19px; font-weight: 800; letter-spacing: .02em; }
        .fak-kode { margin-top: 3px !important; font-size: 13px; color: #c2610c; font-weight: 700; }
        .fak-tanggal { margin-top: 3px !important; font-size: 12px; color: #64748b; }

        .fak-garis { height: 3px; margin: 16px 0 18px; border-radius: 3px;
            background: linear-gradient(90deg, #ea7a2c 0%, #f4a261 100%); }

        .fak-kepada { display: flex; gap: 24px; flex-wrap: wrap; }
        .fak-kepada > div { flex: 1 1 240px; min-width: 0; }
        .fak-label { margin: 0 0 4px; font-size: 10px; letter-spacing: .09em;
            text-transform: uppercase; color: #94a3b8; }
        .fak-nilai { margin: 0; line-height: 1.55; }
        .fak-nilai strong { font-size: 15px; }

        table.fak-rinci { width: 100%; margin-top: 22px; border-collapse: collapse; }
        table.fak-rinci th {
            padding: 8px 10px; text-align: left; font-size: 10px; letter-spacing: .07em;
            text-transform: uppercase; color: #64748b; background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        table.fak-rinci td { padding: 9px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.fak-rinci th.kanan, table.fak-rinci td.kanan { text-align: right; }
        .fak-sub { display: block; margin-top: 2px; font-size: 11px; color: #64748b; }

        .fak-jumlah { margin-top: 18px; display: flex; justify-content: flex-end; }
        .fak-jumlah table { border-collapse: collapse; min-width: 260px; }
        .fak-jumlah td { padding: 6px 10px; }
        .fak-jumlah tr.akhir td {
            border-top: 2px solid #0f172a; font-size: 17px; font-weight: 800; padding-top: 9px;
        }
        .fak-jumlah td:last-child { text-align: right; }

        .fak-nota { margin-top: 20px; padding: 12px 14px; border-left: 3px solid #ea7a2c;
            border-radius: 0 9px 9px 0; background: #fff7ed; font-size: 12px;
            line-height: 1.6; color: #9a3412; }

        .fak-kaki { margin: 22px 0 0; font-size: 11px; text-align: center; color: #94a3b8; }

        .fak-cetak {
            display: block; width: 100%; max-width: 760px; margin: 16px auto 0;
            padding: 11px; border: 0; border-radius: 11px; background: #4f46e5;
            color: #fff; font-size: 14px; font-weight: 700; cursor: pointer;
        }

        @media print {
            body { padding: 0; background: #fff; }
            .fak { max-width: none; box-shadow: none; border-radius: 0; padding: 0; }
            .fak-cetak { display: none; }
        }

        @media (max-width: 560px) {
            .fak { padding: 20px 16px 22px; }
            .fak-kop { flex-direction: column; }
            .fak-kop-kanan { text-align: left; }
        }
    </style>
</head>

<body>
    <div class="fak">
        <div class="fak-kop">
            <img src="{{ asset('assets/img/LogoRSC.png') }}" alt="Rumah Scopus Foundation">
            <div class="fak-kop-kanan">
                <p class="fak-judul">FAKTUR</p>
                <p class="fak-kode">{{ $lembaga->kode }}</p>
                <p class="fak-tanggal">
                    {{ $lembaga->created_at?->locale('id')->translatedFormat('j F Y') }}
                </p>
            </div>
        </div>

        <div class="fak-garis"></div>

        <div class="fak-kepada">
            <div>
                <p class="fak-label">Ditagihkan kepada</p>
                <p class="fak-nilai">
                    <strong>{{ $lembaga->nama_lembaga }}</strong>
                    @if ($lembaga->alamat)
                        <br>{{ $lembaga->alamat }}
                    @endif
                    @if ($lembaga->npwp)
                        <br>NPWP: {{ $lembaga->npwp }}
                    @endif
                    @if ($lembaga->no_po)
                        <br>Nomor surat pesanan: {{ $lembaga->no_po }}
                    @endif
                </p>
            </div>
            <div>
                <p class="fak-label">Narahubung</p>
                <p class="fak-nilai">
                    {{ $lembaga->pic_nama }}
                    @if ($lembaga->pic_email)
                        <br>{{ $lembaga->pic_email }}
                    @endif
                    @if ($lembaga->pic_telp)
                        <br>{{ $lembaga->pic_telp }}
                    @endif
                </p>
            </div>
        </div>

        <table class="fak-rinci">
            <thead>
                <tr>
                    <th>Pendaftaran</th>
                    <th>Peserta</th>
                    <th class="kanan">Kursi</th>
                    <th class="kanan">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($baris as $b)
                    @php
                        $angkatanBaris = Pendaftaran::angkatanBaris($b);
                        $keadaan = Pendaftaran::KEADAAN[Pendaftaran::keadaanDari($b->status)] ?? null;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $b->nomor }}</strong>
                            <span class="fak-sub">
                                {{ $katalog[$b->layanan]['nama'] ?? $b->layanan }}@if (! empty($angkatanBaris['nama'])) &middot; {{ $angkatanBaris['nama'] }}@endif@if (! empty($angkatanBaris['nomor'])) (ke-{{ $angkatanBaris['nomor'] }})@endif
                            </span>
                        </td>
                        <td>
                            {{ $b->nama_orang }}
                            @if ($keadaan)
                                <span class="fak-sub">{{ $keadaan['label'] }}</span>
                            @endif
                        </td>
                        <td class="kanan">{{ max(1, (int) $b->jumlah) }}</td>
                        <td class="kanan">Rp {{ number_format((int) $b->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="color:#94a3b8">
                            Belum ada pendaftaran yang diikat ke pesanan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="fak-jumlah">
            <table>
                <tr>
                    <td style="color:#64748b">Jumlah kursi</td>
                    <td><strong>{{ $jumlahKursi }}</strong></td>
                </tr>
                <tr class="akhir">
                    <td>Total</td>
                    <td>Rp {{ number_format($jumlahUang, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- Tiap pendaftaran punya kode uniknya sendiri, jadi menagihkan satu
             angka gabungan justru membuat pembayarannya tidak bisa
             dicocokkan. Disebut apa adanya supaya tidak ada yang mencoba. --}}
        <p class="fak-nota">
            Pembayarannya <strong>per pendaftaran</strong>, bukan satu transfer gabungan.
            Tiap nomor di atas punya nominal sendiri sampai angka terakhirnya — angka
            itulah yang membuat tiap pembayaran bisa kami kenali. Mohon ditransfer
            terpisah sesuai nominal masing-masing.
        </p>

        <p class="fak-kaki">
            Dicetak {{ now()->locale('id')->translatedFormat('j F Y, H:i') }} ·
            Rumah Scopus Foundation
        </p>
    </div>

    <button type="button" class="fak-cetak" onclick="window.print()">Cetak faktur ini</button>
</body>

</html>
