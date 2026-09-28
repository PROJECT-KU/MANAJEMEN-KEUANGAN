{{--
  Tab "Keamanan" di halaman profil.

  Mengikuti pola tab Data diri: tiap bagian punya kepala bermedali, dan
  gayanya tinggal di account/profil/gaya.blade.php lewat @push('gaya') —
  bukan <style> atau style sebaris di badan berkas.
--}}
<div>
    <div class="prof-bagian">
        <div class="prof-bagian-kepala">
            <span class="mis-medali kecil mis-merah" aria-hidden="true"><i class="fas fa-power-off"></i></span>
            <h4 class="prof-bagian-judul">Keluarkan saya dari perangkat lain</h4>
            <p class="prof-bagian-sub">
                Peramban lain yang masih terbuka ikut keluar. Perangkat ini tetap masuk.
            </p>
        </div>

        <div class="prof-baris-aksi">
            <input type="password" class="form-control-modern @error('kataSandi') is-invalid @enderror"
                wire:model="kataSandi" autocomplete="current-password" aria-label="Kata sandi akun Anda"
                placeholder="Kata sandi akun Anda">
            <button type="button" class="mis-tombol prof-tombol-bahaya-teks" wire:click="keluarkanPerangkatLain"
                wire:loading.attr="disabled">
                <i class="fas fa-sign-out-alt"></i> Keluarkan
            </button>
        </div>

        @error('kataSandi')
            <p class="prof-salah mt-2"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
        @enderror
    </div>

    @if ($sesi->isNotEmpty())
        <div class="prof-bagian">
            <div class="prof-bagian-kepala {{ $sesi->count() > 1 ? 'punya-aksi' : '' }}">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-laptop"></i></span>
                <h4 class="prof-bagian-judul">Perangkat yang sedang masuk</h4>
                <p class="prof-bagian-sub">Ada yang bukan Anda? Akhiri sesinya dari sini.</p>
                @if ($sesi->count() > 1)
                    <span class="mis-pil mis-pil-abu prof-bagian-lencana">{{ $sesi->count() }} perangkat</span>
                @endif
            </div>

            <div class="kmn-daftar">
                @foreach ($sesiTampil as $baris)
                    <div class="kmn-perangkat {{ $baris->ini ? 'ini' : '' }}">
                        <span class="mis-medali mini {{ $baris->ini ? 'mis-hijau' : 'mis-biru' }}" aria-hidden="true">
                            <i class="fas {{ $baris->ini ? 'fa-laptop' : 'fa-desktop' }}"></i>
                        </span>

                        <div class="kmn-perangkat-teks">
                            <p class="kmn-perangkat-nama">
                                {{ \App\Support\NamaPerangkat::ringkas($baris->user_agent) }}
                            </p>
                            {{-- "aktif 8 menit lalu", bukan "8 menit yang lalu":
                                 bentuk pendeknya bawaan Carbon berbunyi "8mnt"
                                 dan "1hr" — tidak terbaca. Membuang kata "yang"
                                 cukup untuk memuat barisnya di layar 390px
                                 tanpa mengorbankan kejelasan. --}}
                            <p class="kmn-perangkat-ket">
                                {{ $baris->ip_address ?: 'IP tidak tercatat' }} &middot;
                                aktif {{ $baris->waktu->locale('id')->diffForHumans(null, true) }} lalu
                            </p>
                        </div>

                        @if ($baris->ini)
                            <span class="mis-pil mis-pil-hijau">Perangkat ini</span>
                        @else
                            <button type="button" class="mis-tombol prof-tombol-bahaya-teks kmn-tombol-kecil"
                                wire:click="akhiriSesi('{{ $baris->id }}')">
                                <i class="fas fa-times-circle"></i> Akhiri
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Daftarnya dilipat, bukan digulir: perangkat yang jarang dibuka
                 tidak perlu dilihat tiap kali, dan riwayat di bawahnya tidak
                 ikut terdorong jauh ke bawah. --}}
            @if ($sisaPerangkat > 0)
                <button type="button" class="kmn-lipat" wire:click="$toggle('semuaPerangkat')">
                    @if ($semuaPerangkat)
                        <i class="fas fa-chevron-up" aria-hidden="true"></i> Sembunyikan lagi
                    @else
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        Tampilkan {{ $sisaPerangkat }} perangkat lain
                    @endif
                </button>
            @endif
        </div>
    @endif

    <div class="prof-bagian">
        <div class="prof-bagian-kepala {{ $gagalTerakhir > 0 ? 'punya-aksi' : '' }}">
            <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-history"></i></span>
            {{-- Bukan lagi "riwayat masuk": daftar ini sekarang juga memuat
                 perubahan penting seperti alamat email, username, dan nomor
                 rekening penggajian. --}}
            <h4 class="prof-bagian-judul">Riwayat keamanan</h4>
            <p class="prof-bagian-sub">Percobaan masuk dan perubahan penting pada akun Anda.</p>
            @if ($gagalTerakhir > 0)
                <span class="mis-pil mis-pil-kuning prof-bagian-lencana">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $gagalTerakhir }} gagal dalam 30 hari
                </span>
            @endif
        </div>

        @if ($riwayat->isEmpty())
            <div class="mis-kosong">
                <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-history"></i></span>
                <p class="mis-kosong-judul">Belum ada catatan masuk</p>
                <p class="mis-kosong-teks">Riwayatnya muncul di sini setelah Anda masuk lagi.</p>
            </div>
        @else
            {{-- mis-tabel-kartu: di bawah 576px tabelnya berubah jadi tumpukan
                 kartu, jadi tidak perlu digeser ke samping di ponsel. --}}
            <div class="mis-tabel-bungkus kmn-tabel">
                <table class="mis-tabel mis-tabel-kartu">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Hasil</th>
                            <th>Alamat IP</th>
                            <th>Perangkat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $baris)
                            <tr>
                                <td class="mis-td-utama">
                                    <span class="kmn-waktu">
                                        {{ $baris->created_at?->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                    </span>
                                </td>
                                {{-- Lencana menjawab "berhasil atau tidak", barisan kecil di
                                     bawahnya menjelaskan caranya. Dulu keduanya dijadikan satu
                                     lencana, jadi ada lencana hijau bertuliskan "kata sandi"
                                     yang tidak menyatakan apa pun soal berhasil. --}}
                                <td data-judul="Hasil">
                                    <div class="kmn-hasil">
                                        @if ($baris->berhasil)
                                            <span class="mis-pil mis-pil-hijau">
                                                <i class="fas fa-check"></i> Berhasil
                                            </span>
                                        @else
                                            <span class="mis-pil mis-pil-merah">
                                                <i class="fas fa-times"></i> Gagal
                                            </span>
                                        @endif
                                        @if ($baris->alasan)
                                            <span class="kmn-samar">{{ $baris->alasan }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td data-judul="Alamat IP" class="kmn-samar">{{ $baris->ip ?: '-' }}</td>
                                <td data-judul="Perangkat">
                                    @if ($baris->perangkat && $baris->perangkat === $perangkatIni)
                                        <span class="mis-pil mis-pil-biru">Perangkat ini</span>
                                    @else
                                        <span class="kmn-samar">
                                            {{ \App\Support\NamaPerangkat::ringkas($baris->peramban) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="prof-aksi-catatan kmn-catatan">
                <i class="fas fa-info-circle mis-ikon-biru"></i>
                Ada baris yang bukan Anda? Segera ganti kata sandi — sesi di perangkat lain ikut berakhir dan
                PIN masuk dimatikan.
            </p>
        @endif
    </div>
</div>
