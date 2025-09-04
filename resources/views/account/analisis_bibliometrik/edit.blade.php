@extends('layouts.account')

@section('title')
Update Pendaftaran Analisis Bibliometrik | MIS
@stop

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<style>
    .custom-file-upload {
        position: relative;
        overflow: hidden;
        margin-top: 10px;
    }

    .inputfile {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }

    .file-upload {
        cursor: pointer;
        display: inline-block;
        padding: 10px 20px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .file-upload:hover {
        background-color: #0056b3;
    }

    #file-selected {
        display: block;
        margin-top: 5px;
        color: #888;
    }

    .image-preview-container {
        margin-top: 10px;
    }

    .image-preview {
        max-width: 150px;
        max-height: 150px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 2px dashed orange;
        /* Added dashed white border */
        border-radius: 5px;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }
</style>
<!--================== END ==================-->

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DATA PENDAFTARAN ANALISIS BIBLIOMETRIK</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.analisisbibliometrik.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!--================== DETAIL BATCH ==================-->
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <h4 class="m-0" style="font-size: 20px;">DATA BATCH</h4>
                    </div>
                    <hr>
                    <div class="card-header-action mr-5">
                        <h4 class="float-right"><i class="fas fa-receipt"></i> ID TRANSAKSI : {{ $data->id_transaksi }}</h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Batch</label>
                                    <select name="categories_analisis_bibliometrik_id" id="kategoriSelect" class="form-control select2">
                                        @foreach ($categories as $item)
                                        <option
                                            value="{{ $item->id }}"
                                            data-mulai="{{ \Carbon\Carbon::parse($item->mulai)->format('Y-m-d') }}"
                                            data-selesai="{{ \Carbon\Carbon::parse($item->selesai)->format('Y-m-d') }}"
                                            data-sisa_kuota="{{ $item->sisa_kuota }}"
                                            data-biaya="{{ $item->biaya }}"
                                            data-kode_diskon="{{ $item->kode_diskon }}"
                                            data-group_wa="{{ $item->group_wa }}"
                                            {{ $data->categories_analisis_bibliometrik_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama }} #{{ $item->nama_ke }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Pemesanan</label>
                                    <div class="input-group">
                                        <input type="text" name="sisa_kuota" id="sisa_kuota" value="{{ $data->created_at ? \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') : '' }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Tanggal Mulai Pelaksanaan</label>
                                    <input type="text" class="form-control" id="mulai" name="mulai" readonly
                                        value="{{ $data->mulai ? \Carbon\Carbon::parse($data->mulai)->translatedFormat('d F Y') : '' }}">
                                </div>
                            </div>

                            <div class="col-md-2 text-center">
                                <label class="d-block mt-5">S/D</label>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Tanggal Selesai Pelaksanaan</label>
                                    <input type="text" class="form-control" id="selesai" name="selesai" readonly
                                        value="{{ $data->selesai ? \Carbon\Carbon::parse($data->selesai)->translatedFormat('d F Y') : '' }}">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!--================== END ==================-->

                <!--================== DETAIL DATA PENDAFTARAN ==================-->
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <h4 class="m-0" style="font-size: 20px;">DATA PENDAFTAR</h4>
                    </div>
                    <hr>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Pendaftar</label>
                                    <div class="input-group">
                                        <input type="text" name="nama" value="{{ $data->nama }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <div class="input-group">
                                        <input type="text" name="email" value="{{ $data->email }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Affiliasi</label>
                                    <div class="input-group">
                                        <input type="text" name="affiliasi" value="{{ $data->affiliasi }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor WhatsApp</label>
                                    <div class="input-group">
                                        <input type="text" name="telp" value="{{ $data->telp }}" class="form-control" readonly>
                                        <div class="input-group-append">
                                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $data->telp)) }}"
                                                target="_blank"
                                                class="btn btn-success"
                                                title="Hubungi via WhatsApp">
                                                <i class="fab fa-whatsapp mt-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!--================== END ==================-->

                <!--================== DETAIL PEMBAYARAN ==================-->
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <h4 class="m-0" style="font-size: 20px;">DATA PEMBAYARAN</h4>
                    </div>
                    <hr>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jumlah Pendaftar</label>
                                    <div class="input-group">
                                        <input type="text" name="jumlah_pendaftar" value="{{ $data->jumlah_pendaftar }}" class="form-control" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">Orang</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya" id="biaya" value="{{ number_format($data->biaya, 0, ',', '.') }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>PPN</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="ppn" value="{{ number_format($data->ppn, 0, ',', '.') }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Unik Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="kode_unik" value="{{ number_format($data->kode_unik, 0, ',', '.') }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nominal Diskon</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" id="nominal_diskon" name="nominal_diskon" value="{{ number_format($data->nominal_diskon, 0, ',', '.') }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Total Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="total_pembayaran" id="total_pembayaran" class="form-control" value="{{ number_format($data->total_pembayaran, 0, ',', '.') }}" style=" font-weight: bold;" readonly>
                                        <input type="hidden" id="total_pembayaran_asli" value="{{ $data->total_pembayaran }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!--================== END ==================-->

                <!--================== DATA LAINNYA ==================-->
                <div class="card">
                    <div class="card-header">
                        <h4>DATA LAINNYA</h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status Pendaftaran</label>
                                    <select class="form-control" name="status" style="height: auto;">
                                        <option value="" disabled selected>-- PILIH STATUS PENDAFTARAN --</option>
                                        <option value="diproses" {{ $data->status == 'diproses' ? 'selected' : '' }}>Dalam Proses Pengecekan</option>
                                        <option value="Pendaftaran Diterima" {{ $data->status == 'Pendaftaran Diterima' ? 'selected' : '' }}>Pendaftaran Diterima</option>
                                        <option value="Pendaftaran Reschedule" {{ $data->status == 'Pendaftaran Reschedule' ? 'selected' : '' }}>Pendaftaran Reschedule</option>
                                        <option value="Pendaftaran Refund" {{ $data->status == 'Pendaftaran Refund' ? 'selected' : '' }}>Pendaftaran Refund</option>
                                        <option value="Pendaftaran Dibatalkan" {{ $data->status == 'Pendaftaran Dibatalkan' ? 'selected' : '' }}>Pendaftaran Dibatalkan</option>
                                        <option value="Pendaftaran Ditolak" {{ $data->status == 'Pendaftaran Ditolak' ? 'selected' : '' }}>Pendaftaran Ditolak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Reschedule</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_reschedule" value="{{ $data->tanggal_reschedule }}" placeholder="Masukkan Tanggal Reschedule" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Link Group WhatsApp</label>
                                    <div class="input-group">
                                        <input type="text" name="group_wa" id="group_wa" value="{{ $data->group_wa }}" placeholder="Masukkan Link Group WhatsApp" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1">Bukti Pembayaran</label>
                                <div class="image-preview-container mt-2">
                                    <div id="imagePreview" class="image-preview">
                                        <img
                                            id="clickableImage"
                                            src="{{ !empty($data->gambar) ? asset('bibliometrik/' . basename($data->gambar)) : asset('bibliometrik/no-image.jpg') }}"
                                            alt="Bukti Transaksi"
                                            style="max-width:100%; height:auto; border-radius: 5px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); cursor: zoom-in;">
                                    </div>
                                    <span id="file-selected" class="mt-1 d-block text-muted small">
                                        {{ !empty($data->gambar) ? basename($data->gambar) : 'no-image.jpg' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Zoom Overlay -->
                        <div id="zoomOverlay" style="display:none;">
                            <img id="zoomOverlayImg" src="" alt="Zoomed Image">
                        </div>
                        <!-- end -->

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <div class="input-group">
                                        <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control" style="width: 100%;">{{ $data->note }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex mt-3">
                            <button class="btn btn-primary mr-1 btn-submit rounded-pill" type="submit" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                            <a href="{{ route('account.kategori.index') }}">
                                <button class="btn btn-warning rounded-pill" style="flex: 1; height:40px; font-size: 15px;">
                                    <i class="fa fa-undo"></i> KEMBALI
                                </button>
                            </a>
                        </div>

                    </div>
                </div>
                <!--================== END ==================-->
            </form>
        </div>
    </section>
</div>

<!--================== ZOOM IMAGE ==================-->
{{-- Script Zoom --}}
<script>
    document.getElementById('clickableImage').addEventListener('click', function() {
        Swal.fire({
            imageUrl: this.src,
            imageAlt: 'Bukti Transaksi',
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            background: 'rgba(0,0,0,0.8)',
            customClass: {
                popup: 'p-0 rounded',
                image: 'rounded shadow-lg'
            }
        });
    });
</script>
<!--================== END ==================-->

<!--================== TOTAL PEMBAYARAN AKAN TERKURANG OTOMATIS JIKA DI KETIKAN NOMINAL DISKON ==================-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const kategoriSelect = document.getElementById("kategoriSelect");
        const totalInput = document.getElementById("total_pembayaran");
        const totalAsliInput = document.getElementById("total_pembayaran_asli");
        const diskonInput = document.getElementById("nominal_diskon");

        const biayaAwalInput = document.getElementById("biaya_awal");
        const refundInput = document.getElementById("refund");

        // Format angka ke format Rupiah (tanpa Rp)
        function formatRupiah(angka) {
            return new Intl.NumberFormat("id-ID", {
                style: "decimal",
                minimumFractionDigits: 0
            }).format(angka);
        }

        // Update total pembayaran (pakai diskon)
        function updateTotal() {
            const totalAsli = parseInt(totalAsliInput.value) || 0;
            let diskonRaw = diskonInput ? diskonInput.value.replace(/\./g, "").replace(/[^0-9]/g, "") : "0";
            let diskon = parseInt(diskonRaw || 0);
            let totalSetelahDiskon = Math.max(totalAsli - diskon, 0);

            totalInput.value = formatRupiah(totalSetelahDiskon);
        }

        // Event: ubah batch → update biaya + total + refund
        kategoriSelect.addEventListener("change", function() {
            let selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
            let biayaBaru = parseInt(selectedOption.getAttribute("data-biaya")) || 0;

            // Update hidden total asli
            totalAsliInput.value = biayaBaru;

            // Hitung refund
            let biayaAwal = parseInt(biayaAwalInput.value) || 0;
            let refund = Math.max(biayaAwal - biayaBaru, 0);
            refundInput.value = refund;

            if (refund > 0) {
                alert("Anda mendapatkan refund sebesar Rp " + formatRupiah(refund));
            }

            // Update tampilan total
            updateTotal();
        });

        // Event: input diskon → hitung ulang total
        if (diskonInput) {
            diskonInput.addEventListener("input", function(e) {
                let value = e.target.value.replace(/\./g, "").replace(/[^0-9]/g, "");
                if (!value) value = "0";

                e.target.value = formatRupiah(value);
                updateTotal();
            });
        }

        // Jalankan sekali saat pertama load
        updateTotal();
    });
</script>
<!--================== END ==================-->

<!--================== KETIKA UPDATE NAMA BATCH TANGGAL BATCH JUGA TERGANTI ==================-->
<script>
    $(document).ready(function() {
        $('#kategoriSelect').on('change', function() {
            let selectedOption = $(this).find('option:selected');
            let mulai = selectedOption.data('mulai');
            let selesai = selectedOption.data('selesai');
            let sisaKuota = selectedOption.data('sisa_kuota');
            let Biaya = selectedOption.data('biaya');
            let KodeDiskon = selectedOption.data('kode_diskon');
            let GroupWA = selectedOption.data('group_wa');

            const formatTanggal = (tanggalStr) => {
                const [year, month, day] = tanggalStr.split('-');
                const tanggal = new Date(year, month - 1, day);
                return new Intl.DateTimeFormat('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(tanggal);
            };

            const formatRupiah = (angka) => {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(angka);
            };

            if (mulai && selesai) {
                $('#mulai').val(formatTanggal(mulai));
                $('#selesai').val(formatTanggal(selesai));
            } else {
                $('#mulai').val('');
                $('#selesai').val('');
            }

            $('#sisa_kuota').val(sisaKuota ? sisaKuota : '');
            $('#biaya').val(Biaya ? formatRupiah(Biaya) : '');
            $('#kode_diskon').val(KodeDiskon ? KodeDiskon : '');
            $('#group_wa').val(GroupWA ? GroupWA : '');
        });
    });
</script>
<!--================== END ==================-->

<!--================== BUTTON LOADER, DATE PICKER & RESET BUTTON ==================-->
<script>
    /**
     * btn submit loader
     */
    $(".btn-submit").click(function() {
        $(".btn-submit").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-submit").removeClass('btn-progress');

        }, 1000);
    });

    /**
     * btn reset loader
     */
    $(".btn-reset").click(function() {
        $(".btn-reset").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-reset").removeClass('btn-progress');

        }, 500);
    })
</script>
<!--================== END ==================-->
@stop