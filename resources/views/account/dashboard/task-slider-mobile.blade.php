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
    /* Keyframes untuk Animasi Muncul */
    @keyframes bjFadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bj-grid-container {
        font-family: 'Outfit', sans-serif;
        padding: 15px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .bj-grid-wrapper {
        display: flex;
        gap: 15px;
        overflow-x: auto;
        padding-bottom: 20px;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .bj-grid-wrapper::-webkit-scrollbar {
        display: none;
    }

    /* Base Card Style dengan Transisi */
    .bj-neo-card {
        position: relative;
        flex: 0 0 85%;
        height: 200px;
        padding: 20px;
        border-radius: 25px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #1a1a1a;
        /* Warna solid agar tidak berkabut */
        border: 1px solid rgba(255, 255, 255, 0.08);
        scroll-snap-align: center;
        overflow: hidden;

        /* Animasi Masuk */
        animation: bjFadeInUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both;

        /* Transisi Hover */
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    /* Staggered Delay untuk tiap card */
    .bj-neo-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .bj-neo-card:nth-child(3) {
        animation-delay: 0.2s;
    }

    /* Hover effect yang modern */
    .bj-neo-card:hover {
        transform: translateY(-8px);
        background: #222222;
        border-color: rgba(255, 255, 255, 0.2);
    }

    @media (min-width: 768px) {
        .bj-grid-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            overflow-x: visible;
            gap: 20px;
        }

        .bj-neo-card {
            flex: none;
            height: 240px;
        }
    }

    .bj-neo-icon {
        font-size: 2rem;
        transition: transform 0.4s ease;
    }

    /* Animasi ikon saat card di-hover */
    .bj-neo-card:hover .bj-neo-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .bj-neo-title {
        font-weight: 800;
        color: #ffffff;
        margin-top: 10px;
        font-size: 1.2rem;
    }

    .bj-neo-desc {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.85rem;
        line-height: 1.3;
    }

    .bj-neo-btn {
        background: #ffffff;
        color: #000 !important;
        padding: 10px 20px;
        border-radius: 14px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        width: fit-content;
        text-decoration: none !important;
        transition: background 0.3s ease;
    }

    .bj-neo-btn:hover {
        background: #e0e0e0;
    }

    .bj-neo-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.05);
        padding: 5px 10px;
        border-radius: 10px;
        font-size: 0.6rem;
        color: #fff;
        font-weight: 700;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .bj-neo-task {
        border-left: 5px solid #00f2fe;
    }

    .bj-neo-warning {
        border-left: 5px solid #f59e0b;
    }

    .bj-neo-danger {
        border-left: 5px solid #f43f5e;
    }

    .bj-neo-bg-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 6rem;
        transform: rotate(-15deg);
        pointer-events: none;
    }
</style>

<div class="bj-grid-container">
    <div class="bj-grid-wrapper">

        @if ($taskCount > 0)
        <div class="bj-neo-card bj-neo-task">
            <i class="fas fa-list-check bj-neo-bg-icon"></i>
            <div class="bj-neo-badge">{{ $taskCount }} NEW ASSIGNMENTS</div>
            <i class="fas fa-bolt bj-neo-icon" style="color: #00f2fe;"></i>
            <div>
                <h5 class="bj-neo-title">Tugas Menanti</h5>
                <p class="bj-neo-desc">Selesaikan tanggung jawab Anda hari ini agar produktivitas tetap terjaga.</p>
            </div>
            <a href="{{ route('account.todolist.index') }}" class="bj-neo-btn">Cek To Do List</a>
        </div>
        @endif

        @php
        // Gunakan 1 sumber data yang pasti akurat
        $cekUser = Auth::user();

        // Validasi super ketat untuk tanggal lahir (cek spasi, null, dan format 0000)
        $tglLahirKosong = empty(trim($cekUser->tanggal_lahir)) || $cekUser->tanggal_lahir === '0000-00-00';
        @endphp

        @if (
        ($cekUser->level !== 'user' && (empty($cekUser->gambar) || empty($cekUser->telp) || empty($cekUser->jobdesk) || $tglLahirKosong || empty($cekUser->norek) || empty($cekUser->bank)))
        ||
        ($cekUser->level === 'user' && (empty($cekUser->gambar) || empty($cekUser->telp) || $tglLahirKosong))
        )
        <div class="bj-neo-card bj-neo-warning">
            <i class="fas fa-user-gear bj-neo-bg-icon"></i>
            <div class="bj-neo-badge">UPDATE REQUIRED</div>
            <i class="fas fa-user-shield bj-neo-icon" style="color: #f59e0b;"></i>
            <div>
                <h5 class="bj-neo-title">Lengkapi Profil</h5>
                <p class="bj-neo-desc">Data diri Anda belum lengkap. Perbarui informasi profil untuk akses penuh sistem.</p>
            </div>
            <a href="{{ route('account.profil.show', ['id' => Auth::user()->id]) }}" class="bj-neo-btn">Update Sekarang</a>
        </div>
        @endif

        @if ($user->status == "nonactive")
        <div class="bj-neo-card bj-neo-danger">
            <i class="fas fa-ban bj-neo-bg-icon"></i>
            <div class="bj-neo-badge">LOCKED</div>
            <i class="fas fa-exclamation-triangle bj-neo-icon" style="color: #f43f5e;"></i>
            <div>
                <h5 class="bj-neo-title">Akun Nonaktif</h5>
                <p class="bj-neo-desc">Akses Anda saat ini dibatasi. Silakan hubungi admin untuk aktivasi kembali.</p>
            </div>
            <a href="https://wa.me/6288983567819" class="bj-neo-btn">Hubungi Admin</a>
        </div>
        @endif

    </div>
</div>