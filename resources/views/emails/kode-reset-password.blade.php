<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Verifikasi</title>
</head>

<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#1e293b">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:28px 12px">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:540px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px -18px rgba(15,23,42,.4)">

                    <tr>
                        <td style="background:linear-gradient(135deg,#7c3aed 0%,#c026d3 55%,#f43f5e 100%);padding:26px 28px;color:#ffffff">
                            <p style="margin:0;font-size:13px;letter-spacing:.09em;text-transform:uppercase;opacity:.85">Rumah Scopus Foundation</p>
                            <h1 style="margin:6px 0 0;font-size:21px;font-weight:800">Kode Atur Ulang Kata Sandi</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px">
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.65">
                                Halo <strong>{{ $user->full_name ?? $user->username }}</strong>,
                            </p>
                            <p style="margin:0 0 22px;font-size:15px;line-height:1.65;color:#475569">
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda. Masukkan kode di
                                bawah ini pada halaman atur ulang kata sandi.
                            </p>

                            <div style="text-align:center;margin:0 0 22px">
                                <div style="display:inline-block;padding:16px 26px;border-radius:14px;background:#faf5ff;border:1px solid #e9d5ff">
                                    <span style="font-size:32px;font-weight:800;letter-spacing:.34em;color:#6b21a8">{{ $kode }}</span>
                                </div>
                            </div>

                            <p style="margin:0 0 10px;font-size:14px;line-height:1.65;color:#475569">
                                Kode ini berlaku <strong>{{ $menit }} menit</strong> dan hanya bisa dipakai satu kali.
                            </p>
                            <p style="margin:0;font-size:14px;line-height:1.65;color:#475569">
                                Jika Anda tidak meminta perubahan ini, abaikan email ini. Kata sandi Anda tidak berubah.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px 24px;border-top:1px solid #e2e8f0;color:#94a3b8;font-size:12px;line-height:1.6">
                            Email otomatis dari sistem MIS Rumah Scopus Foundation. Mohon tidak membalas email ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
