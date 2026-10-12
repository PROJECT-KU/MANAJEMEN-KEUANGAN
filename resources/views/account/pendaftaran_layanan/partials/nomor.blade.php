{{--
    Nomor pendaftaran, satu sumber untuk DUA tempat.

    Di tabel lebar ia duduk di kolom Pendaftar, di atas namanya. Di modus
    kartu ia naik ke kepala kartu, rapat ke kanan di seberang nama
    layanannya — di sana ia jadi penanda kartu, bukan keterangan tentang
    orangnya, dan blok nama kehilangan satu baris lagi.

    Yang tidak berlaku pada lebar saat itu di-display:none, jadi pembaca
    layar hanya menemukan satu.
--}}
<span class="pdl-nomor pdl-nomor-{{ $tempat }}" title="{{ $b->nomor ?: 'Tanpa nomor' }}">{{ $b->nomor ?: 'Tanpa nomor' }}</span>
