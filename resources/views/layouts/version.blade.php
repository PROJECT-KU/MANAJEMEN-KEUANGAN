@php
use Jenssegers\Agent\Agent;
$agent = new Agent();
@endphp

@if ($agent->isMobile())
<style>
  :root {
    /* Glossy Theme Config */
    --nav-bg: rgba(255, 255, 255, 0.75);
    --primary-grad: linear-gradient(135deg, #ff3131 0%, #ff914d 100%);
    --glossy-border: rgba(255, 255, 255, 0.6);
    --icon-default: #94a3b8;
    --text-default: #64748b;
    --active-shadow: rgba(255, 49, 49, 0.3);
  }

  .mobile-bottom-nav {
    position: fixed;
    bottom: 20px;
    left: 20px;
    right: 20px;
    height: 75px;
    background: var(--nav-bg);
    /* High-end Glass Effect */
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-radius: 30px;
    border: 1px solid var(--glossy-border);
    display: flex;
    justify-content: space-around;
    align-items: center;
    z-index: 2000;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1),
      inset 0 1px 1px rgba(255, 255, 255, 0.8);
  }

  .mobile-bottom-nav a {
    text-decoration: none !important;
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;
    -webkit-tap-highlight-color: transparent;
  }

  /* Icon Styling */
  .mobile-bottom-nav a i {
    font-size: 20px;
    margin-bottom: 4px;
    color: var(--icon-default);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }

  .mobile-bottom-nav a span {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-default);
    transition: all 0.3s ease;
    letter-spacing: 0.2px;
  }

  /* Active State - Glossy Glow */
  .mobile-bottom-nav a.active:not(:has(.gaji-circle)) i {
    transform: translateY(-3px) scale(1.1);
    background: var(--primary-grad);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 2px 4px var(--active-shadow));
  }

  .mobile-bottom-nav a.active:not(:has(.gaji-circle)) span {
    color: #ff3131;
    font-weight: 800;
  }

  /* Central Floating Button (Glossy Circle) */
  .gaji-circle {
    position: absolute;
    top: -48px;
    width: 68px;
    height: 68px;
    background: linear-gradient(145deg, #ffffff, #f0f0f0);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    box-shadow: 0 10px 25px rgba(255, 145, 77, 0.35),
      inset 0 -3px 6px rgba(0, 0, 0, 0.03);
    border: 5px solid var(--nav-bg);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }

  /* Glossy Highlight on Circle */
  .gaji-circle::after {
    content: "";
    position: absolute;
    top: 5px;
    left: 15%;
    width: 70%;
    height: 30%;
    background: linear-gradient(to bottom, rgba(255, 255, 255, 0.9), transparent);
    border-radius: 50% 50% 40% 40%;
    z-index: 1;
  }

  .gaji-circle i {
    color: #ff914d !important;
    font-size: 22px !important;
    z-index: 2;
  }

  .gaji-circle span {
    font-size: 9px !important;
    color: #ff914d !important;
    font-weight: 800;
    z-index: 2;
    text-transform: uppercase;
  }

  /* Active Circle Styling */
  .mobile-bottom-nav a.active .gaji-circle {
    background: var(--primary-grad);
    transform: scale(1.1) translateY(-8px);
    box-shadow: 0 15px 30px rgba(255, 49, 49, 0.4);
    border-color: #ffffff;
  }

  .mobile-bottom-nav a.active .gaji-circle i,
  .mobile-bottom-nav a.active .gaji-circle span {
    color: white !important;
  }

  body {
    padding-bottom: 110px;
    background-color: #f8fafc;
  }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

{{-- ================= MOBILE MENU ================== --}}
@if (Auth::user()->level === 'user')
<div class="mobile-bottom-nav">
  <a href="{{ route('account.dashboard.index') }}" class="{{ Request::routeIs('account.dashboard.index') ? 'active' : '' }}">
    <i class="fa-solid fa-house"></i><span>Dashboard</span>
  </a>

  <a href="{{ route('home') }}" class="{{ Request::routeIs('home') ? 'active' : '' }}">
    <i class="fa-solid fa-house"></i><span>Beranda</span>
  </a>

  <a href="{{ route('account.gaji.index') }}" class="{{ Request::routeIs('account.gaji.index') ? 'active' : '' }}">
    <div class="gaji-circle">
      <i class="fa-solid fa-clock-rotate-left"></i><span>Riwayat</span>
    </div>
  </a>

  <a href="{{ route('account.todolist.index') }}" class="{{ Request::routeIs('account.todolist.index') ? 'active' : '' }}">
    <i class="fa-solid fa-list-check"></i><span>To Do</span>
  </a>

  <a href="{{ route('account.profil.show', ['id' => Auth::user()->id]) }}" class="{{ Request::routeIs('account.profil.show') ? 'active' : '' }}">
    <i class="fa-solid fa-user-gear"></i><span>Profil</span>
  </a>
</div>

@elseif(Auth::user()->level !== 'user')
<div class="mobile-bottom-nav">
  <a href="{{ route('account.dashboard.index') }}" class="{{ Request::routeIs('account.dashboard.index') ? 'active' : '' }}">
    <i class="fa-solid fa-house"></i><span>Dashboard</span>
  </a>

  <a href="{{ route('account.presensi.index') }}" class="{{ Request::routeIs('account.presensi.index') ? 'active' : '' }}">
    <i class="fa-solid fa-fingerprint"></i><span>Presensi</span>
  </a>

  <a href="{{ route('account.gaji.index') }}" class="{{ Request::routeIs('account.gaji.index') ? 'active' : '' }}">
    <div class="gaji-circle">
      <i class="fa-solid fa-wallet"></i><span>Gaji</span>
    </div>
  </a>

  <a href="{{ route('account.todolist.index') }}" class="{{ Request::routeIs('account.todolist.index') ? 'active' : '' }}">
    <i class="fa-solid fa-rectangle-list"></i><span>To Do</span>
  </a>

  <a href="{{ route('account.profil.show', ['id' => Auth::user()->id]) }}" class="{{ Request::routeIs('account.profil.show') ? 'active' : '' }}">
    <i class="fa-solid fa-circle-user"></i><span>Profil</span>
  </a>
</div>
@endif

@else
{{-- ================= DESKTOP FOOTER ================== --}}
<style>
  .main-footer {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    position: fixed;
    bottom: 0;
    width: 100%;
    height: 45px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 25px;
    font-size: 12px;
    z-index: 1000;
    color: #64748b;
  }

  .main-footer strong {
    color: #ff914d;
  }

  .main-content {
    padding-bottom: 70px;
  }
</style>

<?php $version = "30.5.26"; ?>
<footer class="main-footer" id="PwaFooter">
  <div class="footer-left">
    © <strong>Rumah Scopus Foundation</strong> {{ date("Y") }}
  </div>
  <div class="footer-right">
    <span class="badge badge-light" style="font-size: 10px;">v{{ $version }}</span>
  </div>
</footer>
@endif