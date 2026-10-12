{{--
    Nomor yang bisa dihubungi, satu sumber untuk DUA tempat.

    Di tabel lebar ia duduk di kolom Pendaftar, di bawah namanya. Di modus
    kartu ia pindah ke kaki kartu, sebaris dengan lencana keadaan dan rapat
    ke kanan — di sana ia tepat di atas tombol Follow up yang memakainya,
    dan tidak lagi menambah baris ketiga pada blok nama.

    Di-include dua kali dengan $tempat yang berbeda, bukan disalin: dua
    salinan yang menyimpang satu sama lain adalah persoalan yang hanya
    ketahuan setelah salah satunya sudah lama salah. Yang tidak berlaku
    pada lebar saat itu di-display:none, jadi pembaca layar pun hanya
    menemukan satu.
--}}
<div class="pdl-kontak pdl-kontak-{{ $tempat }}">
    @if ($wa)
        <a href="{{ $tautanWa }}" target="_blank" rel="noopener"
            title="Hubungi {{ $b->nama_orang }} lewat WhatsApp — pesannya sudah terisi, masih bisa disunting">
            <i class="fab fa-whatsapp mis-ikon-hijau" aria-hidden="true"></i> {{ $b->telp }}
        </a>
    @elseif ($b->telp)
        {{-- Ditandai, bukan dijadikan tautan yang menuntun ke halaman galat
             WhatsApp. Nomor yang kurang dari sembilan angka bukan nomor
             telepon melainkan sisa isian. --}}
        <span title="Nomor ini tidak bisa dipakai menghubungi lewat WhatsApp.">
            <i class="fas fa-phone-slash mis-ikon-kuning" aria-hidden="true"></i> {{ $b->telp }}
        </span>
    @else
        <span class="pdl-kontak-kosong">nomor belum diisi</span>
    @endif
</div>
