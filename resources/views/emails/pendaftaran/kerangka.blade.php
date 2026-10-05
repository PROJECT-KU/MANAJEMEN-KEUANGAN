{{--
  Kerangka email pendaftaran, dipakai bersama kelima surat status.

  DITULIS DENGAN TABEL DAN GAYA SEBARIS, bukan dengan kelas dan flexbox.
  Templat sebelumnya memuat Bootstrap, FontAwesome, dan style.css lewat
  <link> lalu menata isinya dengan `display: flex` — ketiganya dibuang
  mentah-mentah oleh Gmail, Outlook, dan hampir semua klien email. Yang
  sampai ke penerima tinggal teks tanpa gaya, rata kiri, tanpa logo.
  Itu sebabnya emailnya terlihat seperti catatan mentah.

  Yang dipakai di sini hanya yang memang didukung klien email:
  tabel bersarang, lebar tetap, dan atribut style di tiap unsur.

  Yang dikirim pemanggilnya:
    $judul     judul besar di kepala surat
    $lencana   ['teks' => ..., 'latar' => hex, 'tinta' => hex]  (boleh null)
    $sapaan    nama orang yang disapa
    $pembuka   satu paragraf pembuka
    $rincian   larik asosiatif label => nilai, dicetak jadi tabel
    $penutup   larik paragraf penutup (boleh kosong)
    $sorot     ['judul' => ..., 'isi' => ...] kotak sorot (boleh null)
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6fb; font-family:Helvetica,Arial,sans-serif; color:#0f172a;">

{{-- Tabel terluar: yang memusatkan isinya di klien yang tidak mengenal
     margin:auto, dan itu termasuk Outlook. --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="background-color:#f4f6fb; padding:24px 12px;">
    <tr>
        <td align="center">

            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                style="width:600px; max-width:100%; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e7ecf5;">

                {{-- Kepala: logo DI TENGAH. Lebarnya disebut di atribut width
                     juga, bukan hanya di style — Outlook mengabaikan style
                     pada <img>. --}}
                <tr>
                    <td align="center" style="background-color:#ffffff; padding:28px 24px 8px 24px;">
                        <img src="{{ asset('assets/img/logo-rsc-email.png') }}"
                            alt="Rumah Scopus Foundation" width="200"
                            style="display:block; width:200px; max-width:60%; height:auto; border:0;">
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:8px 28px 0 28px;">
                        <h1 style="margin:0; font-size:20px; line-height:1.35; font-weight:bold; color:#0f172a;">
                            {{ $judul }}
                        </h1>
                        @if (! empty($lencana))
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:12px auto 0 auto;">
                                <tr>
                                    <td style="background-color:{{ $lencana['latar'] }}; color:{{ $lencana['tinta'] }}; padding:6px 14px; border-radius:999px; font-size:12px; font-weight:bold; letter-spacing:.04em; text-transform:uppercase;">
                                        {{ $lencana['teks'] }}
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:22px 28px 0 28px;">
                        <p style="margin:0 0 12px 0; font-size:15px; line-height:1.6; color:#334155;">
                            Halo <strong style="color:#0f172a;">{{ $sapaan }}</strong>,
                        </p>
                        <p style="margin:0; font-size:15px; line-height:1.6; color:#334155;">
                            {{ $pembuka }}
                        </p>
                    </td>
                </tr>

                {{-- Rincian sebagai TABEL dua lajur.
                     Templat lama memakai dua <div> ber-flex berdampingan:
                     label di kiri, nilai di kanan. Tanpa dukungan flex,
                     keduanya jatuh bertumpuk dan daftar labelnya terbaca
                     terpisah dari daftar nilainya — label dan angkanya tidak
                     lagi berpasangan. Tabel tidak bisa jatuh seperti itu. --}}
                @if (! empty($rincian))
                    <tr>
                        <td style="padding:18px 28px 0 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:#f8fafc; border:1px solid #e7ecf5; border-radius:12px;">
                                @foreach ($rincian as $label => $nilai)
                                    <tr>
                                        <td style="padding:10px 14px; font-size:13px; color:#64748b; {{ $loop->last ? '' : 'border-bottom:1px solid #e7ecf5;' }}">
                                            {{ $label }}
                                        </td>
                                        <td align="right" style="padding:10px 14px; font-size:14px; font-weight:bold; color:#0f172a; {{ $loop->last ? '' : 'border-bottom:1px solid #e7ecf5;' }}">
                                            {{ $nilai }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif

                @if (! empty($sorot))
                    <tr>
                        <td style="padding:18px 28px 0 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:#eff6ff; border:1px solid #bfdbfe; border-radius:12px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0 0 4px 0; font-size:12px; font-weight:bold; letter-spacing:.04em; text-transform:uppercase; color:#1d4ed8;">
                                            {{ $sorot['judul'] }}
                                        </p>
                                        <p style="margin:0; font-size:14px; line-height:1.6; color:#1e3a8a; word-break:break-word;">
                                            {{ $sorot['isi'] }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @foreach ($penutup ?? [] as $alinea)
                    <tr>
                        <td style="padding:16px 28px 0 28px;">
                            <p style="margin:0; font-size:15px; line-height:1.6; color:#334155;">{{ $alinea }}</p>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td style="padding:22px 28px 0 28px;">
                        <p style="margin:0; font-size:15px; line-height:1.6; color:#334155;">
                            Salam Q1!<br>
                            <strong style="color:#0f172a;">Admin Rumah Scopus Foundation</strong>
                        </p>
                    </td>
                </tr>

                {{-- Kaki: alamat dan tautan sosial, DI TENGAH. --}}
                <tr>
                    <td align="center" style="padding:24px 28px 26px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="border-top:1px solid #e7ecf5; padding-top:18px;" align="center">
                                    <p style="margin:0 0 10px 0; font-size:12px; line-height:1.7; color:#94a3b8;">
                                        Rumah Scopus Foundation<br>
                                        Bangunsari, Jl. Bangunsari, Bangun Kerto, Turi,<br>
                                        Sleman, Daerah Istimewa Yogyakarta 55551<br>
                                        Telp. 0812-2688-3280
                                    </p>
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                                        <tr>
                                            <td style="padding:0 6px;">
                                                <a href="https://www.instagram.com/rumah_scopus/">
                                                    <img src="{{ asset('assets/img/instagram.png') }}"
                                                        alt="Instagram" width="28" style="display:block; width:28px; height:auto; border:0;">
                                                </a>
                                            </td>
                                            <td style="padding:0 6px;">
                                                <a href="https://www.youtube.com/@rumahscopus">
                                                    <img src="{{ asset('assets/img/youtube.png') }}"
                                                        alt="YouTube" width="28" style="display:block; width:28px; height:auto; border:0;">
                                                </a>
                                            </td>
                                            <td style="padding:0 6px;">
                                                <a href="https://www.facebook.com/RumahScopusAkademi">
                                                    <img src="{{ asset('assets/img/facebook.png') }}"
                                                        alt="Facebook" width="28" style="display:block; width:28px; height:auto; border:0;">
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
