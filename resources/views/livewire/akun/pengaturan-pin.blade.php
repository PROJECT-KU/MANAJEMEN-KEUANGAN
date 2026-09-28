{{--
  Tab "PIN masuk" di halaman profil.

  Mengikuti pola tab Data diri: kepala bagian bermedali (ubin + judul +
  keterangan), isian di dalam .mis-kisi-isian, dan satu baris .prof-aksi di
  dasar formulir. Gayanya ada di account/profil/gaya.blade.php yang terbit
  lewat @push('gaya') — bukan <style> di badan berkas, karena aturan di badan
  terbit sebelum CSS <head> sehingga aturan berbobot sama selalu kalah.
--}}
<div>
    {{-- Keadaan PIN saat ini: satu baris ringkas, bukan paragraf --}}
    <div class="pin-keadaan {{ $pengguna->pinAktif() ? 'nyala' : '' }}">
        <span class="mis-medali {{ $pengguna->pinAktif() ? 'mis-biru' : 'mis-kuning' }}" aria-hidden="true">
            <i class="fas {{ $pengguna->pinAktif() ? 'fa-mobile-alt' : 'fa-lock-open' }}"></i>
        </span>

        <div class="pin-keadaan-teks">
            <p class="pin-keadaan-judul">
                {{ $pengguna->pinAktif() ? 'PIN masuk aktif' : 'PIN masuk belum aktif' }}
            </p>
            <p class="pin-keadaan-sub">
                @if ($pengguna->pinAktif())
                    Terakhir diubah
                    {{ $pengguna->pin_diubah_pada ? $pengguna->pin_diubah_pada->locale('id')->translatedFormat('d F Y H:i') : '-' }}
                    WIB
                @else
                    Masuk cukup dengan {{ $panjangPin }} angka, tanpa mengetik kata sandi.
                @endif
            </p>
        </div>

        <span class="prof-lencana {{ $pengguna->pinAktif() ? 'prof-lencana-hijau' : 'prof-lencana-kuning' }}">
            <span class="prof-lencana-titik berdenyut" aria-hidden="true"></span>
            {{ $pengguna->pinAktif() ? 'Aktif' : 'Belum aktif' }}
        </span>
    </div>

    {{-- PIN dikenali per peramban, jadi tiap HP/komputer perlu didaftarkan sendiri --}}
    @if ($pengguna->pinAktif())
        @if ($this->perangkatSiap())
            <div class="pin-perangkat siap">
                <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-check"></i></span>

                <div class="pin-perangkat-teks">
                    <p class="pin-keadaan-judul">Perangkat ini sudah terdaftar</p>
                    <p class="pin-keadaan-sub">
                        Halaman masuk di peramban ini langsung meminta PIN.
                    </p>
                </div>

                <button type="button" class="mis-tombol prof-tombol-halus" wire:click="lupakanPerangkat">
                    <i class="fas fa-unlink"></i> Lupakan perangkat
                </button>
            </div>
        @else
            <div class="pin-perangkat">
                <div class="prof-bagian-kepala">
                    <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-mobile-alt"></i></span>
                    <h4 class="prof-bagian-judul">Pakai PIN di perangkat ini juga</h4>
                    <p class="prof-bagian-sub">
                        PIN Anda sudah aktif, tetapi peramban ini belum terdaftar. Masukkan PIN sekali di sini.
                    </p>
                </div>

                <div class="prof-baris-aksi">
                    <input type="password"
                        class="form-control-modern pin-isian @error('pinPerangkat') is-invalid @enderror"
                        wire:model="pinPerangkat" inputmode="numeric" maxlength="{{ $panjangPin }}"
                        autocomplete="off" placeholder="{{ str_repeat('•', $panjangPin) }}"
                        aria-label="PIN Anda" oninput="this.value = this.value.replace(/\D/g, '')">
                    <button type="button" class="mis-tombol mis-tombol-biru" wire:click="aktifkanDiPerangkat"
                        wire:loading.attr="disabled">
                        <i class="fas fa-plus-circle"></i> Daftarkan
                    </button>
                </div>

                @error('pinPerangkat')
                    <p class="prof-salah mt-2"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                @enderror
            </div>
        @endif

        {{--
          Daftar perangkat yang boleh masuk dengan PIN.

          Ini yang dulu tidak ada sama sekali. Izin PIN cuma hidup di kue
          peramban, jadi pemiliknya tidak bisa melihat perangkat apa saja yang
          punya izin, apalagi mencabutnya dari jauh. HP hilang berarti satu-
          satunya jalan adalah mematikan PIN untuk semua perangkat sekaligus.
        --}}
        @php ($perangkatPin = $this->daftarPerangkatPin())
        @if ($perangkatPin->count() > 1 || ($perangkatPin->count() === 1 && ! $this->perangkatSiap()))
            <div class="prof-bagian">
                <div class="prof-bagian-kepala punya-aksi">
                    <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-mobile-alt"></i></span>
                    <h4 class="prof-bagian-judul">Perangkat yang boleh pakai PIN</h4>
                    <p class="prof-bagian-sub">Kehilangan salah satunya? Cabut izinnya dari sini.</p>
                    <span class="mis-pil mis-pil-abu prof-bagian-lencana">{{ $perangkatPin->count() }} perangkat</span>
                </div>

                <div class="kmn-daftar">
                    @foreach ($perangkatPin as $perangkat)
                        <div class="kmn-perangkat {{ $perangkat->ini ? 'ini' : '' }}">
                            <span class="mis-medali mini {{ $perangkat->ini ? 'mis-hijau' : 'mis-ungu' }}" aria-hidden="true">
                                <i class="fas {{ $perangkat->ini ? 'fa-mobile-alt' : 'fa-desktop' }}"></i>
                            </span>

                            <div class="kmn-perangkat-teks">
                                <p class="kmn-perangkat-nama">
                                    {{ \App\Support\NamaPerangkat::ringkas($perangkat->peramban) }}
                                </p>
                                <p class="kmn-perangkat-ket">
                                    {{ $perangkat->ip ?: 'IP tidak tercatat' }}
                                    @if ($perangkat->terakhir_dipakai_pada)
                                        &middot; dipakai {{ $perangkat->terakhir_dipakai_pada->locale('id')->diffForHumans(null, true) }} lalu
                                    @endif
                                </p>
                            </div>

                            @if ($perangkat->ini)
                                <span class="mis-pil mis-pil-hijau">Perangkat ini</span>
                            @else
                                <button type="button" class="mis-tombol prof-tombol-bahaya-teks kmn-tombol-kecil"
                                    wire:click="lupakanPerangkatLain({{ $perangkat->id }})">
                                    <i class="fas fa-unlink"></i> Cabut
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <form wire:submit="simpan">
        <div class="prof-bagian">
            <div class="prof-bagian-kepala">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-lock"></i></span>
                <h4 class="prof-bagian-judul">Pastikan ini Anda</h4>
                <p class="prof-bagian-sub">Masukkan kata sandi yang dipakai sekarang.</p>
            </div>

            <div class="mis-kisi-isian prof-kisi-dua">
                <div class="mis-isian">
                    <label class="mis-label" for="pin-kata-sandi">
                        Kata sandi akun <span class="prof-wajib" aria-hidden="true">*</span>
                    </label>
                    <input type="password" id="pin-kata-sandi"
                        class="form-control-modern @error('kataSandi') is-invalid @enderror"
                        wire:model="kataSandi" autocomplete="current-password" placeholder="••••••••">
                    @error('kataSandi')
                        <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <p class="prof-catatan-samping">
                    <i class="fas fa-info-circle mis-ikon-biru"></i>
                    Kata sandi diminta supaya orang lain yang memakai komputer Anda tidak bisa mengganti PIN.
                </p>
            </div>
        </div>

        <div class="prof-bagian">
            <div class="prof-bagian-kepala">
                <span class="mis-medali kecil mis-jingga" aria-hidden="true"><i class="fas fa-key"></i></span>
                <h4 class="prof-bagian-judul">{{ $pengguna->pinAktif() ? 'PIN baru' : 'Buat PIN' }}</h4>
                <p class="prof-bagian-sub">{{ $panjangPin }} angka yang Anda ketik di halaman masuk.</p>
            </div>

            {{-- Peringatan lebih dulu, bukan pesan galat sesudah tombol ditekan.
                 Peladen tetap yang memutuskan (lihat simpan()); ini supaya orang
                 tidak terlanjur mengetik angka baru dan mengira PIN di perangkat
                 lain ikut aman. --}}
            @unless ($this->bolehGantiPin())
                <div class="prof-kabar">
                    <span class="mis-medali kecil mis-kuning" aria-hidden="true"><i class="fas fa-lock"></i></span>
                    <p class="prof-kabar-teks">
                        Perangkat ini belum terdaftar, jadi PIN belum bisa diganti dari sini — satu akun hanya
                        punya satu PIN. Isikan PIN yang sekarang untuk memakainya di perangkat ini. Lupa PIN-nya?
                        Matikan dulu PIN lama lewat tombol di bawah, lalu buat yang baru.
                    </p>
                </div>
            @endunless

            <div class="mis-kisi-isian prof-kisi-dua">
                <div class="mis-isian">
                    <label class="mis-label" for="pin-baru">
                        {{ $pengguna->pinAktif() ? 'PIN baru' : 'PIN' }}
                        <span class="prof-wajib" aria-hidden="true">*</span>
                    </label>
                    <input type="password" id="pin-baru"
                        class="form-control-modern pin-isian @error('pin') is-invalid @enderror"
                        wire:model="pin" inputmode="numeric" maxlength="{{ $panjangPin }}" autocomplete="off"
                        placeholder="{{ str_repeat('•', $panjangPin) }}"
                        oninput="this.value = this.value.replace(/\D/g, '')">
                    @error('pin')
                        <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="pin-ulangi">
                        Ulangi PIN <span class="prof-wajib" aria-hidden="true">*</span>
                    </label>
                    <input type="password" id="pin-ulangi"
                        class="form-control-modern pin-isian @error('pinKonfirmasi') is-invalid @enderror"
                        wire:model="pinKonfirmasi" inputmode="numeric" maxlength="{{ $panjangPin }}"
                        autocomplete="off" placeholder="{{ str_repeat('•', $panjangPin) }}"
                        oninput="this.value = this.value.replace(/\D/g, '')">
                    @error('pinKonfirmasi')
                        <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Angka PIN disembunyikan seperti kata sandi; sakelar ini untuk
                 memastikan yang diketik memang benar sebelum disimpan. --}}
            <label class="pin-lihat">
                <input type="checkbox"
                    onchange="document.querySelectorAll('.pin-isian').forEach(function (i) { i.type = this.checked ? 'text' : 'password' }, this)">
                <span>Tampilkan angka PIN</span>
            </label>

            <ul class="pin-tip">
                <li>
                    <span class="mis-medali mini mis-merah" aria-hidden="true"><i class="fas fa-shield-alt"></i></span>
                    Hindari angka berulang, berurutan, atau tanggal lahir.
                </li>
                <li>
                    <span class="mis-medali mini mis-kuning" aria-hidden="true"><i class="fas fa-ban"></i></span>
                    Salah {{ config('auth.pin.batas_gagal', 5) }} kali di halaman masuk &rarr; PIN mati sendiri,
                    Anda dikabari lewat email.
                </li>
                <li>
                    <span class="mis-medali mini mis-biru" aria-hidden="true"><i class="fas fa-laptop"></i></span>
                    Berlaku per perangkat. Di perangkat lain: masuk dengan kata sandi, lalu daftarkan dari sini.
                </li>
            </ul>
        </div>

        <div class="prof-aksi">
            @if ($pengguna->pinAktif())
                <button type="button" class="mis-tombol prof-tombol-bahaya-teks" wire:click="nonaktifkan"
                    wire:loading.attr="disabled">
                    <i class="fas fa-times-circle"></i> Nonaktifkan PIN
                </button>
            @else
                <p class="prof-aksi-catatan">
                    <i class="fas fa-bolt mis-ikon-kuning"></i>
                    Sesudah aktif, masuk cukup {{ $panjangPin }} angka.
                </p>
            @endif

            <button type="submit" class="mis-tombol mis-tombol-ungu" wire:loading.attr="disabled">
                <i class="fas fa-key"></i>
                {{ $pengguna->pinAktif() ? 'Simpan PIN baru' : 'Aktifkan PIN' }}
            </button>
        </div>
    </form>
</div>
