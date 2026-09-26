<div class="kartu">
    <span class="lencana">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="10" rx="2" />
            <path d="M7 11V8a5 5 0 019.9-1" />
        </svg>
        Kata Sandi Baru
    </span>

    @if (empty($token))
        {{-- Halaman ini semestinya dibuka lewat tautan dari email. --}}
        <h2 class="judul-form">Tautan tidak lengkap</h2>
        <p class="teks-bantu">
            Buka halaman ini melalui tautan yang kami kirim ke email Anda. Bila tautannya sudah kedaluwarsa,
            silakan minta yang baru.
        </p>

        <div class="kabar kabar-galat">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
            <span>Token atur ulang tidak ditemukan pada alamat halaman ini.</span>
        </div>

        <a href="{{ route('formemail.reset') }}" class="tombol-utama" style="text-decoration:none">
            <svg viewBox="0 0 24 24"><path d="M22 2L11 13" /><path d="M22 2l-7 20-4-9-9-4 20-7z" /></svg>
            Minta Tautan Baru
        </a>

        <div class="pemisah">ATAU</div>

        <p class="kaki-kartu">
            <a href="{{ route('login') }}" class="tautan">Kembali ke halaman masuk</a>
        </p>
    @else
        <h2 class="judul-form">Buat kata sandi baru</h2>
        <p class="teks-bantu">
            Untuk akun <strong>{{ $email }}</strong>. Pilih kata sandi yang belum pernah Anda pakai sebelumnya.
        </p>

        @error('token')
            <div class="kabar kabar-galat">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                <span>
                    {{ $message }}
                    <a href="{{ route('formemail.reset') }}" class="tautan">Minta tautan baru</a>
                </span>
            </div>
        @enderror

        <form wire:submit="simpan" novalidate>
            <div class="medan">
                <label for="email">Alamat Email</label>
                <div class="kotak-isian">
                    <input type="email" id="email" wire:model="email" autocomplete="email" readonly
                        class="@error('email') salah @enderror">
                    <svg class="ikon-medan" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="M3 7l9 6 9-6" />
                    </svg>
                </div>
                @error('email')
                    <p class="pesan-salah">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                        {{ $message }}
                    </p>
                @else
                    <p class="petunjuk">Diambil dari tautan yang Anda klik.</p>
                @enderror
            </div>

            {{-- sengaja satu kolom: kartu halaman ini lebih sempit daripada
                 halaman daftar, dua kolom membuat isian terlalu sesak --}}
            <div x-data="{
                    tampil: false,
                    sandi: @entangle('kataSandi'),
                    get skor() {
                        let s = 0;
                        if (this.sandi.length >= 8) s++;
                        if (this.sandi.length >= 12) s++;
                        if (/[A-Z]/.test(this.sandi) && /[a-z]/.test(this.sandi)) s++;
                        if (/[0-9]/.test(this.sandi) && /[^A-Za-z0-9]/.test(this.sandi)) s++;
                        return this.sandi.length ? Math.max(s, 1) : 0;
                    },
                    get label() { return ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][this.skor] },
                    get warna() { return ['', '#e11d48', '#d97706', '#0ea5e9', '#059669'][this.skor] }
                }">
                <div class="medan">
                    <label for="kataSandi">Kata Sandi Baru <span class="wajib">*</span></label>
                    <div class="kotak-isian">
                        <input :type="tampil ? 'text' : 'password'" type="password" id="kataSandi"
                            wire:model="kataSandi" x-model="sandi" autocomplete="new-password"
                            placeholder="Minimal 8 karakter" class="@error('kataSandi') salah @enderror" autofocus>
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

                    <div class="bar-kekuatan" x-show="sandi.length > 0" x-cloak>
                        <template x-for="n in 4" :key="n">
                            <i :style="n <= skor ? 'background:' + warna : ''"></i>
                        </template>
                    </div>
                    <p class="label-kekuatan" x-show="sandi.length > 0" x-cloak :style="'color:' + warna"
                        x-text="'Kekuatan: ' + label"></p>

                    @error('kataSandi')
                        <p class="pesan-salah">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="medan">
                    <label for="kataSandiKonfirmasi">Ulangi Kata Sandi <span class="wajib">*</span></label>
                    <div class="kotak-isian">
                        <input type="password" id="kataSandiKonfirmasi" wire:model="kataSandiKonfirmasi"
                            autocomplete="new-password" placeholder="Ketik ulang kata sandi"
                            class="@error('kataSandiKonfirmasi') salah @enderror">
                        <svg class="ikon-medan" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4" />
                            <rect x="3" y="11" width="18" height="10" rx="2" />
                            <path d="M7 11V8a5 5 0 0110 0v3" />
                        </svg>
                    </div>
                    @error('kataSandiKonfirmasi')
                        <p class="pesan-salah">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="simpan">
                <span wire:loading.remove wire:target="simpan" style="display:inline-flex;align-items:center;gap:10px">
                    <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" /></svg>
                    Simpan Kata Sandi Baru
                </span>
                <span wire:loading.flex wire:target="simpan" style="display:none;align-items:center;gap:10px">
                    <span class="pemutar"></span> Menyimpan…
                </span>
            </button>
        </form>

        <div class="pemisah">BUKAN ANDA?</div>

        <p class="kaki-kartu">
            Abaikan saja tautannya, atau <a href="{{ route('login') }}" class="tautan">kembali ke halaman masuk</a>.
        </p>
    @endif
</div>
