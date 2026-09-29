{{--
  Kepala kolom yang bisa diurutkan.

  Berupa tautan, bukan tombol berskrip: ia tetap bekerja tanpa JavaScript,
  bisa dibuka di tab baru, dan urutannya ikut tersimpan di alamat halaman —
  jadi tautan yang disalin orang membawa urutan yang sama.
--}}
@php
    $aktif = ($urut ?? '') === $kolom;
    // Menekan kolom yang sedang aktif membalik arahnya; kolom lain selalu
    // mulai dari menaik, karena itu yang diharapkan saat berganti kolom.
    $arahBaru = $aktif && ($arah ?? 'desc') === 'asc' ? 'turun' : 'naik';
    $ikon = ! $aktif ? 'fa-sort' : (($arah ?? 'desc') === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
@endphp
<a class="pel-urut {{ $aktif ? 'aktif' : '' }}"
    href="{{ route('account.customer.index', array_merge(
        request()->only('cari', 'status', 'verifikasi'),
        ['urut' => $kolom, 'arah' => $arahBaru]
    )) }}"
    title="Urutkan menurut {{ strtolower($label) }}">
    {{ $label }}
    <i class="fas {{ $ikon }}" aria-hidden="true"></i>
</a>
