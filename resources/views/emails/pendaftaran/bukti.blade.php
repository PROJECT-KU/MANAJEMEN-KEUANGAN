{{--
  Kerangka SURAT BUKTI PENDAFTARAN, dipakai bersama semua layanan.

  Diangkat dari surat Webinar Eksklusif atas permintaan pemiliknya 24 Okt
  2026: "semua email diseragamkan seperti webinar eksklusif untuk semua
  pemesanan layanan". Sebelumnya tiap layanan punya desainnya sendiri —
  Scopus Camp memakai logo MIS biru, Bibliometrik dan Scopus Kafe lain lagi —
  jadi satu pelanggan yang memesan dua layanan menerima dua surat yang tidak
  terlihat berasal dari tempat yang sama.

  ---------------------------------------------------------------------------
  DITULIS DENGAN TABEL DAN GAYA SEBARIS
  ---------------------------------------------------------------------------

  Bukan dengan kelas, flexbox, atau blok <style>. Gmail — yang paling banyak
  dipakai pembaca surat ini — membuang <style> sama sekali, dan Outlook
  mengabaikan padding pada <a> serta style pada <img>. Jadi: tabel bersarang,
  lebar disebut di atribut width JUGA, dan tiap unsur bergaya sendiri.

  Perataannya SENGAJA BERGANTI-GANTI: kepala, nomor pendaftaran, dan tombol
  ditengahkan; rincian kiri-kanan; kalimat rata kiri. Rata kiri semuanya
  membuat surat sepanjang ini terbaca sebagai satu balok tanpa titik berhenti.

  ---------------------------------------------------------------------------
  Yang dikirim pemanggilnya
  ---------------------------------------------------------------------------

    $judul      judul besar di tengah kepala surat
    $ringkas    satu kalimat di bawah judul (boleh null)
    $lencana    ['teks','latar','garis','tinta'] (boleh null)
    $sapaan     nama orang yang disapa
    $pembuka    satu paragraf pembuka; boleh memuat <strong>
    $nomor      nomor pendaftaran, dipajang besar di tengah (boleh null)
    $rincian    larik baris: ['label','nilai'] dan boleh ['warna','awalan']
    $total      ['label','nilai'] (boleh null)
    $peserta    larik ['nama','utama'] (boleh kosong)
    $langkah    ['judul','daftar'=>[...]] (boleh null)
    $catatan    larik paragraf kecil berkotak (boleh kosong)
    $tombol     ['teks','url'] (boleh null)
    $tenggat    kalimat batas waktu di bawah tombol (boleh null)
    $kaki       larik baris kaki surat (boleh kosong)
--}}
@php
    /*
     * Logo DISISIPKAN lewat aset_surat(), bukan ditautkan ke peladen. Gmail
     * dan Outlook memblokir gambar jauh secara bawaan, jadi logo bertautan
     * muncul sebagai kotak kosong sampai orangnya menekan "tampilkan gambar".
     */
    $logo = aset_surat('assets/img/logo-rsc-email.png');

    // Semua medan opsional diberi nilai bawaan DI SINI, satu tempat: Blade
    // tidak memuat variabel yang tidak dikirim, dan membacanya langsung
    // melempar "Undefined variable" pada layanan yang tidak memakainya.
    $ringkas = $ringkas ?? null;
    $lencana = $lencana ?? null;
    $nomor = $nomor ?? null;
    $rincian = $rincian ?? [];
    $total = $total ?? null;
    $peserta = $peserta ?? [];
    $langkah = $langkah ?? null;
    $catatan = $catatan ?? [];
    $tombol = $tombol ?? null;
    $tenggat = $tenggat ?? null;
    $kaki = $kaki ?? [];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }}</title>
