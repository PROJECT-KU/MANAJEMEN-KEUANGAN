@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.inputfitur')

@section('title')
Update Biaya Per Sesi | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header-modern">
            <div>
                <h1>Update Biaya Per Sesi</h1>
                <p class="text-muted font-weight-bold mb-0">Sesuaikan tarif layanan dan pengaturan PPN akun Anda.</p>
            </div>
        </div>

        <div class="section-body">
            <div class="card-neo">
                <div class="card-body p-5">
                    <form action="{{ route('account.Clinik-Scopus-Biaya-Persesi.update', $biayaPersesi->id) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <span class="modern-prefix">Rp</span>
                                        <input type="text" id="biaya_persesi" name="biaya_persesi" value="{{ $biayaPersesi->biaya_persesi }}" placeholder="Rp 0" class="form-control-modern">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PPN (%)</label>
                                    <div class="input-group-modern">
                                        <input type="number" id="ppn" name="ppn" value="{{ $biayaPersesi->ppn }}" placeholder="0" class="form-control-modern form-control-ppn">
                                        <span class="input-suffix">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status Layanan</label>
                                    <select class="form-control-modern" name="status">
                                        <option value="" disabled>-- PILIH STATUS --</option>
                                        <option value="active" {{ $biayaPersesi->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="non active" {{ $biayaPersesi->status == 'non active' ? 'selected' : '' }}>Non Active</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-md-nowrap flex-wrap gap-3 mt-5">
                            <button type="submit" class="btn-modern btn-update flex-grow-1">
                                <i class="fas fa-sync-alt"></i> UPDATE DATA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    const biayaInput = document.getElementById('biaya_persesi');

    function formatRupiah(angka) {
        let number = angka.replace(/\D/g, '');
        if (number) {
            return parseInt(number, 10).toLocaleString('id-ID');
        }
        return '';
    }

    biayaInput.addEventListener('input', function() {
        this.value = formatRupiah(this.value);
    });

    // Format saat pertama kali dimuat
    document.addEventListener('DOMContentLoaded', function() {
        if (biayaInput.value) {
            biayaInput.value = formatRupiah(biayaInput.value);
        }
    });

    // Bersihkan format saat submit agar hanya angka yang dikirim ke server
    document.querySelector('form').addEventListener('submit', function() {
        biayaInput.value = biayaInput.value.replace(/\D/g, '');
    });
</script>
@stop