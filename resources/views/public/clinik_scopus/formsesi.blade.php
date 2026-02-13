@extends('public.layout.header')

@section('title')
Sesi Klinik Scopus | Rumah Scopus
@stop

<!--================== BACKGOUND IMAGE ==================-->
<style>
    .hero-bg {
        position: relative;
        background-image: url('/assets/artikel/img/hero-bg.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        padding-top: 120px;
        padding-bottom: 80px;
    }

    .hero-bg::before {
        content: "";
        position: absolute;
        inset: 0;
    }

    .hero-bg>.container {
        position: relative;
        z-index: 2;
    }
</style>
<!--================== END ==================-->

<!--================== SESI STYLE ==================-->
<style>
    .sesi-box {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 10px;
    }

    .sesi-item {
        background: #fff;
        border: 1px dashed #ddd;
        border-radius: 8px;
        padding: 8px 10px;
        text-align: center;
        transition: all .2s ease;
    }

    .sesi-item:hover {
        background: linear-gradient(to right, #ff3131, #ff914d);
        color: #fff;
        border-color: transparent;
    }

    .sesi-clickable {
        cursor: pointer;
    }

    .sesi-clickable:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, .15);
    }

    /* modal */
    /* INFO SESI */
    .info-sesi {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 14px;
    }

    /* FORM WRAPPER */
    .form-sesi .form-label {
        font-size: 13px;
        margin-bottom: 4px;
        color: #555;
    }

    /* INPUT */
    .form-sesi .form-control {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 11px 14px;
        font-size: 14px;
        transition: all .25s ease;
    }

    /* FOCUS EFFECT */
    .form-sesi .form-control:focus {
        border-color: #ff914d;
        box-shadow: 0 0 0 0.18rem rgba(255, 145, 77, .22);
    }

    /* PLACEHOLDER */
    .form-sesi .form-control::placeholder {
        color: #aaa;
        font-size: 13px;
    }

    /* MODAL FOOTER */
    .modal-footer {
        border-top: none;
        padding-top: 0;
    }

    /* BUTTON ANIMATION */
    .modal-footer .btn {
        transition: all .25s ease;
    }

    .modal-footer .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, .15);
    }

    /* VALIDASI MODAL INPUTAN */
    .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 .15rem rgba(220, 53, 69, .15);
    }

    .error-msg {
        font-size: 12px;
    }
</style>
<!--================== END ==================-->

