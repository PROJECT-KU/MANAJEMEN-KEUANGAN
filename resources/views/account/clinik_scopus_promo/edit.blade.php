@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.inputfitur')

@section('title')
Clinik Scopus Edit Promo | MIS
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
                <h1>Update Data Promo</h1>
                <p class="text-muted font-weight-bold mb-0 small">Perbarui detail kampanye diskon dan jadwal event Anda.</p>
            </div>
        </div>

        <div class="section-body">
            <form method="POST" action="{{ route('account.Clinik-Scopus-Promo.update', $promo->id) }}">
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
                                    <label>Nama Promo </label>
                                    <input type="text" name="nama_promo" class="form-control-modern" value="{{ old('nama_promo', $promo->nama_promo) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status Promo</label>
                                    <select name="status" class="form-control-modern" style="height: auto;">
                                        <option value="active" {{ $promo->status=='active'?'selected':'' }}>Active</option>
                                        <option value="non active" {{ $promo->status=='non active'?'selected':'' }}>Non Active</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai</label>
                                    <input type="datetime-local" name="tanggal_mulai_promo" class="form-control-modern" value="{{ $promo->tanggal_mulai_promo }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Selesai</label>
                                    <input type="datetime-local" name="tanggal_selesai_promo" class="form-control-modern" value="{{ $promo->tanggal_selesai_promo }}">
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
                            $hargaDasarAsli = $firstEvent && $firstEvent->biayaPersesi ? $firstEvent->biayaPersesi->biaya_persesi : 0;
                            @endphp
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Harga Normal (Akumulasi Sesi)</label>
                                    <div class="input-group">
                                        <span class="modern-prefix">Rp</span>
                                        <input type="text" name="harga_normal" id="harga_normal" class="form-control-modern" value="{{ number_format($promo->harga_normal, 0, ',', '.') }}" data-harga-db="{{ $hargaDasarAsli }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Diskon</label>
                                    <select class="form-control-modern" name="tipe_diskon" id="tipe_diskon" style="height: auto;" onchange="handleDiskonTypeChange()">
                                        <option value="persentase" {{ $promo->tipe_diskon=='persentase'?'selected':'' }}>PERSENTASE (%)</option>
                                        <option value="nominal" {{ $promo->tipe_diskon=='nominal'?'selected':'' }}>NOMINAL (Rp)</option>
                                        <option value="bundling" {{ $promo->tipe_diskon=='bundling'?'selected':'' }}>BUNDLING</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Diskon Persentase</label>
                                    <div class="input-group-modern">
                                        <input type="number" name="diskon_persentase" id="diskon_persentase" placeholder="0" class="form-control-modern form-control-ppn" value="{{ $promo->diskon_persentase ?? 0 }}">
                                        <span class="input-suffix">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nominal Potongan</label>
                                    <div class="input-group">
                                        <span class="modern-prefix">Rp</span>
                                        <input type="text" name="nominal_diskon" id="nominal_diskon" class="form-control-modern" value="{{ number_format($promo->nominal_diskon, 0, ',', '.') }}" data-nominal-db="{{ $promo->nominal_diskon }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Kupon</label>
                                    <input type="text" name="kode_diskon" id="kode_diskon" class="form-control-modern" value="{{ $promo->kode_diskon ?? '-' }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-4" style="background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <span class="d-block font-weight-bold text-dark">TOTAL BIAYA AKHIR</span>
                                    <small class="text-muted">Estimasi biaya yang dibayarkan peserta.</small>
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="input-group">
                                        <span class="modern-prefix">Rp</span>
                                        <input type="text" name="total_biaya" class="form-control-modern text-left" readonly
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
                        <div class="table-responsive">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th width="60" class="text-center">CEK</th>
                                        <th>DETAIL EVENT</th>
                                        <th>TRAINER</th>
                                        <th>SESI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($events as $event)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="event-checkbox" data-event="{{ $event->id }}" name="clinikscopus_ids[]" value="{{ $event->id }}">
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-dark">{{ $event->tanggal_online->format('d M Y') }}</span>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold" style="font-size: 13px;">{{ $event->user->full_name }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-3">
                                                @php
                                                $sesiList = [1=>$event->sesi, 2=>$event->sesi2, 3=>$event->sesi3, 4=>$event->sesi4, 5=>$event->sesi5, 6=>$event->sesi6, 7=>$event->sesi7, 8=>$event->sesi8, 9=>$event->sesi9];
                                                @endphp
                                                @foreach($sesiList as $nomorSesi => $jam)
                                                @if(!empty($jam))
                                                @php $checked = isset($selectedSesi[$event->id][$nomorSesi]); @endphp
                                                <div class="mr-3">
                                                    <input type="checkbox" class="sesi-checkbox sesi-{{ $event->id }}"
                                                        data-event="{{ $event->id }}" data-harga="{{ $event->biayaPersesi->biaya_persesi ?? 0 }}"
                                                        name="sesi_promo[{{ $event->id }}][]" value="{{ $nomorSesi }}"
                                                        id="s-{{ $event->id }}-{{ $nomorSesi }}" {{ $checked ? 'checked' : '' }}>
                                                    <label for="s-{{ $event->id }}-{{ $nomorSesi }}" class="ml-1 small font-weight-bold">S{{ $nomorSesi }}</label>
                                                </div>
                                                @endif
                                                @endforeach
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
                    <button type="submit" class="btn-modern btn-update flex-grow-1">
                        <i class="fas fa-sync-alt"></i> UPDATE DATA
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
    // Helper Functions
    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '0';
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function toNumber(str) {
        if (!str) return 0;
        return Number(str.toString().replace(/\D/g, '')) || 0;
    }

    // Fungsi Global Toggle UI
    function handleDiskonTypeChange() {
        const tipeSelect = document.getElementById('tipe_diskon');
        const tipe = tipeSelect.value;
        const cardBundling = document.getElementById('card-bundling');
        const persenField = document.getElementById('diskon_persentase');
        const nominalField = document.getElementById('nominal_diskon');
        const kodeField = document.getElementById('kode_diskon');
        const hargaNormalInput = document.getElementById('harga_normal');

        // KONDISI JIKA BELUM MEMILIH TIPE (RESET TOTAL)
        if (!tipe) {
            if (cardBundling) cardBundling.style.display = 'none';
            persenField.disabled = true;
            persenField.readOnly = true;
            persenField.value = '';
            nominalField.readOnly = true;
            nominalField.value = '';
            if (kodeField) {
                kodeField.readOnly = true;
                kodeField.value = '';
            }
            return; // Berhenti di sini
        }

        // TAMPILKAN CARD BUNDLING JIKA TIPE DIPILIH
        if (cardBundling) cardBundling.style.display = 'block';

        // LOGIKA RESET DATA SAAT PINDAH DARI BUNDLING KE TIPE LAIN


        // PENGATURAN INPUT BERDASARKAN TIPE (STATE MANAGEMENT)
        if (tipe === 'persentase') {
            // Aktifkan Persentase
            persenField.disabled = false;
            persenField.readOnly = false;
            nominalField.readOnly = true;
            if (kodeField) kodeField.readOnly = false;

        } else if (tipe === 'nominal') {
            // Matikan Persentase
            persenField.disabled = true;
            persenField.readOnly = true;
            persenField.value = '';
            nominalField.readOnly = false;
            if (kodeField) kodeField.readOnly = false;

        } else if (tipe === 'bundling') {
            persenField.disabled = true;
            persenField.readOnly = true;
            persenField.value = '';
            nominalField.readOnly = false;
            if (kodeField) {
                kodeField.readOnly = true;
                kodeField.value = '';
            }
        }

        // TRIGGER HITUNG ULANG HARGA
        if (typeof hitungHargaNormal === "function") {
            hitungHargaNormal();
        }
    }

    // Pastikan fungsi dipanggil saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', function() {
        handleDiskonTypeChange();
    });

    // Logika Perhitungan
    function hitungHargaNormal() {
        const tipe = document.getElementById('tipe_diskon').value;
        const hargaNormalInput = document.getElementById('harga_normal');
        let total = 0;

        if (tipe === 'bundling') {
            document.querySelectorAll('.sesi-checkbox:checked').forEach(cb => {
                total += toNumber(cb.dataset.harga);
            });
        } else {
            total = toNumber(hargaNormalInput.dataset.hargaDb);
        }

        hargaNormalInput.value = formatRupiah(total);
        updateNominalDiskon();
    }

    function updateNominalDiskon() {
        const hargaNormal = toNumber(document.getElementById('harga_normal').value);
        const tipe = document.getElementById('tipe_diskon').value;
        const nominalInput = document.getElementById('nominal_diskon');
        let diskon = 0;

        if (tipe === 'persentase') {
            const persen = parseFloat(document.getElementById('diskon_persentase').value) || 0;
            diskon = Math.floor(hargaNormal * persen / 100);
            nominalInput.value = formatRupiah(diskon);
        } else {
            diskon = toNumber(nominalInput.value);
        }

        if (diskon > hargaNormal) {
            diskon = hargaNormal;
            nominalInput.value = formatRupiah(diskon);
        }

        const totalAkhir = hargaNormal - diskon;
        document.querySelector('[name="total_biaya"]').value = formatRupiah(totalAkhir < 0 ? 0 : totalAkhir);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Init Rupiah
        document.getElementById('nominal_diskon').addEventListener('input', function() {
            this.value = formatRupiah(toNumber(this.value));
            updateNominalDiskon();
        });

        document.getElementById('diskon_persentase').addEventListener('input', updateNominalDiskon);

        // Sync Checkboxes
        document.querySelectorAll('.event-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const eventId = this.dataset.event;
                document.querySelectorAll('.sesi-' + eventId).forEach(s => s.checked = this.checked);
                hitungHargaNormal();
            });
        });

        document.querySelectorAll('.sesi-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const eventId = this.dataset.event;
                const eventCb = document.querySelector(`.event-checkbox[data-event="${eventId}"]`);
                const allSesi = document.querySelectorAll('.sesi-' + eventId);
                const allChecked = Array.from(allSesi).every(s => s.checked);
                eventCb.checked = allChecked;
                hitungHargaNormal();
            });
        });

        // Trigger Auto Sync load
        document.querySelectorAll('.event-checkbox').forEach(cb => {
            const eventId = cb.dataset.event;
            const allSesi = document.querySelectorAll('.sesi-' + eventId);
            if (Array.from(allSesi).every(s => s.checked) && allSesi.length > 0) cb.checked = true;
        });

        handleDiskonTypeChange();
    });
</script>
@endsection