{{--
  Surat untuk orang yang DIDAFTARKAN panitia.

  Rata kiri semua dan tanpa logo pada versi pertama: terbaca seperti nota
  sistem, bukan kabar dari sebuah yayasan. Sekarang kepalanya berlogo dan
  berwarna, nominal yang harus ditransfer ditaruh di tengah sebagai angka
  besar, dan rinciannya jadi tabel berlabel kiri-nilai kanan.

  Dirakit dengan <table> dan gaya sebaris, bukan kelas CSS: Gmail membuang
  <style> di <head> pada sebagian tampilan, dan flexbox maupun grid tidak
  didukung Outlook.
--}}
@php
    // Nominalnya SUDAH termasuk kode unik — itulah angka yang harus
    // ditransfer persis. Tiga angka terakhirnya adalah kodenya sendiri.
    // Lihat alasannya di slip.blade.php: perbandingan tiga angka terakhir
    // rusak sejak rentang kode unik diseragamkan ke 500-1500.
    $kodeMasuk = $kodeUnik > 0 && $total > $kodeUnik && ($total - $kodeUnik) % 1000 === 0;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Pendaftaran tercatat</title>
</head>

<body style="margin:0;padding:0;background:#f5f6fa;">
    {{-- Pratinjau di daftar kotak masuk: nomornya, sebab itu yang dicari. --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0">
        Nomor pendaftaran Anda {{ $nomor }} — total Rp {{ number_format($total, 0, ',', '.') }}.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:#f5f6fa;padding:30px 12px;font-family:'Segoe UI',Helvetica,Arial,sans-serif">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:580px;background:#ffffff;border-radius:16px;overflow:hidden;
                           box-shadow:0 10px 30px -18px rgba(15,23,42,.45)">

                    {{-- Kop surat: logo Rumah Scopus di atas putih.

                         BUKAN di atas bidang ungu seperti sisa aplikasi:
                         logonya hitam-oranye dengan latar tembus pandang, dan
                         di atas ungu tulisan "RUMAH SCOPUS" hampir tidak
                         terbaca. Surat ini juga dibaca PENDAFTAR, bukan orang
                         dalam — jadi yang pantas tampil merek yayasannya,
                         bukan logo alat internalnya. --}}
                    <tr>
                        <td align="center" style="padding:26px 28px 18px;background:#ffffff">
                            <img src="{{ asset('assets/img/LogoRSC.png') }}"
                                alt="Rumah Scopus Foundation" width="210"
                                style="display:block;border:0;width:210px;max-width:72%;height:auto">
                        </td>
                    </tr>

                    {{-- Garis aksen memakai oranye mereknya, bukan ungu
                         aplikasi. --}}
                    <tr>
                        <td style="padding:0;line-height:0;font-size:0">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="4"
                                        style="height:4px;background:#ea7a2c;
                                               background-image:linear-gradient(90deg,#ea7a2c 0%,#f4a261 100%)">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:22px 28px 0">
                            <p style="margin:0;font-size:20px;font-weight:800;color:#0f172a">
                                Pendaftaran Anda tercatat
                            </p>
                            <p style="margin:5px 0 0;font-size:13px;color:#64748b">
                                {{ $layanan }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 28px 0;font-size:14px;line-height:1.65;color:#334155">
                            <p style="margin:0">
                                Halo <strong>{{ $nama }}</strong>, terima kasih — pendaftaran Anda
                                sudah kami catat. Berikut rinciannya.
                            </p>
                        </td>
                    </tr>

                    {{-- Nomor pendaftaran: dipisahkan dan dibesarkan, sebab
                         inilah yang ditanyakan ulang di kemudian hari. --}}
                    <tr>
                        <td align="center" style="padding:18px 28px 0">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                style="border:1px dashed #f6c9a3;border-radius:12px;background:#fff7ed">
                                <tr>
                                    <td align="center" style="padding:13px 26px">
                                        <p style="margin:0;font-size:11px;letter-spacing:.09em;
                                                  text-transform:uppercase;color:#c2610c">
                                            Nomor pendaftaran
                                        </p>
                                        <p style="margin:3px 0 0;font-size:22px;font-weight:800;
                                                  letter-spacing:.06em;color:#0f172a">
                                            {{ $nomor }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Rincian: label di kiri, nilai di kanan. --}}
                    <tr>
                        <td style="padding:20px 28px 0">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="font-size:14px;color:#334155">
                                @if ($angkatan)
                                    <tr>
                                        <td style="padding:7px 0;border-bottom:1px solid #f1f5f9;color:#64748b">Angkatan</td>
                                        <td align="right" style="padding:7px 0;border-bottom:1px solid #f1f5f9;font-weight:700;color:#0f172a">{{ $angkatan }}</td>
                                    </tr>
                                @endif
                                @if ($tanggal)
                                    <tr>
                                        <td style="padding:7px 0;border-bottom:1px solid #f1f5f9;color:#64748b">Mulai</td>
                                        <td align="right" style="padding:7px 0;border-bottom:1px solid #f1f5f9;font-weight:700;color:#0f172a">{{ $tanggal }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:7px 0;color:#64748b">Cara bayar</td>
                                    <td align="right" style="padding:7px 0;font-weight:700;color:#0f172a">
                                        {{ $caraBayar === 'tunai' ? 'Bayar di tempat' : ($caraBayar === 'doku' ? 'Pembayaran daring' : 'Transfer bank') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Nominal: angka besar di tengah, sebab inilah satu hal
                         yang harus dibaca benar. --}}
                    <tr>
                        <td align="center" style="padding:20px 28px 0">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="border-radius:12px;background:{{ $sudahLunas ? '#ecfdf5' : '#f8fafc' }}">
                                <tr>
                                    <td align="center" style="padding:16px 18px">
                                        <p style="margin:0;font-size:11px;letter-spacing:.09em;
                                                  text-transform:uppercase;color:{{ $sudahLunas ? '#059669' : '#64748b' }}">
                                            {{ $sudahLunas ? 'Sudah lunas' : 'Yang perlu ditransfer' }}
                                        </p>
                                        <p style="margin:4px 0 0;font-size:27px;font-weight:800;
                                                  color:{{ $sudahLunas ? '#047857' : '#0f172a' }}">
                                            Rp {{ number_format($total, 0, ',', '.') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px 0;font-size:14px;line-height:1.65;color:#334155">
                            @if ($sudahLunas)
                                <p style="margin:0">
                                    Pembayaran Anda sudah kami terima. Tidak ada lagi yang perlu
                                    Anda lakukan — sampai jumpa di acaranya.
                                </p>
                            @elseif ($caraBayar === 'tunai')
                                <p style="margin:0">
                                    Pembayarannya diserahkan langsung ke panitia saat Anda datang.
                                    Simpan nomor pendaftaran di atas untuk ditunjukkan.
                                </p>
                            {{-- Hanya kalau nominalnya memang berakhir
                                 dengan kodenya; baris lama dari sebelum
                                 kodenya dimasukkan ke total berakhir 000. --}}
                            @elseif ($kodeMasuk)
                                {{-- Alasannya disebut. Tanpa itu, angka ganjil di ujung nominal
                                     terbaca seperti salah hitung, dan sebagian orang
                                     membulatkannya — lalu pembayarannya tidak bisa dicocokkan. --}}
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                    style="border-left:3px solid #ea7a2c;background:#fff7ed;border-radius:0 10px 10px 0">
                                    <tr>
                                        <td style="padding:13px 15px;font-size:13px;line-height:1.6;color:#9a3412">
                                            Mohon transfer <strong>persis sampai rupiah terakhirnya</strong>.
                                            Nominalnya sudah dilebihkan
                                            <strong>Rp {{ number_format($kodeUnik, 0, ',', '.') }}</strong>
                                            sebagai penanda pendaftaran Anda — bukan kelebihan bayar.
                                            Itulah yang membuat pembayaran Anda bisa kami kenali,
                                            jadi mohon jangan dibulatkan.
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <p style="margin:0">
                                    Pembayarannya lewat transfer ke rekening kami.
                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:22px 28px 26px;font-size:12px;line-height:1.6;color:#94a3b8">
                            Email ini dikirim otomatis karena pendaftaran Anda dicatat oleh panitia.<br>
                            Kalau Anda merasa tidak mendaftar, cukup balas email ini.
                        </td>
                    </tr>
                </table>

                <p style="margin:16px 0 0;font-size:12px;color:#94a3b8">
                    {{ $appName }}
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