<!--================== PROMO STYLE ==================-->
<style>
    .promo-box {
        border-radius: 14px;
        background: linear-gradient(135deg, #fff5f5, #fff);
        border: 1px solid #ffe0e0;
        padding: 18px;
    }

    .promo-header {
        margin-bottom: 12px;
    }

    .badge-promo {
        display: inline-block;
        background: linear-gradient(to right, #ff3131, #ff914d);
        color: #fff;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 20px;
        margin-bottom: 6px;
    }

    .countdown {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .countdown div {
        background: #fff;
        border-radius: 10px;
        padding: 10px 14px;
        text-align: center;
        min-width: 65px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .08);
    }

    .countdown span {
        display: block;
        font-size: 20px;
        font-weight: 700;
        color: #ff3131;
    }

    .countdown small {
        font-size: 11px;
        color: #666;
    }
</style>
<!--================== END ==================-->

<!--================== LEBEL PROMO BUNDLING ==================-->
<style>
    .promo-bundling {
        position: relative;
        border-top: 2px dashed #ff914d;
        padding-top: 28px;
        /* beri ruang untuk label */
    }

    .promo-bundling-label {
        position: absolute;
        top: -11px;
        left: 50%;
        transform: translateX(-50%);
        background: #fff;
        padding: 4px 14px;
        font-size: 11px;
        font-weight: 700;
        color: #ff3131;
        border-radius: 20px;
        letter-spacing: .5px;
        text-transform: uppercase;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
    }
</style>
<!--================== END ==================-->

<!--================== LAYOUT TAMPILAN HARGA SESI REGULER ==================-->
<style>
    #modalKode {
        font-size: 16px;
        color: #ff914d;
    }

    #modalTotal {
        font-size: 18px;
        font-weight: 700;
        background: linear-gradient(to right, #ff3131, #ff914d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .info-sesi-modern {
        background: linear-gradient(145deg, #fff, #fdf6f6);
        border-radius: 16px;
        border: 1px solid #ffe6e6;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .info-sesi-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
    }

    .info-sesi-modern i {
        font-size: 0.9rem;
    }

    .total-gradient {
        font-size: 1.3rem;
        background: linear-gradient(to right, #ff3131, #ff914d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
    }

    @media (max-width: 576px) {

        .col-5,
        .col-7 {
            flex: 100%;
            max-width: 100%;
        }

        .col-5 {
            margin-bottom: 4px;
        }
    }
</style>
<!--================== END ==================-->

<!--================== HEMAT BERAPA PERSEN PADA CLASS REGULER ==================-->
<style>
    /* Pastikan kotaknya jadi reference point */
    .sesi-item {
        position: relative;
        padding-top: 18px;
        padding-bottom: 18px;
    }

    /* Badge duduk TEPAT di garis border atas */
    .badge-hemat {
        position: absolute;
        top: 0;
        /* titik acuan di garis border */
        left: 50%;
        transform: translate(-50%, -50%);
        /* setengah di atas, setengah di bawah garis */
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        color: #ff3131;
        background: #ffffff;
        /* putih supaya jelas di atas garis */
        padding: 3px 10px;
        border-radius: 20px;
        border: 1px solid #ffd6cc;
        box-shadow: 0 2px 6px rgba(255, 49, 49, 0.12);
        white-space: nowrap;
        z-index: 2;

    }

    /* efek hover tetap halus */
    .sesi-item:hover .badge-hemat {
        transform: translate(-50%, -52%);
    }

    /* sesi sudah di pesan */
    .sesi-item.active {
        border: 2px solid #ff914d;
        background: #fff5f0;
    }

    /* style sesi reguler penuh dari rentang tanggal yang ada */
    .sesi-full {
        background: #f1f1f1 !important;
        border: 2px solid #ddd !important;
        color: #999 !important;
        cursor: not-allowed !important;
        pointer-events: none;
        position: relative;
    }

    .badge-full {
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: #dc3545;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;

        /* 🔑 INI KUNCINYA */
        white-space: nowrap;
    }
</style>
<!--================== END ==================-->

@section('konten')
<section class="hero-bg">
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-8 mb-4">
                <div class="card shadow-lg border-0 p-4"
                    style="border-radius:15px;">

                    <div class="row align-items-center">

                        <body data-logged-in="{{ auth()->check() ? 1 : 0 }}">

                            <!-- FOTO -->
                            <div class="col-md-4 text-center mb-3 mb-md-0">
                                <img
                                    src="{{ !empty($clinik->foto)
                                    ? asset('ClinikScopusTrainer/' . basename($clinik->foto))
                                    : asset('ClinikScopusTrainer/no-image.jpg') }}"
                                    class="img-fluid rounded"
                                    style="max-height:220px; object-fit:cover;"
                                    alt="Trainer">
                            </div>

                            <!-- INFO -->
                            <div class="col-md-8">
                                <h3 class="fw-bold mb-1">
                                    {{ $clinik->full_name ?? '-' }}
                                </h3>

                                <p class="text-muted mb-3">
                                    {{ $clinik->jobdesk }}
                                </p>

                                <!-- SESI -->
                                <div class="sesi-box">
                                    <h6 class="fw-bold mb-2 mb-4">Jadwal Sesi</h6>

                                    <div class="row g-2">
                                        @foreach(['sesi','sesi2','sesi3','sesi4','sesi5','sesi6','sesi7','sesi8','sesi9'] as $index => $field)
                                        @php
                                        $value = $clinik->$field ?? null;
                                        $biaya = $clinik->biayaPersesi->biaya_persesi ?? null;
                                        $diskon = 0;

                                        // CARI PROMO YANG TERKAIT DENGAN SESI INI DI clinikscopus_promo_sesi
                                        $promoSesi = $promo
                                        ->filter(fn($p) => in_array((string)($index+1),
                                        $p->sesi_bundling->pluck('sesi_key')->map(fn($v) => (string)$v)->toArray()
                                        ))
                                        ->whereIn('tipe_diskon', ['nominal','persentase'])
                                        ->first();

                                        // HITUNG PERSEN HEMAT KHUSUS SESI INI
                                        $persenHemat = 0;
                                        if ($promoSesi && ($promoSesi->harga_normal ?? 0) > 0) {
                                        $persenHemat = round(
                                        ($promoSesi->nominal_diskon / $promoSesi->harga_normal) * 100
                                        );
                                        }
                                        @endphp

                                        @php
                                        $sesiKey = 'Sesi '.($index + 1);
                                        $sesiName = 'Sesi ' . $sesiKey;
                                        $isFull = collect($rangeTanggal)->every(function ($date) use ($sesiTerpakai, $sesiKey) {

                                        return collect($sesiTerpakai)->contains(function ($booking) use ($date, $sesiKey) {

                                        if ($booking['tanggal'] !== $date->format('Y-m-d')) {
                                        return false;
                                        }

                                        $sesiBooked = collect(explode(',', $booking['sesi']))
                                        ->map(fn($s) => trim($s));

                                        return $sesiBooked->contains($sesiKey);
                                        });
                                        });
                                        @endphp

                                        @php
                                        // Cek apakah sesi disabled
                                        $isDisabled = empty($value) || $value === '-' || strtolower((string)$value) === 'null';
                                        @endphp

                                        <div class="col-6 col-md-4">
                                            <div class="sesi-item 
                {{ $isDisabled ? 'sesi-disabled' : 'sesi-clickable' }} 
                {{ $isFull ? 'sesi-full' : '' }}"
                                                {{-- Hanya beri data-type jika sesi aktif --}}
                                                @if(!$isDisabled && !$isFull)
                                                data-type="reguler"
                                                data-sesi="Sesi {{ $index + 1 }}"
                                                data-sesi-key="{{ $index + 1 }}"
                                                data-jadwal="{{ $value }}"
                                                data-trainer="{{ $clinik->full_name ?? '' }}"
                                                data-harga="{{ $biaya }}"
                                                data-diskon="{{ $diskon }}"
                                                data-klinik-id="{{ $clinik->id }}"
                                                @endif>

                                                {{-- Badge Hemat --}}
                                                @if($promoSesi && $persenHemat > 0)
                                                <span class="badge-hemat">
                                                    <i class="fas fa-bolt me-1"></i>
                                                    HEMAT {{ $persenHemat }}%
                                                </span>
                                                @endif

                                                {{-- Nama Sesi --}}
                                                <div class="fw-bold mb-1">
                                                    Sesi {{ $index + 1 }}
                                                </div>

                                                {{-- Jadwal / Status --}}
                                                <div class="fw-semibold">
                                                    {{ $isDisabled ? 'TIDAK TERSEDIA' : $value }}
                                                </div>

                                                {{-- Badge Full --}}
                                                @if($isFull)
                                                <span class="badge-full">SESI PENUH</span>
                                                @endif

                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <!-- END -->

                            <!-- PROMO -->
                            @if($promo->count())
                            <div class="row g-3 mt-3">

                                @foreach($promo as $item)
                                @if(
                                now()->between($item->tanggal_mulai_promo, $item->tanggal_selesai_promo)
                                && $item->tipe_diskon === 'bundling'
                                )
                                <div class="col-12 {{ $promo->count() > 1 ? 'col-md-6' : '' }}">

                                    <div class="promo-box promo-bundling shadow-sm h-100">
                                        <span class="promo-bundling-label">
                                            PROMO BUNDLING
                                        </span>

                                        <!-- COUNTDOWN -->
                                        <div class="promo-body mb-3">
                                            <div class="countdown"
                                                data-start="{{ $item->tanggal_mulai_promo }}"
                                                data-end="{{ $item->tanggal_selesai_promo }}">

                                                <div>
                                                    <span class="cd-days">00</span>
                                                    <small>Hari</small>
                                                </div>
                                                <div>
                                                    <span class="cd-hours">00</span>
                                                    <small>Jam</small>
                                                </div>
                                                <div>
                                                    <span class="cd-minutes">00</span>
                                                    <small>Menit</small>
                                                </div>
                                                <div>
                                                    <span class="cd-seconds">00</span>
                                                    <small>Detik</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- HEADER -->
                                        <div class="promo-header text-center">
                                            <h5 class="fw-bold mb-1">
                                                {{ $item->nama_promo }}
                                            </h5>
                                            <div class="promo-price text-center mt-2">

                                                {{-- HARGA NORMAL --}}
                                                <div class="text-muted small text-decoration-line-through">
                                                    Rp {{ number_format($item->harga_normal, 0, ',', '.') }}
                                                </div>

                                                {{-- HARGA PROMO --}}
                                                <div class="fw-bold"
                                                    style="font-size:22px; background:linear-gradient(to right,#ff3131,#ff914d); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                                                    Rp {{ number_format($item->total_biaya, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- BUNDLING -->
                                        @php
                                        $bundlingPromo = $promo->filter(fn($p) => $p->tipe_diskon === 'bundling' && $p->sesi_bundling->count());
                                        $isMultiple = $bundlingPromo->count() > 1;
                                        @endphp

                                        @if(
                                        $item->tipe_diskon === 'bundling' &&
                                        $item->sesi_bundling->count()
                                        )
                                        <div class="row g-3 mt-3">

                                            <div class="col-12">
                                                <div class="p-3 rounded h-100"
                                                    style="background:#fff; border:1px dashed #ff914d;">
                                                    <div class="row g-2">
                                                        @foreach($item->sesi_bundling as $sesi)

                                                        <div class="col-6">
                                                            <div class="sesi-item"
                                                                style="font-size:13px;font-weight:600;">

                                                                <div>
                                                                    {{ strtoupper($sesi->sesi_key) }}
                                                                </div>

                                                                <div class="fw-semibold">
                                                                    {{ $sesi->jam }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- BUTTON -->
                                            @php
                                            $jadwalBundling = $item->sesi_bundling
                                            ->filter(fn($s) => !empty($s->jam))
                                            ->map(fn($s) => 'Sesi '.$s->sesi_key)
                                            ->implode(', ');

                                            $bundlingFull = $item->sesi_bundling->every(fn($s) =>
                                            in_array('Sesi '.$s->sesi_key, $sesiPenuh->toArray())
                                            );
                                            @endphp

                                            <div class="mt-3 text-center">
                                                <button type="button"
                                                    class="btn w-100 promo-btn {{ $bundlingFull ? 'disabled promo-full' : '' }}"
                                                    data-type="promo"
                                                    {{ $bundlingFull ? 'disabled' : '' }}

                                                    data-trainer="{{ $clinik->full_name ?? '' }}"
                                                    data-promo="{{ $item->nama_promo }}"
                                                    data-sesi="{{ $item->sesi_bundling->pluck('sesi_key')->join(', ') }}"
                                                    data-jadwal="{{ $item->sesi_bundling->pluck('jam')->join(', ') }}"
                                                    data-harga="{{ $item->harga_normal }}"
                                                    data-diskon="{{ $item->nominal_diskon ?? 0 }}"
                                                    data-klinik-id="{{ $clinik->id }}"

                                                    style="background:linear-gradient(to right,#ff3131,#ff914d); color:#fff; font-weight:600; border-radius:10px; padding:10px 16px;">

                                                    <i class="fas {{ $bundlingFull ? 'fa-ban' : 'fa-calendar-check' }} me-1"></i>

                                                    <span class="promo-text">
                                                        {{ $bundlingFull ? 'SESI PENUH' : 'Pesan Sekarang' }}
                                                    </span>
                                                </button>

                                                <small class="d-block text-muted mt-2">
                                                    Slot terbatas — pilih sesi sekarang
                                                </small>
                                            </div>

                                        </div>
                                        @endif

                                    </div>

                                </div>
                                @endif
                                @endforeach

                            </div>
                            @endif
                            <!-- END PROMO -->

                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!--================== MODAL SESI REGULER ==================-->
<div class="modal fade" id="modalSesi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0"
            style="border-radius:15px;">

            <div class="modal-header"
                style="background:linear-gradient(to right,#ff3131,#ff914d);color:#fff;">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    Booking Sesi Konsultasi
                </h5>
                <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- INFO SESI -->
                <div class="info-sesi mb-4">
                    <div class="row">
                        <div class="col-4 text-muted small">Trainer</div>
                        <div class="col-8 fw-semibold" id="modalTrainer"></div>

                        <!-- PROMO ONLY -->
                        <div class="col-4 text-muted small promo-only d-none">Nama Promo</div>
                        <div class="col-8 fw-semibold promo-only d-none" id="modalPromo"></div>
                        <!-- END PROMO ONLY -->

                        <div class="col-4 text-muted small mt-1">Sesi</div>
                        <div class="col-8 fw-semibold mt-1" id="modalSesiName"></div>

                        <div class="col-4 text-muted small mt-1">Jadwal</div>
                        <div class="col-8 fw-semibold mt-1" id="modalSesiTime"></div>
                    </div>
                </div>

                <hr>

                <!-- KODE & HARGA SESI -->
                <div class="info-sesi-modern mb-3 shadow-lg p-0" style="border-radius: 18px; overflow: hidden; box-shadow: 0 12px 28px rgba(0,0,0,0.08); background: linear-gradient(135deg, #ffe9e3, #fff4f0); font-family: 'Poppins', sans-serif;">

                    <!-- TOTAL / BIAYA -->
                    <div class="p-4 d-flex justify-content-between align-items-center"
                        style="background: linear-gradient(to right, #ff3131, #ff914d); color:#fff; border-radius: 18px 18px 0 0;">

                        <!-- Label -->
                        <div class="fw-semibold" style="font-size: 16px;">
                            Biaya yang harus dibayar
                        </div>

                        <!-- Total -->
                        <div class="fw-bold" style="font-size: 24px; color:#fff;">
                            <span id="modalTotalHeader">101.234</span>
                        </div>
                    </div>

                    <!-- ACCORDION DETAIL -->
                    <div class="accordion" id="accordionHarga">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                    aria-expanded="false" aria-controls="collapseOne"
                                    style="background: #fff; color:#333; font-weight:600;">
                                    Detail Pembayaran
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse"
                                aria-labelledby="headingOne" data-bs-parent="#accordionHarga">
                                <div class="accordion-body p-4" style="background:#fff8f7;">

                                    <div class="row g-3 align-items-center">
                                        <!-- Kode Booking -->
                                        <div class="col-5 d-flex align-items-center text-muted fw-medium">
                                            Kode Booking
                                        </div>
                                        <div class="col-7 fw-bold">
                                            <span id="modalKode" class="text-success" style="color: #fff;">CLS-ABC1234</span>
                                        </div>

                                        <!-- Harga -->
                                        <div class="col-5 d-flex align-items-center text-muted fw-medium">
                                            Harga
                                        </div>
                                        <div class="col-7 fw-semibold text-dark" id="modalHarga">Rp 100.000</div>

                                        <!-- Diskon -->
                                        <div class="col-5 d-flex align-items-center text-muted fw-medium">
                                            Diskon
                                        </div>
                                        <div class="col-7 fw-semibold text-dark" id="modalDiskon">Rp 100.000</div>

                                        <!-- PPN -->
                                        <div class="col-5 d-flex align-items-center text-muted fw-medium">
                                            PPN
                                        </div>
                                        <div class="col-7 fw-semibold text-dark" id="modalPpn">Rp 0</div>

                                        <!-- Kode Unik -->
                                        <div class="col-5 d-flex align-items-center text-muted fw-medium">
                                            Kode Unik
                                        </div>
                                        <div class="col-7 fw-semibold text-danger" id="modalKodeUnik">Rp 1.234</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END -->

                <!-- DISKON (REGULER ONLY) -->
                <div id="diskonWrapper"
                    class="info-sesi-modern mb-3 p-3 d-none"
                    style="border-radius:16px; background:linear-gradient(145deg,#ffffff,#fdf6f6); border:1px dashed #ff914d;">

                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-ticket-alt me-2 text-danger"></i>
                        <strong style="font-size:14px;">Punya Kode Diskon?</strong>
                    </div>

                    <div class="input-group">
                        <input type="text"
                            id="kodeDiskon"
                            class="form-control"
                            placeholder="Masukkan kode diskon"
                            style="border-radius:10px 0 0 10px;">

                        <button class="btn text-white"
                            id="btnCekDiskon"
                            type="button"
                            style="background:linear-gradient(to right,#ff3131,#ff914d);
                   border-radius:0 10px 10px 0;
                   font-weight:600;">
                            Gunakan
                        </button>
                    </div>

                    <small id="diskonInfo"
                        class="d-block mt-2 fw-semibold"
                        style="font-size:13px;"></small>
                </div>
                <!-- END DISKON -->

                <!-- METODE PEMBAYARAN -->
                <div class="info-sesi-modern mb-3 p-3" style="border-radius:16px; background:linear-gradient(145deg,#ffffff,#fdf6f6); border:1px dashed #ff914d;">

                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-money-bill-wave me-2 text-danger"></i>
                        <strong style="font-size:14px;">Metode Pembayaran</strong>
                    </div>

                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ asset('assets/img/bri.jpg') }}"
                            alt="BRI Image"
                            style="width:60px; height:auto; margin-right:12px;">
                        <h5 class="mb-0 fw-bold">Bank BRI</h5>
                    </div>

                    <hr style="border-top:1px dashed #ff914d;">

                    <p class="mb-2">
                        Nomor Rekening:
                        <span id="nomor-rekening"
                            style="font-weight:bold; letter-spacing:1px; font-size:15px;">
                            216401000467563
                        </span>
                    </p>

                    <p class="mb-3">
                        Atas Nama:
                        <b>Rumah Scopus Akademi</b>
                    </p>

                    <button onclick="copyToClipboard('nomor-rekening')"
                        class="btn btn-sm"
                        style="background:#ff914d; color:#fff; border-radius:20px; padding:6px 16px;">
                        <i class="fas fa-copy"></i> Salin Nomor Rekening
                    </button>
                </div>
                <!-- END METODE PEMBAYARAN -->

                <hr>

                <!-- FORM -->
                <form id="formSesi" class="form-sesi" novalidate>
                    <div class="row g-3">

                        <!-- Kendala -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Kendala <span class="text-danger">*</span>
                            </label>
                            <select name="kendala" id="kendala" class="form-control rounded-3" required>
                                <option value="">-- Pilih Kendala --</option>
                                @foreach($spesialis as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-msg"></small>
                        </div>

                        <!-- Penjelasan Kendala -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Jelaskan Kendala <span class="text-danger">*</span>
                            </label>
                            <textarea name="kendala_desc" id="kendala_desc" rows="3"
                                class="form-control rounded-3"
                                placeholder="Jelaskan secara singkat kendala atau pertanyaan Anda"
                                required></textarea>
                            <small class="text-danger error-msg"></small>
                        </div>

                        <!-- Tanggal Periode -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Booking Tanggal <span class="text-danger">*</span>
                            </label>
                            <select name="booking" id="booking" class="form-control rounded-3" required>
                                <option value="">-- Pilih Tanggal --</option>
                                @foreach($rangeTanggal as $date)
                                <option value="{{ $date->format('Y-m-d') }}">
                                    {{ $date->translatedFormat('l, d F Y') }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-msg"></small>
                        </div>

                        <!-- NAMA -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama" id="nama"
                                class="form-control rounded-3"
                                placeholder="Masukkan nama lengkap">
                            <small class="text-danger error-msg"></small>
                        </div>

                        <!-- AFILIASI -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Afiliasi <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="afiliasi" id="afiliasi"
                                class="form-control rounded-3"
                                placeholder="Universitas / Institusi">
                            <small class="text-danger error-msg"></small>
                        </div>

                        <!-- EMAIL -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" id="email"
                                class="form-control rounded-3"
                                placeholder="nama@email.com">
                            <small class="text-danger error-msg"></small>
                        </div>

                        <!-- WHATSAPP -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nomor WhatsApp <span class="text-danger">*</span>
                            </label>
                            <input type="tel" name="whatsapp" id="whatsapp"
                                class="form-control rounded-3"
                                placeholder="08xxxxxxxxxx"
                                oninput="formatPhoneNumber(this)">
                            <small class="text-danger error-msg"></small>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">
                    Batal
                </button>

                <button type="submit"
                    form="formSesi"
                    class="btn text-white"
                    style="background:linear-gradient(to right,#ff3131,#ff914d);
               border-radius:8px;
               padding:10px 20px;
               font-weight:600;">
                    <i class="fas fa-paper-plane"></i>
                    Kirim & Konsultasi
                </button>
            </div>

        </div>
    </div>
</div>
<!--================== END ==================-->

<!--================== SALIN NOMOR REKENING ==================-->
<script>
    function copyToClipboard(elementId) {
        const text = document.getElementById(elementId).innerText.trim();

        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Nomor rekening berhasil disalin',
                timer: 1500,
                showConfirmButton: false
            });
        }).catch(() => {
            // fallback browser lama
            const tempInput = document.createElement("input");
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Nomor rekening berhasil disalin',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }
</script>
<!--================== END ==================-->

<!--================== CEK SESI PENUH ATAU BELUM DARI RENTANG TANGGAL YANG ADA ==================-->
<script>
    const sesiPenuh = @json($sesiPenuh);
</script>
<!--================== END ==================-->

<!--================== MEMASTIKAN TIDAK BISA MEMILIH SESI & TANGGAL YANG SUDAH DI PESAN ==================-->
<script>
    const sesiTerpakai = @json($sesiTerpakai);

    let tipeDipilih = null; // reguler | promo
    let sesiDipilih = null;
    let bundlingSesiDipilih = [];

    // =====================
    // NORMALISASI SESI
    // =====================
    function normalizeSesi(sesi) {
        if (!sesi) return '';

        const match = sesi.match(/sesi\s*\d+/i);
        return match ?
            match[0].replace(/\s+/g, ' ').trim().replace(/^sesi/i, 'Sesi') :
            '';
    }

    // =====================
    // CLICK SESI / PROMO
    // =====================
    document.querySelectorAll('.sesi-clickable, [data-type="promo"]').forEach(el => {
        el.addEventListener('click', function(e) {

            // 🔒 HARD BLOCK kalau promo full
            if (this.classList.contains('promo-full') || this.disabled) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return;
            }

            tipeDipilih = this.dataset.type || 'reguler';

            // =====================
            // REGULER
            // =====================
            if (tipeDipilih === 'reguler') {
                sesiDipilih = normalizeSesi(this.dataset.sesi);
                bundlingSesiDipilih = [];
            }

            // =====================
            // PROMO BUNDLING
            // =====================
            if (tipeDipilih === 'promo') {
                sesiDipilih = null;

                // 🔥 AMBIL DARI data-sesi (BUKAN data-jadwal)
                const rawSesi = this.dataset.sesi || '';
                bundlingSesiDipilih = rawSesi
                    .split(',')
                    .map(s => `Sesi ${s.trim()}`)
                    .filter(Boolean);
            }

            updateTanggalBooking();
        });
    });

    // =====================
    // DISABLE TANGGAL
    // =====================
    function updateTanggalBooking() {
        const select = document.getElementById('booking');

        Array.from(select.options).forEach(option => {
            if (!option.value) return;

            const tanggal = option.value;

            const bookingTanggal = sesiTerpakai.filter(
                item => item.tanggal === tanggal
            );

            let bentrok = false;

            // =====================
            // REGULER
            // =====================
            if (tipeDipilih === 'reguler' && sesiDipilih) {
                bentrok = bookingTanggal.some(item => {
                    const sesiBooked = normalizeSesi(item.sesi);

                    if (item.tipe_promo === 'reguler') {
                        return sesiBooked === sesiDipilih;
                    }

                    if (item.tipe_promo === 'promo') {
                        const sesiPromo = item.sesi
                            .split(',')
                            .map(s => normalizeSesi(s));
                        return sesiPromo.includes(sesiDipilih);
                    }

                    return false;
                });
            }

            // =====================
            // PROMO BUNDLING
            // =====================
            if (tipeDipilih === 'promo' && bundlingSesiDipilih.length) {
                bentrok = bookingTanggal.some(item => {
                    const sesiTerbooking = item.sesi
                        .split(',')
                        .map(s => normalizeSesi(s))
                        .filter(Boolean);

                    // 🔥 SATU SESI SAMA = BLOK
                    return bundlingSesiDipilih.some(s =>
                        sesiTerbooking.includes(s)
                    );
                });
            }

            option.disabled = bentrok;
        });

        // reset kalau sudah kepilih tapi bentrok
        if (select.value && select.selectedOptions[0]?.disabled) {
            select.value = '';
        }

        updatePromoButtonState();
    }

    // =====================
    // DISABLED BUTTON
    // =====================
    function updatePromoButtonState() {
        const promoBtn = document.querySelector('[data-type="promo"].promo-btn');
        if (!promoBtn) return;

        const select = document.getElementById('booking');

        // hitung tanggal yang masih bisa dipilih
        const availableDates = Array.from(select.options).filter(opt =>
            opt.value && !opt.disabled
        );

        const promoText = promoBtn.querySelector('.promo-text');
        const promoIcon = promoBtn.querySelector('i');

        if (availableDates.length === 0) {
            // 🔴 PROMO FULL
            promoBtn.classList.add('promo-full', 'disabled');
            promoBtn.disabled = true;

            if (promoText) promoText.textContent = 'SESI PENUH';
            if (promoIcon) {
                promoIcon.classList.remove('fa-calendar-check');
                promoIcon.classList.add('fa-ban');
            }
        } else {
            // 🟢 PROMO AVAILABLE
            promoBtn.classList.remove('promo-full', 'disabled');
            promoBtn.disabled = false;

            if (promoText) promoText.textContent = 'Pesan Sekarang';
            if (promoIcon) {
                promoIcon.classList.remove('fa-ban');
                promoIcon.classList.add('fa-calendar-check');
            }
        }
    }
</script>
<!--================== END ==================-->

<!--================== MEMASTIKAN USER SUDAH LOGIN ==================-->
<script>
    const IS_LOGGED_IN = document.body.dataset.loggedIn === '1';
</script>

<!--================== END ==================-->

<!--================== MODAL SESI REGULER & PROMO BUNDLING ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modalElement = document.getElementById('modalSesi');
        const modal = new bootstrap.Modal(modalElement);

        const titleEl = document.getElementById('modalTitle');
        const trainerEl = document.getElementById('modalTrainer');
        const promoEl = document.getElementById('modalPromo');
        const sesiNameEl = document.getElementById('modalSesiName');
        const sesiTimeEl = document.getElementById('modalSesiTime');
        const promoFields = document.querySelectorAll('.promo-only');
        const diskonWrapper = document.getElementById('diskonWrapper');
        const diskonInfo = document.getElementById('diskonInfo');
        const kodeDiskonInput = document.getElementById('kodeDiskon');

        // =============================
        // GENERATE KODE BOOKING & KODE UNIK
        // =============================
        function randomAlphaNum(length) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }

        function generateKodeBooking() {
            return `CLS-${randomAlphaNum(6)}`;
        }

        function generateKodeUnik() {
            return Math.floor(Math.random() * (1500 - 500 + 1)) + 500;
        }

        function formatRupiah(number) {
            return 'Rp ' + number.toLocaleString('id-ID');
        }

        // =======================
        // NORMALISASI SESI REGULER
        // =======================
        function parseSesiReguler(rawSesi) {
            if (!rawSesi) return [];
            return rawSesi
                .split(',')
                .map(s => s.trim()) // reguler biasanya sudah ada "Sesi X"
                .filter(Boolean);
        }

        // =======================
        // NORMALISASI SESI PROMO BUNDLING
        // =======================
        function parseSesiPromo(rawSesi) {
            if (!rawSesi) return [];
            return rawSesi
                .split(',')
                .map(s => 'Sesi ' + s.trim()) // tambahkan prefix "Sesi "
                .filter(Boolean);
        }

        // =============================
        // HANDLER UNTUK SESI & PROMO
        // =============================
        document.querySelectorAll('.sesi-clickable, [data-type="promo"]').forEach(item => {
            item.addEventListener('click', function() {

                // BLOK JIKA PROMO SUDAH PENUH
                if (this.disabled || this.classList.contains('disabled')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Promo Tidak Tersedia',
                        text: 'Semua tanggal untuk promo bundling ini sudah penuh'
                    });
                    return;
                }

                // Pastikan pengguna sudah login
                if (!IS_LOGGED_IN) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Login Diperlukan',
                        text: 'Silakan login terlebih dahulu.',
                        confirmButtonText: 'Login Sekarang',
                        confirmButtonColor: '#ff914d',
                        showCancelButton: true,
                        cancelButtonText: 'Batal'
                    }).then(res => {
                        if (res.isConfirmed) window.location.href = '/login';
                    });
                    return;
                }

                const isPromo = item.dataset.type === 'promo';

                promoFields.forEach(el => el.classList.toggle('d-none', !isPromo));
                diskonWrapper.classList.toggle('d-none', isPromo);

                titleEl.textContent = isPromo ? 'Booking Promo Bundling' : 'Booking Sesi Konsultasi';
                trainerEl.textContent = item.dataset.trainer;
                promoEl.textContent = item.dataset.promo || '';

                // Untuk promo bundling, parsing sesi dan jadwal
                const sesi = isPromo ? parseSesiPromo(item.dataset.sesi) : parseSesiReguler(item.dataset.sesi);

                const jadwal = item.dataset.jadwal ?
                    item.dataset.jadwal.split(',').map(s => s.trim()) : [];

                // Set modal content
                document.getElementById('modalSesiName').textContent = sesi.length ? sesi.join(', ') : 'Sesi Tidak Tersedia';
                document.getElementById('modalSesiTime').textContent = jadwal.length ? jadwal.join(', ') : 'Jadwal Tidak Tersedia';

                // Atur data klinik dan sesi key untuk digunakan di form
                modalElement.dataset.klinikId = item.dataset.klinikId;
                modalElement.dataset.sesiKey = item.dataset.sesiKey;

                // GENERATE KODE BOOKING & KODE UNIK
                document.getElementById('modalKode').textContent = generateKodeBooking();
                const kodeUnik = generateKodeUnik();
                document.getElementById('modalKodeUnik').textContent = formatRupiah(kodeUnik);

                // HARGA & DISKON AWAL
                const harga = Number(item.dataset.harga) || 0;
                let diskon = Number(item.dataset.diskon) || 0; // diskon default 0
                const total = harga - diskon + kodeUnik;

                document.getElementById('modalHarga').textContent = formatRupiah(harga);
                document.getElementById('modalDiskon').textContent = formatRupiah(diskon);
                document.getElementById('modalTotalHeader').textContent = formatRupiah(total);

                // Ambil PPN dari database
                fetch('/cek-ppn-sesi/Clinik-Scopus')
                    .then(res => res.json())
                    .then(data => {
                        const ppnPersen = Number(data.ppn) || 0;
                        // Hitung PPN dari (harga - diskon)
                        const nominalPpn = Math.round((harga - diskon) * (ppnPersen / 100));
                        document.getElementById('modalPpn').textContent = formatRupiah(nominalPpn);

                        const totalBaru = (harga - diskon) + nominalPpn + kodeUnik;
                        document.getElementById('modalTotalHeader').textContent = formatRupiah(totalBaru);
                    });

                // Reset input kode diskon
                kodeDiskonInput.value = '';
                diskonInfo.textContent = '';

                modal.show();
            });
        });

        // =============================
        // CEK KODE DISKON (REGULER)
        // =============================
        document.getElementById('btnCekDiskon').addEventListener('click', function() {
            const kode = kodeDiskonInput.value.trim().toUpperCase();
            const harga = parseInt(document.getElementById('modalHarga').textContent.replace(/[^\d]/g, '')) || 0;
            const kodeUnik = parseInt(document.getElementById('modalKodeUnik').textContent.replace(/[^\d]/g, '')) || 0;
            const klinik_id = modalElement.dataset.klinikId;
            const sesi_key = modalElement.dataset.sesiKey;

            if (!kode) {
                diskonInfo.textContent = 'Masukkan kode diskon terlebih dahulu';
                diskonInfo.className = 'd-block mt-2 fw-semibold text-danger';
                return;
            }

            fetch('/cek-diskon-sesi/Clinik-Scopus', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        kode,
                        harga,
                        klinik_id,
                        sesi_key
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        diskonInfo.textContent = data.message;
                        diskonInfo.className = 'd-block mt-2 fw-semibold text-success';

                        // tampilkan nominal diskon & total baru
                        document.getElementById('modalDiskon').textContent = formatRupiah(data.potongan_rupiah);
                        document.getElementById('modalTotalHeader').textContent = formatRupiah(data.totalBaru + kodeUnik); // tambahkan kode unik

                        // <!--====== HITUNG ULANG PPN SETELAH DISKON ======-->
                        fetch('/cek-ppn-sesi/Clinik-Scopus')
                            .then(res => res.json())
                            .then(ppnData => {
                                const ppnPersen = Number(ppnData.ppn) || 0;

                                const hargaSetelahDiskon = data.totalBaru;
                                const nominalPpnBaru = Math.round(hargaSetelahDiskon * (ppnPersen / 100));

                                document.getElementById('modalPpn').textContent = formatRupiah(nominalPpnBaru);

                                document.getElementById('modalTotalHeader').textContent =
                                    formatRupiah(hargaSetelahDiskon + nominalPpnBaru + kodeUnik);
                            });
                        // <!--====== END ======-->

                    } else {
                        diskonInfo.textContent = data.message;
                        diskonInfo.className = 'd-block mt-2 fw-semibold text-danger';

                        // reset diskon ke 0
                        document.getElementById('modalDiskon').textContent = formatRupiah(0);

                        // HITUNG ULANG PPN (MESKIPUN KODE SALAH)
                        fetch('/cek-ppn-sesi/Clinik-Scopus')
                            .then(res => res.json())
                            .then(ppnData => {
                                const ppnPersen = Number(ppnData.ppn) || 0;

                                const nominalPpn = Math.round(harga * (ppnPersen / 100));

                                document.getElementById('modalPpn').textContent = formatRupiah(nominalPpn);

                                const totalFinal = harga + nominalPpn + kodeUnik;
                                document.getElementById('modalTotalHeader').textContent = formatRupiah(totalFinal);
                            });
                    }

                });
        });

        // =============================
        // VALIDASI FORM
        // =============================
        document.getElementById('formSesi').addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            const showError = (input, message) => {
                const error = input.parentElement.querySelector('.error-msg');
                error.innerText = message;
                input.classList.add('is-invalid');
                isValid = false;
            };
            const clearError = (input) => {
                const error = input.parentElement.querySelector('.error-msg');
                error.innerText = '';
                input.classList.remove('is-invalid');
            };

            const kendala = document.getElementById('kendala');
            const kendalaDesc = document.getElementById('kendala_desc');
            const booking = document.getElementById('booking');
            const nama = document.getElementById('nama');
            const afiliasi = document.getElementById('afiliasi');
            const email = document.getElementById('email');
            const whatsapp = document.getElementById('whatsapp');

            [kendala, kendalaDesc, booking, nama, afiliasi, email, whatsapp].forEach(clearError);

            if (!kendala.value) showError(kendala, 'Kendala wajib dipilih');
            if (!kendalaDesc.value.trim() || kendalaDesc.value.trim().length < 5)
                showError(kendalaDesc, 'Jelaskan kendala minimal 5 karakter');
            if (!booking.value) showError(booking, 'Tanggal booking wajib dipilih');

            if (nama.value.trim().length < 3) showError(nama, 'Nama minimal 3 karakter');
            if (afiliasi.value.trim().length < 5) showError(afiliasi, 'Afiliasi minimal 5 karakter');

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) showError(email, 'Format email tidak valid');

            let phone = whatsapp.value.trim();
            let phoneForValidation = phone.replace(/-/g, '');
            if (!/^\+?[0-9]+$/.test(phoneForValidation))
                showError(whatsapp, 'Nomor WA hanya boleh angka dan + di depan');

            const digits = phoneForValidation.replace(/\D/g, '');
            if (digits.length < 8 || digits.length > 15)
                showError(whatsapp, 'Nomor WA harus 8–15 digit');

            if (isValid) {

                const payload = {
                    klinik_id: modalElement.dataset.klinikId,
                    sesi: sesiNameEl.textContent,
                    jam_sesi: sesiTimeEl.textContent,
                    kendala: kendala.value,
                    kendala_desc: kendalaDesc.value,
                    booking: booking.value,
                    nama: nama.value,
                    afiliasi: afiliasi.value,
                    email: email.value,
                    whatsapp: whatsapp.value,
                    harga: parseInt(document.getElementById('modalHarga').textContent.replace(/[^\d]/g, '')),
                    diskon: parseInt(document.getElementById('modalDiskon').textContent.replace(/[^\d]/g, '')),
                    ppn: parseInt(document.getElementById('modalPpn').textContent.replace(/[^\d]/g, '')),
                    kode_unik: parseInt(document.getElementById('modalKodeUnik').textContent.replace(/[^\d]/g, '')),
                    total: parseInt(document.getElementById('modalTotalHeader').textContent.replace(/[^\d]/g, '')),
                    kode_booking: document.getElementById('modalKode').textContent,
                    kode_diskon: kodeDiskonInput.value || null,
                    tipe_promo: promoEl.textContent ? 'promo' : 'reguler'
                };

                fetch('/Clinik-Scopus/Pemesanan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(async response => {
                        const data = await response.json();
                        return {
                            status: response.status,
                            body: data
                        };
                    })
                    .then(res => {

                        // =============================
                        // SUCCESS
                        // =============================
                        if (res.status === 200 && res.body.success) {
                            const idPemesanan = res.body.id_pemesanan;

                            Swal.fire({
                                icon: 'success',
                                title: res.body.title ?? 'Berhasil',
                                text: res.body.message,
                                confirmButtonColor: '#ff914d'
                            }).then(() => {
                                showUploadBuktiModal(idPemesanan);
                            });

                            return;
                        }

                        // =============================
                        // BENTROK (409)
                        // =============================
                        if (res.status === 409 && res.body.type === 'bentrok') {
                            Swal.fire({
                                icon: 'warning',
                                title: res.body.title,
                                text: res.body.message,
                                confirmButtonText: 'Pilih Sesi Lain',
                                confirmButtonColor: '#ff914d',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                            return;
                        }

                        // =============================
                        // ERROR LAIN
                        // =============================
                        Swal.fire({
                            icon: 'error',
                            title: res.body.title || 'Gagal',
                            text: res.body.message || 'Terjadi kesalahan'
                        });
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Bermasalah',
                            text: 'Tidak dapat terhubung ke server'
                        });
                    });
            }
        });

        // =============================
        // FORMAT NOMOR WA OTOMATIS
        // =============================
        document.getElementById('whatsapp').addEventListener('input', function() {
            let val = this.value.trim();
            let prefix = '';
            if (val.startsWith('+')) {
                prefix = '+';
                val = val.slice(1);
            }
            val = val.replace(/\D/g, '');
            if (val.length <= 4) {
                // do nothing
            } else if (val.length <= 8) {
                val = val.replace(/(\d{4})(\d+)/, '$1-$2');
            } else if (val.length <= 12) {
                val = val.replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3');
            } else {
                val = val.replace(/(\d{4})(\d{4})(\d{4})(\d+)/, '$1-$2-$3-$4');
            }
            this.value = prefix + val;
        });
    });
