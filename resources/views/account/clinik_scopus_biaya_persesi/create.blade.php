@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.inputfitur')

@section('title')
Tambah Biaya Per Sesi | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header-modern">
            <div>
                <h1>Tambah Biaya Per Sesi</h1>
                <p class="text-muted font-weight-bold mb-0">Konfigurasi tarif dan administrasi layanan secara presisi.</p>
            </div>
        </div>

        <div class="section-body">
            <div class="card-neo">
                <div class="card-body p-5">
                    <form id="trainerForm" action="{{ route('account.Clinik-Scopus-Biaya-Persesi.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi <span class="badge-required">*</span></label>
                                    <div class="input-group">
                                        <span class="modern-prefix">Rp</span>
                                        <input type="text" id="biaya_persesi" name="biaya_persesi" class="form-control-modern" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PPN (%)</label>
                                    <div class="input-group-modern">
                                        <input type="number" id="ppn" name="ppn" placeholder="0" class="form-control-modern form-control-ppn">
                                        <span class="input-suffix">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status Layanan <span class="badge-required">*</span></label>
                                    <select class="form-control-modern" name="status" required>
                                        <option value="" disabled selected>-- PILIH STATUS --</option>
                                        <option value="active">Active</option>
                                        <option value="non active">Non Active</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-md-nowrap flex-wrap gap-3 mt-5">
                            <button type="submit" class="btn-modern btn-save flex-grow-1">
                                <i class="fas fa-save"></i> SIMPAN DATA
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // Format Mata Uang Rupiah Otomatis
    const biayaInput = document.getElementById('biaya_persesi');
    biayaInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value, 10).toLocaleString('id-ID');
            this.value = value;
        } else {
            this.value = '';
        }
    });
</script>
@stop