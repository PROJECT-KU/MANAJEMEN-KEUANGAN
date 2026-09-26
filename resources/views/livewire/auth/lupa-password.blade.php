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
            Jika <strong>{{ $email }}</strong> terdaftar, tautan untuk membuat kata sandi baru sudah kami kirim ke
            sana. Periksa juga folder spam atau promosi.
        </p>

        <div class="kabar kabar-sukses" role="status">
            <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" /></svg>
            <span>Tautan berlaku {{ $menitBerlaku }} menit dan hanya bisa dipakai satu kali.</span>
        </div>

        <button type="button" class="tombol-utama tombol-kedua" wire:click="kirimTautan"
            wire:loading.attr="disabled" wire:target="kirimTautan">
            <span wire:loading.remove wire:target="kirimTautan" style="display:inline-flex;align-items:center;gap:10px">
                <svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 019-9 9 9 0 018 5" /><path d="M21 3v5h-5" /><path d="M21 12a9 9 0 01-9 9 9 9 0 01-8-5" /><path d="M3 21v-5h5" /></svg>
                Kirim ulang tautan
            </span>
            <span wire:loading.flex wire:target="kirimTautan" style="display:none;align-items:center;gap:10px">
                <span class="pemutar" style="border-color:rgba(100,116,139,.35);border-top-color:currentColor"></span> Mengirim…
            </span>
        </button>

        @error('email')
            <p class="pesan-salah" style="justify-content:center;margin-top:12px">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                {{ $message }}
            </p>
        @enderror

        <p class="kaki-kartu">
            Salah ketik alamatnya?
            <button type="button" class="tombol-teks" wire:click="$set('terkirim', false)">Ubah email</button>
            <br>
            <span style="display:inline-block;margin-top:8px">
                <a href="{{ route('login') }}" class="tautan">Kembali ke halaman masuk</a>
            </span>
        </p>
    @else
        <h2 class="judul-form">Lupa kata sandi?</h2>
        <p class="teks-bantu">Masukkan email akun Anda. Kami kirimkan tautan aman untuk membuat kata sandi baru.</p>

        <form wire:submit="kirimTautan" novalidate x-data x-sinkron-livewire>
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
                    <p class="petunjuk">Tautan berlaku {{ $menitBerlaku }} menit dan hanya bisa dipakai satu kali.</p>
                @enderror
            </div>

            <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="kirimTautan">
                <span wire:loading.remove wire:target="kirimTautan" style="display:inline-flex;align-items:center;gap:10px">
                    <svg viewBox="0 0 24 24">
                        <path d="M22 2L11 13" />
                        <path d="M22 2l-7 20-4-9-9-4 20-7z" />
                    </svg>
                    Kirim Tautan Atur Ulang
                </span>
                <span wire:loading.flex wire:target="kirimTautan" style="display:none;align-items:center;gap:10px">
                    <span class="pemutar"></span> Mengirim…
                </span>
            </button>
        </form>

        <p class="kaki-kartu">
            Ingat kata sandi Anda? <a href="{{ route('login') }}" class="tautan">Kembali ke halaman masuk</a>
        </p>
    @endif
</div>
