<div>
    @if ($pesan !== '')
        <div class="alert border-0 shadow-sm d-flex align-items-start mb-4"
            style="border-radius: 16px; background: {{ $jenisPesan === 'sukses' ? '#ecfdf5' : '#fff1f2' }};">
            <i class="fas {{ $jenisPesan === 'sukses' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger' }} mr-3 fa-lg mt-1"></i>
            <div class="font-weight-bold text-dark small">{{ $pesan }}</div>
        </div>
    @endif

    {{-- Keadaan PIN saat ini --}}
    <div class="d-flex align-items-center mb-4 p-3"
        style="border-radius: 16px; background: {{ $pengguna->pinAktif() ? '#ecfeff' : '#f8fafc' }}; border: 1px solid {{ $pengguna->pinAktif() ? '#a5f3fc' : '#e2e8f0' }};">
        <i class="fas {{ $pengguna->pinAktif() ? 'fa-mobile-alt text-info' : 'fa-lock text-muted' }} fa-lg mr-3"></i>
        <div class="flex-grow-1">
            <div class="font-weight-800 text-dark">
                {{ $pengguna->pinAktif() ? 'PIN masuk aktif' : 'PIN masuk belum aktif' }}
            </div>
            <div class="small text-muted">
                @if ($pengguna->pinAktif())
                    Terakhir diubah
                    {{ $pengguna->pin_diubah_pada ? $pengguna->pin_diubah_pada->locale('id')->translatedFormat('d F Y H:i') : '-' }}
                    WIB
                @else
                    Aktifkan untuk bisa masuk hanya dengan {{ $panjangPin }} angka, tanpa mengetik kata sandi.
                @endif
            </div>
        </div>
        <span class="badge {{ $pengguna->pinAktif() ? 'badge-info' : 'badge-secondary' }} px-3 py-2"
            style="border-radius: 10px;">
            {{ $pengguna->pinAktif() ? 'AKTIF' : 'NONAKTIF' }}
        </span>
    </div>

    <form wire:submit="simpan">
        <div class="row">
            <div class="col-md-12 form-group">
                <label class="small font-weight-bold">
                    Kata Sandi Akun <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control-modern @error('kataSandi') is-invalid @enderror"
                    wire:model="kataSandi" autocomplete="current-password" placeholder="••••••••">
                <small class="text-muted">Dipakai untuk memastikan yang mengubah PIN memang Anda.</small>
                @error('kataSandi')
                    <div class="text-danger small font-weight-bold mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label class="small font-weight-bold">
                    {{ $pengguna->pinAktif() ? 'PIN Baru' : 'PIN' }} ({{ $panjangPin }} Angka)
                    <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control-modern pin-isian @error('pin') is-invalid @enderror"
                    wire:model="pin" inputmode="numeric" maxlength="{{ $panjangPin }}" autocomplete="off"
                    placeholder="{{ str_repeat('•', $panjangPin) }}"
                    oninput="this.value = this.value.replace(/\D/g, '')">
                @error('pin')
                    <div class="text-danger small font-weight-bold mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 form-group">
                <label class="small font-weight-bold">
                    Ulangi PIN <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control-modern pin-isian @error('pinKonfirmasi') is-invalid @enderror"
                    wire:model="pinKonfirmasi" inputmode="numeric" maxlength="{{ $panjangPin }}" autocomplete="off"
                    placeholder="{{ str_repeat('•', $panjangPin) }}"
                    oninput="this.value = this.value.replace(/\D/g, '')">
                @error('pinKonfirmasi')
                    <div class="text-danger small font-weight-bold mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="small text-muted mb-0" style="cursor: pointer;">
                <input type="checkbox"
                    onchange="document.querySelectorAll('.pin-isian').forEach(function (i) { i.type = this.checked ? 'text' : 'password' }, this)">
                Tampilkan angka PIN
            </label>
        </div>

        <div class="alert border-0 mb-4" style="border-radius: 14px; background: #fffbeb;">
            <div class="small text-dark">
                <i class="fas fa-shield-alt text-warning mr-2"></i>
                Hindari angka yang mudah ditebak: berulang (111111), berurutan (123456), atau tanggal lahir.
                Setelah {{ config('auth.pin.batas_gagal', 5) }} kali PIN salah di halaman masuk, PIN dinonaktifkan
                otomatis dan Anda diberi tahu lewat email — masuk tetap bisa dengan kata sandi.
            </div>
            <div class="small text-dark mt-2">
                <i class="fas fa-laptop text-warning mr-2"></i>
                PIN berlaku di <strong>perangkat ini</strong>: peramban ini akan mengingat username Anda supaya di
                halaman masuk cukup mengetik PIN, tanpa username dan kata sandi. Di perangkat lain, masuk dulu dengan
                kata sandi lalu aktifkan PIN dari sini.
            </div>
        </div>

        <div class="form-group mt-2 d-flex flex-column flex-sm-row" style="gap: 10px;">
            <button type="submit" class="btn-modern btn-gradient flex-grow-1" wire:loading.attr="disabled">
                <i class="fas fa-key mr-2"></i>
                {{ $pengguna->pinAktif() ? 'SIMPAN PIN BARU' : 'AKTIFKAN PIN' }}
            </button>

            @if ($pengguna->pinAktif())
                <button type="button" class="btn btn-outline-danger font-weight-bold px-4"
                    style="border-radius: 14px;" wire:click="nonaktifkan" wire:loading.attr="disabled">
                    <i class="fas fa-times-circle mr-2"></i> NONAKTIFKAN
                </button>
            @endif
        </div>
    </form>

    <style>
        .pin-isian {
            letter-spacing: .35em;
            font-weight: 700;
            text-align: center;
        }
    </style>
</div>
