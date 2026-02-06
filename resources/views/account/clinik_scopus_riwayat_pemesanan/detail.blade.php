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
                                    <p class="text-muted">
                                        @if ($datas->status === 'pending')
                                        <span class="badge bg-warning" style="padding: 6px 12px; border-radius: 6px; color: #ffffff;">
                                            Pending
                                        </span>

                                        @elseif ($datas->status === 'paid')
                                        <span class="badge bg-success" style="padding: 6px 12px; border-radius: 6px; color: #ffffff;">
                                            Paid
                                        </span>

                                        @elseif ($datas->status === 'canceled')
                                        <span class="badge bg-danger" style="padding: 6px 12px; border-radius: 6px; color: #ffffff;">
                                            Canceled
                                        </span>

                                        @elseif ($datas->status === 'completed')
                                        <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 6px; color: #fff;">
                                            Completed
                                        </span>

                                        @else
                                        <span class="badge bg-secondary px-3 py-2 rounded">
                                            Unknown
                                        </span>
                                        @endif
                                    </p>
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