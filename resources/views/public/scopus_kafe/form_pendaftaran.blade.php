@extends('public.layout.header')

@section('title')
Form MemePendaftaran | Rumah Scopus
@stop


@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero align-items-center mt-5">
    <div class="container">
        <div class="section-body">
            <div class="card" style="background: linear-gradient(to right, #ff3131, #ff914d); border-radius: 15px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px;">
                <h1 class="text-center mt-3" style="font-weight: bold; color: white;">
                    Form Pendaftaran Scopus Kafe
                </h1>
                <div class="card-body" style="color: white;">
                    <form action="{{ route('account.meme.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <div class="input-group">
                                        <input type="text" name="nama" id="nama" value="{{ old('nama') }}" placeholder="Masukkan Nama Lengkap" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Pemesanan</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_pemesanan" id="tanggal_pemesanan" value="{{ old('tanggal_pemesanan') }}" placeholder="Masukkan Tanggal Pemesanan" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi" id="sesi" required>
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1">SESI 1</option>
                                        <option value="sesi 2">SESI 2</option>
                                        <option value="sesi 3">SESI 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai" id="waktu_mulai" value="" placeholder="Masukkan Waktu Mulai Per Sesi" class="form-control" readonly>
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
                                        <input type="text" name="waktu_selesai" id="waktu_selesai" value="" placeholder="Masukkan Waktu Selesai Per Sesi" class="form-control" readonly>
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
                                    <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" placeholder="Masukkan Lokasi" class="form-control" readonly>
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
                                        <input type="text" name="biaya" id="biaya" value="{{ old('biaya') }}" placeholder="Masukkan Jumlah Biaya Per Sesi" class="form-control currency" readonly>
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

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Total Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="total_pembayaran" id="TotalPembayaran" value="{{ old('total_pembayaran') }}" placeholder="Total Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                    <div class="d-flex mt-3">
                        <button class="btn btn-primary btn-submit" type="submit" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-paper-plane"></i> DAFTAR SEKARANG</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!--================== MENAMPILKAN WAKTU SESI OTOMATIS ==================-->
<script>
    const sesiSelect = document.getElementById('sesi');
    const waktuMulaiInput = document.getElementById('waktu_mulai');
    const waktuSelesaiInput = document.getElementById('waktu_selesai');
    const lokasiInput = document.getElementById('lokasi');
    const biayaInput = document.getElementById('biaya');
    const kodeUnikInput = document.getElementById('kodeUnik');
    const totalPembayaranInput = document.getElementById('TotalPembayaran');

    // Format number to Rupiah
    function formatRupiah(number) {
        return Number(number).toLocaleString('id-ID');
    }

    // Generate a random unique code
    function generateUniqueCode() {
        return Math.floor(Math.random() * 1000) + 1; // Random number between 1 and 1000
    }

    // Event listener for session selection
    sesiSelect.addEventListener('change', (e) => {
        const selectedSesi = e.target.value;
        let biaya = 500000; // Default biaya
        let kodeUnik = generateUniqueCode(); // Generate kode unik
        let totalPembayaran; // Total pembayaran (biaya + kode unik)

        switch (selectedSesi) {
            case 'sesi 1':
                waktuMulaiInput.value = '08:00';
                waktuSelesaiInput.value = '12:00';
                lokasiInput.value = 'Rumah Scopus Pusat';
                break;
            case 'sesi 2':
                waktuMulaiInput.value = '12:30';
                waktuSelesaiInput.value = '16:30';
                lokasiInput.value = 'Rumah Scopus Pusat';
                break;
            case 'sesi 3':
                waktuMulaiInput.value = '17:00';
                waktuSelesaiInput.value = '21:00';
                lokasiInput.value = 'Rumah Scopus Pusat';
                break;
            default:
                waktuMulaiInput.value = '';
                waktuSelesaiInput.value = '';
                lokasiInput.value = '';
        }

        // Update biaya, kode unik, and total pembayaran
        biayaInput.value = formatRupiah(biaya);
        kodeUnikInput.value = formatRupiah(kodeUnik);
        totalPembayaran = biaya + kodeUnik;
        totalPembayaranInput.value = formatRupiah(totalPembayaran);
    });
</script>
<!--================== END ==================-->
@stop