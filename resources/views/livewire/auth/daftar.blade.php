<div class="kartu kartu-lebar">
    <h2 class="judul-form">Buat akun Anda</h2>
    <p class="teks-bantu">Isi data di bawah ini. Tanda <span class="wajib">*</span> berarti wajib diisi.</p>

    @if ($errors->any())
        <div class="kabar kabar-galat ringkasan-galat" role="alert" aria-live="assertive" tabindex="-1"
            x-data x-init="$nextTick(() => $el.focus())">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
            <span>
                Ada {{ $errors->count() }} isian yang perlu diperbaiki:
                <ul>
                    @foreach ($errors->getMessages() as $medan => $pesanMedan)
                        @foreach ($pesanMedan as $pesan)
                            <li>
                                @if ($medan === 'kodePos2')
                                    {{ $pesan }}
                                @else
                                    <a href="#{{ $medan }}" class="tautan-galat"
                                        @click.prevent="document.getElementById(@js($medan))?.focus()">{{ $pesan }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </span>
        </div>
    @endif

    <form wire:submit="daftar" novalidate>
        {{-- jebakan bot: disembunyikan dari manusia, diabaikan pembaca layar --}}
        <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden">
            <label for="kodePos2">Abaikan isian ini</label>
            <input type="text" id="kodePos2" wire:model="kodePos2" tabindex="-1" autocomplete="off"
                data-lpignore="true" data-1p-ignore data-form-type="other">
        </div>

        <div class="baris-medan">
            <div class="medan">
                <label for="namaLengkap">Nama Lengkap <span class="wajib">*</span></label>
                <div class="kotak-isian">
                    <input type="text" id="namaLengkap" wire:model.blur="namaLengkap" autocomplete="name"
                        placeholder="Nama sesuai identitas" class="@error('namaLengkap') salah @enderror"
                    @error('namaLengkap') aria-invalid="true" aria-describedby="galat-namaLengkap" @enderror>
                    <svg class="ikon-medan" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                @error('namaLengkap')
                    <p class="pesan-salah" id="galat-namaLengkap" role="alert">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="medan">
                <label for="username">Username <span class="wajib">*</span></label>
                <div class="kotak-isian">
                    <input type="text" id="username" wire:model.blur="username" autocomplete="username"
                        placeholder="Dipakai untuk masuk" class="@error('username') salah @enderror"
                    @error('username') aria-invalid="true" aria-describedby="galat-username" @enderror>
                    <svg class="ikon-medan" viewBox="0 0 24 24">
                        <path d="M4 20v-1a5 5 0 015-5h6a5 5 0 015 5v1" />
                        <circle cx="12" cy="8" r="4" />
                    </svg>
                </div>
                @error('username')
                    <p class="pesan-salah" id="galat-username" role="alert">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <div class="baris-medan">
            <div class="medan">
                <label for="email">Alamat Email <span class="wajib">*</span></label>
                <div class="kotak-isian">
                    <input type="email" id="email" wire:model.blur="email" autocomplete="email"
                        placeholder="nama@email.com" class="@error('email') salah @enderror"
                    @error('email') aria-invalid="true" aria-describedby="galat-email" @enderror>
                    <svg class="ikon-medan" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="M3 7l9 6 9-6" />
                    </svg>
                </div>
                @error('email')
                    <p class="pesan-salah" id="galat-email" role="alert">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="medan">
                <label for="telp">Nomor Telepon</label>
                <div class="kotak-isian">
                    <input type="tel" id="telp" wire:model.blur="telp" autocomplete="tel"
                        placeholder="08xxxxxxxxxx" class="@error('telp') salah @enderror"
                    @error('telp') aria-invalid="true" aria-describedby="galat-telp" @enderror>
                    <svg class="ikon-medan" viewBox="0 0 24 24">
                        <path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1 1 .3 1.9.6 2.8a2 2 0 01-.5 2.1L8.1 9.7a16 16 0 006 6l1.1-1.1a2 2 0 012.1-.5c.9.3 1.8.5 2.8.6a2 2 0 011.7 2z" />
                    </svg>
                </div>
                @error('telp')
                    <p class="pesan-salah" id="galat-telp" role="alert">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                        {{ $message }}
                    </p>
                @else
                    <p class="petunjuk">Opsional.</p>
                @enderror
            </div>
        </div>

        <div class="baris-medan"
            x-data="{
                tampil: false,
                tampil2: false,
                sandi: @entangle('kataSandi'),
                get skor() {
                    let s = 0;
                    if (this.sandi.length >= 8) s++;
                    if (this.sandi.length >= 12) s++;
                    if (/[A-Z]/.test(this.sandi) && /[a-z]/.test(this.sandi)) s++;
                    if (/[0-9]/.test(this.sandi) && /[^A-Za-z0-9]/.test(this.sandi)) s++;
                    return this.sandi.length ? Math.max(s, 1) : 0;
                },
                get label() {
                    return ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][this.skor];
                },
                get warna() {
                    return ['', '#e11d48', '#d97706', '#0ea5e9', '#059669'][this.skor];
                }
            }">
            <div class="medan">
                <label for="kataSandi">Kata Sandi <span class="wajib">*</span></label>
                <div class="kotak-isian">
                    <input :type="tampil ? 'text' : 'password'" type="password" id="kataSandi"
                        wire:model.blur="kataSandi" x-model="sandi" autocomplete="new-password"
                        placeholder="Minimal 8 karakter" class="@error('kataSandi') salah @enderror"
                    @error('kataSandi') aria-invalid="true" aria-describedby="galat-kataSandi" @enderror>
                    <svg class="ikon-medan" viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="10" rx="2" />
                        <path d="M7 11V8a5 5 0 0110 0v3" />
                    </svg>
                    <button type="button" class="tombol-mata" @click="tampil = !tampil" aria-label="Tampilkan kata sandi">
                        <svg x-show="!tampil" viewBox="0 0 24 24">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg x-show="tampil" x-cloak viewBox="0 0 24 24">
                            <path d="M17.9 17.9A10.3 10.3 0 0112 19C5.6 19 2 12 2 12a18.5 18.5 0 015.1-5.9M9.9 4.2A9.6 9.6 0 0112 4c6.4 0 10 7 10 7a18.6 18.6 0 01-2.2 3.2" />
                            <path d="M9.9 9.9a3 3 0 104.2 4.2M2 2l20 20" />
                        </svg>
                    </button>
                </div>

                <ul class="syarat-sandi" aria-hidden="true">
                    <li>Minimal 8 karakter</li>
                    <li>Memuat huruf dan angka</li>
                    <li>Bukan kata sandi yang pernah bocor</li>
                </ul>

                <div class="bar-kekuatan" x-show="sandi.length > 0" x-cloak>
                    <template x-for="n in 4" :key="n">
                        <i :style="n <= skor ? 'background:' + warna : ''"></i>
                    </template>
                </div>
                <p class="label-kekuatan" x-show="sandi.length > 0" x-cloak :style="'color:' + warna"
                    x-text="'Kekuatan: ' + label"></p>

                @error('kataSandi')
                    <p class="pesan-salah" id="galat-kataSandi" role="alert">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="medan">
                <label for="kataSandiKonfirmasi">Ulangi Kata Sandi <span class="wajib">*</span></label>
                <div class="kotak-isian">
                    <input :type="tampil2 ? 'text' : 'password'" type="password" id="kataSandiKonfirmasi"
                        wire:model.blur="kataSandiKonfirmasi" autocomplete="new-password"
                        placeholder="Ketik ulang kata sandi" class="@error('kataSandiKonfirmasi') salah @enderror"
                    @error('kataSandiKonfirmasi') aria-invalid="true" aria-describedby="galat-kataSandiKonfirmasi" @enderror>
                    <svg class="ikon-medan" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4" />
                        <rect x="3" y="11" width="18" height="10" rx="2" />
                        <path d="M7 11V8a5 5 0 0110 0v3" />
                    </svg>
                    <button type="button" class="tombol-mata" @click="tampil2 = !tampil2" aria-label="Tampilkan kata sandi">
                        <svg x-show="!tampil2" viewBox="0 0 24 24">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg x-show="tampil2" x-cloak viewBox="0 0 24 24">
                            <path d="M17.9 17.9A10.3 10.3 0 0112 19C5.6 19 2 12 2 12a18.5 18.5 0 015.1-5.9M9.9 4.2A9.6 9.6 0 0112 4c6.4 0 10 7 10 7a18.6 18.6 0 01-2.2 3.2" />
                            <path d="M9.9 9.9a3 3 0 104.2 4.2M2 2l20 20" />
                        </svg>
                    </button>
                </div>
                @error('kataSandiKonfirmasi')
                    <p class="pesan-salah" id="galat-kataSandiKonfirmasi" role="alert">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <div class="medan">
            <label class="centang">
                <input type="checkbox" id="setuju" wire:model="setuju">
                <span>Saya menyetujui
                    <a href="{{ route('ketentuan') }}" target="_blank" rel="noopener" class="tautan">kebijakan privasi dan ketentuan layanan</a>
                    Rumah Scopus Foundation.</span>
            </label>
            @error('setuju')
                <p class="pesan-salah" id="galat-setuju" role="alert">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="daftar">
            <span wire:loading.remove wire:target="daftar" style="display:inline-flex;align-items:center;gap:10px">
                <svg viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M19 8v6M22 11h-6" />
                </svg>
                Daftar Sekarang
            </span>
            <span wire:loading.flex wire:target="daftar" style="display:none;align-items:center;gap:10px">
                <span class="pemutar"></span> Menyimpan…
            </span>
        </button>
    </form>

    <p class="kaki-kartu">
        Sudah punya akun? <a href="{{ route('login') }}" class="tautan">Masuk di sini</a>
    </p>
</div>
