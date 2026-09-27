<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Verifikasi Email</title>
</head>

<body style="margin:0;padding:0;background:#f1f5f9;">
    {{-- Pratinjau di daftar kotak masuk --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0">
        Tautan verifikasi berlaku {{ $jam }} jam.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:#f1f5f9;padding:32px 12px;font-family:'Segoe UI',Helvetica,Arial,sans-serif">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:520px;background:#ffffff;border:1px solid #e6eaf0;border-radius:18px;overflow:hidden">

                    {{-- logo (utuh, hanya diperkecil) --}}
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
                                Verifikasi alamat email Anda
                            </h1>
                            <p style="margin:0 0 6px;font-size:15px;line-height:1.7;color:#334155">
                                Halo <strong>{{ $user->full_name ?? $user->username }}</strong>,
                            </p>
                            <p style="margin:0;font-size:15px;line-height:1.7;color:#475569">
                                Terima kasih sudah mendaftar. Satu langkah lagi: klik tombol di bawah ini untuk
                                memastikan alamat email ini benar milik Anda.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:26px 32px 4px">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="border-radius:11px;background:#0d9488">
                                        <a href="{{ $tautan }}"
                                            style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:11px">
                                            Verifikasi Email Saya
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 32px 0">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background:#ecfdf5;border-radius:11px">
                                <tr>
                                    <td style="padding:13px 16px;font-size:13.5px;line-height:1.65;color:#6b21a8">
                                        Tautan verifikasi berlaku <strong>{{ $jam }} jam</strong>. Selama email
                                        belum diverifikasi, sebagian fitur akun dibatasi.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 0">
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569">
                                Jika Anda merasa tidak pernah mendaftar, abaikan saja email ini. Akun tidak akan
                                aktif tanpa verifikasi.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 26px">
                            <p style="margin:0 0 6px;font-size:12px;color:#94a3b8">
                                Tombol tidak bisa diklik? Salin alamat berikut ke peramban Anda:
                            </p>
                            <p style="margin:0;font-size:12px;line-height:1.6;word-break:break-all">
                                <a href="{{ $tautan }}" style="color:#0d9488">{{ $tautan }}</a>
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
