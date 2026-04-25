@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.inputfitur')

@section('title')
Clinik Scopus Create Promo | MIS
@stop

<style>
    .card-neo {
        background: white;
        border-radius: var(--radius-xl);
        border: none;
        box-shadow: var(--shadow-soft);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .card-header-neo {
        padding: 20px 25px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fcfcfd;
    }

    .card-header-neo i {
        font-size: 18px;
        color: var(--accent);
    }

    .card-header-neo span {
        font-weight: 800;
        color: var(--text-dark);
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .form-control-modern[readonly] {
        background-color: #f1f5f9 !important;
        color: #0b0b0b !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }

    .input-group-modern:has(input[readonly]) {
        background-color: #f1f5f9 !important;
        cursor: not-allowed !important;
        border-color: #e2e8f0 !important;
    }

    .input-group-modern:has(input[readonly]):focus-within {
        border-color: #e2e8f0 !important;
        box-shadow: none !important;
        background-color: #f1f5f9 !important;
    }

    /* 🔹 Table Styling */
    .table-modern thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 700;
        padding: 15px;
        border: none;
    }

    .table-modern tbody td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f8fafc;
        font-size: 13px;
    }

    /* 🔹 Buttons */
    .btn-modern {
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        border: none;
    }

    .btn-primary-gradient {
        background: var(--accent-gradient);
        color: white;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.2);
    }

    .btn-primary-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(99, 102, 241, 0.3);
        color: white;
    }

    .alert-modern {
        background: linear-gradient(to right, #1e293b 0%, #6366f1 100%);
        border-left: 4px solid var(--accent);
        border-radius: 12px;
        color: #1e40af;
        font-weight: 600;
    }
</style>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header-modern">
            <div>
                <h1>Tambah Data Promo</h1>
                <p class="text-muted font-weight-bold mb-0 small">Buat kampanye diskon baru dan atur jadwal sesi secara otomatis.</p>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('account.Clinik-Scopus-Promo.store') }}" method="POST">
                @csrf

                <div class="card-neo">
                    <div class="card-header-neo">
                        <i class="fas fa-edit"></i>
                        <span>Informasi Utama Promo</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Promo <span class="badge-required">*</span></label>
                                    <input type="text" name="nama_promo" class="form-control-modern" placeholder="Contoh: Promo Ramadhan" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status Promo <span class="badge-required">*</span></label>
                                    <select name="status" class="form-control-modern" style="height: auto;" required>
                                        <option value="" disabled selected>-- PILIH STATUS --</option>
                                        <option value="active">Active</option>
                                        <option value="non active">Non Active</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai <span class="badge-required">*</span></label>
                                    <input type="datetime-local" name="tanggal_mulai_promo" class="form-control-modern" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Selesai <span class="badge-required">*</span></label>
                                    <input type="datetime-local" name="tanggal_selesai_promo" class="form-control-modern" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-neo">
                    <div class="card-header-neo">
                        <i class="fas fa-calculator"></i>
                        <span>Konfigurasi Biaya & Diskon</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @php
                            $firstEvent = $events->first();
                            $hargaNormal = $firstEvent && $firstEvent->biayaPersesi ? $firstEvent->biayaPersesi->biaya_persesi : 0;
                            @endphp
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Harga Normal (Akumulasi Sesi)</label>
                                    <div class="input-group">
                                        <span class="modern-prefix">Rp</span>
                                        <input type="text" name="harga_normal" id="harga_normal" value="{{ number_format($hargaNormal, 0, ',', '.') }}" class="form-control-modern" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Diskon <span class="badge-required">*</span></label>
                                    <select class="form-control-modern" name="tipe_diskon" id="tipe_diskon" style="height: auto;" onchange="handleDiskonTypeChange()">
                                        <option value="" disabled selected>-- PILIH TIPE DISKON --</option>
                                        <option value="persentase">PERSENTASE (%)</option>
                                        <option value="nominal">NOMINAL (Rp)</option>
                                        <option value="bundling">BUNDLING</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Diskon Persentase</label>
                                    <div class="input-group-modern">
                                        <input type="number" name="diskon_persentase" id="diskon_persentase" placeholder="0" class="form-control-modern form-control-ppn" disabled oninput="updateNominalDiskon()">
                                        <span class="input-suffix">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nominal Potongan</label>
                                    <div class="input-group">
                                        <span class="modern-prefix">Rp</span>
                                        <input type="text" name="nominal_diskon" id="nominal_diskon" placeholder="0" class="form-control-modern">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Kupon</label>
                                    <input type="text" name="kode_diskon" id="kode_diskon" placeholder="CONTOH: HEMAT77" class="form-control-modern">
                                </div>
                            </div>
                        </div>

                        <div class="p-4" style="background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <span class="d-block font-weight-bold text-dark">TOTAL BIAYA AKHIR</span>
                                    <small class="text-muted">Estimasi biaya yang dibayarkan peserta.</small>
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="input-group">
                                        <span class="modern-prefix">Rp</span>
                                        <input type="text" name="total_biaya" id="total_biaya" class="form-control-modern text-left" readonly
                                            style="font-size: 20px; font-weight: 800; color: var(--accent); background: white !important; border: 1.5px solid var(--accent);">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-neo" id="card-bundling" style="display:none;">
                    <div class="card-header-neo">
                        <i class="fas fa-tasks"></i>
                        <span>Pilih Target Event & Sesi</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-4">
                            <div class="alert alert-modern mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                Centang event dan pilih sesi yang ingin diikutkan dalam promo ini.
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th width="60" class="text-center">CEK</th>
                                        <th>DETAIL EVENT</th>
                                        <th>TRAINER</th>
                                        <th>SESI TERSEDIA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($events as $event)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="event-checkbox" data-event="{{ $event->id }}" data-harga="{{ $event->biayaPersesi->biaya_persesi ?? 0 }}" name="clinikscopus_ids[]" value="{{ $event->id }}">
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-dark">{{ $event->tanggal_online->format('d M Y') }}</span>
                                            <div class="text-muted x-small">Sampai: {{ $event->tanggal_offline->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold" style="font-size: 13px;">{{ $event->user->full_name }}</div>
                                        </td>
                                        <td>
                                            @php
                                            $sesiList = [1=>$event->sesi, 2=>$event->sesi2, 3=>$event->sesi3, 4=>$event->sesi4, 5=>$event->sesi5, 6=>$event->sesi6, 7=>$event->sesi7, 8=>$event->sesi8, 9=>$event->sesi9];
                                            @endphp
                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse($sesiList as $nomorSesi => $jam)
                                                @if(!empty($jam))
                                                <div class="mr-3 mb-1">
                                                    <input type="checkbox" class="sesi-checkbox sesi-{{ $event->id }}" data-event="{{ $event->id }}" name="sesi_promo[{{ $event->id }}][]" value="{{ $nomorSesi }}" id="s-{{ $event->id }}-{{ $nomorSesi }}">
                                                    <label for="s-{{ $event->id }}-{{ $nomorSesi }}" class="ml-1 font-weight-bold text-muted small" style="cursor: pointer;">S{{ $nomorSesi }}</label>
                                                </div>
                                                @endif
                                                @empty
                                                <span class="text-muted x-small italic">Sesi tidak tersedia</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-md-nowrap flex-wrap gap-3 mt-5">
                    <button type="submit" class="btn-modern btn-save flex-grow-1">
                        <i class="fas fa-save"></i> SIMPAN DATA
                </div>
            </form>
        </div>
    </section>
</div>

<script>
    // 1. Helper Functions
    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '0';
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function toNumber(str) {
        if (!str) return 0;
        return Number(str.toString().replace(/\D/g, '')) || 0;
    }

    // Fungsi Global agar onchange="handleDiskonTypeChange()" di HTML bekerja
    function handleDiskonTypeChange() {
        const tipeSelect = document.getElementById('tipe_diskon');
        const tipe = tipeSelect.value;
        const persenField = document.getElementById('diskon_persentase');
        const nominalField = document.getElementById('nominal_diskon');
        const cardBundling = document.getElementById('card-bundling');
        const kodeDiskonField = document.getElementById('kode_diskon');

        // Kondisi jika belum memilih tipe
        if (!tipe) {
            if (cardBundling) cardBundling.style.display = 'none';
            persenField.disabled = true;
            persenField.readOnly = true;
            nominalField.readOnly = true;
            if (kodeDiskonField) kodeDiskonField.readOnly = true;
            return;
        }

        // Tampilkan card bundling jika ada tipe yang dipilih
        if (cardBundling) cardBundling.style.display = 'block';

        // Pengaturan Input berdasarkan tipe
        if (tipe === 'persentase') {
            // AKTIFKAN Persentase
            persenField.disabled = false;
            persenField.readOnly = false; // <-- Ini yang sebelumnya terlewat

            // MATIKAN Nominal
            nominalField.readOnly = true;

            if (kodeDiskonField) kodeDiskonField.readOnly = false;

        } else if (tipe === 'nominal') {
            // MATIKAN Persentase
            persenField.disabled = true;
            persenField.readOnly = true;
            persenField.value = '';

            // AKTIFKAN Nominal
            nominalField.readOnly = false;

            if (kodeDiskonField) kodeDiskonField.readOnly = false;

        } else if (tipe === 'bundling') {
            // MATIKAN Persentase
            persenField.disabled = true;
            persenField.readOnly = true;
            persenField.value = '';

            // AKTIFKAN Nominal (Karena bundling mengisi nominal secara manual/otomatis)
            nominalField.readOnly = false;

            if (kodeDiskonField) kodeDiskonField.readOnly = true;
        }

        // Trigger hitung ulang harga
        if (window.hitungHargaNormal) window.hitungHargaNormal();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const hargaNormalInput = document.getElementById('harga_normal');
        const tipeDiskonSelect = document.getElementById('tipe_diskon');
        const persenInput = document.getElementById('diskon_persentase');
        const nominalInput = document.getElementById('nominal_diskon');
        const totalBiayaInput = document.getElementById('total_biaya');

        // Simpan harga asli dari database
        const HARGA_DASAR_DB = toNumber(hargaNormalInput.value);

        // Fungsi Hitung Harga Normal (Global agar bisa dipanggil handleDiskonTypeChange)
        window.hitungHargaNormal = function() {
            const tipe = tipeDiskonSelect.value;
            let total = 0;

            if (tipe === 'bundling') {
                // 🔵 LOGIKA BUNDLING: Akumulasi harga sesi
                document.querySelectorAll('.sesi-checkbox:checked').forEach(sesi => {
                    const eventId = sesi.dataset.event;
                    const eventCheckbox = document.querySelector(`.event-checkbox[data-event="${eventId}"]`);
                    if (eventCheckbox) {
                        total += parseInt(eventCheckbox.dataset.harga || 0);
                    }
                });
            } else {
                // 🔴 LOGIKA PERSENTASE & NOMINAL: Harga Statis
                total = HARGA_DASAR_DB;
            }

            hargaNormalInput.value = formatRupiah(total);
            updateNominalDiskon();
        }

        // Fungsi Hitung Diskon
        window.updateNominalDiskon = function() {
            const hargaNormal = toNumber(hargaNormalInput.value);
            const tipe = tipeDiskonSelect.value;
            let nominalDiskon = 0;

            if (tipe === 'persentase') {
                const persen = parseFloat(persenInput.value) || 0;
                nominalDiskon = Math.floor(hargaNormal * persen / 100);
                nominalInput.value = formatRupiah(nominalDiskon);
            } else {
                nominalDiskon = toNumber(nominalInput.value);
            }

            // Safety: Diskon tak boleh lebih besar dari harga
            if (nominalDiskon > hargaNormal) {
                nominalDiskon = hargaNormal;
                nominalInput.value = formatRupiah(hargaNormal);
            }

            let akhir = hargaNormal - nominalDiskon;
            totalBiayaInput.value = formatRupiah(akhir < 0 ? 0 : akhir);
        }

        // Event Listener untuk input manual nominal
        nominalInput.addEventListener('input', function() {
            this.value = formatRupiah(toNumber(this.value));
            updateNominalDiskon();
        });

        // Event Listener untuk Ceklis
        document.querySelectorAll('.event-checkbox, .sesi-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.classList.contains('event-checkbox')) {
                    const eventId = this.dataset.event;
                    document.querySelectorAll('.sesi-' + eventId).forEach(s => s.checked = this.checked);
                }
                window.hitungHargaNormal();
            });
        });

        // Inisialisasi awal jika ada pilihan lama (misal saat edit atau reload)
        if (tipeDiskonSelect.value) {
            handleDiskonTypeChange();
        }
    });
</script>

@endsection