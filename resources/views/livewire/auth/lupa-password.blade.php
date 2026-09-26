<div class="kartu">
    <span class="lencana">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 2l-2 2m-7.6 7.6a5 5 0 11-7.1 7.1 5 5 0 017.1-7.1zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3" />
        </svg>
        Pemulihan Akun
    </span>

    <h2 class="judul-form">Lupa kata sandi?</h2>
    <p class="teks-bantu">Masukkan email akun Anda. Kami kirim kode verifikasi 6 digit untuk membuat kata sandi baru.</p>

    <div class="kabar kabar-info">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 16v-4M12 8h.01" /></svg>
        <span>Kode berlaku {{ config('auth.passwords.users.expire', 60) }} menit dan hanya bisa dipakai satu kali.</span>
    </div>

    <form wire:submit="kirimKode" novalidate>
        <div class="medan">
            <label for="email">Alamat Email <span class="wajib">*</span></label>
            <div class="kotak-isian">
                <input type="email" id="email" wire:model="email" autocomplete="email"
                    placeholder="email terdaftar Anda" class="@error('email') salah @enderror" autofocus>
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

        <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="kirimKode">
            <span wire:loading.remove wire:target="kirimKode" style="display:inline-flex;align-items:center;gap:10px">
                <svg viewBox="0 0 24 24">
                    <path d="M22 2L11 13" />
                    <path d="M22 2l-7 20-4-9-9-4 20-7z" />
                </svg>
                Kirim Kode Verifikasi
            </span>
            <span wire:loading.flex wire:target="kirimKode" style="display:none;align-items:center;gap:10px">
                <span class="pemutar"></span> Mengirim…
            </span>
        </button>
    </form>

    <div class="pemisah">SUDAH PUNYA KODE?</div>

    <p class="kaki-kartu" style="margin-bottom:14px">
        <a href="{{ route('password.atur-ulang') }}" class="tautan">Langsung masukkan kode</a>
    </p>

    <p class="kaki-kartu">
        Ingat kata sandi Anda? <a href="{{ route('login') }}" class="tautan">Kembali ke halaman masuk</a>
    </p>
</div>
