@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

$user = Auth::user();
$userId = $user->id;
$slides = [];

// TO DO LIST
if ($user->level === 'manager') {
$tasks = DB::table('todolist')->where('status', 'Assign Task')->get();
} else {
$tasks = DB::table('todolist')
->where('status', 'Assign Task')
->where(function ($query) use ($userId) {
$query->where('user_id', $userId)
->orWhere('user_id_kedua', $userId);
})
->get();
}

if ($tasks->count() > 0) {
$slides[] = [
'type' => 'task',
'html' => '
<div class="d-flex align-items-center justify-content-center" style="min-height: 250px;">
    <div class="alert alert-info mx-auto mb-3" role="alert" style="width: 100%; max-width: 400px; text-align: center; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <b style="font-size: 20px;">Notifikasi Tugas</b>
        <hr>
        <p style="font-size: 15px;">Anda memiliki tugas yang perlu segera diselesaikan.<br>Silakan lihat detail tugas di menu To Do List.</p>
    </div>
</div>'
];
}

// NONACTIVE ACCOUNT
if ($user->status === 'nonactive') {
$slides[] = [
'type' => 'nonactive',
'html' => '
<div class="d-flex align-items-center justify-content-center" style="min-height: 250px;">
    <div class="alert alert-danger mx-auto mb-3" role="alert" style="width: 100%; max-width: 400px; text-align: center; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <b style="font-size: 20px;">Akun Anda Telah Dinonaktifkan</b>
        <hr>
        <p style="font-size: 15px;">Silakan hubungi admin untuk mengaktifkan kembali akun Anda.</p>
    </div>
</div>'
];
}

// DATA DIRI KOSONG
if (
!$user->company || !$user->telp || !$user->nik || !$user->norek ||
!$user->bank || !$user->gambar || !$user->jobdesk
) {
$slides[] = [
'type' => 'datadiri',
'html' => '
<div class="d-flex align-items-center justify-content-center" style="min-height: 250px;">
    <div class="alert alert-warning mx-auto mb-3" role="alert" style="width: 100%; max-width: 400px; text-align: center; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <b style="font-size: 20px;">Data Diri Belum Lengkap</b>
        <hr>
        <p style="font-size: 15px;">Beberapa informasi pribadi Anda belum lengkap.<br>Silakan lengkapi data diri Anda terlebih dahulu.</p>
    </div>
</div>'
];
}

// MAINTENANCE
if (isset($maintenances) && !$maintenances->isEmpty()) {
foreach ($maintenances as $maintenance) {
if ($maintenance->status === 'aktif' && now() <= Carbon::parse($maintenance->end_date)->endOfDay()) {
    $start = Carbon::parse($maintenance->start_date)->isoFormat('D MMMM YYYY HH:mm');
    $end = Carbon::parse($maintenance->end_date)->isoFormat('D MMMM YYYY HH:mm');
    $slides[] = [
    'type' => 'maintenance',
    'html' => '
    <div class="d-flex align-items-center justify-content-center" style="min-height: 250px;">
        <div class="alert alert-danger mx-auto mb-3" role="alert" style="width: 100%; max-width: 400px; text-align: center; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <b style="font-size: 22px; text-transform: uppercase;">' . e($maintenance->title) . '</b>
            <hr>
            <p style="font-size: 16px;">' . e($maintenance->note) . '</p>
            <p style="font-size: 14px;">Dari tanggal ' . $start . ' - ' . $end . '</p>
        </div>
    </div>'
    ];
    break;
    }
    }
    }
    @endphp

    @if(count($slides) > 0)
    <div id="taskSlider" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            @foreach($slides as $index => $slide)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                {!! $slide['html'] !!}
            </div>
            @endforeach
        </div>

        @if(count($slides) > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#taskSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sebelumnya</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#taskSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Berikutnya</span>
        </button>
        <div class="carousel-indicators">
            @foreach($slides as $index => $slide)
            <button type="button" data-bs-target="#taskSlider" data-bs-slide-to="{{ $index }}"
                class="{{ $index === 0 ? 'active' : '' }}"
                aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
        @endif
    </div>
    @endif