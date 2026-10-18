{{-- Gayanya sebaris, susunannya tabel, dan perataannya berganti-ganti —
     alasan ketiganya sama dengan email_pendaftaran.

     LOGONYA Rumah Scopus, bukan MIS. Dua logo hidup berdampingan di proyek
     ini: logo-email.png (MIS, "Management Integration System") dipakai 21
     surat, dan logo-rsc-email.png di surat yang keluar ke pelanggan. MIS
     adalah nama sistem internalnya; pesertanya tidak pernah mendengar nama
     itu, dan surat bermerek asing dari pengirim yang tidak dikenal adalah
     surat yang dihapus tanpa dibuka. --}}
@php
    $logo = aset_surat('assets/img/logo-rsc-email.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kursi Anda dilepas — {{ $pendaftaran->id_transaksi }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">

                {{-- Kepala putih berlogo, sama dengan dua surat lainnya:
                     tulisan di logonya hitam dan hilang di atas latar pekat. --}}
                <tr>
                    <td align="center" style="padding:28px 26px 20px;border-bottom:1px solid #eef2f7;">
                        <img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="220"
                            style="display:block;width:220px;max-width:70%;height:auto;border:0;">
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:26px 26px 6px;">
                        {{-- Lencananya ABU-ABU, bukan merah. Merah dibaca
                             sebagai kesalahan orangnya; ini cuma waktu yang
                             habis, dan kursinya bisa diambil lagi. --}}
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 14px;">
                            <tr>
                                <td style="padding:6px 14px;border-radius:999px;background:#f1f5f9;border:1px solid #e2e8f0;">
                                    <span style="font-size:12px;font-weight:bold;letter-spacing:.6px;color:#475569;">BATAS WAKTU LEWAT</span>
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

                        {{-- Kalimat pertamanya menyebut KEADAANNYA SEKARANG,
                             bukan riwayatnya. Yang membaca surat ini perlu
                             tahu lebih dulu bahwa pendaftarannya tidak
                             berlaku lagi — baru sesudah itu kenapa. --}}
                        <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#334155;">
                            Pembayaran untuk pendaftaran Anda belum masuk sampai batas waktunya,
                            jadi <strong style="color:#0f2b5b;">kursinya sudah kami lepas</strong>
                            untuk peserta lain. Pendaftaran ini tidak berlaku lagi.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#334155;">
                            @php($baris = array_filter([
                                'Nomor pendaftaran' => $pendaftaran->id_transaksi,
                                'Atas nama' => $pendaftaran->nama,
                                'Batas pembayaran' => $pendaftaran->kedaluwarsa_pada
                                    ? $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('d F Y, H:i') . ' WIB'
                                    : null,
                            ]))

                            @foreach ($baris as $judul => $isi)
                                <tr>
                                    <td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">{{ $judul }}</td>
                                    <td align="right" style="padding:9px 0;font-weight:bold;color:#0f2b5b;border-bottom:1px solid #f1f5f9;">{{ $isi }}</td>
                                </tr>
                            @endforeach
                        </table>

                        {{-- Kalimat yang paling penting di surat ini, dan
                             sebabnya tidak kelihatan dari kodenya: yang uangnya
                             SUDAH terpotong tetapi kabarnya tidak sampai juga
                             akan menerima surat ini. Tanpa kalimat ini, ia
                             mengira uangnya hangus bersama kursinya dan tidak
                             pernah menghubungi siapa pun. --}}
                        <p style="margin:20px 0 0;padding:13px 15px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;font-size:13px;color:#9a3412;line-height:1.6;">
                            <strong>Merasa sudah membayar?</strong> Kalau uang Anda terpotong,
                            balas email ini atau hubungi panitia dengan menyebut nomor di atas.
                            Uangnya tidak hilang — kami telusuri dan kursinya kami kembalikan.
                        </p>

                        <p style="margin:20px 0 0;font-size:14px;line-height:1.7;color:#334155;">
                            Masih ingin ikut? Kursinya masih bisa diambil lagi selama kuotanya
                            belum penuh.
                        </p>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px auto 8px;">
                            <tr>
                                <td style="background:#0f2b5b;border-radius:10px;">
                                    <a href="{{ $tautanDaftar }}"
                                        style="display:inline-block;padding:13px 26px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">
                                        Daftar lagi
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- Kaki surat disamakan dengan dua surat lainnya: tiga surat
                     dari pengirim yang sama yang kakinya berbeda terbaca
                     seperti datang dari tiga tempat. --}}
                <tr>
                    <td align="center" style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.7;">
                        <strong style="color:#475569;">{{ config('mail.from.name') }}</strong><br>
                        Ada yang perlu ditanyakan? Balas saja email ini.<br>
                        <span style="color:#94a3b8;">Email ini dikirim otomatis karena batas pembayaran pendaftaran Anda lewat.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
