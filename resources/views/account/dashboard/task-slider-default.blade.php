@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

$user = Auth::user();
$userId = $user->id;

if ($user->level === 'manager') {
$tasks = DB::table('todolist')->where('status', 'Assign Task')->get();
} else {
$tasks = DB::table('todolist')
->where('status', 'Assign Task')
->where(function ($query) use ($userId) {
$query->where('user_id', $userId)->orWhere('user_id_kedua', $userId);
})->get();
}
$taskCount = $tasks->count();
@endphp

<!-- Library Pendukung -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .bj-neo-wrapper {
        font-family: 'Outfit', sans-serif;
        padding: 40px 20px;
        /* Jarak atas-bawah pembungkus diperlebar */
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        /* Menambahkan jarak horizontal dan vertikal antar card */
        gap: 50px 40px;
    }

    .bj-neo-card {
        position: relative;
        /* Mengatur agar card tidak terlalu berdempetan di layar kecil */
        flex: 1 1 340px;
        max-width: 380px;
        min-height: 220px;
        padding: 30px;
        border-radius: 30px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        background: rgba(15, 15, 15, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        z-index: 1;
        overflow: hidden;
    }

    /* Ikon Background (Watermark) */
    .bj-neo-bg-icon {
        position: absolute;
        right: -15px;
        bottom: -15px;
        font-size: 8rem;
        transform: rotate(-15deg);
        pointer-events: none;
        z-index: -1;
        transition: all 0.5s ease;
    }

    .bj-neo-card:hover .bj-neo-bg-icon {
        transform: rotate(0deg) scale(1.1);
        color: rgba(255, 255, 255, 0.1);
    }

    .bj-neo-card:hover {
        /* Naik sedikit saat hover tanpa mengganggu posisi card lain karena gap */
        transform: translateY(-15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Glow Orbs */
    .bj-neo-card::before {
        content: "";
        position: absolute;
        width: 140px;
        height: 140px;
        top: -40px;
        right: -40px;
        border-radius: 50%;
        filter: blur(50px);
        opacity: 0.4;
        z-index: -2;
    }

    .bj-neo-task::before {
        background: #00f2fe;
    }

    .bj-neo-warning::before {
        background: #f59e0b;
    }

    .bj-neo-danger::before {
        background: #f43f5e;
    }

    .bj-neo-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        background: linear-gradient(135deg, #fff 30%, rgba(255, 255, 255, 0.3) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.3));
    }

    .bj-neo-title {
        font-weight: 800;
        font-size: 1.4rem;
        color: #ffffff;
        margin-bottom: 10px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }

    .bj-neo-desc {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.5;
        margin-bottom: 25px;
    }

    .bj-neo-btn {
        background: #ffffff;
        color: #000000 !important;
        padding: 12px 24px;
        border-radius: 15px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none !important;
        width: fit-content;
        transition: 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .bj-neo-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.4);
    }

    .bj-neo-badge {
        position: absolute;
        top: 25px;
        right: 25px;
        background: rgba(255, 255, 255, 0.1);
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 0.7rem;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.1);
        font-weight: 700;
    }
</style>

<div class="bj-neo-wrapper mt-4">

    {{-- NOTIFIKASI TUGAS --}}
    @if ($taskCount > 0)
    <div class="bj-neo-card bj-neo-task">
        <i class="fas fa-list-check bj-neo-bg-icon"></i>
        <div class="bj-neo-badge">{{ $taskCount }} NEW ASSIGNMENTS</div>
        <i class="fas fa-bolt bj-neo-icon"></i>
        <div>
            <h5 class="bj-neo-title">Tugas Menanti!</h5>
            <p class="bj-neo-desc">Selesaikan tanggung jawab Anda hari ini agar produktivitas tetap terjaga.</p>
        </div>
        <a href="{{ route('account.todolist.index') }}" class="bj-neo-btn">Cek To Do List</a>
    </div>
    @endif

    {{-- DATA DIRI KOSONG --}}
    @if (!$user->company || !$user->telp || !$user->nik || !$user->norek || !$user->bank || !$user->gambar || !$user->jobdesk)
    <div class="bj-neo-card bj-neo-warning">
        <i class="fas fa-user-gear bj-neo-bg-icon"></i>
        <div class="bj-neo-badge">UPDATE REQUIRED</div>
        <i class="fas fa-user-shield bj-neo-icon"></i>
        <div>
            <h5 class="bj-neo-title">Lengkapi Profil</h5>
            <p class="bj-neo-desc">Data diri Anda belum lengkap. Perbarui informasi profil untuk akses penuh sistem.</p>
        </div>
        <a href="{{ route('account.profil.show', ['id' => Auth::user()->id]) }}" class="bj-neo-btn">Update Sekarang</a>
    </div>
    @endif

    {{-- AKUN NONAKTIF --}}
    @if ($user->status == "nonactive")
    <div class="bj-neo-card bj-neo-danger">
        <i class="fas fa-ban bj-neo-bg-icon"></i>
        <div class="bj-neo-badge">LOCKED</div>
        <i class="fas fa-exclamation-triangle bj-neo-icon"></i>
        <div>
            <h5 class="bj-neo-title">Akun Nonaktif</h5>
            <p class="bj-neo-desc">Akses Anda saat ini dibatasi. Silakan hubungi admin untuk aktivasi kembali.</p>
        </div>
        <a href="https://wa.me/6288983567819" class="bj-neo-btn">Hubungi Admin</a>
    </div>
    @endif

</div>