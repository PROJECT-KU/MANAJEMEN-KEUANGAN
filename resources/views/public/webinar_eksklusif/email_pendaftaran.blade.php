{{--
    Bukti pendaftaran Webinar Eksklusif.

    Gayanya ditulis SEBARIS di tiap unsur, bukan lewat <style>. Banyak klien
    surat — Gmail yang paling banyak dipakai di sini — membuang blok <style>
    sama sekali, dan yang sampai ke orangnya jadi teks telanjang.

    Susunannya tabel, bukan flex atau grid, dengan alasan yang sama.

    Perataannya SENGAJA BERGANTI-GANTI: kepala, nomor pendaftaran, dan tombol
    ditengahkan; rincian kiri-kanan; kalimat rata kiri. Rata kiri semuanya
    membuat surat sepanjang ini terbaca sebagai satu balok tanpa titik
    berhenti, dan mata tidak tahu mana yang penting.
--}}
@php
    // Logo DISISIPKAN sebagai lampiran, bukan ditautkan ke peladen. Gmail dan
    // Outlook memblokir gambar jauh secara bawaan, jadi logo bertautan muncul
    // sebagai kotak kosong sampai orangnya menekan "tampilkan gambar".
    $logo = $message->embed(public_path('assets/img/logo-rsc-email.png'));
    $belumBayar = ! $pendaftaran->lunas;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran {{ $sesi->nama }}</title>
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
                        <img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="220"
                            style="display:block;width:220px;max-width:70%;height:auto;border:0;">
                    </td>
                </tr>

                {{-- Keadaan pendaftarannya disebut PALING ATAS dan ditengahkan,
                     sebab itu pertanyaan pertama orang saat membuka surat ini:
                     "jadi saya sudah terdaftar atau belum?" --}}
                <tr>
                    <td align="center" style="padding:26px 26px 6px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 14px;">
                            <tr>
                                <td style="padding:6px 14px;border-radius:999px;background:{{ $belumBayar ? '#fff7ed' : '#ecfdf5' }};border:1px solid {{ $belumBayar ? '#fed7aa' : '#a7f3d0' }};">
                                    <span style="font-size:12px;font-weight:bold;letter-spacing:.6px;color:{{ $belumBayar ? '#9a3412' : '#065f46' }};">
                                        {{ $belumBayar ? 'BELUM DIBAYAR' : 'SUDAH DIBAYAR' }}
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <div style="font-size:21px;font-weight:bold;color:#0f2b5b;line-height:1.35;">
                            Data Anda sudah kami terima
                        </div>

                        <div style="margin-top:8px;font-size:14px;color:#64748b;line-height:1.6;">
                            {{ $belumBayar
                                ? 'Kursi Anda ditahan sementara. Tinggal satu langkah lagi: pembayaran.'
                                : 'Pendaftaran Anda lengkap. Sampai jumpa di sesinya.' }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:22px 26px 0;">
                        <p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#334155;">
                            Halo <strong style="color:#0f2b5b;">{{ $pendaftaran->nama }}</strong>, terima kasih sudah
                            mendaftar <strong style="color:#0f2b5b;">{{ $sesi->nama }}</strong>.
                        </p>

                        {{-- Nomor pendaftaran ditengahkan dan dibesarkan: ini
                             yang disebut orang saat menghubungi panitia, dan
                             satu-satunya hal di surat ini yang perlu dibaca
                             ulang nanti. --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                            style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:22px;">
                            <tr>
                                <td align="center" style="padding:16px 18px;">
                                    <div style="font-size:11px;letter-spacing:1.2px;color:#64748b;text-transform:uppercase;">Nomor pendaftaran</div>
                                    <div style="font-size:22px;font-weight:bold;color:#0f2b5b;margin-top:5px;letter-spacing:.5px;">{{ $pendaftaran->id_transaksi }}</div>
                                </td>
                            </tr>
                        </table>

                        {{-- Rincian kiri-kanan: labelnya kiri, isinya kanan.
                             Dua kolom begini terbaca sekali lirik, sedangkan
                             label dan isi yang sama-sama rata kiri harus
                             dibaca baris demi baris. --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                            @php($baris = [
                                'Tanggal' => \App\Support\RentangTanggal::tulis(
                                    $sesi->mulai ? \Carbon\Carbon::parse($sesi->mulai) : null,
                                    $sesi->selesai ? \Carbon\Carbon::parse($sesi->selesai) : null
                                ) ?: 'Menyusul',
                                'Jam' => $sesi->jam ?: 'Menyusul',
                                'Lewat' => $sesi->platform ?: 'Daring',
                                'Atas nama' => $pendaftaran->nama,
                                'Jumlah peserta' => $pendaftaran->jumlah_pendaftar . ' orang',
                            ])

                            @foreach ($baris as $judul => $isi)
                                <tr>
                                    <td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">{{ $judul }}</td>
                                    <td align="right" style="padding:9px 0;font-weight:bold;color:#0f2b5b;border-bottom:1px solid #f1f5f9;">{{ $isi }}</td>
                                </tr>
                            @endforeach

                            @if ((int) $pendaftaran->nominal_diskon > 0)
                                <tr>
                                    <td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">
                                        Potongan ({{ $pendaftaran->kode_diskon }})
                                    </td>
                                    <td align="right" style="padding:9px 0;font-weight:bold;color:#0f9b74;border-bottom:1px solid #f1f5f9;">
                                        &minus; Rp {{ number_format((int) $pendaftaran->nominal_diskon, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td style="padding:14px 0 0;color:#0f2b5b;font-weight:bold;font-size:15px;">Total bayar</td>
                                <td align="right" style="padding:14px 0 0;font-weight:bold;color:#ff6a00;font-size:19px;">
                                    Rp {{ number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.') }}
                                </td>
                            </tr>
                        </table>

                        {{-- Daftar peserta ikut dikirim: inilah yang dipakai
                             pendaftar memeriksa ejaan namanya sebelum
                             sertifikat diterbitkan. Salah ejaan baru ketahuan
                             saat sertifikatnya jadi adalah pekerjaan ulang
                             yang bisa dicegah di sini. --}}
                        @php($semuaPeserta = $pendaftaran->semuaPeserta())

                        @if (count($semuaPeserta) > 1)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="margin-top:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <div style="font-size:12px;font-weight:bold;color:#0f2b5b;margin-bottom:8px;">
                                            Nama peserta ({{ count($semuaPeserta) }} orang)
                                        </div>
                                        @foreach ($semuaPeserta as $i => $orang)
                                            <div style="font-size:13px;color:#334155;padding:3px 0;">
                                                {{ $i + 1 }}. {{ $orang['nama'] }}{{ $orang['utama'] ? ' (pendaftar)' : '' }}
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

                @if ($belumBayar)
                    {{-- Langkah berikutnya ditulis sebagai urutan bernomor,
                         bukan paragraf. Orang yang baru mengisi borang perlu
                         tahu persis apa yang harus dikerjakan sekarang. --}}
                    <tr>
                        <td style="padding:24px 26px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div style="font-size:14px;font-weight:bold;color:#9a3412;margin-bottom:10px;">
                                            Langkah berikutnya
                                        </div>
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;color:#7c2d12;line-height:1.65;">
                                            <tr>
                                                <td valign="top" style="padding:3px 8px 3px 0;font-weight:bold;">1.</td>
                                                <td style="padding:3px 0;">Buka halaman status lewat tombol di bawah.</td>
                                            </tr>
                                            <tr>
                                                <td valign="top" style="padding:3px 8px 3px 0;font-weight:bold;">2.</td>
                                                <td style="padding:3px 0;">Transfer sesuai nominal dan rekening yang tertera di sana.</td>
                                            </tr>
                                            <tr>
                                                <td valign="top" style="padding:3px 8px 3px 0;font-weight:bold;">3.</td>
                                                <td style="padding:3px 0;">Kirim bukti transfernya ke panitia lewat WhatsApp.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                {{-- Tombolnya tabel ber-background dan DITENGAHKAN, bukan <a>
                     bergaya tombol: Outlook mengabaikan padding pada <a>. --}}
                <tr>
                    <td align="center" style="padding:24px 26px 6px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
                            <tr>
                                <td style="background:#ff6a00;border-radius:10px;">
                                    <a href="{{ $tautanStatus }}"
                                        style="display:inline-block;padding:14px 30px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">
                                        {{ $belumBayar ? 'Lihat cara pembayaran' : 'Lihat rincian pendaftaran' }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @if ($pendaftaran->kedaluwarsa_pada && $belumBayar)
                    <tr>
                        <td align="center" style="padding:14px 26px 0;">
                            <div style="font-size:13px;color:#9a3412;line-height:1.6;">
                                Kursi Anda ditahan sampai
                                <strong>{{ $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('d F Y, H:i') }} WIB</strong>.
                            </div>
                        </td>
                    </tr>
                @endif

                {{-- Tautan utuh ditulis juga, tetapi dikecilkan dan diredupkan:
                     sebagian klien surat memblokir tautan di tombol, dan yang
                     tersisa harus tetap bisa disalin — tanpa ia ikut bersaing
                     dengan tombolnya. --}}
                <tr>
                    <td align="center" style="padding:16px 26px 24px;">
                        <div style="font-size:11px;color:#94a3b8;line-height:1.6;word-break:break-all;">
                            Tombolnya tidak bisa ditekan? Salin alamat ini:<br>{{ $tautanStatus }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.7;">
                        <strong style="color:#475569;">{{ config('mail.from.name') }}</strong><br>
                        Tautan masuk sesinya dibagikan panitia lewat grup peserta di WhatsApp.<br>
                        <span style="color:#94a3b8;">Email ini dikirim otomatis karena ada pendaftaran atas nama Anda.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
