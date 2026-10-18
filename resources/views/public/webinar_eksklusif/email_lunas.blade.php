{{-- Gayanya sebaris, susunannya tabel, perataannya berganti-ganti — alasan
     ketiganya sama dengan email_pendaftaran. Logonya yayasan, bukan MIS;
     alasannya di email_kedaluwarsa. --}}
@php
    $logo = aset_surat('assets/img/logo-rsc-email.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran diterima — {{ $pendaftaran->id_transaksi }}</title>
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
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 14px;">
                            <tr>
                                <td style="padding:6px 14px;border-radius:999px;background:#ecfdf5;border:1px solid #a7f3d0;">
                                    <span style="font-size:12px;font-weight:bold;letter-spacing:.6px;color:#15803d;">LUNAS</span>
                                </td>
                            </tr>
                        </table>

                        <div style="font-size:21px;font-weight:bold;color:#0f2b5b;line-height:1.35;">
                            {{ $sesi?->nama ?: 'Webinar Eksklusif' }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:26px;">
                        <p style="margin:0 0 16px;font-size:15px;">Halo {{ $pendaftaran->nama }},</p>

                        <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#334155;">
                            Pembayaran Anda sudah kami terima dan
                            <strong style="color:#0f2b5b;">kursi Anda aman</strong>.
                            Tidak ada lagi yang perlu Anda kerjakan.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#334155;">
                            @php($baris = array_filter([
                                'Nomor pendaftaran' => $pendaftaran->id_transaksi,
                                'Jumlah peserta' => ((int) $pendaftaran->jumlah_pendaftar) . ' orang',
                                'Jumlah dibayar' => 'Rp ' . number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.'),
                                'Cara pembayaran' => $pendaftaran->bayar_kanal ?: 'Dikonfirmasi panitia',
                                'Tanggal' => $pendaftaran->bayar_pada
                                    ? $pendaftaran->bayar_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
                                    : null,
                            ]))

                            @foreach ($baris as $judul => $isi)
                                <tr>
                                    <td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">{{ $judul }}</td>
                                    <td align="right" style="padding:9px 0;font-weight:bold;color:#0f2b5b;border-bottom:1px solid #f1f5f9;">{{ $isi }}</td>
                                </tr>
                            @endforeach
                        </table>

                        {{-- Lampirannya DISEBUT. Lampiran yang tidak disebut
                             tidak dibuka, dan kwitansi yang tidak pernah
                             dibuka sama saja dengan tidak dikirim — terutama
                             bagi yang harus menyerahkannya ke bendahara
                             kantornya untuk penggantian biaya. --}}
                        <p style="margin:20px 0 0;padding:13px 15px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;color:#475569;line-height:1.6;">
                            <strong style="color:#0f2b5b;">Kwitansinya terlampir</strong> di email ini
                            sebagai berkas PDF. Simpan kalau Anda perlu mengurus penggantian biaya.
                        </p>

                        <p style="margin:20px 0 0;font-size:14px;line-height:1.7;color:#334155;">
                            Tautan masuk sesinya dibagikan panitia lewat grup peserta di WhatsApp,
                            paling lambat sehari sebelum acara.
                        </p>

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

                <tr>
                    <td align="center" style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.7;">
                        <strong style="color:#475569;">{{ config('mail.from.name') }}</strong><br>
                        Ada yang perlu ditanyakan? Balas saja email ini.<br>
                        <span style="color:#94a3b8;">Email ini dikirim otomatis karena pembayaran Anda sudah tercatat lunas.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
