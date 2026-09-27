@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Dashboard | MIS
@stop

@section('content')
<div class="main-content" style="padding-top: 110px; background-color: #f4f7ff; min-height: 100vh;">
    <section class="section">
        <div class="section-body">
            {{-- Satu tampilan untuk semua perangkat. Pemisahan ponsel/desktop
                 lewat Jenssegers\Agent dihapus: tablet ikut kena tampilan
                 desktop, dan dua berkas tampilannya lambat laun berbeda isi. --}}
            <livewire:akun.dasbor />
        </div>
    </section>
</div>
@endsection
