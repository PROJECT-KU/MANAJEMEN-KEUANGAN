{{--
  Kepala kolom yang bisa diurutkan — dipakai bersama semua layar daftar.

  Berupa tautan, bukan tombol berskrip: ia tetap bekerja tanpa JavaScript,
  bisa dibuka di tab baru, dan urutannya ikut tersimpan di alamat halaman —
  jadi tautan yang disalin orang membawa urutan yang sama.

  Yang harus dikirim pemanggilnya:
    $rute   nama rute daftarnya
    $kolom  nama kolom seperti yang dikenal peladen
    $label  tulisan di kepala kolom
    $urut   kolom yang sedang aktif
    $arah   'asc' atau 'desc'
    $bawa   saringan yang ikut dibawa (larik asosiatif), boleh kosong
--}}
@php
    $aktif = ($urut ?? '') === $kolom;
    // Menekan kolom yang sedang aktif membalik arahnya; kolom lain selalu
    // mulai dari menaik, karena itu yang diharapkan saat berganti kolom.
    $arahBaru = $aktif && ($arah ?? 'desc') === 'asc' ? 'turun' : 'naik';
    $ikon = ! $aktif ? 'fa-sort' : (($arah ?? 'desc') === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
@endphp
<a class="mis-urut {{ $aktif ? 'aktif' : '' }}"
    href="{{ route($rute, array_merge($bawa ?? [], ['urut' => $kolom, 'arah' => $arahBaru])) }}"
    title="Urutkan menurut {{ strtolower($label) }}">
    {{ $label }}
    <i class="fas {{ $ikon }}" aria-hidden="true"></i>
</a>
