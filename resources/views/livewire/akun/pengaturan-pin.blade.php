<div>
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
        <span class="lencana-pin {{ $pengguna->pinAktif() ? 'lencana-aktif' : 'lencana-mati' }}">
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

        <ul class="catatan-pin mb-4">
            <li><i class="fas fa-shield-alt"></i> Hindari angka berulang, berurutan, atau tanggal lahir.</li>
            <li><i class="fas fa-ban"></i> Salah {{ config('auth.pin.batas_gagal', 5) }} kali di halaman masuk &rarr; PIN mati sendiri, Anda dikabari lewat email.</li>
            <li><i class="fas fa-laptop"></i> Berlaku per perangkat. Di perangkat lain: masuk dengan kata sandi, lalu daftarkan dari sini.</li>
        </ul>

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
        /* Senada dengan kotak kode di halaman masuk. */
        .pin-isian {
            height: 58px;
            text-align: center;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: .5em;
            text-indent: .5em;
            color: #0b1324;
        }

        .pin-isian::placeholder {
            font-size: 1rem;
            letter-spacing: .3em;
            font-weight: 600;
        }

        .lencana-pin {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            white-space: nowrap;
        }

        .lencana-aktif {
            color: #0e7490;
            background: #cffafe;
            border: 1px solid #a5f3fc;
        }

        .lencana-mati {
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .catatan-pin {
            list-style: none;
            margin: 0;
            padding: 14px 16px;
            border-radius: 14px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            display: grid;
            gap: 8px;
        }

        .catatan-pin li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: .82rem;
            line-height: 1.5;
            color: #334155;
        }

        .catatan-pin li i {
            margin-top: 3px;
            color: #d97706;
            flex: 0 0 14px;
        }
    </style>
</div>
