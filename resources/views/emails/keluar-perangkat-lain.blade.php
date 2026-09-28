<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Sesi di perangkat lain diakhiri</title>
</head>

<body style="margin:0;padding:0;background:#f1f5f9;">
    {{-- Pratinjau di daftar kotak masuk --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0">
        Semua sesi selain perangkat yang sedang dipakai sudah diakhiri.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:#f1f5f9;padding:32px 12px;font-family:'Segoe UI',Helvetica,Arial,sans-serif">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:520px;background:#ffffff;border:1px solid #e6eaf0;border-radius:18px;overflow:hidden">

                    <tr>
                        <td align="center" style="padding:30px 32px 6px">
                            <img src="{{ $message->embed(public_path('assets/img/logo-email.png')) }}"
                                alt="MIS — Management Integration System by Rumah Scopus" width="190"
                                style="display:block;border:0;width:190px;max-width:60%;height:auto">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 32px 0">
                            <h1 style="margin:0 0 10px;font-size:20px;line-height:1.35;font-weight:700;color:#0b1324">
                                Sesi di perangkat lain diakhiri
                            </h1>
                            <p style="margin:0 0 6px;font-size:15px;line-height:1.7;color:#334155">
                                Halo <strong>{{ $user->full_name ?? $user->username }}</strong>,
                            </p>
                            <p style="margin:0;font-size:15px;line-height:1.7;color:#475569">
                                Semua sesi akun MIS Anda selain di perangkat yang sedang dipakai baru saja
                                diakhiri. Perangkat lain harus masuk ulang dengan kata sandi.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 32px 0">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background:#ecfeff;border-radius:11px">
                                <tr>
                                    <td style="padding:14px 16px;font-size:13.5px;line-height:1.7;color:#0e7490">
                                        <strong>Waktu</strong><br>
                                        {{ now()->format('d/m/Y H:i') }} WIB<br>
                                        <strong>Akun</strong><br>
                                        {{ $user->email }}
                                        @if ($ip !== '')
                                            <br><strong>Alamat IP</strong><br>{{ $ip }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 32px 26px">
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569">
                                <strong style="color:#9f1239">Bukan Anda yang melakukannya?</strong> Berarti ada
                                orang lain yang sedang masuk ke akun Anda. Segera ganti kata sandi dan hubungi
                                admin Rumah Scopus Foundation.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 32px 26px;border-top:1px solid #eef1f6;font-size:12px;line-height:1.7;color:#94a3b8">
                            Email otomatis dari MIS Rumah Scopus Foundation. Mohon tidak membalas email ini.
                        </td>
                    </tr>
                </table>

                <p style="margin:16px 0 0;font-size:12px;color:#94a3b8">
                    &copy; {{ date('Y') }} Rumah Scopus Foundation
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
