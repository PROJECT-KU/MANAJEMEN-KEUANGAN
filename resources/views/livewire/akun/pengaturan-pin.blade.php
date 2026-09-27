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

    {{-- Perangkat: PIN dikenali per peramban, jadi HP perlu didaftarkan sendiri --}}
    @if ($pengguna->pinAktif())
        @if ($this->perangkatSiap())
            <div class="d-flex align-items-center mb-4 p-3"
                style="border-radius: 16px; background: #ecfdf5; border: 1px solid #a7f3d0;">
                <i class="fas fa-check-circle text-success fa-lg mr-3"></i>
                <div class="flex-grow-1">
                    <div class="font-weight-800 text-dark">Perangkat ini sudah terdaftar</div>
                    <div class="small text-muted">
                        Di peramban ini, halaman masuk langsung meminta PIN — tanpa username dan kata sandi.
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold"
                    style="border-radius: 12px;" wire:click="lupakanPerangkat">
                    Lupakan perangkat
                </button>
            </div>
        @else
            <div class="p-3 mb-4" style="border-radius: 16px; background: #eff6ff; border: 1px solid #bfdbfe;">
                <div class="d-flex align-items-start mb-3">
                    <i class="fas fa-mobile-alt text-primary fa-lg mr-3 mt-1"></i>
                    <div>
                        <div class="font-weight-800 text-dark">Pakai PIN di perangkat ini juga</div>
                        <div class="small text-muted">
                            PIN Anda sudah aktif, tetapi peramban ini belum terdaftar. Masukkan PIN Anda sekali di
                            sini, lalu halaman masuk di perangkat ini cukup meminta PIN.
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row" style="gap: 10px;">
                    <input type="password"
                        class="form-control-modern pin-isian flex-grow-1 @error('pinPerangkat') is-invalid @enderror"
                        wire:model="pinPerangkat" inputmode="numeric" maxlength="{{ $panjangPin }}"
                        autocomplete="off" placeholder="{{ str_repeat('•', $panjangPin) }}"
                        oninput="this.value = this.value.replace(/\D/g, '')">
                    <button type="button" class="btn-modern btn-gradient px-4" wire:click="aktifkanDiPerangkat"
                        wire:loading.attr="disabled">
                        <i class="fas fa-plus-circle mr-2"></i> DAFTARKAN
                    </button>
                </div>
                @error('pinPerangkat')
                    <div class="text-danger small font-weight-bold mt-2">{{ $message }}</div>
                @enderror
            </div>
        @endif
    @endif

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
