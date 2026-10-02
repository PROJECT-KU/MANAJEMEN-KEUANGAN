{{--
    Bukti pendaftaran Webinar Eksklusif.

    Gayanya ditulis SEBARIS di tiap unsur, bukan lewat <style>. Banyak klien
    surat (Gmail yang paling banyak dipakai di sini) membuang blok <style>
    sama sekali, dan yang sampai ke orangnya jadi teks telanjang.

    Susunannya tabel, bukan flex atau grid, dengan alasan yang sama.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran {{ $sesi->nama }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">

                <tr>
                    <td style="background:#0f2b5b;padding:22px 26px;">
                        <div style="color:#ffffff;font-size:18px;font-weight:bold;">Pendaftaran Anda tersimpan</div>
                        <div style="color:#c7d2e4;font-size:13px;margin-top:4px;">{{ config('app.name') }}</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:26px;">
                        <p style="margin:0 0 16px;font-size:15px;">Halo {{ $pendaftaran->nama }},</p>

                        <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#334155;">
                            Terima kasih, pendaftaran Anda untuk
                            <strong style="color:#0f2b5b;">{{ $sesi->nama }}</strong> sudah kami terima.
                            Simpan email ini — di dalamnya ada tautan untuk melihat status
                            dan cara pembayaran Anda kapan saja. Tautan masuk ke sesinya
                            dibagikan panitia lewat grup peserta di WhatsApp.
                        </p>

                        {{-- Nomor pendaftaran ditaruh paling menonjol: ini yang
                             disebut orang saat menghubungi panitia. --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                            style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:20px;">
                            <tr>
                                <td style="padding:16px 18px;">
                                    <div style="font-size:11px;letter-spacing:1px;color:#64748b;text-transform:uppercase;">Nomor pendaftaran</div>
                                    <div style="font-size:19px;font-weight:bold;color:#0f2b5b;margin-top:3px;">{{ $pendaftaran->id_transaksi }}</div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#334155;">
                            @php($baris = [
                                'Tanggal' => \App\Support\RentangTanggal::tulis(
                                    $sesi->mulai ? \Carbon\Carbon::parse($sesi->mulai) : null,
                                    $sesi->selesai ? \Carbon\Carbon::parse($sesi->selesai) : null
                                ) ?: 'Menyusul',
                                'Jam' => $sesi->jam ?: 'Menyusul',
                                'Lewat' => $sesi->platform ?: 'Daring',
                                'Jumlah peserta' => $pendaftaran->jumlah_pendaftar . ' orang',
                                'Total bayar' => 'Rp ' . number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.'),
                            ])

                            @foreach ($baris as $judul => $isi)
                                <tr>
                                    <td style="padding:7px 0;color:#64748b;width:42%;">{{ $judul }}</td>
                                    <td style="padding:7px 0;font-weight:bold;color:#0f2b5b;">{{ $isi }}</td>
                                </tr>
                            @endforeach
                        </table>

                        {{-- Tombolnya tabel ber-background, bukan <a> bergaya
                             tombol: Outlook mengabaikan padding pada <a>. --}}
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0 8px;">
                            <tr>
                                <td style="background:#ff6a00;border-radius:10px;">
                                    <a href="{{ $tautanStatus }}"
                                        style="display:inline-block;padding:13px 26px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">
                                        Lihat status &amp; cara bayar
                                    </a>
                                </td>
                            </tr>
                        </table>

                        {{-- Tautannya ditulis utuh juga: sebagian klien surat
                             memblokir tautan di tombol, dan yang tersisa harus
                             tetap bisa disalin. --}}
                        <p style="margin:12px 0 0;font-size:12px;color:#64748b;line-height:1.6;word-break:break-all;">
                            Kalau tombolnya tidak bisa ditekan, salin alamat ini ke peramban:<br>
                            {{ $tautanStatus }}
                        </p>

                        @if ($pendaftaran->kedaluwarsa_pada)
                            <p style="margin:18px 0 0;padding:12px 14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;font-size:13px;color:#9a3412;line-height:1.6;">
                                Kursi Anda ditahan sampai
                                <strong>{{ $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('d F Y, H:i') }} WIB</strong>.
                                Lewat dari itu kursinya dilepas lagi untuk pendaftar lain.
                            </p>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.6;">
                        Email ini dikirim otomatis karena ada pendaftaran atas nama Anda.
                        Kalau Anda merasa tidak mendaftar, abaikan saja email ini.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
