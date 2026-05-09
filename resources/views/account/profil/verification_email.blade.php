<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - RSC</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
        }

        .glass-card {
            /* Fallback warna solid jika email tidak mendukung transparansi */
            background-color: #ffffff;
            /* Efek Glossy Gradien */
            background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0.85) 100%);
            /* Pantulan cahaya kaca di pinggiran */
            border-top: 2px solid #ffffff;
            border-left: 2px solid #ffffff;
            border-right: 1px solid rgba(255, 255, 255, 0.4);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            /* Bayangan super menonjol (3D Effect) */
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25), 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            overflow: hidden;
        }

        @media screen and (max-width: 600px) {
            .glass-card {
                border-radius: 16px !important;
            }

            .content-pad {
                padding: 30px 20px !important;
            }

            .otp-text {
                font-size: 36px !important;
                letter-spacing: 8px !important;
            }

            .bg-wrapper {
                padding: 20px 10px !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f1f5f9;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="bg-wrapper" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); padding: 60px 15px;">
        <tr>
            <td align="center">

                <table border="0" cellpadding="0" cellspacing="0" class="glass-card" style="background-color: #ffffff; border-top: 2px solid #ffffff; border-left: 2px solid #ffffff; border-radius: 24px; max-width: 600px; width: 100%; box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);">

                    <tr>
                        <td align="center" style="padding: 40px 20px 20px 20px;">
                            <a href="https://rumahscopusfoundation.com/" target="_blank">
                                <img src="{{ asset('assets/img/LogoRSC.png') }}" alt="Rumah Scopus Foundation" style="max-width: 200px; height: auto; border: 0; outline: none;">
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td align="center">
                            <div style="height: 1px; background: linear-gradient(to right, transparent, rgba(99, 102, 241, 0.2), transparent); width: 80%;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td class="content-pad" style="padding: 40px; color: #1e293b; text-align: center;">

                            <h2 style="margin: 0 0 10px 0; font-size: 26px; font-weight: 800; color: #1e293b;">
                                Halo, {{ $user->full_name }}
                            </h2>

                            <p style="margin: 0 0 30px 0; font-size: 15px; line-height: 1.6; color: #475569;">
                                Seseorang mencoba mengakses atau mengubah pengaturan akun Anda. Gunakan kode verifikasi (OTP) di bawah ini untuk melanjutkan.
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <div style="background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%); border: 1px solid #cbd5e1; border-top: 2px solid #ffffff; border-radius: 16px; padding: 25px 40px; display: inline-block; margin-bottom: 30px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.05), 0 5px 15px rgba(0,0,0,0.05);">
                                            <span class="otp-text" style="font-family: 'Courier New', Courier, monospace; font-size: 44px; font-weight: 900; letter-spacing: 14px; color: #4f46e5; text-shadow: 1px 1px 0px #ffffff;">
                                                {{ $verificationCode }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; padding: 15px 20px; border-radius: 8px; margin-bottom: 10px; text-align: left;">
                                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #92400e;">
                                    <strong>Peringatan:</strong> Rahasiakan kode ini. Tim RSC tidak akan pernah meminta kode verifikasi Anda dengan alasan apa pun.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: rgba(248, 250, 252, 0.8); padding: 30px 40px; text-align: center; border-top: 1px solid rgba(226, 232, 240, 0.8); border-bottom-left-radius: 24px; border-bottom-right-radius: 24px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #1e293b;">Rumah Scopus Foundation (RSC)</h4>
                            <p style="margin: 0 0 20px 0; font-size: 12px; line-height: 1.6; color: #64748b;">
                                Jl. Bangunsari, Bangun Kerto, Turi,<br>
                                Sleman, DI Yogyakarta 55551<br>
                                0812-2688-3280
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td style="padding: 0 8px;">
                                        <a href="https://www.instagram.com/rumah_scopus/" target="_blank">
                                            <img src="{{ asset('assets/img/instagram.png') }}" alt="IG" width="30" height="30" style="display: block;">
                                        </a>
                                    </td>
                                    <td style="padding: 0 8px;">
                                        <a href="https://www.youtube.com/@rumahscopus" target="_blank">
                                            <img src="{{ asset('assets/img/youtube.png') }}" alt="YT" width="30" height="30" style="display: block;">
                                        </a>
                                    </td>
                                    <td style="padding: 0 8px;">
                                        <a href="https://www.facebook.com/RumahScopusAkademi" target="_blank">
                                            <img src="{{ asset('assets/img/facebook.png') }}" alt="FB" width="30" height="30" style="display: block;">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="center" style="padding: 20px 0; color: rgba(255,255,255,0.9); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                            &copy; {{ date('Y') }} Rumah Scopus Foundation
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>