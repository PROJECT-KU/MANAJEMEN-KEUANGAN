@php
use Jenssegers\Agent\Agent;
$agent = new Agent();
@endphp

@if ($agent->isMobile())
{{-- Tampilkan view untuk perangkat mobile --}}
@include('account.dashboard.divacemobile')
@else
{{-- Tampilkan view untuk perangkat non-mobile (laptop/tablet) --}}
@include('account.dashboard.divacedefault')
@endif