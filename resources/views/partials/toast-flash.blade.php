{{--
  Mengubah pesan kilat sesi jadi toast SweetAlert2.

  Dipakai dengan @include('partials.toast-flash') di layar mana pun. Tujuannya
  supaya pemberitahuan di seluruh aplikasi berbentuk sama — bukan kotak alert
  Bootstrap yang menggeser tata letak dan harus ditutup sendiri.

  misToast() didefinisikan di public/assets/js/mis-ui.js dan sudah termuat
  lewat layout, jadi di sini cukup memanggilnya.
--}}
@php
    /*
     * Kunci sesi yang dianggap kabar baik / buruk / sekadar info. Nama-nama
     * lamanya bermacam-macam karena ditulis di waktu yang berbeda; didaftar
     * apa adanya supaya layar yang sudah ada tidak perlu diubah dulu.
     */
    $petaToast = [
        'berhasil' => ['success', 'statusdataprofil', 'statusverifikasiemail', 'status'],
        'gagal' => ['error', 'erroremailterpakai', 'errorsandiemail'],
        'info' => ['info', 'warning'],
    ];

    $pesanToast = [];

    foreach ($petaToast as $jenis => $kunci) {
        foreach ($kunci as $k) {
            if (session()->has($k) && is_string(session($k)) && trim(session($k)) !== '') {
                $pesanToast[] = ['jenis' => $jenis, 'pesan' => session($k)];
            }
        }
    }

    // Galat validasi ikut jadi toast supaya orang tidak perlu mencari-cari
    // tulisan merah kecil di tengah formulir yang panjang.
    if ($errors->any()) {
        $pesanToast[] = ['jenis' => 'gagal', 'pesan' => $errors->first()];
    }
@endphp

@if (! empty($pesanToast))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @foreach ($pesanToast as $t)
                    window.misToast(@json($t['jenis']), @json($t['pesan']));
                @endforeach
            });
        </script>
    @endpush
@endif
