<div class="kartu">
    <span class="lencana">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="10" rx="2" />
            <path d="M7 11V8a5 5 0 019.9-1" />
        </svg>
        Kata Sandi Baru
    </span>

    <h2 class="judul-form">Atur ulang kata sandi</h2>
    <p class="teks-bantu">Masukkan kode dari email, lalu tentukan kata sandi baru Anda.</p>

    @if (session('info'))
        <div class="kabar kabar-info">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 16v-4M12 8h.01" /></svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <form wire:submit="simpan" novalidate>
        <div class="medan">
            <label for="email">Alamat Email <span class="wajib">*</span></label>
            <div class="kotak-isian">
                <input type="email" id="email" wire:model="email" autocomplete="email"
                    placeholder="email terdaftar Anda" class="@error('email') salah @enderror">
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
            @enderror
        </div>

        <div class="medan kotak-kode">
            <label for="kode">Kode Verifikasi <span class="wajib">*</span></label>
            <div class="kotak-isian">
                <input type="text" id="kode" wire:model="kode" inputmode="numeric" maxlength="6"
                    autocomplete="one-time-code" placeholder="······" class="@error('kode') salah @enderror" autofocus>
            </div>
            @error('kode')
                <p class="pesan-salah">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                    {{ $message }}
                </p>
            @else
                <p class="petunjuk">6 angka yang dikirim ke email Anda.</p>
            @enderror
        </div>

        <div class="baris-medan" x-data="{ tampil: false }">
            <div class="medan">
                <label for="kataSandi">Kata Sandi Baru <span class="wajib">*</span></label>
                <div class="kotak-isian">
                    <input :type="tampil ? 'text' : 'password'" type="password" id="kataSandi"
                        wire:model="kataSandi" autocomplete="new-password" placeholder="Minimal 8 karakter"
                        class="@error('kataSandi') salah @enderror">
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

    <div class="pemisah">TIDAK MENERIMA KODE?</div>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap"
        x-data="{ sisa: 0, mulai() { this.sisa = 60; const t = setInterval(() => { if (--this.sisa <= 0) clearInterval(t) }, 1000) } }">
        <button type="button" class="tombol-teks" wire:click="kirimUlang" x-bind:disabled="sisa > 0"
            @click="if (sisa === 0) mulai()" wire:loading.attr="disabled" wire:target="kirimUlang">
            <span wire:loading.remove wire:target="kirimUlang">
                <span x-show="sisa === 0">Kirim ulang kode</span>
                <span x-show="sisa > 0" x-cloak>Kirim ulang dalam <span class="hitung-mundur" x-text="sisa"></span> detik</span>
            </span>
            <span wire:loading wire:target="kirimUlang">Mengirim…</span>
        </button>

        <a href="{{ route('login') }}" class="tautan">Kembali ke halaman masuk</a>
    </div>
</div>
