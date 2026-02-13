@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Clinik Scopus Create Promo | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">

        <div class="section-header">
            <h1>TAMBAH DATA PROMO</h1>
        </div>

        <div class="section-body">

            <form action="{{ route('account.Clinik-Scopus-Promo.store') }}" method="POST">

                <div class="card">
                    <div class="card-body">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Nama Promo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nama_promo"
                                        class="form-control"
                                        placeholder="Contoh: Promo Awal Tahun"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status</label>
                                    <select name="status" class="form-control" style="height: auto;">
                                        <option value="" disabled selected>-- PILIH STATUS PROMO --</option>
                                        <option value="active">Active</option>
                                        <option value="non active">Non Active</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Tanggal Mulai <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local"
                                        name="tanggal_mulai_promo"
                                        class="form-control"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Tanggal Selesai <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local"
                                        name="tanggal_selesai_promo"
                                        class="form-control"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        <div class="row align-items-center">
                            @php
                            $firstEvent = $events->first();
                            $hargaNormal = $firstEvent && $firstEvent->biayaPersesi
                            ? $firstEvent->biayaPersesi->biaya_persesi
                            : 0;
                            @endphp
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Harga Normal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="harga_normal" id="harga_normal" value="{{ number_format($hargaNormal, 0, ',', '.') }}" placeholder="Masukkan Harga Normal" class="form-control" oninput="updateNominalDiskon()" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Diskon</label>
                                    <div class="input-group">
                                        <select class="form-control" name="tipe_diskon" id="tipe_diskon" style="height: auto;" onchange="handleDiskonTypeChange()">
                                            <option value="" disabled selected>-- PILIH TIPE DISKON --</option>
                                            <option value="persentase">PERSENTASE</option>
                                            <option value="nominal">NOMINAL</option>
                                            <option value="bundling">BUNDLING</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Diskon Persentase</label>
                                    <div class="input-group">
                                        <input type="number" name="diskon_persentase" id="diskon_persentase" value="{{ old('diskon_persentase') }}" placeholder="Masukkan Total Persentase" class="form-control" disabled oninput="updateNominalDiskon()">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nominal Diskon</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="nominal_diskon" id="nominal_diskon" value="{{ old('nominal_diskon') }}" placeholder="Masukkan Total Nominal Diskon" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Diskon</label>
                                    <div class="input-group">
                                        <input type="text" name="kode_diskon" id="kode_diskon" value="{{ old('kode_diskon') }}" placeholder="Masukkan Kode Diskon" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Total Biaya</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="total_biaya" id="total_biaya" value="{{ old('total_biaya') }}" placeholder="Masukkan Total Biaya" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        <div class="mt-4" id="card-bundling" style="display:none;">
                            <div class="alert alert-light">
                                <small>
                                    Promo akan mengikuti <b>jadwal sesi & trainer</b> dari event yang dipilih.
                                </small>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>Tanggal Active</th>
                                            <th>Trainer</th>
                                            <th>Sesi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($events as $event)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox"
                                                    class="event-checkbox"
                                                    data-event="{{ $event->id }}"
                                                    data-harga="{{ $event->biayaPersesi->biaya_persesi ?? 0 }}"
                                                    name="clinikscopus_ids[]"
                                                    value="{{ $event->id }}">
                                            </td>
                                            <td>{{ $event->tanggal_online->format('d M Y') }} - {{ $event->tanggal_offline->format('d M Y') }}</td>
                                            <td>{{ $event->user->full_name }}</td>
                                            <td>
                                                @php
                                                $sesiList = [
                                                1 => $event->sesi,
                                                2 => $event->sesi2,
                                                3 => $event->sesi3,
                                                4 => $event->sesi4,
                                                5 => $event->sesi5,
                                                6 => $event->sesi6,
                                                7 => $event->sesi7,
                                                8 => $event->sesi8,
                                                9 => $event->sesi9,
                                                ];
                                                @endphp

                                                @forelse($sesiList as $nomorSesi => $jam)
                                                @if(!empty($jam))
                                                <label class="d-block">
                                                    <input type="checkbox"
                                                        class="sesi-checkbox sesi-{{ $event->id }}"
                                                        data-event="{{ $event->id }}"
                                                        name="sesi_promo[{{ $event->id }}][]"
                                                        value="{{ $nomorSesi }}">

                                                    <strong>Sesi {{ $nomorSesi }}</strong> — {{ $jam }}
                                                </label>
                                                @endif
                                                @empty
                                                <small class="text-muted">Tidak ada sesi</small>
                                                @endforelse
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
                                <a href="{{ route('account.Clinik-Scopus-Promo.index') }}"
                                    class="btn btn-warning btn-submit rounded-pill w-100 w-md-auto mb-2 mb-md-0">
                                    <i class="fa fa-undo"></i> KEMBALI
                                </a>

                            </div>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </section>
</div>

<!--================== KONDISI CEKLIS CEKBOX ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        function hitungHargaNormal() {
            let total = 0;

            document.querySelectorAll('.sesi-checkbox:checked').forEach(sesi => {
                const eventId = sesi.dataset.event;
                const eventCheckbox = document.querySelector(
                    `.event-checkbox[data-event="${eventId}"]`
                );

                if (eventCheckbox) {
                    total += parseInt(eventCheckbox.dataset.harga || 0);
                }
            });

            document.getElementById('harga_normal').value =
                new Intl.NumberFormat('id-ID').format(total);

            updateTotalBiaya();
        }

        // ✔️ EVENT CHECKBOX → AUTO CEKLIS SESI + HITUNG
        document.querySelectorAll('.event-checkbox').forEach(eventCheckbox => {
            eventCheckbox.addEventListener('change', function() {
                const eventId = this.dataset.event;
                const sesiCheckboxes = document.querySelectorAll('.sesi-' + eventId);

                sesiCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });

                // 🔥 INI YANG KURANG
                hitungHargaNormal();
            });
        });

        // ✔️ SESI MANUAL CHECK
        document.querySelectorAll('.sesi-checkbox').forEach(cb => {
            cb.addEventListener('change', hitungHargaNormal);
        });

    });
