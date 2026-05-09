<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Analisis Bibliometrik</title>
    <style>
        /* CSS ini dikhususkan untuk Media Queries perangkat Mobile */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                border-radius: 0 !important;
                margin: 0 !important;
            }

            .content-wrapper {
                padding: 20px !important;
            }

            .data-table td {
                display: block !important;
                width: 100% !important;
                text-align: left !important;
            }

            .data-table td.value {
                padding-top: 2px !important;
                padding-bottom: 15px !important;
                font-size: 16px !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f4f7f6;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%); padding: 40px 20px;">
        <tr>
            <td align="center">

                <table class="email-container" width="600" border="0" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); overflow: hidden;">

                    <tr>
                        <td align="center" style="padding: 40px 20px 20px 20px;">
                            <a href="https://rumahscopusfoundation.com/" target="_blank">
                                <img src="{{ $message->embed(public_path('assets/img/LogoRSC.png')) }}" alt="Rumah Scopus" width="220" style="display: block; border: 0;">
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td align="center">
                            <div style="height: 1px; background: linear-gradient(to right, transparent, rgba(99, 102, 241, 0.2), transparent); width: 80%;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td class="content-wrapper" style="padding: 20px 40px; color: #334155;">

                            <h1 style="margin: 0 0 15px 0; font-size: 26px; color: #1e293b; font-weight: 800; text-align: center;">
                                Hallo, {{ $analisisbibliometrik->nama }} 👋
                            </h1>

                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 25px; text-align: center;">
                                Terima kasih sudah melakukan pendaftaran! Berikut adalah rincian data Analisis Bibliometrik Anda:
                            </p>

                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background: linear-gradient(to bottom right, #ffffff, #f8fafc); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02); margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 25px;">

                                        <table class="data-table" width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size: 14px;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #64748b; font-weight: 600; border-bottom: 1px dashed #cbd5e1;" width="45%">Kode Transaksi</td>
                                                <td class="value" style="padding: 8px 0; color: #3b82f6; font-weight: 800; text-align: right; border-bottom: 1px dashed #cbd5e1; letter-spacing: 1px;">
                                                    {{ strtoupper($analisisbibliometrik->id_transaksi) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0 8px 0; color: #64748b; font-weight: 600; border-bottom: 1px dashed #cbd5e1;">Batch</td>
                                                <td class="value" style="padding: 12px 0 8px 0; color: #0f172a; font-weight: 700; text-align: right; border-bottom: 1px dashed #cbd5e1;">
                                                    {{ $categoriesanalisisbibliometrik->nama }} #{{ $categoriesanalisisbibliometrik->nama_ke }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0 8px 0; color: #64748b; font-weight: 600; border-bottom: 1px dashed #cbd5e1;">Mulai Pelaksanaan</td>
                                                <td class="value" style="padding: 12px 0 8px 0; color: #0f172a; font-weight: 700; text-align: right; border-bottom: 1px dashed #cbd5e1;">
                                                    {{ strftime('%d %B %Y', strtotime($categoriesanalisisbibliometrik->mulai)) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0 8px 0; color: #64748b; font-weight: 600; border-bottom: 1px dashed #cbd5e1;">Selesai Pelaksanaan</td>
                                                <td class="value" style="padding: 12px 0 8px 0; color: #0f172a; font-weight: 700; text-align: right; border-bottom: 1px dashed #cbd5e1;">
                                                    {{ strftime('%d %B %Y', strtotime($categoriesanalisisbibliometrik->selesai)) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0 0 0; color: #64748b; font-weight: 600;">Total Pembayaran</td>
                                                <td class="value" style="padding: 12px 0 0 0; color: #10b981; font-size: 18px; font-weight: 900; text-align: right;">
                                                    Rp. {{ number_format($analisisbibliometrik->total_pembayaran, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px;">
                                <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.5;">
                                    Status pendaftaran Anda saat ini: <strong style="text-transform: uppercase;">{{ $status }}</strong>.<br>
                                    Silakan tunggu maksimal 1x24 jam untuk proses konfirmasi.
                                </p>
                            </div>

                            <p style="font-size: 15px; font-weight: bold; color: #1e293b; margin-bottom: 30px;">Salam Q1!</p>

                            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 20px;">

                            <p style="font-size: 13px; line-height: 1.6; color: #64748b; margin-bottom: 20px;">
                                <strong>Admin Rumah Scopus Foundation</strong><br>
                                Bangunsari, Jl. Bangunsari, Bangun Kerto, Turi,<br>
                                Sleman Regency, Special Region of Yogyakarta 55551<br>
                                Telp: 0812-2688-3280
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-right: 15px;">
                                        <a href="https://www.instagram.com/rumah_scopus/" target="_blank">
                                            <img src="{{ $message->embed(public_path('assets/img/instagram.png')) }}" alt="Instagram" width="35" style="display: block; border: 0;">
                                        </a>
                                    </td>
                                    <td style="padding-right: 15px;">
                                        <a href="https://www.youtube.com/@rumahscopus" target="_blank">
                                            <img src="{{ $message->embed(public_path('assets/img/youtube.png')) }}" alt="YouTube" width="35" style="display: block; border: 0;">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="https://www.facebook.com/RumahScopusAkademi" target="_blank">
                                            <img src="{{ $message->embed(public_path('assets/img/facebook.png')) }}" alt="Facebook" width="35" style="display: block; border: 0;">
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <tr>
                        <td height="20"></td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>