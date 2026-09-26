<div class="kartu">
    <h2 class="judul-form">Masuk ke akun Anda</h2>
    <p class="teks-bantu">Gunakan username atau alamat email yang terdaftar.</p>

    @if (session('success') || session('reset'))
        <div class="kabar kabar-sukses" role="status">
            <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" /></svg>
            <span>{{ session('success') ?? session('reset') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="kabar kabar-galat" role="alert">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form wire:submit="masuk" novalidate>
        <div class="medan">
            <label for="identitas">Username atau Email <span class="wajib">*</span></label>
            <div class="kotak-isian">
                <input type="text" id="identitas" wire:model="identitas" autocomplete="username"
                    placeholder="mis. budisantoso" class="@error('identitas') salah @enderror"
                    @error('identitas') aria-invalid="true" aria-describedby="galat-identitas" @enderror autofocus>
                <svg class="ikon-medan" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </div>
            @error('identitas')
                <p class="pesan-salah" id="galat-identitas" role="alert">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="medan" x-data="{ tampil: false, caps: false }">
            <label for="kataSandi">Kata Sandi <span class="wajib">*</span></label>
            <div class="kotak-isian">
                <input :type="tampil ? 'text' : 'password'" type="password" id="kataSandi"
                    @keyup="caps = $event.getModifierState && $event.getModifierState('CapsLock')"
                    wire:model="kataSandi"
                    autocomplete="current-password" placeholder="Masukkan kata sandi"
                    class="@error('kataSandi') salah @enderror"
                    @error('kataSandi') aria-invalid="true" aria-describedby="galat-kataSandi" @enderror>
                <svg class="ikon-medan" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="10" rx="2" />
                    <path d="M7 11V8a5 5 0 0110 0v3" />
                </svg>
                <button type="button" class="tombol-mata" @click="tampil = !tampil"
                    :aria-label="tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
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
            <p class="peringatan-caps" x-show="caps" x-cloak role="status">
                <svg viewBox="0 0 24 24"><path d="M12 4l7 7h-4v4H9v-4H5l7-7z" /><path d="M9 19h6" /></svg>
                Caps Lock sedang aktif.
            </p>

            @error('kataSandi')
                <p class="pesan-salah" id="galat-kataSandi" role="alert">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="baris-sela">
            <label class="centang">
                <input type="checkbox" wire:model="ingatSaya">
                <span>Ingat saya</span>
            </label>
            <a href="{{ route('formemail.reset') }}" class="tautan">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="masuk">
            <span wire:loading.remove wire:target="masuk" style="display:inline-flex;align-items:center;gap:10px">
                <svg viewBox="0 0 24 24">
                    <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4" />
                    <path d="M10 17l5-5-5-5M15 12H3" />
                </svg>
                Masuk Sekarang
            </span>
            <span wire:loading.flex wire:target="masuk" style="display:none;align-items:center;gap:10px">
                <span class="pemutar"></span> Memeriksa…
            </span>
        </button>
    </form>

    <p class="kaki-kartu">
        Belum punya akun? <a href="{{ route('register') }}" class="tautan">Buat akun baru</a>
    </p>
</div>
