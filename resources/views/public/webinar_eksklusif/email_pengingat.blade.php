{{-- Gayanya sebaris, susunannya tabel, dan perataannya berganti-ganti —
     alasan ketiganya sama dengan email_pendaftaran. --}}
@php
    $logo = $message->embed(public_path('assets/img/logo-rsc-email.png'));
@endphp
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

                {{-- Kepala putih berlogo, sama dengan bukti pendaftaran:
                     tulisan di logonya hitam dan hilang di atas latar pekat. --}}
                <tr>
                    <td align="center" style="padding:28px 26px 20px;border-bottom:1px solid #eef2f7;">
                        <img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="220"
                            style="display:block;width:220px;max-width:70%;height:auto;border:0;">
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:26px 26px 6px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 14px;">
                            <tr>
                                <td style="padding:6px 14px;border-radius:999px;background:#fff7ed;border:1px solid #fed7aa;">
                                    <span style="font-size:12px;font-weight:bold;letter-spacing:.6px;color:#9a3412;">BESOK</span>
                                </td>
                            </tr>
                        </table>

                        <div style="font-size:21px;font-weight:bold;color:#0f2b5b;line-height:1.35;">{{ $sesi->nama }}</div>
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
                                    <td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">{{ $judul }}</td>
                                    <td align="right" style="padding:9px 0;font-weight:bold;color:#0f2b5b;border-bottom:1px solid #f1f5f9;">{{ $isi }}</td>
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

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px auto 8px;">
                            <tr>
                                <td style="background:#0f2b5b;border-radius:10px;">
                                    <a href="{{ $tautanStatus }}"
                                        style="display:inline-block;padding:13px 26px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">
                                        Lihat rincian pendaftaran
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- Kaki surat disamakan dengan bukti pendaftaran: dua surat
                     dari pengirim yang sama yang kakinya berbeda terbaca
                     seperti datang dari dua tempat. --}}
                <tr>
                    <td align="center" style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.7;">
                        <strong style="color:#475569;">{{ config('mail.from.name') }}</strong><br>
                        Tautan masuk sesinya dibagikan panitia lewat grup peserta di WhatsApp.<br>
                        <span style="color:#94a3b8;">Email ini dikirim otomatis karena Anda terdaftar di sesi ini.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
