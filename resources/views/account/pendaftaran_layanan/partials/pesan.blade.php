{{--
  Pemberitahuan singkat sesudah tindakan.

  Lewat misToast(), bukan alert Bootstrap: pembungkus bersama itu yang
  menjaga rupanya seragam di seluruh MIS. Dirender sebagai skrip, jadi
  pesannya dilewatkan @json supaya tanda kutip dan karakter < di dalam nama
  orang tidak pernah memutus skripnya.
--}}
@if (session('sukses') || session('error') || session('info') || $errors->any())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('sukses'))
                window.misToast('berhasil', @json(session('sukses')));
            @endif
            @if (session('error'))
                window.misToast('gagal', @json(session('error')));
            @endif
            @if (session('info'))
                window.misToast('info', @json(session('info')));
            @endif
            @if ($errors->any())
                window.misToast('gagal', @json($errors->first()));
            @endif
        });
    </script>
    @endpush
@endif
