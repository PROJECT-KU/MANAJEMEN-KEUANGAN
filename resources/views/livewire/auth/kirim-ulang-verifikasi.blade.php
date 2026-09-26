<div class="kartu">
    @if ($terkirim)
        <span class="ikon-keadaan" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <rect x="3" y="5" width="18" height="14" rx="2" />
                <path d="M3 7l9 6 9-6" />
            </svg>
        </span>

        <h2 class="judul-form" style="text-align:center">Cek email Anda</h2>
        <p class="teks-bantu" style="text-align:center">
            Jika <strong>{{ $email }}</strong> terdaftar dan belum terverifikasi, tautan baru sudah kami kirim ke
            sana. Periksa juga folder spam atau promosi.
        </p>

        <div class="kabar kabar-sukses" role="status">
            <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" /></svg>
            <span>Tautan berlaku {{ $jamBerlaku }} jam.</span>
        </div>

        <p class="kaki-kartu">
            Salah ketik alamatnya?
            <button type="button" class="tombol-teks" wire:click="$set('terkirim', false)">Ubah email</button>
            <br>
            <span style="display:inline-block;margin-top:8px">
                <a href="{{ route('login') }}" class="tautan">Kembali ke halaman masuk</a>
            </span>
        </p>
    @else
        <h2 class="judul-form">Kirim ulang verifikasi</h2>
        <p class="teks-bantu">
            Masukkan email akun Anda. Kami kirimkan tautan verifikasi yang baru.
        </p>

        <form wire:submit="kirimUlang" novalidate x-data x-sinkron-livewire>
            <div class="medan">
                <label for="email">Alamat Email <span class="wajib">*</span></label>
                <div class="kotak-isian">
                    <input type="email" id="email" wire:model="email" autocomplete="email"
                        placeholder="email terdaftar Anda" class="@error('email') salah @enderror"
                        @error('email') aria-invalid="true" aria-describedby="galat-email" @enderror x-init="if (window.innerWidth >= 640) $el.focus()">
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
                @else
                    <p class="petunjuk">Tautan verifikasi berlaku {{ $jamBerlaku }} jam.</p>
                @enderror
            </div>

            <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="kirimUlang">
                <span wire:loading.remove wire:target="kirimUlang" style="display:inline-flex;align-items:center;gap:10px">
                    <svg viewBox="0 0 24 24"><path d="M22 2L11 13" /><path d="M22 2l-7 20-4-9-9-4 20-7z" /></svg>
                    Kirim Ulang Tautan
                </span>
                <span wire:loading.flex wire:target="kirimUlang" style="display:none;align-items:center;gap:10px">
                    <span class="pemutar"></span> Mengirim…
                </span>
            </button>
        </form>

        <p class="kaki-kartu">
            Sudah terverifikasi? <a href="{{ route('login') }}" class="tautan">Masuk ke akun Anda</a>
        </p>
    @endif
</div>
