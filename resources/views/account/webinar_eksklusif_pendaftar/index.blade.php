@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Pendaftar Webinar Eksklusif | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>PENDAFTAR WEBINAR EKSKLUSIF</h1>
        </div>

        <div class="section-body">

            @if (session('sukses'))
                <div class="alert alert-success">{{ session('sukses') }}</div>
            @endif
            @if (session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            {{-- Angka ringkas di muka: yang pertama ditanya panitia adalah
                 "berapa yang belum bayar", bukan daftar barisnya. --}}
            <div class="row">
                @php($kartu = [
                    ['Menunggu bayar', $ringkasan['menunggu'], 'warning', 'fa-clock'],
                    ['Lunas', $ringkasan['lunas'], 'success', 'fa-check-circle'],
                    ['Kedaluwarsa', $ringkasan['kedaluwarsa'], 'danger', 'fa-times-circle'],
                    ['Peserta lunas', $ringkasan['peserta_lunas'], 'primary', 'fa-users'],
                ])

                @foreach ($kartu as [$judul, $angka, $warna, $ikon])
                    <div class="col-6 col-lg-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-{{ $warna }}">
                                <i class="fas {{ $ikon }}"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>{{ $judul }}</h4></div>
                                <div class="card-body">{{ number_format($angka, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> DAFTAR PENDAFTARAN</h4>
                </div>

                <div class="card-body">
                    <form method="GET" class="row mb-4">
                        <div class="col-md-5 mb-2">
                            <input type="search" name="cari" class="form-control"
                                value="{{ request('cari') }}"
                                placeholder="Cari nama, email, nomor WA, atau nomor pendaftaran">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select name="status" class="form-control">
                                <option value="">Semua status</option>
                                @foreach (['pending' => 'Menunggu bayar', 'paid' => 'Lunas', 'expired' => 'Kedaluwarsa', 'cancel' => 'Batal'] as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected(request('status') === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select name="sesi" class="form-control">
                                <option value="">Semua sesi</option>
                                @foreach ($sesi as $s)
                                    <option value="{{ $s->id }}" @selected(request('sesi') === $s->id)>
                                        {{ \Illuminate\Support\Str::limit($s->nama, 40) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 mb-2">
                            <button class="btn btn-primary btn-block"><i class="fas fa-search"></i></button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Pendaftar</th>
                                    <th>Sesi</th>
                                    <th class="text-center">Peserta</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse ($pendaftaran as $p)
                                <tr>
                                    <td>
                                        <code>{{ $p->id_transaksi }}</code>
                                        <div class="text-small text-muted">{{ $p->created_at?->format('d M Y H:i') }}</div>
                                    </td>
                                    <td>
                                        <strong>{{ $p->nama }}</strong>
                                        <div class="text-small text-muted">{{ $p->email }}</div>
                                        <div class="text-small">
                                            <a href="https://wa.me/{{ $p->telp }}" target="_blank" rel="noopener">
                                                <i class="fab fa-whatsapp"></i> {{ $p->telp }}
                                            </a>
                                        </div>
                                        @if ($p->affiliasi)
                                            <div class="text-small text-muted">{{ $p->affiliasi }}</div>
                                        @endif
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($p->angkatan->nama ?? '-', 34) }}</td>
                                    <td class="text-center">
                                        {{ $p->jumlah_pendaftar }}
                                        {{-- Nama peserta rombongan ditampilkan di sini:
                                             inilah yang dipakai menerbitkan sertifikat
                                             dan memasukkan orang ke grup. --}}
                                        @if ($p->pesertaLain->isNotEmpty())
                                            <div class="text-small text-muted mt-1" style="text-align:left;">
                                                @foreach ($p->semuaPeserta() as $i => $orang)
                                                    {{ $i + 1 }}. {{ $orang['nama'] }}<br>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        Rp {{ number_format((int) $p->total_pembayaran, 0, ',', '.') }}
                                        @if ((int) $p->nominal_diskon > 0)
                                            <div class="text-small text-success">
                                                potongan {{ $p->kode_diskon }}
                                            </div>
                                        @endif
                                        @if ((int) $p->kode_unik > 0)
                                            {{-- Kode uniknya ditampilkan: inilah yang
                                                 dicocokkan panitia dengan mutasi
                                                 rekening. --}}
                                            <div class="text-small text-warning">
                                                kode unik {{ number_format((int) $p->kode_unik, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php($rupa = ['pending' => 'warning', 'paid' => 'success', 'expired' => 'danger', 'cancel' => 'secondary'])
                                        <span class="badge badge-{{ $rupa[$p->status] ?? 'secondary' }}">
                                            {{ \App\WebinarEksklusifPendaftaran::STATUS[$p->status] ?? $p->status }}
                                        </span>
                                        @if ($p->status === 'pending' && $p->kedaluwarsa_pada)
                                            <div class="text-small text-muted">
                                                batas {{ $p->kedaluwarsa_pada->format('d M H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        @if ($p->status !== 'paid')
                                            <form method="POST"
                                                action="{{ route('account.webinarpendaftar.lunasi', $p->getKey()) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Tandai {{ $p->id_transaksi }} sebagai LUNAS?')">
                                                @csrf
                                                <button class="btn btn-sm btn-success" title="Tandai lunas">
                                                    <i class="fas fa-check"></i> Lunas
                                                </button>
                                            </form>
                                        @endif

                                        @if (! in_array($p->status, ['cancel', 'expired'], true))
                                            <form method="POST"
                                                action="{{ route('account.webinarpendaftar.batalkan', $p->getKey()) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Batalkan {{ $p->id_transaksi }}? Kursinya dikembalikan.')">
                                                @csrf
                                                <button class="btn btn-sm btn-danger" title="Batalkan">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('public.webinareksklusif.status', $p->getKey()) }}"
                                            target="_blank" rel="noopener"
                                            class="btn btn-sm btn-light" title="Lihat halaman statusnya">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada pendaftaran yang cocok dengan saringan ini.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $pendaftaran->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@stop
