@extends('public.layout.header')

@section('title')
Form Pendaftaran Scopus Kafe | Rumah Scopus
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

<!--================== POSISI HEADER TEXT IN CARD ==================-->
<style>
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title {
        font-weight: bold;
        color: #696969;
        font-size: 20px;
        margin: 0;
    }

    .current-date {
        font-size: 16px;
        color: #696969;
    }
</style>
<!--================== END ==================-->

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="align-items-center mt-5">
    <div class="container">
        <div class="section-body">
            <form method="POST" action="{{ route('public.pendaftaranscopuskafe.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- DATA DIRI -->
                <div class="card" style="box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px;">
                    <h1 class="text-center mt-3" style="font-weight: bold; color: black;">
                        Form Pendaftaran Scopus Kafe
                    </h1>
                    <hr>
                    <div class="card-body" style="color: black;">
                        <div class="header-container">
                            <h1 class="header-title">Form Data Diri</h1>
                            <p id="currentDate" class="current-date"></p>
                        </div>

                        <script>
                            // Mendapatkan tanggal saat ini
                            const today = new Date();

                            // Mengambil elemen tanggal, bulan, dan tahun
                            const day = today.getDate();
                            const month = today.toLocaleString('default', {
                                month: 'long'
                            }); // Nama bulan
                            const year = today.getFullYear();

                            // Menampilkan hasil ke elemen HTML
                            document.getElementById('currentDate').textContent = `${day} ${month} ${year}`;
                        </script>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <div class="input-group">
                                        <input type="text" name="nama" id="nama" value="{{ old('nama') }}" placeholder="Masukkan Nama Lengkap" class="form-control" required>
                                    </div>
                                    @error('nama')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Reservasi</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_pemesanan" id="tanggal_pemesanan" value="{{ old('tanggal_pemesanan') }}" placeholder="Masukkan Tanggal Pemesanan" class="form-control" required>
                                    </div>
                                    @error('tanggal_pemesanan')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <div class="input-group">
                                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Masukkan Email Aktif" class="form-control" maxlength="100" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                    </div>
                                    @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Telp</label>
                                    <div class="input-group">
                                        <input type="text" name="telp" id="telp" value="{{ old('telp') }}" placeholder="Masukkan Telp Aktif" class="form-control" maxlength="20" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatPhoneNumber(this)" required>
                                    </div>
                                    @error('telp')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END -->

                <!-- PILIH SESI 1 -->
                <div class="card" style="box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px;">
                    <div class="card-body" style="color: black;">
                        <div class="header-container">
                            <h1 class="header-title mt-3 mb-3">Form Sesi</h1>
                            <button type="button" class="btn btn-info" id="AddformPertama" style="height: 40px; white-space: nowrap;">
                                <i class="fas fa-plus"></i> Tambah Sesi
                            </button>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi" id="sesi" required>
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1">SESI 1</option>
                                        <option value="sesi 2">SESI 2</option>
                                        <!-- <option value="sesi 3">SESI 3</option> -->
                                    </select>
                                    @error('sesi')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai" id="waktu_mulai" value="" placeholder="Waktu Mulai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Selesai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_selesai" id="waktu_selesai" value="" placeholder="Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" placeholder="Lokasi Scopus Kafe" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya" id="biaya" value="{{ old('biaya') }}" placeholder="Jumlah Biaya Per Sesi" class="form-control currency" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Unik Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="kode_unik_pembayaran" id="kodeUnik" value="{{ old('kode_unik_pembayaran') }}" placeholder="Kode Unik Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subtotal Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="subtotal_pembayaran" id="TotalPembayaran" value="{{ old('subtotal_pembayaran') }}" placeholder="Subtotal Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END SESI 1 -->

                <!-- PILIH SESI 2 -->
                <div class="card ScopusKafe-Card-Kedua" style="box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px; display: none;">
                    <div class="card-body" style="color: black;">
                        <div class="header-container">
                            <h1 class="header-title mt-3 mb-3">Form Sesi Kedua</h1>
                            <button type="button" class="btn btn-danger" id="RemoveformKedua" style="height: 40px; white-space: nowrap;">
                                <i class="fas fa-trash"></i> Hapus Sesi
                            </button>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi_kedua" id="sesi_kedua">
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1 kedua">SESI 1</option>
                                        <option value="sesi 2 kedua">SESI 2</option>
                                        <!-- <option value="sesi 3 kedua">SESI 3</option> -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai_kedua" id="waktu_mulai_kedua" value="" placeholder="Waktu Mulai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Selesai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_selesai_kedua" id="waktu_selesai_kedua" value="" placeholder="Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi_kedua" id="lokasi_kedua" value="{{ old('lokasi_kedua') }}" placeholder="Lokasi Scopus Kafe" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya_kedua" id="biaya_kedua" value="{{ old('biaya_kedua') }}" placeholder="Jumlah Biaya Per Sesi" class="form-control currency" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Unik Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="kode_unik_pembayaran_kedua" id="kodeUnik_kedua" value="{{ old('kode_unik_pembayaran_kedua') }}" placeholder="Kode Unik Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subtotal Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="subtotal_pembayaran_kedua" id="TotalPembayaran_kedua" value="{{ old('subtotal_pembayaran_kedua') }}" placeholder="Subtotal Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- END SESI 2 -->

                <!-- PILIH SESI 3 -->
                <div class="card ScopusKafe-Card-Ketiga" style="box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px; display: none;">
                    <div class="card-body" style="color: black;">
                        <div class="header-container">
                            <h1 class="header-title mt-3 mb-3">Form Sesi Ketiga</h1>
                            <button type="button" class="btn btn-danger" id="RemoveformKetiga" style="height: 40px; white-space: nowrap;">
                                <i class="fas fa-trash"></i> Hapus Sesi
                            </button>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi_ketiga" id="sesi_ketiga">
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1 ketiga">SESI 1</option>
                                        <option value="sesi 2 ketiga">SESI 2</option>
                                        <!-- <option value="sesi 3 ketiga">SESI 3</option> -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai_ketiga" id="waktu_mulai_ketiga" value="" placeholder="Waktu Mulai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Selesai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_selesai_ketiga" id="waktu_selesai_ketiga" value="" placeholder="Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi_ketiga" id="lokasi_ketiga" value="{{ old('lokasi_ketiga') }}" placeholder="Lokasi Scopus Kafe" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya_ketiga" id="biaya_ketiga" value="{{ old('biaya_ketiga') }}" placeholder="Jumlah Biaya Per Sesi" class="form-control currency" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Unik Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="kode_unik_pembayaran_ketiga" id="kodeUnik_ketiga" value="{{ old('kode_unik_pembayaran_ketiga') }}" placeholder="Kode Unik Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subtotal Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="subtotal_pembayaran_ketiga" id="TotalPembayaran_ketiga" value="{{ old('subtotal_pembayaran_ketiga') }}" placeholder="Subtotal Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END SESI 3 -->

                <div class="card" style="box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px;">
                    <div class="card-body" style="color: black;">

                        <!-- TOTAL KESELURUHAN -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body" style="color: black;">
                                        <h1 class="header-title mt-3 mb-3">Metode Pembayaran</h1>
                                        <div class="accordion" id="accordionExample">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingOne">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                        <img src="{{ asset('assets/img/bri.jpg') }}" alt="BRI Image" style="width: 50px; height: 30px; margin-right:10px;"> BRI
                                                    </button>
                                                </h2>
                                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <p class="card-text">Nomor Rekening : <b><span id="nomor-rekening">1381 0100 0107 564</span></b>
                                                            <button onclick="copyToClipboard('nomor-rekening')" class="btn btn-primary"><i class="fas fa-copy"></i></button>
                                                            <br>
                                                            Atas Nama : <b>Rumah Scopus Indonesia</b>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr style="width: 100%;">
                                        <h1 class="header-title mt-3 mb-3">Upload Bukti Pembayaran</h1>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group custom-file-upload" style="margin-top: -3px;">
                                                    <label>Bukti Pembayaran</label>
                                                    <div class="input-group">
                                                        <input type="file" name="gambar" id="gambar" class="inputfile" accept="image/*" required>
                                                        <label for="gambar" class="file-upload">
                                                            <i class="fas fa-cloud-upload-alt"></i> Choose Image
                                                        </label>
                                                        @error('gambar')
                                                        <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="image-preview-container">
                                                    <div id="imagePreview" class="image-preview"></div>
                                                    <span id="file-selected" style="color: black;"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <span style="text-align: center; font-size:21px; font-weight: bold;" class="mt-3">Total yang Harus Dibayarkan</span>
                                    <div class="card-body mt-3" style="color: black; display: flex; justify-content: space-between; align-items: center;">
                                        <label style="white-space: nowrap; font-weight: bold;">Subtotal :</label>
                                        <div style="display: flex; justify-content: flex-end; width: 100%;">
                                            <span style="margin-right: 5px;">Rp.</span>
                                            <span id="subtotalKeseluruhanPembayaran">0</span>
                                        </div>
                                    </div>
                                    <hr style="margin: 10px;">
                                    <div class="card-body" style="color: black; display: flex; justify-content: space-between; align-items: center;">
                                        <label style="white-space: nowrap; font-weight: bold;">Tax :</label>
                                        <div style="display: flex; justify-content: flex-end; width: 100%;">
                                            <span style="margin-right: 5px;">Rp.</span>
                                            <span>0</span>
                                        </div>
                                    </div>
                                    <hr style="margin: 10px;">
                                    <div class="card-body mb-3" style="color: black; display: flex; justify-content: space-between; align-items: center;">
                                        <label style="white-space: nowrap; font-weight: bold;">Total :</label>
                                        <div style="display: flex; justify-content: flex-end; width: 100%;">
                                            <span style="margin-right: 5px;">Rp.</span>
                                            <span id="totalKeseluruhanPembayaran">0</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="total_keseluruhan_pembayaran" id="totalKeseluruhanPembayaranhidden" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- END TOTAL KESELURUHAN -->

                        <div class="d-flex">
                            <button class="btn btn-primary btn-submit" type="submit" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-paper-plane"></i> DAFTAR SEKARANG</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</section>

