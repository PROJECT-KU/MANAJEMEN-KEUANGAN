{{-- Gayanya sebaris dan susunannya tabel, alasannya sama dengan
     email_pendaftaran: banyak klien surat membuang blok <style>. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Besok: {{ $sesi->nama }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">

                <tr>
                    <td style="background:#ff6a00;padding:22px 26px;">
                        <div style="color:#ffffff;font-size:18px;font-weight:bold;">Sesinya besok</div>
                        <div style="color:#ffe8d6;font-size:13px;margin-top:4px;">{{ $sesi->nama }}</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:26px;">
                        <p style="margin:0 0 16px;font-size:15px;">Halo {{ $pendaftaran->nama }},</p>

                        <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#334155;">
                            Mengingatkan saja — sesi yang Anda daftarkan berlangsung
                            <strong style="color:#0f2b5b;">besok</strong>. Siapkan waktunya, ya.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#334155;">
                            @php($baris = [
                                'Tanggal' => \App\Support\RentangTanggal::tulis(
                                    $sesi->mulai ? \Carbon\Carbon::parse($sesi->mulai) : null,
                                    $sesi->selesai ? \Carbon\Carbon::parse($sesi->selesai) : null
                                ) ?: 'Menyusul',
                                'Jam' => $sesi->jam ?: 'Menyusul',
                                'Lewat' => $sesi->platform ?: 'Daring',
                                'Nomor pendaftaran' => $pendaftaran->id_transaksi,
                            ])

                            @foreach ($baris as $judul => $isi)
                                <tr>
                                    <td style="padding:7px 0;color:#64748b;width:42%;">{{ $judul }}</td>
                                    <td style="padding:7px 0;font-weight:bold;color:#0f2b5b;">{{ $isi }}</td>
                                </tr>
                            @endforeach
                        </table>

                        {{-- Pembayaran yang belum selesai disebut TEGAS di sini:
                             ini kesempatan terakhir yang masuk akal untuk
                             mengingatkan sebelum harinya tiba. --}}
                        @if (! $pendaftaran->lunas)
                            <p style="margin:20px 0 0;padding:13px 15px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;font-size:13px;color:#9a3412;line-height:1.6;">
                                Pembayaran Anda <strong>belum tercatat lunas</strong>.
                                Kalau sudah membayar, kirim buktinya ke panitia supaya kursinya aman.
                            </p>
                        @endif

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0 8px;">
                            <tr>
                                <td style="background:#0f2b5b;border-radius:10px;">
                                    <a href="{{ $tautanStatus }}"
                                        style="display:inline-block;padding:13px 26px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">
                                        Lihat rincian pendaftaran
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:12px 0 0;font-size:12px;color:#64748b;line-height:1.6;">
                            Tautan masuk sesinya dibagikan panitia lewat grup peserta di WhatsApp.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
