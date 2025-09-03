@php
use Jenssegers\Agent\Agent;
$agent = new Agent();
@endphp

@if ($agent->isMobile())
<style>
  .mobile-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 80px;
    background-color: #ffffff;
    border-top: 3px solid #ff914d;
    display: flex;
    justify-content: space-around;
    align-items: center;
    z-index: 1000;
  }

  .mobile-bottom-nav a {
    text-decoration: none;
    font-size: 12px;
    text-align: center;
    flex: 1;
    position: relative;
    margin-bottom: 20px;
  }

  .mobile-bottom-nav a i {
    font-size: 20px;
    display: block;
    margin-bottom: 5px;
    color: #6495ED;
    transition: color 0.3s ease;
  }

  .mobile-bottom-nav a span {
    color: #333;
    transition: color 0.3s ease;
  }

  /* === AKTIF BIASA (bukan Gaji) === */
  .mobile-bottom-nav a.active:not(:has(.gaji-circle)) i,
  .mobile-bottom-nav a.active:not(:has(.gaji-circle)) span {
    background: linear-gradient(to right, #ff3131, #ff914d);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }


  /* === Gaji Circle Style === */
  .gaji-circle {
    position: absolute;
    top: -55px;
    left: 50%;
    transform: translateX(-50%);
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background-color: #ffffff;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    font-size: 11px;
    text-align: center;
    z-index: 1;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  }

  .gaji-circle::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 50%;
    padding: 3px;
    background: conic-gradient(from 285deg, #ff914d 0deg 150deg, transparent 60deg 360deg);
    -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3px), black calc(100% - 3px));
    mask: radial-gradient(farthest-side, transparent calc(100% - 3px), black calc(100% - 3px));
    z-index: 0;
  }

  .gaji-circle i,
  .gaji-circle span {
    position: relative;
    z-index: 1;
    color: #ff914d;
    display: block;
    text-align: center;
    transition: color 0.3s ease;
  }

  .gaji-circle i {
    font-size: 22px;
    margin-bottom: 4px;
  }

  /* === Gaji Aktif === */
  .mobile-bottom-nav a.active .gaji-circle {
    background: linear-gradient(to right, #ff3131, #ff914d);
    color: white !important;
  }

  .mobile-bottom-nav a.active .gaji-circle i,
  .mobile-bottom-nav a.active .gaji-circle span {
    color: white !important;
  }

  body {
    padding-bottom: 90px;
  }
</style>

<!-- Font Awesome CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

<!-- Mobile Footer -->
<div class="mobile-bottom-nav">
  <a href="{{ route('account.dashboard.index') }}" class="{{ Request::routeIs('account.dashboard.index') ? 'active' : '' }}">
    <i class="fas fa-home"></i>
    <span>Dashboard</span>
  </a>
  <a href="{{ route('account.presensi.index') }}" class="{{ Request::routeIs('account.presensi.index') ? 'active' : '' }}">
    <i class="fas fa-user-check"></i>
    <span>Presensi</span>
  </a>
  <a href="{{ route('account.gaji.index') }}" class="{{ Request::routeIs('account.gaji.index') ? 'active' : '' }}">
    <div class="gaji-circle">
      <i class="fas fa-wallet"></i>
      <span>Gaji</span>
    </div>
  </a>
  <a href="{{ route('account.todolist.index') }}" class="{{ Request::routeIs('account.todolist.index') ? 'active' : '' }}">
    <i class="fas fa-list-check"></i>
    <span>To Do</span>
  </a>
  <a href="{{ route('account.profil.show', ['id' => Auth::user()->id]) }}" class="{{ Request::routeIs('account.profil.show') ? 'active' : '' }}">
    <i class="fas fa-user"></i>
    <span>Profil</span>
  </a>
</div>

@else
<!-- Desktop Footer -->
<style>
  .main-footer {
    border-top: 4px solid #ff914d;
    background-color: rgba(255, 255, 255, 0.95);
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 50px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 500;
  }

  .main-content {
    padding-bottom: 80px;
    overflow-x: hidden;
  }

  .sidebar {
    position: relative;
    z-index: 1;
  }
</style>

<?php $version = "5.1.12"; ?>
<footer class="main-footer" id="PwaFooter">
  <div class="footer-left">
    © <strong>Rumah Scopus Foundation</strong> {{ date("Y") }}
  </div>
  <div class="footer-right">
    Version {{ $version }}
  </div>
</footer>
@endif