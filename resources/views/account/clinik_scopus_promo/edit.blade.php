@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Clinik Scopus Edit Promo | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">

        <div class="section-header">
            <h1>EDIT DATA PROMO</h1>
        </div>

        <div class="section-body">

            <form method="POST"
                action="{{ route('account.Clinik-Scopus-Promo.update', $promo->id) }}">
                @csrf

                {{-- ================= BASIC PROMO ================= --}}
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Nama Promo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        name="nama_promo"
                                        class="form-control"
                                        value="{{ old('nama_promo', $promo->nama_promo) }}"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status</label>
                                    <select name="status" class="form-control" style="height: auto;">
                                        <option value="" disabled selected>-- PILIH STATUS PROMO --</option>
                                        <option value="active" {{ $promo->status=='active'?'selected':'' }}>
                                            Active
                                        </option>
                                        <option value="non active" {{ $promo->status=='non active'?'selected':'' }}>
                                            Non Active
                                        </option>
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
                                        value="{{ $promo->tanggal_mulai_promo }}"
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
                                        value="{{ $promo->tanggal_selesai_promo }}"
                                        required>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ================= HARGA & DISKON ================= --}}
                <div class="card">
                    <div class="card-body">

                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Harga Normal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text"
                                            name="harga_normal"
                                            id="harga_normal"
                                            class="form-control"
                                            value="0"
                                            data-harga-db="{{ $promo->harga_normal ?? 0 }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Diskon</label>
                                    <select class="form-control"
                                        name="tipe_diskon"
                                        id="tipe_diskon" style="height: auto;">
                                        <option value="persentase" {{ $promo->tipe_diskon=='persentase'?'selected':'' }}>
                                            PERSENTASE
                                        </option>
                                        <option value="nominal" {{ $promo->tipe_diskon=='nominal'?'selected':'' }}>
                                            NOMINAL
                                        </option>
                                        <option value="bundling" {{ $promo->tipe_diskon=='bundling'?'selected':'' }}>
                                            BUNDLING
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Diskon Persentase</label>
                                    <div class="input-group">
                                        <input type="number"
                                            name="diskon_persentase"
                                            id="diskon_persentase"
                                            class="form-control"
                                            value="{{ $promo->diskon_persentase ?? 0 }}">
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
                                        <input type="text"
                                            name="nominal_diskon"
                                            id="nominal_diskon"
                                            class="form-control"
                                            value="{{ $promo->nominal_diskon }}"
                                            data-nominal-db="{{ $promo->nominal_diskon }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Diskon</label>
                                    <input type="text"
                                        name="kode_diskon"
                                        class="form-control"
                                        value="{{ $promo->kode_diskon ?? '-'}}">
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
                                        <input type="text"
                                            name="total_biaya"
                                            class="form-control"
                                            value="{{ $promo->total_biaya }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ================= EVENT & SESI ================= --}}
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
                                                @php
                                                $checked = isset($selectedSesi[$event->id][$nomorSesi]);
                                                @endphp

                                                <label class="d-block">
                                                    <input type="checkbox"
                                                        class="sesi-checkbox sesi-{{ $event->id }}"
                                                        data-event="{{ $event->id }}"
                                                        data-harga="{{ $event->biayaPersesi->biaya_persesi ?? 0 }}"
                                                        name="sesi_promo[{{ $event->id }}][]"
                                                        value="{{ $nomorSesi }}"
                                                        {{ $checked ? 'checked' : '' }}>

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
            </form>

        </div>
    </section>
</div>

