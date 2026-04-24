<style>
    .akses-cepat-container {
        background: #ffffff;
        border-radius: 28px;
        padding: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.03);
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .menu-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none !important;
        padding: 12px 5px;
        border-radius: 20px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .menu-item:hover {
        background: #fdfdfd;
        transform: translateY(-5px);
        border-color: #f1f5f9;
        box-shadow: 0 10px 20px rgba(0,0,0,0.04);
    }

    /* Icon Circle dengan Soft Gradient & Glow */
    .icon-circle {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        position: relative;
        color: white;
        font-size: 22px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        box-shadow: 0 8px 15px rgba(99, 102, 241, 0.2);
        transition: all 0.3s ease;
    }

    .menu-item:hover .icon-circle {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 12px 20px rgba(99, 102, 241, 0.3);
    }

    /* Variasi Warna Ikon Berdasarkan Kategori */
    .bg-finance { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 8px 15px rgba(16, 185, 129, 0.2); }
    .bg-manager { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 8px 15px rgba(245, 158, 11, 0.2); }
    .bg-info { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2); }
    .bg-dark { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); box-shadow: 0 8px 15px rgba(30, 41, 59, 0.2); }

    .badge-notif {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 10px;
        border: 2px solid #fff;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .menu-label {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-align: center;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }

    /* Logic Toggle */
    .menu-item.hidden {
        display: none;
        opacity: 0;
        transform: translateY(10px);
    }

    .toggle-wrapper {
        text-align: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #f1f5f9;
    }

    #toggleBtn {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 8px 20px;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    #toggleBtn:hover {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    @media (max-width: 576px) {
        .icon-circle { width: 48px; height: 48px; font-size: 18px; }
        .menu-label { font-size: 9px; }
        .akses-cepat-container { padding: 15px 10px; }
    }
</style>

<div class="akses-cepat-container">
    <div class="menu-grid" id="menuGrid">
        
        {{-- 1. FITUR UMUM --}}
        @if (in_array(Auth::user()->level, ['manager', 'karyawan']))
            <a href="{{ route('account.PerjalananDinas.index') }}" class="menu-item">
                <div class="icon-circle bg-info"><i class="fas fa-suitcase-rolling"></i></div>
                <span class="menu-label">Perjalanan Dinas</span>
            </a>

            <a href="{{ route('account.clinikscopus.index') }}" class="menu-item">
                <div class="icon-circle" style="background: linear-gradient(135deg, #6d28d9 0%, #db2777 100%);">
                    <i class="fas fa-coffee"></i>
                </div>
                <span class="menu-label">Clinik Scopus</span>
            </a>

           <a href="{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.index') }}" class="menu-item">
    <div class="icon-circle bg-dark">
        <i class="fas fa-file-medical"></i>
        
        @php
            $user = Auth::user();
            $totalNotifPemesanan = 0;

            if ($user->level === 'manager') {
                // Manager melihat semua yang pending
                $totalNotifPemesanan = DB::table('clinikscopus_pemesanan')
                    ->where('status', 'pending')
                    ->count();
            } else {
                // Karyawan/Staff melihat pending berdasarkan trainer_id mereka
                $totalNotifPemesanan = DB::table('clinikscopus_pemesanan')
                    ->where('status', 'pending')
                    ->where('trainer_id', $user->id)
                    ->count();
            }
        @endphp

        @if ($totalNotifPemesanan > 0)
            <span class="badge-notif">{{ $totalNotifPemesanan }}</span>
        @endif
    </div>
    <span class="menu-label">Pem. Clinik Scopus</span>