</script>
<!--================== END ==================-->

<!--================== PROMO ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.countdown').forEach(function(el) {

            const startTime = new Date(el.dataset.start).getTime();
            const endTime = new Date(el.dataset.end).getTime();

            const daysEl = el.querySelector('.cd-days');
            const hoursEl = el.querySelector('.cd-hours');
            const minutesEl = el.querySelector('.cd-minutes');
            const secondsEl = el.querySelector('.cd-seconds');

            const update = () => {
                const now = new Date().getTime();

                // 🔴 PROMO BELUM MULAI
                if (now < startTime) {
                    let diff = startTime - now;

                    render(diff);
                    el.classList.add('text-warning');
                    return;
                }

                // 🟢 PROMO SEDANG BERJALAN
                if (now >= startTime && now <= endTime) {
                    let diff = endTime - now;

                    render(diff);
                    el.classList.remove('text-warning');
                    return;
                }

                // ⚫ PROMO SUDAH BERAKHIR
                el.innerHTML = `
                <span class="fw-bold text-danger">
                    Promo Berakhir
                </span>
            `;
            };

            const render = (distance) => {
                daysEl.textContent = Math.floor(distance / (1000 * 60 * 60 * 24));
                hoursEl.textContent = Math.floor((distance / (1000 * 60 * 60)) % 24);
                minutesEl.textContent = Math.floor((distance / (1000 * 60)) % 60);
                secondsEl.textContent = Math.floor((distance / 1000) % 60);
            };

            update();
            setInterval(update, 1000);
        });

    });