<!--================== MENAMPILKAN WAKTU SESI OTOMATIS ==================-->
<script>
    // Format number to Rupiah
    function formatRupiah(number) {
        return Number(number).toLocaleString('id-ID');
    }

    // Generate a random unique code
    function generateUniqueCode() {
        return Math.floor(Math.random() * 1000) + 1; // Random number between 1 and 1000
    }

    // Fungsi untuk menangani perubahan pada setiap form
    function handleFormChange(sesiSelect, waktuMulaiInput, waktuSelesaiInput, lokasiInput, biayaInput, kodeUnikInput, totalPembayaranInput, sesiMap, updateTotalKeseluruhan) {
        sesiSelect.addEventListener('change', (e) => {
            const selectedSesi = e.target.value;
            const biaya = 1000000; // Default biaya
            const kodeUnik = generateUniqueCode(); // Generate kode unik
            const totalPembayaran = biaya + kodeUnik;

            // Atur waktu dan lokasi berdasarkan sesi yang dipilih
            if (sesiMap[selectedSesi]) {
                waktuMulaiInput.value = sesiMap[selectedSesi].waktuMulai;
                waktuSelesaiInput.value = sesiMap[selectedSesi].waktuSelesai;
                lokasiInput.value = sesiMap[selectedSesi].lokasi;
            } else {
                waktuMulaiInput.value = '';
                waktuSelesaiInput.value = '';
                lokasiInput.value = '';
            }

            // Update biaya, kode unik, dan total pembayaran
            biayaInput.value = formatRupiah(biaya);
            kodeUnikInput.value = formatRupiah(kodeUnik);
            totalPembayaranInput.value = formatRupiah(totalPembayaran);

            // Update total keseluruhan pembayaran
            updateSubTotalKeseluruhan();
            updateTotalKeseluruhan();
        });
    }

    // Fungsi untuk menghitung subtotal keseluruhan pembayaran dari semua form
    function updateSubTotalKeseluruhan() {
        const totalPembayaranPertama = parseInt(document.getElementById('TotalPembayaran').value.replace(/\D/g, '')) || 0;
        const totalPembayaranKedua = parseInt(document.getElementById('TotalPembayaran_kedua').value.replace(/\D/g, '')) || 0;
        const totalPembayaranKetiga = parseInt(document.getElementById('TotalPembayaran_ketiga').value.replace(/\D/g, '')) || 0;

        const subtotalKeseluruhan = totalPembayaranPertama + totalPembayaranKedua + totalPembayaranKetiga;

        // Tampilkan total keseluruhan di input
        document.getElementById('subtotalKeseluruhanPembayaran').textContent = `${formatRupiah(subtotalKeseluruhan)}`;
    }

    // Fungsi untuk menghitung total keseluruhan pembayaran dari semua form
    function updateTotalKeseluruhan() {
        const totalPembayaranPertama = parseInt(document.getElementById('TotalPembayaran').value.replace(/\D/g, '')) || 0;
        const totalPembayaranKedua = parseInt(document.getElementById('TotalPembayaran_kedua').value.replace(/\D/g, '')) || 0;
        const totalPembayaranKetiga = parseInt(document.getElementById('TotalPembayaran_ketiga').value.replace(/\D/g, '')) || 0;

        const totalKeseluruhan = totalPembayaranPertama + totalPembayaranKedua + totalPembayaranKetiga;

        // Tampilkan total keseluruhan di input
        document.getElementById('totalKeseluruhanPembayaran').textContent = `${formatRupiah(totalKeseluruhan)}`;
        document.getElementById('totalKeseluruhanPembayaranhidden').value = totalKeseluruhan;
    }

    // Data sesi untuk setiap form
    const sesiMapPertama = {
        'sesi 1': {
            waktuMulai: '08:00',
            waktuSelesai: '13:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 2': {
            waktuMulai: '13:00',
            waktuSelesai: '18:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        // 'sesi 3': {
        //     waktuMulai: '17:00',
        //     waktuSelesai: '21:00',
        //     lokasi: 'Rumah Scopus Pusat'
        // }
    };

    const sesiMapKedua = {
        'sesi 1': {
            waktuMulai: '08:00',
            waktuSelesai: '13:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 2': {
            waktuMulai: '13:00',
            waktuSelesai: '18:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        // 'sesi 3': {
        //     waktuMulai: '17:00',
        //     waktuSelesai: '21:00',
        //     lokasi: 'Rumah Scopus Pusat'
        // }
    };

    const sesiMapKetiga = {
        'sesi 1': {
            waktuMulai: '08:00',
            waktuSelesai: '13:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 2': {
            waktuMulai: '13:00',
            waktuSelesai: '18:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        // 'sesi 3': {
        //     waktuMulai: '17:00',
        //     waktuSelesai: '21:00',
        //     lokasi: 'Rumah Scopus Pusat'
        // }
    };

    // Inisialisasi form pertama
    handleFormChange(
        document.getElementById('sesi'),
        document.getElementById('waktu_mulai'),
        document.getElementById('waktu_selesai'),
        document.getElementById('lokasi'),
        document.getElementById('biaya'),
        document.getElementById('kodeUnik'),
        document.getElementById('TotalPembayaran'),
        sesiMapPertama,
        updateTotalKeseluruhan
    );

    // Inisialisasi form kedua
    handleFormChange(
        document.getElementById('sesi_kedua'),
        document.getElementById('waktu_mulai_kedua'),
        document.getElementById('waktu_selesai_kedua'),
        document.getElementById('lokasi_kedua'),
        document.getElementById('biaya_kedua'),
        document.getElementById('kodeUnik_kedua'),
        document.getElementById('TotalPembayaran_kedua'),
        sesiMapKedua,
        updateTotalKeseluruhan
    );

    // Inisialisasi form ketiga
    handleFormChange(
        document.getElementById('sesi_ketiga'),
        document.getElementById('waktu_mulai_ketiga'),
        document.getElementById('waktu_selesai_ketiga'),
        document.getElementById('lokasi_ketiga'),
        document.getElementById('biaya_ketiga'),
        document.getElementById('kodeUnik_ketiga'),
        document.getElementById('TotalPembayaran_ketiga'),
        sesiMapKetiga,
        updateTotalKeseluruhan
    );
</script>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default image on page load
        var output = document.getElementById('imagePreview');
        output.innerHTML = `<img src="{{ asset('assets/img/meme/no-image.jpg') }}" alt="No Image" style="max-width: 100%; height: auto;">`;
    });

    document.getElementById('gambar').addEventListener('change', function(event) {
        var fileInput = event.target;
        var file = fileInput.files[0];
        var fileName = file.name;
        var fileSize = (file.size / 1024).toFixed(2); // in KB
        var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

        // Validate file type
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Hanya file PNG, JPEG, dan JPG yang diizinkan. Harap pilih jenis file yang valid.'
            });
            resetImagePreview();
            return;
        }

        // Validate file size (max 2MB)
        if (fileSize > 2000) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Ukuran file melebihi batas maksimum 2MB. Harap pilih file yang lebih kecil.'
            });
            resetImagePreview();
            return;
        }

        // Display selected file name and size
        document.getElementById('file-selected').innerHTML = fileName + ' (' + fileSize + ' KB)';

        // Display image preview
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('imagePreview');
            output.innerHTML = `<img src="${reader.result}" alt="Selected Image" style="max-width: 100%; height: auto;">`;
        };
        reader.readAsDataURL(file);
    });

    // Reset image preview if file is invalid or cleared
    function resetImagePreview() {
        var output = document.getElementById('imagePreview');
        output.innerHTML = `<img src="{{ asset('assets/img/meme/no-image.jpg') }}" alt="No Image" style="max-width: 100%; height: auto;">`;
        document.getElementById('file-selected').innerHTML = ''; // Clear file name display
    }

    // Check if the image preview is set before submitting the form
    document.querySelector('form').addEventListener('submit', function(event) {
        var imagePreview = document.getElementById('imagePreview').innerHTML;
        if (imagePreview.includes('no-image.jpg')) { // Check if default image is still shown
            Swal.fire({
                icon: 'error',
                title: 'Gambar Tidak Dipilih',
                text: 'Harap unggah gambar sebelum mengirimkan formulir.'
            });
            event.preventDefault(); // Prevent form submission
        }
    });