<!--================== FORMAT RUPIAH ==================-->
<script>
    function formatRupiah(value) {
        if (!value) return '';
        value = value.toString().replace(/[^0-9]/g, '');

        let sisa = value.length % 3;
        let rupiah = value.substr(0, sisa);
        let ribuan = value.substr(sisa).match(/\d{3}/g);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return rupiah;
    }

    function bindRupiah(input) {
        if (!input) return;

        // FORMAT SAAT LOAD
        input.value = formatRupiah(input.value);

        // FORMAT SAAT KETIK / PASTE
        input.addEventListener('input', function() {
            let cursor = this.selectionStart;
            let before = this.value.length;

            this.value = formatRupiah(this.value);

            let after = this.value.length;
            this.setSelectionRange(cursor + (after - before), cursor + (after - before));
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        bindRupiah(document.getElementById('harga_normal'));
        bindRupiah(document.getElementById('nominal_diskon'));
        bindRupiah(document.querySelector('[name="total_biaya"]'));
    });
</script>
<!--================== END ==================-->

<!--================== HITUNG DISKON ==================-->
<script>
    function toNumber(rupiah) {
        if (!rupiah) return 0;
        return parseInt(rupiah.toString().replace(/\D/g, '')) || 0;
    }

    function updateNominalDiskon() {
        const hargaNormal = toNumber(document.getElementById('harga_normal')?.value);
        const persenDiskon = parseFloat(document.getElementById('diskon_persentase')?.value) || 0;
        const nominalDiskonField = document.getElementById('nominal_diskon');
        const tipe = document.getElementById('tipe_diskon')?.value;

        let diskon = 0;

        // 🔹 HITUNG DISKON
        if (tipe === 'persentase') {
            diskon = hargaNormal * persenDiskon / 100;
            nominalDiskonField.value = formatRupiah(Math.round(diskon));
        }

        if (tipe === 'nominal' || tipe === 'bundling') {
            diskon = toNumber(nominalDiskonField.value);
        }

        // 🔹 TOTAL SETELAH DISKON
        let subtotal = hargaNormal - diskon;
        if (subtotal < 0) subtotal = 0;

        // 🔹 SET TOTAL
        document.querySelector('[name="total_biaya"]').value = formatRupiah(Math.round(total));
    }

    document.addEventListener('DOMContentLoaded', function() {

        updateNominalDiskon();

        ['harga_normal', 'diskon_persentase', 'nominal_diskon', 'tipe_diskon']
        .forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateNominalDiskon);
                el.addEventListener('change', updateNominalDiskon);
            }
        });

    });
</script>
<!--================== END ==================-->

<!--================== KONDISI CEKLIS CEKBOX ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // 🔹 Sinkron sesi → event checkbox
        function syncEventCheckbox(eventId) {
            const sesiCheckboxes = document.querySelectorAll('.sesi-' + eventId);
            const eventCheckbox = document.querySelector('.event-checkbox[data-event="' + eventId + '"]');

            if (!eventCheckbox || sesiCheckboxes.length === 0) return;

            // cek apakah semua sesi tercentang
            const allChecked = Array.from(sesiCheckboxes).every(cb => cb.checked);
            eventCheckbox.checked = allChecked;
        }

        // 🔹 Saat klik EVENT → semua sesi ikut
        document.querySelectorAll('.event-checkbox').forEach(function(eventCheckbox) {
            eventCheckbox.addEventListener('change', function() {
                const eventId = this.dataset.event;
                document.querySelectorAll('.sesi-' + eventId).forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        });

        // 🔹 Saat klik SESI → cek ulang event checkbox
        document.querySelectorAll('.sesi-checkbox').forEach(function(sesiCheckbox) {
            sesiCheckbox.addEventListener('change', function() {
                const eventId = this.className.match(/sesi-(\S+)/)[1];
                syncEventCheckbox(eventId);
            });
        });

        // 🔹 AUTO CEK SAAT LOAD HALAMAN EDIT
        document.querySelectorAll('.event-checkbox').forEach(function(eventCheckbox) {
            syncEventCheckbox(eventCheckbox.dataset.event);
        });

    });
</script>
<!--================== END ==================-->

