<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Matikan PIN Masuk</title>
</head>

<body style="margin:0;padding:0;background:#f1f5f9;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0">
        Tautan berlaku {{ $menit }} menit untuk mematikan PIN masuk Anda.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:#f1f5f9;padding:32px 12px;font-family:'Segoe UI',Helvetica,Arial,sans-serif">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:520px;background:#ffffff;border:1px solid #e6eaf0;border-radius:18px;overflow:hidden">

                    <tr>
                        <td align="center" style="padding:30px 32px 6px">
                            <img src="{{ asset('assets/img/logo-email.png') }}"
                                alt="MIS — Management Integration System by Rumah Scopus" width="190"
                                style="display:block;border:0;width:190px;max-width:60%;height:auto">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 32px 0">
                            <h1 style="margin:0 0 10px;font-size:20px;line-height:1.35;font-weight:700;color:#0b1324">
                                Lupa PIN masuk?
                            </h1>
                            <p style="margin:0 0 6px;font-size:15px;line-height:1.7;color:#334155">
                                Halo <strong>{{ $user->full_name ?? $user->username }}</strong>,
                            </p>
                            <p style="margin:0;font-size:15px;line-height:1.7;color:#475569">
                                Ada permintaan untuk mematikan PIN masuk pada akun Anda. Klik tombol di bawah untuk
                                mematikannya. Setelah itu Anda tetap bisa masuk memakai kata sandi, dan bisa membuat
                                PIN baru dari halaman profil.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:26px 32px 4px">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="border-radius:11px;background:#e11d48">
                                        <a href="{{ $tautan }}"
                                            style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:11px">
                                            Matikan PIN Saya
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 32px 0">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background:#fff1f2;border-radius:11px">
                                <tr>
                                    <td style="padding:13px 16px;font-size:13.5px;line-height:1.65;color:#9f1239">
                                        Tautan berlaku <strong>{{ $menit }} menit</strong>. Kata sandi Anda tidak
                                        berubah, dan tautan ini tidak bisa dipakai untuk masuk.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 26px">
                            <p style="margin:0 0 6px;font-size:12px;color:#94a3b8">
                                Tombol tidak bisa diklik? Salin alamat berikut ke peramban Anda:
                            </p>
                            <p style="margin:0;font-size:12px;line-height:1.6;word-break:break-all">
                                <a href="{{ $tautan }}" style="color:#e11d48">{{ $tautan }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 32px 26px;border-top:1px solid #eef1f6;font-size:12px;line-height:1.7;color:#94a3b8">
                            Bukan Anda yang meminta? Abaikan email ini — PIN Anda tetap aktif.
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