</script>
<!--================== END ==================-->

<!--================== ADD AND REMOVE CARD FORM SCOPUS KAFE ==================-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        var ScopusKafeCounter = 0;

        $('#AddformPertama').on('click', function() {
            if (ScopusKafeCounter === 0) {
                $('.ScopusKafe-Card-Kedua').show();
                $('#RemoveformKedua').show();
            } else if (ScopusKafeCounter === 1) {
                $('.ScopusKafe-Card-Ketiga').show();
                $('#RemoveformKetiga').show();
                // Sembunyikan tombol AddformPertama setelah Anatomy-Card-Third ditampilkan
                $(this).hide();
            }
            ScopusKafeCounter++;
        });

        // Remove Anatomy-Card-Second
        $('#RemoveformKedua').on('click', function() {
            $('.ScopusKafe-Card-Kedua').hide();
            $('#RemoveformKedua').hide();
            ScopusKafeCounter--;

            // Tampilkan tombol AddformPertama jika kurang dari 2 cards yang tampil
            if (ScopusKafeCounter < 2) {
                $('#AddformPertama').show();
            }
        });

        // Remove Anatomy-Card-Third
        $('#RemoveformKetiga').on('click', function() {
            $('.ScopusKafe-Card-Ketiga').hide();
            $('#RemoveformKetiga').hide();
            ScopusKafeCounter--;

            // Tampilkan tombol AddformPertama jika kurang dari 2 cards yang tampil
            if (ScopusKafeCounter < 2) {
                $('#AddformPertama').show();
            }
        });

    });
