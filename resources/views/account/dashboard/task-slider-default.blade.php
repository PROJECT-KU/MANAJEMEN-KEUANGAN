@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

$user = Auth::user();
$userId = $user->id;

// Ambil tugas sesuai level
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

<div class="d-flex flex-wrap justify-content-center mt-3">
    {{-- NOTIFIKASI TUGAS --}}
    @if ($taskCount > 0)
    <div class="alert alert-info m-2 d-flex flex-column justify-content-center align-items-center text-center"
        style="max-width: 400px; min-height: 150px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <b style="font-size: 18px;">Notifikasi Tugas</b>
        <hr class="w-100">
        <p style="font-size: 14px;">Anda memiliki tugas yang harus dikerjakan.<br>Silakan lihat detail tugas di menu To Do List.</p>
    </div>
    @endif

    {{-- AKUN NONAKTIF --}}
    @if ($user->status == "nonactive")
    <div class="alert alert-danger m-2 d-flex flex-column justify-content-center align-items-center text-center"
        style="max-width: 400px; min-height: 150px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <b style="font-size: 18px;">Akun Anda Dinonaktifkan</b>
        <hr class="w-100">
        <p style="font-size: 14px;">Silakan hubungi admin untuk mengaktifkan kembali akun Anda.</p>
    </div>
    @endif

    {{-- DATA DIRI KOSONG --}}
    @if (
    !$user->company || !$user->telp || !$user->nik || !$user->norek ||
    !$user->bank || !$user->gambar || !$user->jobdesk
    )
    <div class="alert alert-warning m-2 d-flex flex-column justify-content-center align-items-center text-center"
        style="max-width: 400px; min-height: 150px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <b style="font-size: 18px;">Data Diri Belum Lengkap</b>
        <hr class="w-100">
        <p style="font-size: 14px;">Beberapa data diri Anda masih kosong.<br>Silakan lengkapi terlebih dahulu.</p>
    </div>
    @endif
</div>