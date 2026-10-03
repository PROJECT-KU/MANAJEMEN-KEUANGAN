<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Pendaftaran tercatat</title>
</head>

<body style="margin:0;padding:0;background:#f1f5f9;">
    {{-- Pratinjau di daftar kotak masuk: nomornya, sebab itu yang dicari. --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0">
        Nomor pendaftaran Anda {{ $nomor }}.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:#f1f5f9;padding:32px 12px;font-family:'Segoe UI',Helvetica,Arial,sans-serif">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden">
                    <tr>
                        <td style="padding:26px 28px 6px">
                            <p style="margin:0 0 4px;font-size:13px;color:#64748b">{{ $appName }}</p>
                            <h1 style="margin:0;font-size:20px;color:#0f172a">Pendaftaran Anda tercatat</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 28px 0;font-size:14px;line-height:1.6;color:#334155">
                            <p style="margin:0 0 14px">
                                Halo {{ $nama }}, pendaftaran Anda untuk
                                <strong>{{ $layanan }}</strong> sudah kami catat.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:4px 28px 0">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background:#f8fafc;border-radius:10px">
                                <tr>
                                    <td style="padding:14px 16px;font-size:14px;color:#334155">
                                        <p style="margin:0 0 7px">
                                            Nomor pendaftaran:
                                            <strong style="color:#0f172a">{{ $nomor }}</strong>
                                        </p>
                                        @if ($angkatan)
                                            <p style="margin:0 0 7px">Angkatan: <strong>{{ $angkatan }}</strong></p>
                                        @endif
                                        @if ($tanggal)
                                            <p style="margin:0 0 7px">Mulai: <strong>{{ $tanggal }}</strong></p>
                                        @endif
                                        <p style="margin:0">
                                            Total bayar:
                                            <strong style="color:#0f172a">Rp {{ number_format($total, 0, ',', '.') }}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 0;font-size:14px;line-height:1.6;color:#334155">
                            @if ($sudahLunas)
                                <p style="margin:0">
                                    Pembayaran Anda sudah kami terima. Tidak ada yang perlu Anda
                                    lakukan lagi.
                                </p>
                            @elseif ($caraBayar === 'tunai')
                                <p style="margin:0">
                                    Pembayarannya diserahkan langsung ke panitia saat Anda datang.
                                </p>
                            @else
                                <p style="margin:0 0 10px">
                                    Pembayarannya lewat transfer ke rekening kami.
                                </p>
                                @if ($kodeUnik > 0)
                                    {{-- Alasannya disebut. Tanpa itu, angka ganjil di ujung
                                         nominal terbaca seperti salah hitung, dan sebagian
                                         orang membulatkannya — lalu pembayarannya tidak bisa
                                         dicocokkan. --}}
                                    <p style="margin:0;padding:11px 14px;background:#eff6ff;border-radius:9px;color:#1d4ed8">
                                        Mohon transfer <strong>persis</strong>
                                        Rp {{ number_format($total, 0, ',', '.') }} sampai angka
                                        terakhirnya. Angka {{ number_format($kodeUnik, 0, ',', '.') }}
                                        di ujungnya adalah penanda pendaftaran Anda — itulah yang
                                        membuat pembayaran Anda bisa kami kenali.
                                    </p>
                                @endif
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 28px 26px;font-size:12px;color:#94a3b8">
                            Email ini dikirim otomatis karena pendaftaran Anda dicatat oleh panitia.
                            Kalau Anda merasa tidak mendaftar, balas email ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
