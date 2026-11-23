{{-- Gayanya sebaris, susunannya tabel, perataannya berganti-ganti — alasan
     ketiganya sama dengan email_lunas. Logonya yayasan, bukan MIS. --}}
@php
    $logo = aset_surat('assets/img/logo-rsc-email.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $baru ? 'Akun MIS Anda sudah siap' : 'Akses rekaman webinar' }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">

                <tr>
                    <td align="center" style="padding:28px 26px 20px;border-bottom:1px solid #eef2f7;">
                        <img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="220"
                            style="display:block;width:220px;max-width:70%;height:auto;border:0;">
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:26px 26px 6px;">
                        <div style="font-size:21px;font-weight:bold;color:#0f2b5b;line-height:1.35;">
                            {{ $baru ? 'Akun MIS Anda sudah siap' : 'Rekamannya lewat akun MIS Anda' }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:14px 26px 0;font-size:15px;line-height:1.75;">
                        <p style="margin:0 0 14px;">Halo <strong>{{ $nama }}</strong>,</p>

                        <p style="margin:0 0 14px;">
                            Pembayaran Anda untuk
                            <strong>{{ $sesi?->nama ?: 'Webinar Eksklusif' }}</strong>
                            sudah kami terima. Terima kasih.
                        </p>

                        <p style="margin:0 0 14px;">
                            @if ($baru)
                                Kami sudah membuatkan akun MIS atas nama Anda. Lewat akun ini
                                Anda bisa menonton <strong>rekaman webinarnya</strong> setelah
                                acara selesai, kapan saja.
                            @else
                                Anda sudah punya akun MIS. Pakai akun yang sama untuk menonton
                                <strong>rekaman webinarnya</strong> setelah acara selesai.
                            @endif
                        </p>
                    </td>
                </tr>

                {{-- Kotak keterangan masuk. Nama pengguna selalu disebut; sandi
                     HANYA untuk akun yang baru dibuat — sandi akun lama adalah
                     yang dipilih orangnya sendiri, dan kami memang tidak
                     menyentuhnya. Menyebutkan sandi bawaan di surat untuk akun
                     lama akan membuat orangnya mencobanya, gagal, lalu mengira
                     akunnya rusak. --}}
                <tr>
                    <td style="padding:6px 26px 0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                            style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <tr>
                                <td style="padding:16px 18px;font-size:15px;line-height:1.8;">
                                    <div style="color:#64748b;font-size:12px;letter-spacing:.5px;">NAMA PENGGUNA</div>
                                    <div style="font-weight:bold;font-size:17px;color:#0f2b5b;word-break:break-all;">{{ $namaPengguna }}</div>

                                    @if ($baru)
                                        <div style="height:12px;line-height:12px;">&nbsp;</div>
                                        <div style="color:#64748b;font-size:12px;letter-spacing:.5px;">KATA SANDI SEMENTARA</div>
                                        <div style="font-weight:bold;font-size:17px;color:#0f2b5b;">{{ $sandi }}</div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @if ($baru)
                <tr>
                    <td style="padding:14px 26px 0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                            style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;">
                            <tr>
                                <td style="padding:14px 18px;font-size:14px;line-height:1.7;color:#78350f;">
                                    Kata sandi di atas <strong>sementara</strong> dan sama untuk semua
                                    peserta. Saat pertama kali masuk, Anda akan diminta menggantinya
                                    dulu sebelum bisa membuka halaman lain. Sebelum diganti, orang
                                    lain yang tahu nama Anda bisa ikut masuk ke akun ini.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif

                <tr>
                    <td align="center" style="padding:22px 26px 6px;">
                        <a href="{{ $tautanMasuk }}"
                            style="display:inline-block;padding:13px 30px;border-radius:999px;background:#0f2b5b;color:#ffffff;font-weight:bold;font-size:15px;text-decoration:none;">
                            Masuk ke MIS
                        </a>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 26px 26px;font-size:13px;line-height:1.7;color:#64748b;">
                        <p style="margin:0 0 8px;">
                            Kalau tombolnya tidak bisa ditekan, salin alamat ini ke peramban:<br>
                            <span style="word-break:break-all;color:#0f2b5b;">{{ $tautanMasuk }}</span>
                        </p>
                        <p style="margin:0;">
                            Rekamannya kami pasang setelah webinarnya selesai. Begitu tersedia,
                            ia muncul sendiri di menu <strong>Rekaman Webinar</strong> setelah Anda masuk.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:16px 26px 24px;border-top:1px solid #eef2f7;font-size:12px;color:#94a3b8;">
                        Rumah Scopus Foundation
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
