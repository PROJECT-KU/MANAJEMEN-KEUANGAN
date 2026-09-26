@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Jejak Aktivitas Masuk | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>JEJAK AKTIVITAS MASUK</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger"><i class="fas fa-user-lock"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Gagal 24 Jam Terakhir</h4></div>
                            <div class="card-body">{{ $ringkasan['gagal_24_jam'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success"><i class="fas fa-sign-in-alt"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Berhasil 24 Jam Terakhir</h4></div>
                            <div class="card-body">{{ $ringkasan['berhasil_24_jam'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('account.aktivitas-masuk.index') }}" method="GET" class="form-inline">
                        <input type="text" name="q" value="{{ $cari }}" class="form-control mr-2 mb-2"
                            placeholder="Cari username, email, atau IP">
                        <select name="status" class="form-control mr-2 mb-2">
                            <option value="">Semua status</option>
                            <option value="berhasil" {{ $status === 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                            <option value="gagal" {{ $status === 'gagal' ? 'selected' : '' }}>Gagal</option>
                        </select>
                        <label class="mr-2 mb-2">Dari</label>
                        <input type="date" name="dari" value="{{ $dari }}" class="form-control mr-2 mb-2">
                        <label class="mr-2 mb-2">Sampai</label>
                        <input type="date" name="sampai" value="{{ $sampai }}" class="form-control mr-2 mb-2">
                        <button type="submit" class="btn btn-primary mb-2 mr-2">Tampilkan</button>
                        <a href="{{ route('account.aktivitas-masuk.index') }}" class="btn btn-secondary mb-2 mr-2">Reset</a>
                        <a href="{{ route('account.aktivitas-masuk.ekspor', request()->only('q', 'status', 'dari', 'sampai')) }}"
                            class="btn btn-success mb-2">
                            <i class="fas fa-file-csv"></i> Unduh CSV
                        </a>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>DAFTAR PERCOBAAN MASUK</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Identitas</th>
                                    <th>Akun</th>
                                    <th>Status</th>
                                    <th>Alasan</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($aktivitas as $baris)
                                    <tr>
                                        <td>{{ optional($baris->created_at)->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ $baris->identitas }}</td>
                                        <td>{{ optional($baris->user)->full_name ?? '-' }}</td>
                                        <td>
                                            @if ($baris->berhasil)
                                                <span class="badge badge-success">Berhasil</span>
                                            @else
                                                <span class="badge badge-danger">Gagal</span>
                                            @endif
                                        </td>
                                        <td>{{ $baris->alasan ?? '-' }}</td>
                                        <td>{{ $baris->ip }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada catatan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="float-right">
                        {{ $aktivitas->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
