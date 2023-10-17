@extends('layouts.account')

@section('title')
Tambah Pengguna | MANAGEMENT
@stop

@section('content')
<div class="container">
    <h2>{{ $maintenance->title }}</h2>
    <p><strong>Start Date:</strong> {{ $maintenance->start_date }}</p>
    <p><strong>End Date:</strong> {{ $maintenance->end_date }}</p>

    <div class="mt-3">
        <img src="{{ asset($maintenance->gambar) }}" alt="Maintenance Image" width="200" height="200">
    </div>

    <div class="mt-3">
        <p><strong>Note:</strong></p>
        <p>{{ $maintenance->note }}</p>
    </div>
</div>
@endsection