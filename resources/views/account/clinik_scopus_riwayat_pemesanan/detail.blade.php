@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Detail Pemesanan | MIS
@stop

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');

    :root {
        --accent: #6366f1;
        --accent-light: #818cf8;
        --bg-main: #f4f7ff;
        --card-bg: rgba(255, 255, 255, 0.9);
        --radius-xl: 24px;
        --shadow-soft: 0 20px 40px rgba(0, 0, 0, 0.04);
        --text-dark: #1e293b;
        --bg-readonly: #f1f5f9;
        /* Warna khusus Read Only */
    }

    .main-content {
        padding-top: 110px !important;
        background-color: var(--bg-main);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }

    .section-header-modern {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border-radius: var(--radius-xl);
        padding: 25px 30px;
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: var(--shadow-soft);
        margin-bottom: 30px;
    }

    .section-header-modern h1 {
        font-size: 28px !important;
        font-weight: 800 !important;
        letter-spacing: -1.5px;

        /* Gunakan kombinasi ini agar gradient muncul dengan kuat */
        background: linear-gradient(to right, #1e293b 0%, #6366f1 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        color: transparent !important;
        /* Fallback */

        /* Tambahkan ini agar gradient tidak terputus */
        display: inline-block;
        line-height: 1.2;
        margin: 0;
        text-decoration: none !important;
    }

    .card-neo {
        background: var(--card-bg);
        backdrop-filter: blur(15px);
        border-radius: var(--radius-xl);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: var(--shadow-soft);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .form-group label {
        font-weight: 700;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-modern {
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        padding: 12px 18px;
        height: auto;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s ease;
        background: rgba(248, 250, 252, 0.8);
        color: var(--text-dark);
    }

    .form-control-modern:focus {
        border-color: var(--accent);
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    /* 🔹 STYLE KHUSUS READ ONLY */
    .form-control-modern[readonly] {
        background-color: var(--bg-readonly) !important;
        border-color: #cbd5e1;
        color: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
    }

    .btn-modern {
        border-radius: 16px;
        padding: 14px 28px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        cursor: pointer;
    }

    .btn-gradient {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }

    .btn-gradient:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
        color: white;
    }

    .custom-popup {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
    }

    .custom-popup-content {
        position: relative;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 450px;
        width: 90%;
        background: white;
        padding: 35px;
        border-radius: var(--radius-xl);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .custom-popup-close {
        position: absolute;
        right: 20px;
        top: 15px;
        font-size: 28px;
        color: #94a3b8;
        cursor: pointer;
    }
</style>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header-modern">
            <h1>Detail Pemesanan</h1>
            <p class="text-muted font-weight-bold mb-0">Informasi lengkap mengenai jadwal sesi, rincian pembayaran, dan kendala ilmiah customer.</p>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-4 col-md-5">
                    <div class="card-neo p-4 text-center position-relative overflow-hidden">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 80px; background: linear-gradient(to bottom, rgba(99, 102, 241, 0.05), transparent);"></div>

                        <div class="position-relative">
                            <img src="{{ isset($datas->trainer->gambar) ? asset('assets/img/profil/' . $datas->trainer->gambar) : asset('assets/img/profil/no-image.jpg') }}"
                                style="width: 140px; height: 140px; border-radius: 45px; object-fit: cover; border: 6px solid #fff; box-shadow: 0 15px 35px rgba(0,0,0,0.1); transition: transform 0.3s ease;" class="hover-zoom">

                            <div style="position: absolute; bottom: 10px; right: 25%; width: 22px; height: 22px; background: #22c55e; border: 4px solid #fff; border-radius: 50%; shadow: 0 2px 5px rgba(0,0,0,0.2);"></div>
                        </div>

                        <h5 class="mt-4 font-weight-800 text-dark mb-1" style="letter-spacing: -0.5px;">
                            {{ $datas->trainer->full_name ?? '-' }}
                        </h5>

                        <div class="d-flex justify-content-center align-items-center">
                            <div style="display: inline-flex; align-items: center; justify-content: center; background: rgba(99, 102, 241, 0.08); padding: 6px 16px; border-radius: 100px; border: 1px solid rgba(99, 102, 241, 0.15);">
                                <i class="fas fa-certificate mr-2" style="color: #6366f1; font-size: 10px;"></i>
                                <span style="color: #6366f1; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1;">
                                    Trainer Rumah Scopus
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-neo p-4">
                        <div class="mb-4">
                            <label class="text-muted small font-weight-bold">ID Transaksi</label>
                            <span class="font-weight-bold d-block">{{ $datas->id_transaksi }}</span>
                        </div>
                        <hr style="border-top: 1px dashed #e2e8f0;">
                        <div class="mb-4">
                            <label class="text-muted small font-weight-bold">ID Booking</label>
                            <span class="font-weight-bold d-block">{{ $datas->kode_booking }}</span>
                        </div>
                        <hr style="border-top: 1px dashed #e2e8f0;">
                        <div class="mb-4">
                            <label class="text-muted small font-weight-bold">Tanggal Booking</label>
                            <span class="font-weight-bold d-block">{{ $datas->tanggal_booking ? \Carbon\Carbon::parse($datas->tanggal_booking)->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <hr style="border-top: 1px dashed #e2e8f0;">
                        <div>
                            <label class="text-muted small font-weight-bold">Status Booking</label>
                            <div class="mt-1">
                                @php $isManager = Auth::user()->level === 'manager'; @endphp
                                @if($isManager)
                                <form action="{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.updateStatus', $datas->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="form-control" style="border-radius: 12px; font-weight: 800; font-size: 12px; height: 40px;">
                                        <option value="pending" {{ $datas->status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                        <option value="paid" {{ $datas->status == 'paid' ? 'selected' : '' }}>✅ Paid</option>
                                        <option value="completed" {{ $datas->status == 'completed' ? 'selected' : '' }}>🏁 Done</option>
                                        <option value="canceled" {{ $datas->status == 'canceled' ? 'selected' : '' }}>❌ Cancel</option>
                                    </select>
                                </form>
                                @else
                                <span class="badge badge-primary px-3 py-2" style="border-radius: 10px;">{{ strtoupper($datas->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-md-7">
                    <div class="card-neo shadow-soft h-100 border-0">
                        <div class="card-header bg-transparent border-0 p-4 pb-0">
                            <ul class="nav nav-pills nav-fill p-2" id="pills-tab" role="tablist"
                                style="background: #f1f5f9; border-radius: 18px; border: 1px solid #e2e8f0;">
                                <li class="nav-item">
                                    <a class="nav-link active font-weight-bold d-flex align-items-center justify-content-center"
                                        id="pills-activity-tab" data-toggle="pill" href="#activity" role="tab"
                                        style="border-radius: 14px; padding: 12px; transition: 0.3s;">
                                        <i class="fas fa-wallet mr-2"></i> Pembayaran
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold d-flex align-items-center justify-content-center"
                                        id="pills-settings-tab" data-toggle="pill" href="#settings" role="tab"
                                        style="border-radius: 14px; padding: 12px; transition: 0.3s;">
                                        <i class="fas fa-layer-group mr-2"></i> Detail Sesi
                                    </a>
                                </li>
                                @if (Auth::user()->level !== 'user')
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold d-flex align-items-center justify-content-center"
                                        id="pills-testimoni-tab" data-toggle="pill" href="#testimoni" role="tab"
                                        style="border-radius: 14px; padding: 12px; transition: 0.3s;">
                                        <i class="fas fa-comment-dots mr-2"></i> Testimoni
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>

                        <div class="card-body p-4">
                            <div class="tab-content" id="pills-tabContent">

                                <div class="tab-pane fade show active" id="activity" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="p-3" style="background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                                                <label class="small font-weight-bold text-muted mb-2 d-block">HARGA & DISKON</label>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-dark">Biaya Per Sesi</span>
                                                    <span class="font-weight-bold">Rp {{ number_format($datas->harga_persesi, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-danger">Diskon</span>
                                                    <span class="font-weight-bold text-danger">- Rp {{ number_format($datas->diskon, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="p-3" style="background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                                                <label class="small font-weight-bold text-muted mb-2 d-block">BIAYA TAMBAHAN</label>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-dark">PPN</span>
                                                    <span class="font-weight-bold">Rp {{ number_format($datas->ppn, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted">Kode Unik</span>
                                                    <span class="badge badge-light font-weight-bold text-dark">Rp {{ number_format($datas->kode_unik, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mb-4">
                                            <div class="p-4 shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #475569 100%); border-radius: 24px; position: relative; overflow: hidden;">
                                                <div style="position: absolute; right: -20px; bottom: -20px; opacity: 0.1; font-size: 100px; color: #fff;">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-md-7 text-white">
                                                        <p class="mb-1 text-white-50 font-weight-bold small">TOTAL AKHIR PEMBAYARAN</p>
                                                        <h2 class="mb-0 font-weight-800" style="letter-spacing: -1px;">Rp {{ number_format($datas->total_pembayaran, 0, ',', '.') }}</h2>
                                                    </div>
                                                    <div class="col-md-5 text-md-right mt-3 mt-md-0">
                                                        <span class="px-3 py-2" style="background: rgba(255,255,255,0.1); border-radius: 12px; color: #fff; font-size: 12px; border: 1px solid rgba(255,255,255,0.2);">
                                                            <i class="fas fa-bolt mr-1"></i> {{ $datas->tipe_promo ?? 'Reguler' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-2">
                                            <label class="form-label font-weight-bold text-dark small mb-3">
                                                <i class="fas fa-camera-retro mr-2 text-primary"></i>BUKTI TRANSAKSI DIGITAL
                                            </label>
                                            @if(!empty($datas->gambar))
                                            <div class="text-center p-2" style="background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                                                <img src="{{ asset('ClinikScopusPemesanan/' . $datas->gambar) }}" class="img-fluid"
                                                    style="border-radius: 16px; max-height: 400px; width: 100%; object-fit: contain;">
                                            </div>
                                            @else
                                            <div class="text-center py-5" style="border: 2px dashed #cbd5e1; border-radius: 20px; background: #f8fafc;">
                                                <i class="fas fa-image-slash fa-3x text-muted mb-3"></i>
                                                <p class="text-muted font-weight-bold mb-0">Belum ada lampiran pembayaran</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="settings" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12 mb-4">
                                            <div class="p-3 shadow-sm d-flex align-items-center"
                                                style="background: linear-gradient(to right, #fff5f8, #ffffff); border-radius: 20px; border: 1px solid #fbcfe8;">
                                                <div class="mr-3 d-flex align-items-center justify-content-center shadow-sm"
                                                    style="width: 60px; height: 60px; background: #db2777; color: #fff; border-radius: 16px;">
                                                    <i class="fas fa-user-tie fa-2x"></i>
                                                </div>
                                                <div>
                                                    <label class="small text-muted mb-0 font-weight-bold" style="letter-spacing: 1px;">TRAINER PENANGGUNG JAWAB</label>
                                                    <h5 class="mb-0 font-weight-800 text-dark">{{ $datas->trainer->full_name ?? '-' }}</h5>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-7 mb-4">
                                            <div class="card-neo h-100 p-4 border-0 shadow-sm"
                                                style="background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0 !important; position: relative; overflow: hidden;">

                                                <div style="position: absolute; top: -15px; right: -15px; width: 80px; height: 80px; background: rgba(99, 102, 241, 0.05); border-radius: 50%;"></div>

                                                <h6 class="font-weight-800 mb-4 text-dark d-flex align-items-center" style="letter-spacing: -0.5px;">
                                                    <span class="mr-2 d-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px; background: #6366f1; color: white; border-radius: 8px; font-size: 14px;">
                                                        <i class="fas fa-user-circle"></i>
                                                    </span>
                                                    Profil Customer
                                                </h6>

                                                <div class="row">
                                                    <div class="col-12 mb-3">
                                                        <div class="p-3" style="background: #f8fafc; border-radius: 16px; border: 1px solid #f1f5f9;">
                                                            <label class="small font-weight-bold text-muted uppercase mb-1 d-block" style="font-size: 10px; opacity: 0.8;">Nama Lengkap</label>
                                                            <p class="font-weight-800 text-dark mb-0" style="font-size: 15px; word-break: break-word;">{{ $datas->nama_pemesan }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <div class="p-3" style="background: #f8fafc; border-radius: 16px; border: 1px solid #f1f5f9;">
                                                            <label class="small font-weight-bold text-muted uppercase mb-1 d-block" style="font-size: 10px; opacity: 0.8;">Instansi / Afiliasi</label>
                                                            <p class="font-weight-700 text-dark mb-0" style="line-height: 1.5; word-break: break-word;">{{ $datas->afiliasi_pemesan }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6 col-md-12 mb-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3 text-primary bg-light d-flex align-items-center justify-content-center"
                                                                style="width: 35px; height: 35px; border-radius: 10px; min-width: 35px;">
                                                                <i class="fas fa-envelope"></i>
                                                            </div>
                                                            <div style="overflow: hidden;">
                                                                <label class="small font-weight-bold text-muted uppercase mb-0 d-block" style="font-size: 9px;">Email</label>
                                                                <p class="font-weight-700 text-dark mb-0" style="font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $datas->email_pemesan }}">
                                                                    {{ $datas->email_pemesan }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6 col-md-12 mb-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3 text-success bg-light d-flex align-items-center justify-content-center"
                                                                style="width: 35px; height: 35px; border-radius: 10px; min-width: 35px;">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </div>
                                                            <div>
                                                                <label class="small font-weight-bold text-muted uppercase mb-0 d-block" style="font-size: 9px;">WhatsApp</label>
                                                                <p class="font-weight-700 text-dark mb-0" style="font-size: 13px;">{{ $datas->telp_pemesan }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-5 mb-4">
                                            <div class="card-neo h-100 p-4 border-0 shadow-sm text-center d-flex flex-column justify-content-center"
                                                style="background: linear-gradient(145deg, #6366f1, #4f46e5); border-radius: 24px; color: white;">
                                                <div class="mb-3">
                                                    <i class="fas fa-clock fa-3x opacity-50"></i>
                                                </div>
                                                <label class="small font-weight-bold text-white-50 uppercase mb-2">Jadwal Sesi Terpilih</label>
                                                <h3 class="font-weight-800 mb-1">{{ $datas->jam_sesi }}</h3>
                                                <div class="mt-2">
                                                    <span class="badge badge-light px-3 py-2 font-weight-800" style="color: #4f46e5; border-radius: 10px; font-size: 12px;">
                                                        {{ $datas->sesi }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mb-4">
                                            <div class="card-neo p-4 border-0 shadow-sm" style="background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0 !important;">
                                                <div class="d-flex align-items-center mb-4">
                                                    <div class="p-2 bg-warning-light rounded-lg mr-3" style="background: #fffbeb; color: #d97706;">
                                                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                                                    </div>
                                                    <h6 class="font-weight-800 mb-0 text-dark">Detail Kendala Ilmiah</h6>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="small font-weight-bold text-muted uppercase">Kategori Utama</label>
                                                    <div class="p-3" style="background: #f8fafc; border-radius: 12px; border-left: 4px solid #fbbf24; font-weight: 700; color: #1e293b;">
                                                        {{ $datas->kendala }}
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="small font-weight-bold text-muted uppercase">Deskripsi Masalah</label>
                                                    <div class="p-3" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; min-height: 100px; color: #475569; line-height: 1.6;">
                                                        {{ $datas->desc_kendala ?? 'Tidak ada deskripsi tambahan.' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="p-3 d-flex justify-content-between align-items-center"
                                                style="background: #f1f5f9; border-radius: 16px; border: 1px solid #e2e8f0;">
                                                <div class="small text-muted font-weight-bold">
                                                    <i class="fas fa-globe-americas mr-1"></i> IP Address: <span class="text-dark">{{ $datas->ip_address }}</span>
                                                </div>
                                                <div class="small text-muted font-weight-bold">
                                                    <i class="fas fa-laptop mr-1"></i> Browser: <span class="text-dark" style="font-size: 10px;">{{ $datas->browser }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="testimoni" role="tabpanel">
                                    @if($datasTesti)
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 p-4 border-0 shadow-sm position-relative overflow-hidden"
                                                style="background: #ffffff; border-radius: 24px; border: 1px solid #f5d0fe !important; transition: transform 0.3s ease;">

                                                <div style="position: absolute; right: -10px; top: -10px; opacity: 0.05; transform: rotate(15deg);">
                                                    <i class="fas fa-star fa-6x" style="color: #d946ef;"></i>
                                                </div>

                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="mr-3 d-flex align-items-center justify-content-center"
                                                        style="width: 45px; height: 45px; background: #fdf4ff; color: #d946ef; border-radius: 12px;">
                                                        <i class="fas fa-user-check fa-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 font-weight-800" style="color: #701a75; letter-spacing: -0.5px;">Trainer Review</h6>
                                                        <div class="mt-1">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <i class="{{ $i <= ($datasTesti->rating ?? 0) ? 'fas' : 'far' }} fa-star" style="color: #d946ef; font-size: 10px;"></i>
                                                                @endfor
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="position-relative p-3" style="background: #fdf4ff; border-radius: 16px; min-height: 100px;">
                                                    <i class="fas fa-quote-left" style="position: absolute; top: 10px; left: 10px; color: rgba(217, 70, 239, 0.1); font-size: 24px;"></i>
                                                    <p class="text-dark font-italic mb-0 position-relative" style="line-height: 1.6; font-size: 14px; padding: 5px 10px;">
                                                        {{ $datasTesti->komentar ?? 'Tidak ada komentar khusus untuk trainer.' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 p-4 border-0 shadow-sm position-relative overflow-hidden"
                                                style="background: #ffffff; border-radius: 24px; border: 1px solid #bbf7d0 !important; transition: transform 0.3s ease;">

                                                <div style="position: absolute; right: -10px; top: -10px; opacity: 0.05; transform: rotate(15deg);">
                                                    <i class="fas fa-laptop-code fa-6x" style="color: #22c55e;"></i>
                                                </div>

                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="mr-3 d-flex align-items-center justify-content-center"
                                                        style="width: 45px; height: 45px; background: #f0fdf4; color: #22c55e; border-radius: 12px;">
                                                        <i class="fas fa-mobile-alt fa-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 font-weight-800" style="color: #14532d; letter-spacing: -0.5px;">System Review</h6>
                                                        <div class="mt-1">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <i class="{{ $i <= ($datasTesti->rating_aplikasi ?? 0) ? 'fas' : 'far' }} fa-star" style="color: #22c55e; font-size: 10px;"></i>
                                                                @endfor
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="position-relative p-3" style="background: #f0fdf4; border-radius: 16px; min-height: 100px;">
                                                    <i class="fas fa-quote-left" style="position: absolute; top: 10px; left: 10px; color: rgba(34, 197, 94, 0.1); font-size: 24px;"></i>
                                                    <p class="text-dark font-italic mb-0 position-relative" style="line-height: 1.6; font-size: 14px; padding: 5px 10px;">
                                                        {{ $datasTesti->komentar_aplikasi ?? 'Tidak ada komentar khusus untuk sistem.' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-center py-5">
                                        <div class="mb-4 d-inline-flex align-items-center justify-content-center shadow-sm"
                                            style="width: 100px; height: 100px; background: #f8fafc; border-radius: 50%; color: #cbd5e1;">
                                            <i class="fas fa-comment-slash fa-3x"></i>
                                        </div>
                                        <h5 class="text-dark font-weight-800 mb-2">Belum Ada Ulasan</h5>
                                        <p class="text-muted mx-auto" style="max-width: 400px;">
                                            Sesi ini belum mendapatkan feedback dari customer. Testimoni akan muncul secara otomatis setelah customer mengisi form penilaian.
                                        </p>
                                    </div>
                                    @endif
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