</script>
<!--================== END ==================-->

<script>
    function showUploadBuktiModal(idPemesanan) {
        Swal.fire({
            width: 600,
            showConfirmButton: false,
            allowOutsideClick: false,
            html: `
            <h4 class="mb-3">Upload Bukti Pembayaran</h4>

            <input type="file"
                id="buktiPembayaran"
                class="form-control mb-2"
                accept="image/png, image/jpeg">

            <small class="text-muted d-block mb-3">
                Format: JPG, JPEG, PNG • Maks 2 MB
            </small>

            <button id="btnUploadBukti"
                class="btn w-100 text-white"
                style="background:linear-gradient(to right,#ff3131,#ff914d);">
                Upload Bukti
            </button>
        `,
            didOpen: () => {

                document.getElementById('btnUploadBukti').onclick = () => {

                    const inputFile = document.getElementById('buktiPembayaran');
                    const file = inputFile.files[0];

                    /* ================= VALIDASI ================= */

                    // ❌ File kosong
                    if (!file) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Oops...',
                            text: 'Silakan pilih bukti pembayaran terlebih dahulu'
                        }).then(() => {
                            showUploadBuktiModal(idPemesanan);
                        });
                        return;
                    }

                    // ❌ Format file
                    const allowedTypes = ['image/jpeg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format Tidak Didukung',
                            text: 'Gunakan gambar JPG, JPEG, atau PNG'
                        }).then(() => {
                            inputFile.value = '';
                            showUploadBuktiModal(idPemesanan);
                        });
                        return;
                    }

                    // ❌ Ukuran file > 2MB
                    const maxSize = 2 * 1024 * 1024;
                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ukuran Terlalu Besar',
                            text: 'Ukuran gambar maksimal 2 MB'
                        }).then(() => {
                            inputFile.value = '';
                            showUploadBuktiModal(idPemesanan);
                        });
                        return;
                    }

                    /* ================= UPLOAD ================= */

                    const data = new FormData();
                    data.append('gambar', file);
                    data.append('id_pemesanan', idPemesanan);

                    Swal.fire({
                        title: 'Mengupload...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch("{{ route('public.ClinikScopusPemesanan.uploadBukti') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: data
                        })
                        .then(res => res.json())
                        .then(resp => {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: resp.message
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('Gagal', resp.message, 'error')
                                    .then(() => showUploadBuktiModal(idPemesanan));
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Terjadi kesalahan server', 'error')
                                .then(() => showUploadBuktiModal(idPemesanan));
                        });
                };
            }
        });
    }
</script>

@stop