<!--================== DISABLED JIKA TIPE PROMO BUNDLING ==================-->
<script>
    function handleDiskonTypeChange(reset = false) {
        const tipe = document.getElementById('tipe_diskon').value;

        const hargaNormalRow = document.getElementById('harga_normal')?.closest('.row');
        const persenRow = document.getElementById('diskon_persentase')?.closest('.row');
        const nominalRow = document.getElementById('nominal_diskon')?.closest('.row');

        const persenField = document.getElementById('diskon_persentase');
        const nominalField = document.getElementById('nominal_diskon');
        const cardBundling = document.getElementById('card-bundling');

        // 🔄 SEMUA DISHOW DULU
        if (hargaNormalRow) hargaNormalRow.style.display = 'flex';
        if (persenRow) persenRow.style.display = 'flex';
        if (nominalRow) nominalRow.style.display = 'flex';

        // RESET STATE
        persenField.disabled = true;
        nominalField.readOnly = true;

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
        handleDiskonTypeChange(false);

        document.getElementById('tipe_diskon')
            .addEventListener('change', () => handleDiskonTypeChange(true));
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

<!--================== MENGHITUNG HARGA NORMAL ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        function hitungHargaNormal() {
            const tipe = document.getElementById('tipe_diskon')?.value;
            const hargaNormalInput = document.getElementById('harga_normal');

            let total = 0;

            // 🔥 BUNDLING → HITUNG DARI SESI
            if (tipe === 'bundling') {
                document.querySelectorAll('.sesi-checkbox:checked').forEach(cb => {
                    const harga = parseInt(
                        cb.dataset.harga?.toString().replace(/\D/g, '')
                    ) || 0;

                    total += harga;
                });
            }
            // 🔥 PERSENTASE / NOMINAL → AMBIL DARI DB
            else {
                total = parseInt(
                    hargaNormalInput.dataset.hargaDb?.toString().replace(/\D/g, '')
                ) || 0;
            }

            hargaNormalInput.value = formatRupiah(total);
            updateNominalDiskon();
        }

        // 🔁 EVENT
        document.getElementById('tipe_diskon')
            ?.addEventListener('change', hitungHargaNormal);

        document.querySelectorAll('.sesi-checkbox')
            .forEach(cb => cb.addEventListener('change', hitungHargaNormal));

        // 🔥 AUTO LOAD EDIT
        hitungHargaNormal();
        updateNominalDiskon();
    });
</script>
<!--================== END ==================-->

<!--================== MENGHITUNG PERSENTASE DISKON ==================-->
<script>
    function updateNominalDiskon() {
        const hargaNormal = toNumber(document.getElementById('harga_normal')?.value);
        const persen = parseFloat(document.getElementById('diskon_persentase')?.value) || 0;
        const nominalField = document.getElementById('nominal_diskon');
        const tipe = document.getElementById('tipe_diskon')?.value;

        let nominalDiskon = 0;

        // 🔵 PERSENTASE
        if (tipe === 'persentase') {
            nominalDiskon = hargaNormal * persen / 100;
            nominalField.value = formatRupiah(Math.round(nominalDiskon));
        }

        // 🟢 NOMINAL & 🟣 BUNDLING
        if (tipe === 'nominal' || tipe === 'bundling') {
            nominalDiskon = toNumber(nominalField.value);
        }

        // ⛔ SAFETY
        if (nominalDiskon > hargaNormal) {
            nominalDiskon = hargaNormal;
            nominalField.value = formatRupiah(hargaNormal);
        }

        // 🔹 TOTAL
        let subtotal = hargaNormal - nominalDiskon;
        const total = subtotal;

        document.querySelector('[name="total_biaya"]').value =
            formatRupiah(Math.round(total));
    }
</script>
<!--================== END ==================-->

<!--================== MANMAPILKAN NOMINAL DISKON DARI DB TIPE BUNDLING ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipe = document.getElementById('tipe_diskon')?.value;
        const nominalField = document.getElementById('nominal_diskon');

        if ((tipe === 'bundling' || tipe === 'nominal') && nominalField) {
            const dbValue = nominalField.dataset.nominalDb;

            if (dbValue && !toNumber(nominalField.value)) {
                nominalField.value = formatRupiah(dbValue);
                updateNominalDiskon();
            }

            // Pastikan kolom tampil (tidak tersembunyi)
            nominalField.readOnly = (tipe === 'bundling') ? false : false; // nominal bisa diubah
        }
    });
</script>

<!--================== END ==================-->
@endsection