</a>

            <a href="{{ route('account.cuti.index') }}" class="menu-item">
                <div class="icon-circle" style="background: linear-gradient(135deg, #0ea5e9, #2dd4bf);"><i class="fas fa-calendar-alt"></i></div>
                <span class="menu-label">Cuti</span>
            </a>
        @endif

        {{-- 2. FITUR KHUSUS MANAGER --}}
        @if (Auth::user()->level === 'manager')
            <a href="{{ route('account.company.edit', ['id' => Auth::user()->id]) }}" class="menu-item">
                <div class="icon-circle bg-manager"><i class="fas fa-building"></i></div>
                <span class="menu-label">Company</span>
            </a>
            <a href="{{ route('account.pengguna.index') }}" class="menu-item">
                <div class="icon-circle bg-manager"><i class="fas fa-users-cog"></i></div>
                <span class="menu-label">Data Karyawan</span>
            </a>
            <a href="{{ route('account.presensi.index') }}" class="menu-item">
                <div class="icon-circle bg-manager"><i class="fas fa-user-check"></i></div>
                <span class="menu-label">Presensi</span>
            </a>
            <a href="{{ route('account.refrensi-paper.index') }}" class="menu-item">
                <div class="icon-circle bg-manager"><i class="fas fa-book"></i></div>
                <span class="menu-label">Ref. Paper</span>
            </a>
        @endif

        {{-- --- MENU HIDDEN (Lainnya) --- --}}

        @if (in_array(Auth::user()->level, ['manager', 'karyawan']))
            <a href="{{ route('account.todolist.index') }}" class="menu-item hidden">
                <div class="icon-circle" style="background: #10b981;"><i class="fas fa-clipboard-list"></i></div>
                <span class="menu-label">To Do List</span>
            </a>
            <a href="{{ route('account.categories_debit.index') }}" class="menu-item hidden">
                <div class="icon-circle bg-finance"><i class="fas fa-tags"></i></div>
                <span class="menu-label">Kat. Uang Masuk</span>
            </a>
            <a href="{{ route('account.debit.index') }}" class="menu-item hidden">
                <div class="icon-circle bg-finance"><i class="fas fa-wallet"></i></div>
                <span class="menu-label">Uang Masuk</span>
            </a>
            <a href="{{ route('account.categories_credit.index') }}" class="menu-item hidden">
                <div class="icon-circle bg-finance" style="opacity: 0.8;"><i class="fas fa-tag"></i></div>
                <span class="menu-label">Kat. Uang Keluar</span>
            </a>
            <a href="{{ route('account.credit.index') }}" class="menu-item hidden">
                <div class="icon-circle bg-finance"><i class="fas fa-money-bill-wave"></i></div>
                <span class="menu-label">Uang Keluar</span>
            </a>
        @endif

        @if (Auth::user()->level === 'manager')
            <a href="{{ route('account.customer.index') }}" class="menu-item hidden">
                <div class="icon-circle" style="background: #f97316;"><i class="fas fa-user-tag"></i></div>
                <span class="menu-label">Data Customer</span>
            </a>
            <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.index') }}" class="menu-item hidden">
                <div class="icon-circle" style="background: #f43f5e;"><i class="fas fa-hand-holding-usd"></i></div>
                <span class="menu-label">Biaya Clinik Scopus</span>
            </a>
            <a href="{{ route('account.Clinik-Scopus-Promo.index') }}" class="menu-item hidden">
                <div class="icon-circle" style="background: #ec4899;"><i class="fas fa-percentage"></i></div>
                <span class="menu-label">Pro. Clinik Scopus</span>
            </a>
            <a href="{{ route('account.Artikel.index') }}" class="menu-item hidden">
                <div class="icon-circle bg-dark"><i class="fas fa-file-alt"></i></div>
                <span class="menu-label">Artikel</span>
            </a>
            <a href="{{ route('account.analisisbibliometrik.index') }}" class="menu-item hidden">
                <div class="icon-circle bg-dark"><i class="fas fa-file-signature"></i></div>
                <span class="menu-label">Bibliometrik</span>
            </a>
            <a href="{{ route('account.pendaftaranscopuscamp.index') }}" class="menu-item hidden">
                <div class="icon-circle bg-dark"><i class="fas fa-campground"></i></div>
                <span class="menu-label">Scopus Camp</span>
            </a>
            <a href="{{ route('account.camp.index') }}" class="menu-item hidden">
                <div class="icon-circle bg-dark"><i class="fas fa-file-invoice-dollar"></i></div>
                <span class="menu-label">Laporan Camp</span>
            </a>
        @endif
    </div>

    <div class="toggle-wrapper">
        <button id="toggleBtn" type="button">
            <i class="fas fa-chevron-down"></i> <span id="toggleText">Lainnya</span>
        </button>
    </div>
</div>

<script>
    document.getElementById('toggleBtn').addEventListener('click', function() {
        const hiddenItems = document.querySelectorAll('.menu-item.hidden');
        const text = document.getElementById('toggleText');
        const icon = this.querySelector('i');
        
        let isOpening = text.innerText === 'Lainnya';

        hiddenItems.forEach((item, index) => {
            if (isOpening) {
                item.style.display = 'flex';
                // Animasi cascade effect
                setTimeout(() => { 
                    item.style.opacity = '1'; 
                    item.style.transform = 'translateY(0)';
                }, index * 50);
            } else {
                item.style.opacity = '0';
                item.style.transform = 'translateY(10px)';
                setTimeout(() => { item.style.display = 'none'; }, 300);
            }
        });

        text.innerText = isOpening ? 'Tutup' : 'Lainnya';
        icon.style.transform = isOpening ? 'rotate(180deg)' : 'rotate(0deg)';
        icon.style.transition = '0.3s ease';
    });
</script>