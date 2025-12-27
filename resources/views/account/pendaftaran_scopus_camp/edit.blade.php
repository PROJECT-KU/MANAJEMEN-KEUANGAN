@extends('layouts.account')

@section('title')
Update Pendaftaran Scopus Camp | MIS
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
            <h1>DATA PENDAFTARAN SCOPUS CAMP</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.pendaftaranscopuscamp.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <!--================== DETAIL BATCH ==================-->
                <div class="card">
                    <div class="d-flex justify-content-between align-items-center mt-3 px-4">
                        <h4 class="m-0">Detail Batch</h4>

                        <h4 class="m-0">
                            <i class="fas fa-receipt"></i>
                            ID TRANSAKSI : {{ $data->id_transaksi }}
                        </h4>
                    </div>
                    <hr>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Batch</label>
                                    <select name="scopus_camp_kategori_id" id="kategoriSelect" class="form-control select2">
                                        @foreach ($categories as $item)
                                        <option
                                            value="{{ $item->id }}"
                                            data-mulai="{{ \Carbon\Carbon::parse($item->mulai)->format('Y-m-d') }}"
                                            data-selesai="{{ \Carbon\Carbon::parse($item->selesai)->format('Y-m-d') }}"
                                            data-sisa_kuota="{{ $item->sisa_kuota }}"
                                            data-biaya="{{ $item->biaya }}"
                                            data-kode_diskon="{{ $item->kode_diskon }}"
                                            data-group_wa="{{ $item->group_wa }}"
                                            {{ $data->scopus_camp_kategori_id == $item->id ? 'selected' : '' }}>
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
                                        <input type="text" value="{{ $data->created_at ? \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') : '' }}" class="form-control" readonly>
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
                    <div class="d-flex justify-content-between align-items-center mt-3 px-4">
                        <h4 class="m-0">Detail Pendaftar</h4>
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
                    <div class="d-flex justify-content-between align-items-center mt-3 px-4">
                        <h4 class="m-0">Detail Pembayaran</h4>
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
                    <div class="d-flex justify-content-between align-items-center mt-3 px-4">
                        <h4 class="m-0">Detail Lainnya</h4>
                    </div>
                    <hr>

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
                                            src="{{ !empty($data->gambar) ? asset('ScopusCamp/' . basename($data->gambar)) : asset('ScopusCamp/no-image.jpg') }}"
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

                        <div class="mt-3">
                            <div class="d-flex flex-md-nowrap flex-wrap gap-2 mt-4">

                                <!-- Tombol Simpan -->
                                <button type="submit"
                                    class="btn btn-primary btn-submit rounded-pill w-100 w-md-auto mb-2 mb-md-0">
                                    <i class="fa fa-paper-plane"></i> SIMPAN
                                </button>

                                <!-- Tombol Kembali -->
                                <a href="{{ route('account.pendaftaranscopuscamp.index') }}"
                                    class="btn btn-warning btn-submit rounded-pill w-100 w-md-auto mb-2 mb-md-0">
                                    <i class="fa fa-undo"></i> KEMBALI
                                </a>

                            </div>
                        </div>

                    </div>
                </div>
                <!--================== END ==================-->
            </form>
        </div>
    </section>
</div>

<!--================== UBAH NAMA BATCH TOTAL PEMBAYARAN BERUBAH ==================-->
<script>
    $(document).ready(function() {
        function formatRupiah(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Fungsi hitung ulang total pembayaran
        function hitungTotal() {
            let jumlah_pendaftar = parseInt($("input[name='jumlah_pendaftar']").val().replace(/\./g, '')) || 0;
            let biaya = parseInt($("#biaya").val().replace(/\./g, '')) || 0;
            let ppn = parseInt($("input[name='ppn']").val().replace(/\./g, '')) || 0;
            let kode_unik = parseInt($("input[name='kode_unik']").val().replace(/\./g, '')) || 0;
            let nominal_diskon = parseInt($("#nominal_diskon").val().replace(/\./g, '')) || 0;

            let total = (jumlah_pendaftar * biaya) + ppn + kode_unik - nominal_diskon;

            $("#total_pembayaran").val(formatRupiah(total));
            $("#total_pembayaran_asli").val(total);
        }

        // Event saat pilih batch
        $("#kategoriSelect").on("change", function() {
            let selected = $(this).find(":selected");

            let biaya = selected.data("biaya");
            $("#biaya").val(formatRupiah(biaya));

            // Hitung ulang total pembayaran
            hitungTotal();
        });

        // Event jika diskon diubah manual
        $("#nominal_diskon").on("keyup", function() {
            hitungTotal();
        });
    });
</script>
<!--================== END ==================-->

<!--================== ZOOM IMAGE ==================-->
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
        const diskonInput = document.getElementById('nominal_diskon');
        const totalInput = document.getElementById('total_pembayaran');
        const totalAsliInput = document.getElementById('total_pembayaran_asli');

        // Format angka ke format Rupiah (tanpa simbol Rp)
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'decimal',
                minimumFractionDigits: 0
            }).format(angka);
        }

        // Fungsi update total pembayaran
        function updateTotal() {
            const totalAsli = parseInt(totalAsliInput.value);
            let diskonRaw = diskonInput.value.replace(/\./g, '').replace(/[^0-9]/g, '');
            let diskon = parseInt(diskonRaw || 0);
            let totalSetelahDiskon = Math.max(totalAsli - diskon, 0);

            totalInput.value = formatRupiah(totalSetelahDiskon);
        }

        // Format input diskon sambil mengetik
        diskonInput.addEventListener('input', function(e) {
            // Ambil angka mentah
            let value = e.target.value.replace(/\./g, '').replace(/[^0-9]/g, '');
            if (!value) value = '0';

            // Format ke Rupiah dan tampilkan kembali
            e.target.value = formatRupiah(value);

            // Update total pembayaran
            updateTotal();
        });
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
@stop