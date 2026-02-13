@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Profil | MANAGEMENT
@stop

@section('content')
<!--================== STYLE TABS ==================-->
<style>
    .nav-pills .nav-link {
        color: #555;
        border-radius: 30px;
        padding: 6px 14px;
        transition: all 0.3s ease;
    }

    /* ACTIVE TAB */
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        background: linear-gradient(to right, #ff3131, #ff914d) !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(255, 49, 49, 0.35);
    }

    /* Hover */
    .nav-pills .nav-link:hover {
        background: rgba(255, 49, 49, 0.12);
    }
</style>
<!--================== END ==================-->

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>PROFIL</h1>
        </div>

        <div class="section-body">

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">

                            <!--================== FOTO PROFIL ==================-->
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Data Trainer</h3>
                                </div>
                                <div class="card-body box-profile">
                                    <div class="text-center">
                                        @if (Auth::user()->gambar == null)
                                        <img alt="User profile picture" id="image-preview" src="{{ asset('assets/img/profil/no-image.jpg') }}" class="profile-user-img img-fluid img-circle" style="width: 128px; height: 128px; border-radius: 50%;">
                                        @else
                                        <img id="image-preview" class="profile-user-img img-fluid img-circle" src="{{ asset('assets/img/profil/' . Auth::user()->gambar) }}" alt="User profile picture" style="width: 128px; height: 128px; border-radius: 50%;">
                                        @endif
                                    </div>

                                    <h3 class="profile-username text-center"> {{ $datas->trainer->full_name ?? '-' }}</h3>
                                    <p class="text-muted text-center">{{ $datas->trainer->level }}</p>
                                </div>
                            </div>
                            <!--================== END ==================-->

                            <!--================== DATA PROFIL ==================-->
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Transaksi</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Id transaksi -->
                                    <strong><i class="fas fa-envelope-open mr-1"></i> ID Transaksi</strong>
                                    <p class="text-muted">
                                        {{ $datas->id_transaksi }}
                                    </p>
                                    <hr>

                                    <!-- id booking -->
                                    <strong><i class="fas fa-briefcase"></i> ID Booking</strong>
                                    <p class="text-muted">
                                        {{ $datas->kode_booking }}
                                    </p>
                                    <hr>

                                    <!-- tanggal booking -->
                                    <strong><i class="fas fa-calendar-alt"></i> Tanggal Booking</strong>
                                    <p class="text-muted">
                                        {{ $datas->tanggal_booking ? \Carbon\Carbon::parse($datas->tanggal_booking)->translatedFormat('d F Y') : '-' }}
                                    </p>
                                    <hr>

                                    <!-- status booking -->
                                    <strong><i class="fas fa-calendar-alt"></i> Status Booking</strong>
                                    <p class="text-muted mt-2">

                                        @php
                                        $isManager = Auth::user()->level === 'manager';
                                        @endphp

                                        @if($isManager)
                                    <form action="{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.updateStatus', $datas->id) }}"
                                        method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('PUT')

                                        <select name="status"
                                            class="form-select form-select-sm d-inline-block"
                                            style="width: 200px; border:2px solid #ff914d; font-weight:600;"
                                            onchange="this.form.submit()">

                                            <option value="pending" {{ $datas->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="paid" {{ $datas->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="completed" {{ $datas->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="canceled" {{ $datas->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                        </select>
                                    </form>

                                    @else
                                    {{-- VIEW ONLY --}}
                                    @if ($datas->status === 'pending')
                                    <span class="badge bg-warning px-3 py-2" style="padding: 6px 12px; border-radius: 6px; color: #ffffff;">Pending</span>
                                    @elseif ($datas->status === 'paid')
                                    <span class="badge bg-success px-3 py-2" style="padding: 6px 12px; border-radius: 6px; color: #ffffff;">Paid</span>
                                    @elseif ($datas->status === 'canceled')
                                    <span class="badge bg-danger px-3 py-2" style="padding: 6px 12px; border-radius: 6px; color: #ffffff;">Canceled</span>
                                    @elseif ($datas->status === 'completed')
                                    <span class="badge bg-primary px-3 py-2" style="padding: 6px 12px; border-radius: 6px; color: #ffffff;">Completed</span>
                                    @else
                                    <span class="badge bg-secondary px-3 py-2" style="padding: 6px 12px; border-radius: 6px; color: #ffffff;">Unknown</span>
                                    @endif
                                    @endif

                                    </p>
                                    <!-- end status booking -->
                                    <hr>

                                </div>
                            </div>
                            <!--================== END ==================-->
                        </div>

                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Riwayat Pembayaran</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Riwayat Pemesanan</a></li>
                                        @if (Auth::user()->level !== 'user')
                                        <li class="nav-item"><a class="nav-link" href="#testimoni" data-toggle="tab">Testimoni</a></li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">

                                        <!--================== TAB RIWAYAT PEMBAYARAN ==================-->
                                        <div class="active tab-pane" id="activity" style="margin-top: -20px;">
                                            <div class="post">

                                                <div class="row mt-3 g-3">
                                                    <div class="col-md-6 col-12">
                                                        <label>Harga Per Sesi</label>
                                                        <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" value="Rp {{ number_format($datas->harga_persesi, 0, ',', '.') }}" readonly>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <label>Diskon</label>
                                                        <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="Rp {{ number_format($datas->diskon, 0, ',', '.') }}" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mt-3 g-3">
                                                    <div class="col-md-4 col-12">
                                                        <label>PPN</label>
                                                        <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" value="Rp {{ number_format($datas->ppn, 0, ',', '.') }}" readonly>
                                                    </div>
                                                    <div class="col-md-4 col-12">
                                                        <label>Kode Unik Pembayaran</label>
                                                        <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="Rp {{ number_format($datas->kode_unik, 0, ',', '.') }}" readonly>
                                                    </div>
                                                    <div class="col-md-4 col-12">
                                                        <label>Tipe Pemesanan</label>
                                                        <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="{{ $datas->tipe_promo }}" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mt-3 g-3">
                                                    <div class="col-md-12 col-12">
                                                        <label>
                                                            Total Pembayaran
                                                        </label>
                                                        <input
                                                            type="text" class="form-control" style="height: 48px; font-size: 16px; font-weight: 700; background-color: #f8f9fa; border: 2px solid #ff914d; color: #000;"
                                                            value="Rp {{ number_format($datas->total_pembayaran, 0, ',', '.') }}" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mt-3 g-3">
                                                    <div class="col-md-12 col-12">
                                                        <label>Bukti Pembayaran</label>

                                                        @if(!empty($datas->gambar))
                                                        <div class="mt-2">
                                                            <img
                                                                src="{{ asset('ClinikScopusPemesanan/' . $datas->gambar) }}"
                                                                alt="Bukti Pembayaran"
                                                                class="img-fluid"
                                                                style="max-height: 300px; border-radius: 12px; border: 2px solid #ff914d; padding: 6px; background: #f8f9fa;">
                                                        </div>
                                                        @else
                                                        <div class="alert alert-warning mt-2">
                                                            Bukti pembayaran belum diupload
                                                        </div>
                                                        @endif

                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <!--================== END TAB RIWAYAT PEMBAYARAN ==================-->

                                        <!--================== TAB RIWAYAT PEMESANAN ==================-->
                                        <div class="tab-pane" id="settings" style="margin-top: -40px;">

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-12 col-12">
                                                    <label>Nama Trainer</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" value="{{ $datas->trainer->full_name ?? '-' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-6 col-12">
                                                    <label>Nama Customer</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="{{ $datas->nama_pemesan }}" readonly>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <label>Affiliasi Customer</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" value="{{ $datas->afiliasi_pemesan }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-6 col-12">
                                                    <label>Email Customer</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="{{ $datas->email_pemesan }}" readonly>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <label>Telp Customer</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="{{ $datas->telp_pemesan }}" readonly>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-4 col-12">
                                                    <label>Sesi</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="{{ $datas->sesi }}" readonly>
                                                </div>
                                                <div class="col-md-4 col-12">
                                                    <label>Jam Sesi</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" value="{{ $datas->jam_sesi }}" readonly>
                                                </div>
                                                <div class="col-md-4 col-12">
                                                    <label>Kendala</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="{{ $datas->kendala }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-12 col-12">
                                                    <label>Deskripsi Kendala</label>
                                                    <textarea style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" readonly>{{ $datas->desc_kendala }}</textarea>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-4 col-12">
                                                    <label>IP Pemesanan</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="{{ $datas->ip_address }}" readonly>
                                                </div>
                                                <div class="col-md-8 col-12">
                                                    <label>Browser Pemesanan</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" value="{{ $datas->browser }}" readonly>
                                                </div>
                                            </div>

                                        </div>
                                        <!--================== END TAB RIWAYAT PEMESANAN ==================-->

                                        <!--================== TAB TESTIMONI ==================-->
                                        <div class="tab-pane" id="testimoni" style="margin-top: -40px;">

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-6 col-12">
                                                    <label>Nama Trainer</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" value="{{ $datasTesti->trainer->full_name ?? '-' }}" readonly>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <label>Nama Customer</label>
                                                    <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Username" value="{{ $datasTesti->customer->full_name ?? '-' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-6 col-12">
                                                    <label>Rating Trainer</label>
                                                    <div style="font-size: 16px; color: #ffbf00;">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <=($datasTesti->rating ?? 0))
                                                            <i class="fas fa-star"></i>
                                                            @else
                                                            <i class="far fa-star"></i>
                                                            @endif
                                                            @endfor
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <label>Rating Aplikasi</label>
                                                    <div style="font-size: 16px; color: #ffbf00;">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <=($datasTesti->rating_aplikasi ?? 0))
                                                            <i class="fas fa-star"></i>
                                                            @else
                                                            <i class="far fa-star"></i>
                                                            @endif
                                                            @endfor
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-6 col-12">
                                                    <label>Komentar Trainer</label>
                                                    <textarea
                                                        class="form-control form-control-sm"
                                                        style="height: 80px; font-size: 14px;"
                                                        readonly>{{ $datasTesti->komentar ?? '-' }}</textarea>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <label>Komentar Aplikasi</label>
                                                    <textarea
                                                        class="form-control form-control-sm"
                                                        style="height: 80px; font-size: 14px;"
                                                        readonly>{{ $datasTesti->komentar_aplikasi ?? '-' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <!--================== END TAB TESTIMONI ==================-->

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </section>
</div>

<!--================== FORMAT NO TELP ==================-->
<script>
    function formatPhoneNumber(input) {
        // Menghapus semua karakter non-digit
        var phoneNumber = input.value.replace(/\D/g, '');

        // Menentukan panjang nomor telepon
        var phoneNumberLength = phoneNumber.length;

        // Memeriksa panjang nomor telepon dan menerapkan format yang sesuai
        if (phoneNumberLength === 11) {
            phoneNumber = phoneNumber.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (phoneNumberLength === 12) {
            phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (phoneNumberLength === 13) {
            phoneNumber = phoneNumber.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
        }

        // Mengatur nilai input dengan nomor telepon yang diformat
        input.value = phoneNumber;
    }
</script>
<!--================== END ==================-->

@stop