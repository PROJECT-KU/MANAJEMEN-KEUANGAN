{{--
  Promo layanan untuk PDF.

  Cetakannya mengikuti satu bentuk yang sama dengan berkas lain yang keluar
  dari MIS: kop berisi nama lembaga, judul, tanggal cetak, DAN saringan yang
  dipakai. Yang terakhir wajib — berkas berisi 29 dari 101 baris tidak boleh
  terbaca seperti daftar yang lengkap.

  Gaya ditulis sebaris di sini, bukan menumpang mis-ui.css: Dompdf tidak
  memuat berkas luar (setIsRemoteEnabled(false)), jadi kelas bersama tidak
  akan sampai dan hasilnya halaman tanpa gaya sama sekali.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Promo Layanan</title>
    <style>
        @page { margin: 18mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }

        .kop { border-bottom: 2px solid #4f46e5; padding-bottom: 8px; margin-bottom: 10px; }
        .kop h1 { margin: 0; font-size: 15px; color: #312e81; }
        .kop p { margin: 2px 0 0; font-size: 8.5px; color: #64748b; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px 6px; border: 1px solid #e2e8f0; vertical-align: top; }
        th { background: #eef2ff; color: #312e81; font-size: 8.5px; text-align: left; }

        /* Angka rata kanan supaya satuannya sejajar dan bisa dibandingkan
           sekilas; teks tetap rata kiri. */
        .angka { text-align: right; white-space: nowrap; }

        .samar { color: #94a3b8; }
        .kosong { padding: 14px; text-align: center; color: #64748b; }

        .kaki { margin-top: 10px; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>Promo Layanan</h1>
        <p>Rumah Scopus Foundation &middot; dicetak {{ now()->locale('id')->translatedFormat('j F Y, H:i') }}</p>
        <p>{{ $saringan }} Jumlah baris: {{ $daftar->count() }}.</p>
    </div>

    @if ($daftar->isEmpty())
        <p class="kosong">Tidak ada promo yang cocok dengan saringan itu.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Promo</th>
                    <th>Potongan</th>
                    <th>Periode</th>
                    <th>Keadaan</th>
                    <th class="angka">Angkatan</th>
                    <th class="angka">Kuota</th>
                    <th class="angka">Terpakai</th>
                    <th class="angka">Peserta</th>
                    <th class="angka">Total potongan</th>
                    <th>Dibuat oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daftar as $p)
                    @php ($angka = \App\Support\DampakPromo::untuk((string) $p->getKey()))
                    <tr>
                        <td>
                            {{ $p->nama }}
                            @if ($p->catatan)
                                <br><span class="samar">{{ \Illuminate\Support\Str::limit($p->catatan, 70) }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $p->potongan_ringkas }}
                            @if ($p->tipe === \App\PromoLayanan::PERSEN && $p->batas_potongan)
                                <br><span class="samar">maks Rp {{ number_format($p->batas_potongan, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>{{ $p->periode_terbaca }}</td>
                        <td>{{ $p->keadaan_terbaca }}</td>
                        <td class="angka">{{ (int) ($p->angkatan_count ?? 0) }}</td>
                        <td class="angka">{{ $p->kuota === null ? '—' : number_format($p->kuota) }}</td>
                        <td class="angka">{{ number_format($angka['pendaftaran']) }}</td>
                        <td class="angka">{{ number_format($angka['peserta']) }}</td>
                        <td class="angka">
                            {{ $angka['potongan'] ? 'Rp ' . number_format($angka['potongan'], 0, ',', '.') : '—' }}
                        </td>
                        <td>{{ $p->penginput?->full_name ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="kaki">
            Terpakai dihitung dari pendaftaran yang benar-benar tertali ke promonya, bukan dari namanya.
        </p>
    @endif
</body>
</html>