</script>
<!--================== END ==================-->

<!--================== MENGHITUNG DISKON DAN DISABLED DISKON ==================-->
<script>
    function handleDiskonTypeChange() {
        const tipe = document.getElementById('tipe_diskon').value;

        const persenField = document.getElementById('diskon_persentase');
        const nominalField = document.getElementById('nominal_diskon');
        const cardBundling = document.getElementById('card-bundling');
        const kodeDiskonField = document.querySelector('[name="kode_diskon"]');

        // Reset default
        persenField.disabled = true;
        nominalField.readOnly = true;
        kodeDiskonField.readOnly = false;

        persenField.value = '';

        if (cardBundling) {
            cardBundling.style.display = 'block';
        }

        if (tipe === 'persentase') {
            persenField.disabled = false;
            nominalField.readOnly = true;

        } else if (tipe === 'nominal') {
            persenField.disabled = true;
            nominalField.readOnly = false;

        } else if (tipe === 'bundling') {
            persenField.disabled = true;
            nominalField.readOnly = false;
            kodeDiskonField.readOnly = true;
        }

        updateNominalDiskon();
    }

    window.addEventListener('DOMContentLoaded', function() {
        handleDiskonTypeChange();

        document.getElementById('tipe_diskon')
            .addEventListener('change', handleDiskonTypeChange);

        document.getElementById('diskon_persentase')
            .addEventListener('input', updateNominalDiskon);

        document.getElementById('nominal_diskon')
            .addEventListener('input', function(e) {
                const angka = e.target.value.replace(/\D/g, '');
                e.target.value = formatRupiah(angka);
                updateNominalDiskon();
            });

        document.getElementById('biaya')
            .addEventListener('input', updateNominalDiskon);
    });
</script>
<!--================== END ==================-->

<!--================== FORMAT RUPIAH ==================-->
<script>
    // ===============================
    // FORMAT RUPIAH
    // ===============================
    function formatRupiah(angka) {
        if (!angka) return '';
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function toNumber(rupiah) {
        return parseInt((rupiah || '0').replace(/\D/g, '')) || 0;
    }

    // Konversi string rupiah ke number
    function toNumber(str) {
        if (!str) return 0;
        // Hapus semua karakter kecuali angka
        return Number(str.replace(/\D/g, '')) || 0;
    }

    // Format number ke rupiah
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // ===============================
    // HITUNG TOTAL BIAYA
    // ===============================
    function updateTotalBiaya() {
        const hargaNormal = toNumber(document.getElementById('harga_normal').value);
        const nominalDiskon = toNumber(document.getElementById('nominal_diskon').value);

        let total = hargaNormal - nominalDiskon;
        if (total < 0) total = 0;

        document.getElementById('total_biaya').value = formatRupiah(total);
    }
    // ===============================
    // EVENT LISTENER
    // ===============================
    document.addEventListener('DOMContentLoaded', function() {
        const hargaNormalInput = document.getElementById('harga_normal');
        const nominalDiskonInput = document.getElementById('nominal_diskon');

        // Harga Normal
        hargaNormalInput.addEventListener('input', function(e) {
            const angka = toNumber(e.target.value);
            e.target.value = formatRupiah(angka);
            updateTotalBiaya();
        });

        // Nominal Diskon
        nominalDiskonInput.addEventListener('input', function(e) {
            const angka = toNumber(e.target.value);
            e.target.value = formatRupiah(angka);
            updateTotalBiaya();
        });

        // Hitung pertama kali
        updateTotalBiaya();
    });
</script>
<!--================== END ==================-->

<!--================== MENGHITUNG HARGA NORMAL ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const hargaNormalInput = document.getElementById('harga_normal');

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        function toNumber(str) {
            return Number((str || '0').replace(/\D/g, '')) || 0;
        }

        function hitungHargaNormal() {
            let total = 0;

            document.querySelectorAll('.sesi-checkbox:checked').forEach(sesi => {
                const eventId = sesi.dataset.event;
                const eventCheckbox = document.querySelector(
                    `.event-checkbox[data-event="${eventId}"]`
                );

                if (eventCheckbox) {
                    const hargaSesi = parseInt(eventCheckbox.dataset.harga || 0);
                    total += hargaSesi;
                }
            });

            hargaNormalInput.value = formatRupiah(total);

            // 🔥 otomatis hitung ulang total biaya
            updateTotalBiaya();
        }

        // Hitung setiap sesi dicentang / dilepas
        document.querySelectorAll('.sesi-checkbox').forEach(cb => {
            cb.addEventListener('change', hitungHargaNormal);
        });

    });
</script>
<!--================== END ==================-->

<!--================== MENGHITUNG DISKON PERSENTASE ==================-->
<script>
    function updateNominalDiskon() {
        const hargaNormal = toNumber(document.getElementById('harga_normal').value);
        const tipeDiskon = document.getElementById('tipe_diskon').value;
        const persenInput = document.getElementById('diskon_persentase');
        const nominalInput = document.getElementById('nominal_diskon');

        // Jika diskon persentase
        if (tipeDiskon === 'persentase') {
            const persen = parseFloat(persenInput.value) || 0;
            const nominalDiskon = Math.floor(hargaNormal * persen / 100);

            nominalInput.value = formatRupiah(nominalDiskon);
        }

        // Tetap hitung total biaya
        updateTotalBiaya();
    }
</script>
<!--================== END ==================-->

@endsection