</script>
<!--================== END ==================-->

<!--================== BUTTOM COPY ==================-->
<script>
    function copyToClipboard(elementId) {
        var tempInput = document.createElement("textarea");
        tempInput.value = document.getElementById(elementId).innerText;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);

        // Menggunakan SweetAlert2 untuk menampilkan pesan
        Swal.fire({
            icon: 'success',
            title: 'Nomor rekening berhasil disalin',
            text: 'Nomor rekening: ' + tempInput.value,
            showConfirmButton: true, // Menampilkan tombol konfirmasi
            confirmButtonText: 'OK', // Mengubah teks tombol konfirmasi
            confirmButtonColor: '#3085d6', // Warna tombol konfirmasi
        });
    }
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT JIKA BELUM UPLOAD BUKTI PEMBAYARAN ==================-->
<script>
    document.querySelector('.btn-submit').addEventListener('click', function(event) {
        // Get the file input element
        const fileInput = document.getElementById('gambar');

        // Check if the file input is empty
        if (!fileInput.files.length) {
            // Prevent form submission
            event.preventDefault();

            // Show SweetAlert warning
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Wajib upload bukti pembayaran sebelum melanjutkan.',
                confirmButtonText: 'OK'
            });
        }
    });
</script>
<!--================== END ==================-->

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