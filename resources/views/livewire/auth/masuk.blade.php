<div class="kartu">
    <h2 class="judul-form">Masuk ke akun Anda</h2>
    <p class="teks-bantu">
        @if ($mode === 'pin')
            Cukup masukkan PIN {{ $panjangPin }} angka — tanpa username dan kata sandi.
        @else
            Gunakan username atau alamat email yang terdaftar.
        @endif
    </p>

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

    {{-- Cara masuk: kata sandi selalu ada; PIN baru hidup bila sudah
         diaktifkan dari halaman profil di perangkat ini. --}}
    <div class="pemilih-mode" role="group" aria-label="Pilih cara masuk">
        <button type="button" class="{{ $mode === 'sandi' ? 'aktif' : '' }}" wire:click="gantiMode('sandi')"
            aria-pressed="{{ $mode === 'sandi' ? 'true' : 'false' }}">
            <svg viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="10" rx="2" />
                <path d="M7 11V8a5 5 0 0110 0v3" />
            </svg>
            Kata Sandi
        </button>

        @if ($this->siapPin)
            <button type="button" class="{{ $mode === 'pin' ? 'aktif' : '' }}" wire:click="gantiMode('pin')"
                aria-pressed="{{ $mode === 'pin' ? 'true' : 'false' }}">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="16" rx="3" />
                    <path d="M8 9h.01M12 9h.01M16 9h.01M8 13h.01M12 13h.01M16 13h.01M9 17h6" />
                </svg>
                PIN
            </button>
        @else
            <button type="button" class="mati" disabled
                title="PIN belum aktif. Aktifkan dulu dari Profil setelah masuk.">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="16" rx="3" />
                    <path d="M8 9h.01M12 9h.01M16 9h.01M8 13h.01M12 13h.01M16 13h.01M9 17h6" />
                </svg>
                PIN <span class="label-mati">belum aktif</span>
            </button>
        @endif
    </div>

    <form wire:submit="masuk" novalidate x-data x-sinkron-livewire>
        @if ($mode === 'pin')
            {{-- Akun sudah dikenali perangkat ini, jadi tidak perlu diketik lagi --}}
            <div class="akun-perangkat">
                <span class="akun-avatar" aria-hidden="true">{{ Str::upper(Str::substr($akunPerangkat, 0, 1)) }}</span>
                <span class="akun-teks">
                    <small>Masuk sebagai</small>
                    <strong>{{ $akunPerangkat }}</strong>
                </span>
                <button type="button" class="tombol-teks" wire:click="lupakanSaya">Ganti akun</button>
            </div>

            <div class="medan" x-data="{
                tampil: false,
                terakhirKirim: 0,
                /* PIN dikirim sendiri begitu angkanya lengkap. Jeda 1,2 detik
                   menahan kiriman ganda, tapi tetap mengizinkan coba lagi
                   setelah PIN sebelumnya ditolak. */
                cekPenuh(nilai) {
                    if (nilai.length < {{ $panjangPin }}) return;
                    if (Date.now() - this.terakhirKirim < 1200) return;
                    this.terakhirKirim = Date.now();
                    this.$root.closest('form').requestSubmit();
                },
            }">
                <label for="pin">PIN {{ $panjangPin }} Angka <span class="wajib">*</span></label>
                <div class="kotak-isian kotak-kode">
                    <input :type="tampil ? 'text' : 'password'" type="password" id="pin" wire:model="pin"
                        inputmode="numeric" autocomplete="off" maxlength="{{ $panjangPin }}"
                        placeholder="{{ str_repeat('•', $panjangPin) }}"
                        class="@error('pin') salah @enderror"
                        @error('pin') aria-invalid="true" aria-describedby="galat-pin" @enderror
                        x-init="if (window.innerWidth >= 640) $el.focus()"
                        x-on:input="$el.value = $el.value.replace(/\D/g, '').slice(0, {{ $panjangPin }}); cekPenuh($el.value)">
                    <button type="button" class="tombol-mata" @click="tampil = !tampil"
                        :aria-label="tampil ? 'Sembunyikan PIN' : 'Tampilkan PIN'">
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

                @error('pin')
                    <p class="pesan-salah" id="galat-pin" role="alert">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" /></svg>
                        {{ $message }}
                    </p>
                @enderror

                <p class="petunjuk">PIN langsung terkirim setelah {{ $panjangPin }} angka terisi.</p>
            </div>
        @else
            <div class="medan">
                <label for="identitas">
                    Username atau Email <span class="wajib">*</span>
                    @if ($identitasTersimpan)
                        <button type="button" class="tombol-teks label-kanan" wire:click="lupakanSaya">Bukan Anda?</button>
                    @endif
                </label>
                <div class="kotak-isian">
                    <input type="text" id="identitas" wire:model="identitas" autocomplete="username"
                        placeholder="mis. budisantoso" class="@error('identitas') salah @enderror"
                        @error('identitas') aria-invalid="true" aria-describedby="galat-identitas" @enderror
                        x-init="if (window.innerWidth >= 640 && ! $el.value) $el.focus()">
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
        @endif

        @if ($detikTunggu > 0)
            <div class="kabar kabar-galat" role="alert"
                x-data="{ sisa: @js($detikTunggu) }"
                x-init="const t = setInterval(() => { if (--sisa <= 0) clearInterval(t) }, 1000)">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                <span x-show="sisa > 0">
                    Dikunci sementara. Coba lagi dalam <span class="hitung-mundur" x-text="sisa"></span> detik.
                </span>
                <span x-show="sisa <= 0" x-cloak>Silakan coba masuk kembali.</span>
            </div>
        @endif

        @if ($mode === 'pin')
            <div class="baris-sela">
                <button type="button" class="tombol-teks" wire:click="gantiMode('sandi')">
                    Lupa PIN? Masuk dengan kata sandi
                </button>
                <button type="button" class="tombol-teks tombol-teks-lembut" wire:click="mintaMatikanPin"
                    wire:loading.attr="disabled" wire:target="mintaMatikanPin">
                    <span wire:loading.remove wire:target="mintaMatikanPin">Matikan PIN lewat email</span>
                    <span wire:loading wire:target="mintaMatikanPin">Mengirim…</span>
                </button>
            </div>
        @else
            <div class="baris-sela">
                <label class="centang">
                    <input type="checkbox" wire:model.live="ingatSaya">
                    <span>Ingat saya di perangkat ini</span>
                </label>
                <a href="{{ route('formemail.reset') }}" class="tautan">Lupa kata sandi?</a>
            </div>

            <p class="petunjuk petunjuk-ingat">
                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2" /><path d="M7 11V8a5 5 0 0110 0v3" /></svg>
                <span>Dicentang: hanya username/email Anda yang diingat di peramban ini. Kata sandi tidak pernah
                    disimpan.</span>
            </p>
        @endif

        <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="masuk">
            <span wire:loading.remove wire:target="masuk" style="display:inline-flex;align-items:center;gap:10px">
                <svg viewBox="0 0 24 24">
                    <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4" />
                    <path d="M10 17l5-5-5-5M15 12H3" />
                </svg>
                {{ $mode === 'pin' ? 'Masuk dengan PIN' : 'Masuk Sekarang' }}
            </span>
            <span wire:loading.flex wire:target="masuk" style="display:none;align-items:center;gap:10px">
                <span class="pemutar"></span> Memeriksa…
            </span>
        </button>
    </form>

    @if (! $this->siapPin)
        <p class="petunjuk petunjuk-pin-mati">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3" /><path d="M9 17h6" /></svg>
            <span>
                Ingin masuk cukup dengan PIN? Masuk dulu di sini, lalu aktifkan di
                <strong>Profil &rsaquo; PIN Masuk</strong>.
            </span>
        </p>
    @endif

    <p class="kaki-kartu">
        Belum punya akun? <a href="{{ route('register') }}" class="tautan">Buat akun baru</a>
        <br>
        <span style="display:inline-block;margin-top:8px">
            Belum menerima email verifikasi?
            <a href="{{ route('verification.resend') }}" class="tautan">Kirim ulang</a>
        </span>
    </p>
</div>