</head>
<body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                style="max-width:580px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">

                {{-- Kepala putih dengan logo DITENGAHKAN. Latarnya harus
                     terang: tulisan di logonya hitam, dan di atas pita navy
                     ia hilang sama sekali. --}}
                <tr>
                    <td align="center" style="padding:28px 26px 20px;border-bottom:1px solid #eef2f7;">
                        <img src="{{ $logo }}" alt="{{ config('mail.from.name') }}" width="220"
                            style="display:block;width:220px;max-width:70%;height:auto;border:0;">
                    </td>
                </tr>

                {{-- Keadaan pesanannya disebut PALING ATAS dan ditengahkan,
                     sebab itu pertanyaan pertama orang saat membuka surat
                     ini: "jadi pesanan saya sudah masuk atau belum?" --}}
                <tr>
                    <td align="center" style="padding:26px 26px 6px;">
                        @if ($lencana)
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 14px;">
                                <tr>
                                    <td style="padding:6px 14px;border-radius:999px;background:{{ $lencana['latar'] }};border:1px solid {{ $lencana['garis'] }};">
                                        <span style="font-size:12px;font-weight:bold;letter-spacing:.6px;color:{{ $lencana['tinta'] }};">
                                            {{ $lencana['teks'] }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <div style="font-size:21px;font-weight:bold;color:#0f2b5b;line-height:1.35;">
                            {{ $judul }}
                        </div>

                        @if ($ringkas)
                            <div style="margin-top:8px;font-size:14px;color:#64748b;line-height:1.6;">
                                {{ $ringkas }}
                            </div>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:22px 26px 0;">
                        <p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#334155;">
                            Halo <strong style="color:#0f2b5b;">{{ $sapaan }}</strong>, {!! $pembuka !!}
                        </p>

                        {{-- Nomor pendaftaran ditengahkan dan dibesarkan: ini
                             yang disebut orang saat menghubungi panitia, dan
                             satu-satunya hal di surat ini yang perlu dibaca
                             ulang nanti. --}}
                        @if ($nomor)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:22px;">
                                <tr>
                                    <td align="center" style="padding:16px 18px;">
                                        <div style="font-size:11px;letter-spacing:1.2px;color:#64748b;text-transform:uppercase;">Nomor pendaftaran</div>
                                        <div style="font-size:22px;font-weight:bold;color:#0f2b5b;margin-top:5px;letter-spacing:.5px;">{{ $nomor }}</div>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        {{-- Rincian kiri-kanan: labelnya kiri, isinya kanan.
                             Dua kolom begini terbaca sekali lirik, sedangkan
                             label dan isi yang sama-sama rata kiri harus
                             dibaca baris demi baris. --}}
                        @if ($rincian !== [] || $total)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                @foreach ($rincian as $baris)
                                    <tr>
                                        <td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">{{ $baris['label'] }}</td>
                                        <td align="right" style="padding:9px 0;font-weight:bold;color:{{ $baris['warna'] ?? '#0f2b5b' }};border-bottom:1px solid #f1f5f9;">
                                            {!! ($baris['awalan'] ?? '') !!}{{ $baris['nilai'] }}
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($total)
                                    <tr>
                                        <td style="padding:14px 0 0;color:#0f2b5b;font-weight:bold;font-size:15px;">{{ $total['label'] }}</td>
                                        <td align="right" style="padding:14px 0 0;font-weight:bold;color:#ff6a00;font-size:19px;">
                                            {{ $total['nilai'] }}
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        @endif

                        {{-- Daftar peserta ikut dikirim: inilah yang dipakai
                             pendaftar memeriksa ejaan namanya sebelum
                             sertifikat diterbitkan. Salah ejaan yang baru
                             ketahuan saat sertifikatnya jadi adalah pekerjaan
                             ulang yang bisa dicegah di sini. --}}
                        @if (count($peserta) > 1)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="margin-top:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <div style="font-size:12px;font-weight:bold;color:#0f2b5b;margin-bottom:8px;">
                                            Nama peserta ({{ count($peserta) }} orang)
                                        </div>
                                        @foreach ($peserta as $i => $orang)
                                            <div style="font-size:13px;color:#334155;padding:3px 0;">
                                                {{ $i + 1 }}. {{ $orang['nama'] }}{{ ! empty($orang['utama']) ? ' (pendaftar)' : '' }}
                                            </div>
                                        @endforeach
                                        <div style="font-size:11px;color:#94a3b8;margin-top:8px;line-height:1.5;">
                                            Sertifikat diterbitkan atas nama ini. Kalau ada yang salah eja,
                                            kabari panitia sebelum hari pelaksanaan.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                </tr>

                {{-- Langkah berikutnya ditulis sebagai urutan BERNOMOR, bukan
                     paragraf. Orang yang baru mengisi borang perlu tahu persis
                     apa yang harus dikerjakan sekarang. --}}
                @if ($langkah && ! empty($langkah['daftar']))
                    <tr>
                        <td style="padding:24px 26px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div style="font-size:14px;font-weight:bold;color:#9a3412;margin-bottom:10px;">
                                            {{ $langkah['judul'] ?? 'Langkah berikutnya' }}
                                        </div>
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;color:#7c2d12;line-height:1.65;">
                                            @foreach ($langkah['daftar'] as $i => $isi)
                                                <tr>
                                                    <td valign="top" style="padding:3px 8px 3px 0;font-weight:bold;">{{ $i + 1 }}.</td>
                                                    <td style="padding:3px 0;">{!! $isi !!}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                {{-- Bantalan ATASNYA 14px, dan itu bukan sekadar kerapian:
                     baris di atasnya ditutup "padding: 24px 26px 0", jadi nol
                     di sini membuat kedua kartunya bersentuhan dan terbaca
                     sebagai satu kotak yang warnanya berubah di tengah. 14px,
                     bukan 24px seperti jarak antarbagian lain — keduanya
                     memang sepasang dan harus dibaca berurutan. --}}
                @foreach ($catatan as $isi)
                    <tr>
                        <td style="padding:14px 26px 0;">
                            <p style="margin:0;padding:13px 15px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;color:#475569;line-height:1.6;">
                                {!! $isi !!}
                            </p>
                        </td>
                    </tr>
                @endforeach

                {{-- Tombolnya tabel ber-background dan DITENGAHKAN, bukan <a>
                     bergaya tombol: Outlook mengabaikan padding pada <a>. --}}
                @if ($tombol)
                    <tr>
                        <td align="center" style="padding:24px 26px 6px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td style="background:#ff6a00;border-radius:10px;">
                                        <a href="{{ $tombol['url'] }}"
                                            style="display:inline-block;padding:14px 30px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">
                                            {{ $tombol['teks'] }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if ($tenggat)
                    <tr>
                        <td align="center" style="padding:14px 26px 0;">
                            <div style="font-size:13px;color:#9a3412;line-height:1.6;">{!! $tenggat !!}</div>
                        </td>
                    </tr>
                @endif

                {{-- Tautan utuh ditulis juga, tetapi dikecilkan dan diredupkan:
                     sebagian klien surat memblokir tautan di tombol, dan yang
                     tersisa harus tetap bisa disalin — tanpa ia ikut bersaing
                     dengan tombolnya. --}}
                @if ($tombol)
                    <tr>
                        <td align="center" style="padding:16px 26px 24px;">
                            <div style="font-size:11px;color:#94a3b8;line-height:1.6;word-break:break-all;">
                                Tombolnya tidak bisa ditekan? Salin alamat ini:<br>{{ $tombol['url'] }}
                            </div>
                        </td>
                    </tr>
                @else
                    {{-- Tanpa tombol, isinya tetap butuh jarak ke kaki surat;
                         tanpa baris ini paragraf terakhirnya menempel. --}}
                    <tr><td style="padding:0 26px 24px;"></td></tr>
                @endif

                <tr>
                    <td align="center" style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.7;">
                        <strong style="color:#475569;">{{ config('mail.from.name') }}</strong><br>
                        @foreach ($kaki as $baris)
                            {!! $baris !!}<br>
                        @endforeach
                        <span style="color:#94a3b8;">Email ini dikirim otomatis karena ada pemesanan atas nama Anda.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
