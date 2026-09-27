@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Profil | MIS
@stop

@include('account.profil.gaya')

@section('content')
@php
    /* $user selalu akun yang sedang masuk: ProfilController@show memantulkan
       alamat milik orang lain kembali ke profil sendiri. Dulu di sini ada dua
       variabel dan sebagian kotak isian terisi data orang lain sementara
       tombol Simpan menulis ke akun sendiri. */

    // Masa kerja hanya bermakna kalau email terverifikasi dan akun aktif.
    $masaKerja = ($user->email_verified_at && $user->status === 'active') ? $workDuration : null;
    $masaKerjaRingkas = $masaKerja
        ? (trim((string) preg_replace('/\s*\b0 (tahun|bulan|hari)\b/', '', $masaKerja)) ?: 'Baru')
        : null;

    // Istilah sistem diterjemahkan ke bahasa sehari-hari.
    $labelPeran = [
        'manager' => 'Manager',
        'karyawan' => 'Karyawan',
        'staff' => 'Staff',
        'user' => 'Pengguna layanan',
    ][$user->level] ?? 'Belum ditentukan';

    $labelJenis = [
        'bisnis' => 'Atas nama lembaga',
        'perorangan' => 'Atas nama pribadi',
    ][$user->jenis] ?? 'Belum ditentukan';

    $daftarBank = config('bank');
    $namaBank = $daftarBank[$user->bank] ?? '';

    $fotoProfil = $user->gambar
        ? asset('assets/img/profil/' . $user->gambar)
        : asset('assets/img/profil/no-image.jpg');
@endphp

<div class="main-content" style="padding-top: 110px; background-color: #f4f7ff; min-height: 100vh;">
  <section class="section">
    <div class="prof">

      {{-- ========================================================= kepala --}}
      <header class="mis-kepala">
        <span class="prof-kepala-foto" aria-hidden="true">
          <img src="{{ $fotoProfil }}" alt="">
        </span>

        <div class="mis-kepala-teks">
          <h1 class="mis-judul">Profil saya</h1>
          <p class="mis-sub">
            <span>{{ $user->full_name ?: $user->username }}</span>
            <span class="prof-titik" aria-hidden="true">&middot;</span>
            <span>{{ $user->jobdesk ?: 'Tanpa jabatan' }}</span>
            @if ($user->level !== 'user' && $user->company)
              <span class="prof-titik" aria-hidden="true">&middot;</span>
              <span>{{ $user->company }}</span>
            @endif
          </p>
        </div>

        {{-- Lencana status. Titik di kirinya berdenyut supaya keadaan akun
             terbaca sekilas tanpa harus membaca tulisannya dulu. Denyutnya
             hanya untuk keadaan yang BAIK dan sedang berjalan; keadaan yang
             perlu ditindak dibiarkan diam supaya tidak terasa seperti alarm
             yang menuntut perhatian terus-menerus. --}}
        <div class="mis-kepala-aksi prof-lencana-deret">
          @if ($user->email_verified_at)
            <span class="prof-lencana prof-lencana-hijau">
              <span class="prof-lencana-titik berdenyut" aria-hidden="true"></span>
              Email terverifikasi
            </span>
          @else
            <span class="prof-lencana prof-lencana-kuning">
              <span class="prof-lencana-titik" aria-hidden="true"></span>
              Email belum terverifikasi
            </span>
          @endif

          @if ($user->status === 'active')
            <span class="prof-lencana prof-lencana-biru">
              <span class="prof-lencana-titik berdenyut" aria-hidden="true"></span>
              Akun aktif
            </span>
          @else
            <span class="prof-lencana prof-lencana-merah">
              <span class="prof-lencana-titik" aria-hidden="true"></span>
              Akun nonaktif
            </span>
          @endif
        </div>
      </header>

      <div class="prof-tata">

        {{-- ================================================== kolom kiri --}}
        <aside class="prof-sisi">

          <section class="mis-kartu prof-identitas">
            <div class="prof-foto-bingkai">
              <img id="prf-pratinjau" class="prof-foto" src="{{ $fotoProfil }}"
                alt="Foto profil {{ $user->full_name }}">
              @if ($user->email_verified_at)
                {{-- Lencana bergerigi seperti tanda terverifikasi di Instagram.
                     Bentuknya digambar sebagai satu path SVG (12 tonjolan), bukan
                     lingkaran CSS, karena tepi bergelombangnya tidak bisa dibuat
                     dengan border-radius. --}}
                <span class="prof-foto-lencana" title="Email sudah terverifikasi">
                  <svg viewBox="-7 -7 114 114" aria-hidden="true">
                    <path class="prof-lencana-tepi" d="M89.0 50.0 Q104.4 64.6 83.8 69.5 Q89.8 89.8 69.5 83.8 Q64.6 104.4 50.0 89.0 Q35.4 104.4 30.5 83.8 Q10.2 89.8 16.2 69.5 Q-4.4 64.6 11.0 50.0 Q-4.4 35.4 16.2 30.5 Q10.2 10.2 30.5 16.2 Q35.4 -4.4 50.0 11.0 Q64.6 -4.4 69.5 16.2 Q89.8 10.2 83.8 30.5 Q104.4 35.4 89.0 50.0Z" />
                    <path class="prof-lencana-isi" d="M89.0 50.0 Q104.4 64.6 83.8 69.5 Q89.8 89.8 69.5 83.8 Q64.6 104.4 50.0 89.0 Q35.4 104.4 30.5 83.8 Q10.2 89.8 16.2 69.5 Q-4.4 64.6 11.0 50.0 Q-4.4 35.4 16.2 30.5 Q10.2 10.2 30.5 16.2 Q35.4 -4.4 50.0 11.0 Q64.6 -4.4 69.5 16.2 Q89.8 10.2 83.8 30.5 Q104.4 35.4 89.0 50.0Z" />
                    <path class="prof-lencana-centang" d="M33 51 L45 63 L68 38" />
                  </svg>
                </span>
              @else
                <span class="prof-foto-lencana belum" title="Email belum terverifikasi">
                  <svg viewBox="-7 -7 114 114" aria-hidden="true">
                    <path class="prof-lencana-tepi" d="M89.0 50.0 Q104.4 64.6 83.8 69.5 Q89.8 89.8 69.5 83.8 Q64.6 104.4 50.0 89.0 Q35.4 104.4 30.5 83.8 Q10.2 89.8 16.2 69.5 Q-4.4 64.6 11.0 50.0 Q-4.4 35.4 16.2 30.5 Q10.2 10.2 30.5 16.2 Q35.4 -4.4 50.0 11.0 Q64.6 -4.4 69.5 16.2 Q89.8 10.2 83.8 30.5 Q104.4 35.4 89.0 50.0Z" />
                    <path class="prof-lencana-isi" d="M89.0 50.0 Q104.4 64.6 83.8 69.5 Q89.8 89.8 69.5 83.8 Q64.6 104.4 50.0 89.0 Q35.4 104.4 30.5 83.8 Q10.2 89.8 16.2 69.5 Q-4.4 64.6 11.0 50.0 Q-4.4 35.4 16.2 30.5 Q10.2 10.2 30.5 16.2 Q35.4 -4.4 50.0 11.0 Q64.6 -4.4 69.5 16.2 Q89.8 10.2 83.8 30.5 Q104.4 35.4 89.0 50.0Z" />
                    <path class="prof-lencana-centang" d="M50 30 L50 56 M50 68 L50 70" />
                  </svg>
                </span>
              @endif
            </div>

            <h2 class="prof-nama">{{ $user->full_name ?: $user->username }}</h2>
            <p class="prof-username">&#64;{{ $user->username }}</p>

            <div class="prof-pil-baris">
              <span class="mis-pil mis-pil-ungu"><i class="fas fa-briefcase"></i> {{ $user->jobdesk ?: 'Tanpa jabatan' }}</span>
              @if ($user->level !== 'user' && $user->company)
                <span class="mis-pil mis-pil-abu"><i class="fas fa-building"></i> {{ $user->company }}</span>
              @endif
            </div>

            <div class="prof-mini-kisi">
              <div class="prof-mini">
                <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-user-check"></i></span>
                <p class="prof-mini-angka">{{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</p>
                <p class="prof-mini-label">Status</p>
              </div>
              <div class="prof-mini">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                <p class="prof-mini-angka">{{ $labelPeran }}</p>
                <p class="prof-mini-label">Peran</p>
              </div>
              <div class="prof-mini">
                <span class="mis-medali kecil mis-jingga" aria-hidden="true"><i class="fas fa-hourglass-half"></i></span>
                <p class="prof-mini-angka" title="{{ $masaKerja ?: 'Belum dihitung' }}">{{ $masaKerjaRingkas ?: '—' }}</p>
                <p class="prof-mini-label">Bergabung</p>
              </div>
            </div>

            <form action="{{ route('account.profil.updatePhoto') }}" method="POST"
              enctype="multipart/form-data" class="prof-unggah-bungkus">
              @csrf
              <input type="file" name="gambar" id="foto" class="prof-berkas"
                accept="image/jpeg,image/png,image/gif">
              <label for="foto" class="prof-unggah">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-camera"></i></span>
                <span class="prof-unggah-nama" id="prf-nama-berkas">Pilih foto baru</span>
                <span class="mis-bantuan">JPG, PNG, atau GIF &middot; maksimal 3 MB</span>
              </label>
              <button type="submit" id="updatePhotoBtn" class="mis-tombol mis-tombol-ungu prof-penuh" disabled>
                <i class="fas fa-cloud-upload-alt"></i> Simpan foto
              </button>
            </form>

            {{-- Email ikut di kartu ini, bukan kartu sendiri: isinya sama-sama
                 "siapa saya dan bagaimana dihubungi", dan satu kartu terpisah
                 untuk satu baris membuat kolom kiri jauh lebih tinggi daripada
                 kolom kanan sehingga menyisakan petak kosong. --}}
            <div class="prof-email-blok">

              {{-- Judulnya cukup satu baris kecil; ikon besar dan kalimat
                   penjelas di atas baris ini hanya mengulang apa yang sudah
                   terlihat, sementara tingginya ikut menekan kolom kanan. --}}
              <p class="mis-label prof-email-label">Alamat email</p>

              <div class="prof-baris">
                {{-- Ubin, alamat, keterangan, dan tombol ubah dalam satu kisi: ubin dan
                     tombol merentang dua baris lalu dirata-tengahkan, jadi keduanya
                     sejajar dengan BLOK teksnya — aturan yang sama dengan kepala
                     bagian. Keterangan otomatis menjorok karena ia di kolom kedua. --}}
                <span class="mis-medali mini mis-biru" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                <p class="prof-nilai">{{ $user->email }}</p>
                @if ($user->email_verified_at)
                  <p class="mis-bantuan prof-baris-ket"><i class="fas fa-check-circle mis-ikon-hijau"></i> Sudah diverifikasi</p>
                @else
                  <p class="mis-bantuan prof-baris-ket"><i class="fas fa-exclamation-circle mis-ikon-kuning"></i> Belum diverifikasi</p>
                @endif
                <button type="button" class="mis-tombol-garis" id="openPopupButtonEmail" title="Ganti alamat email">
                  <i class="fas fa-pen"></i>
                </button>
              </div>

              @if (! $user->email_verified_at)
                <form id="verify-email-form" action="{{ route('account.profil.verify.email') }}" method="POST"
                  class="prof-verif">
                  @csrf
                  <input type="hidden" name="code_verified_mail" value="{{ $user->code_verified_mail }}">
                  <div id="container-verify-btn">
                    <button type="button" id="btn-verify-email" class="mis-tombol mis-tombol-biru prof-penuh">
                      <i class="fas fa-paper-plane"></i> Kirim kode verifikasi
                    </button>
                    <p class="mis-bantuan">Kode 6 angka dikirim ke alamat di atas.</p>
                  </div>
                </form>
              @endif
            </div>
          </section>

          {{-- Kelengkapan profil. Bukan hiasan: tanpa ini profil yang separuh
               terisi tampak sama saja dengan yang lengkap, dan orang baru
               tahu ada yang kurang saat sistem lain menolaknya. --}}
          <section class="mis-kartu prof-lengkap">
            <div class="prof-lengkap-atas">
              <span class="prof-cincin" role="img"
                aria-label="Profil {{ $kelengkapan['persen'] }} persen lengkap"
                style="--nilai: {{ $kelengkapan['persen'] }}">
                <span class="prof-cincin-isi">
                  <strong>{{ $kelengkapan['persen'] }}<small>%</small></strong>
                </span>
              </span>

              <div class="prof-lengkap-teks">
                <h3 class="mis-kartu-judul">
                  @if ($kelengkapan['persen'] === 100)
                    Profil Anda lengkap
                  @else
                    Lengkapi profil Anda
                  @endif
                </h3>
                <p class="mis-kartu-sub">
                  {{ $kelengkapan['selesai'] }} dari {{ $kelengkapan['total'] }} hal sudah terisi.
                </p>
              </div>
            </div>

            @if ($kelengkapan['kurang'])
              <ul class="prof-lengkap-daftar">
                @foreach ($kelengkapan['kurang'] as $butir)
                  <li>
                    <button type="button" class="prof-lengkap-butir"
                      data-tab="{{ $butir['tab'] ?? '' }}" data-medan="{{ $butir['medan'] ?? '' }}"
                      data-kunci="{{ $butir['kunci'] }}">
                      <span class="mis-medali kecil mis-{{ $butir['warna'] }}" aria-hidden="true">
                        <i class="fas {{ $butir['ikon'] }}"></i>
                      </span>
                      <span class="prof-lengkap-butir-judul">{{ $butir['judul'] }}</span>
                      <span class="mis-bantuan">{{ $butir['catatan'] }}</span>
                      <i class="fas fa-chevron-right prof-lengkap-panah" aria-hidden="true"></i>
                    </button>
                  </li>
                @endforeach
              </ul>
            @else
              <p class="prof-lengkap-tuntas">
                <i class="fas fa-check-circle mis-ikon-hijau"></i>
                Tidak ada yang perlu diisi lagi.
              </p>
            @endif
          </section>

        </aside>

        {{-- ================================================= kolom kanan --}}
        <div class="prof-utama">
          <div class="mis-kartu prof-tab-kartu">
            <div class="prof-tab-kepala">
              {{-- Memakai pola kepala bagian yang sudah baku di halaman ini:
                   ubin ikon merentang dua baris lalu dirata-tengahkan, jadi ia
                   sejajar dengan blok judul + keterangan. Sebelumnya ikonnya
                   menempel di dalam <h3> sehingga hanya setinggi judulnya, dan
                   ukurannya ikut terkunci 20px oleh aturan global layout. --}}
              <div class="prof-bagian-kepala prof-kepala-kartu">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-id-card"></i></span>
                <h3 class="prof-bagian-judul">Pengaturan akun</h3>
                <p class="prof-bagian-sub">Data diri, kata sandi, PIN masuk, dan riwayat keamanan.</p>
              </div>

              <ul class="prof-tab nav nav-pills" id="pills-tab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="pills-activity-tab" data-toggle="pill" href="#activity" role="tab">
                    <i class="fas fa-user-circle mis-ikon-ungu"></i> Data diri
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="pills-settings-tab" data-toggle="pill" href="#settings" role="tab">
                    <i class="fas fa-key mis-ikon-jingga"></i> Kata sandi
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="pills-pin-tab" data-toggle="pill" href="#pin" role="tab">
                    <i class="fas fa-mobile-alt mis-ikon-biru"></i> PIN masuk
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="pills-keamanan-tab" data-toggle="pill" href="#keamanan" role="tab">
                    <i class="fas fa-user-shield mis-ikon-hijau"></i> Keamanan
                  </a>
                </li>
              </ul>
            </div>

            <div class="prof-tab-isi">
              <div class="tab-content" id="profileTabContent">

                {{-- ---------------------------------------- tab 1: data diri --}}
                <div class="tab-pane fade show active" id="activity" role="tabpanel">
                  @if (! $user->email_verified_at)
                    <div class="prof-kabar">
                      <span class="mis-medali kecil mis-kuning" aria-hidden="true"><i class="fas fa-shield-alt"></i></span>
                      <p class="prof-kabar-teks">
                        Email Anda belum diverifikasi. Tanpa itu, akun tidak bisa dipulihkan kalau
                        kata sandi terlupa.
                      </p>
                    </div>
                  @endif

                  <form id="form-update-data" action="{{ route('account.profil.update') }}" method="POST">
                    @csrf

                    <div class="prof-bagian">
                      <div class="prof-bagian-kepala">
                        <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-id-badge"></i></span>
                        <h4 class="prof-bagian-judul">Nama &amp; kontak</h4>
                        <p class="prof-bagian-sub">Yang tampil di sistem dan cara kami menghubungi Anda.</p>
                      </div>

                      <div class="mis-kisi-isian">
                        <div class="mis-isian">
                          <label class="mis-label" for="prof-nama">Nama lengkap</label>
                          <input class="form-control-modern @error('full_name') is-invalid @enderror"
                            id="prof-nama" type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}">
                          @error('full_name')
                            <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                          @enderror
                        </div>

                        <div class="mis-isian">
                          <label class="mis-label" for="prof-username">Username</label>
                          <input class="form-control-modern @error('username') is-invalid @enderror"
                            id="prof-username" type="text" name="username" value="{{ old('username', $user->username) }}">
                          @error('username')
                            <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                          @else
                            <p class="mis-bantuan">Dipakai untuk masuk, jadi harus unik.</p>
                          @enderror
                        </div>

                        <div class="mis-isian">
                          <label class="mis-label" for="prof-telp">Nomor WhatsApp</label>
                          <input class="form-control-modern @error('telp') is-invalid @enderror"
                            id="prof-telp" type="text" name="telp" value="{{ old('telp', $user->telp) }}"
                            inputmode="numeric" placeholder="08xxxxxxxxxx" oninput="formatPhoneNumber(this)">
                          @error('telp')
                            <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                          @enderror
                        </div>
                      </div>

                      {{-- Kisi kedua. Lima isian di satu kisi tiga kolom menyisakan
                           satu sel kosong sesudah tanggal lahir; dipecah 3 + 2 supaya
                           tiap baris terisi penuh. auto-fit meniadakan jalur yang tidak
                           terpakai, jadi dua isian ini melebar mengisi barisnya. --}}
                      <div class="mis-kisi-isian prof-kisi-dua">
                        @if ($user->level !== 'user')
                          <div class="mis-isian">
                            <label class="mis-label" for="prof-jobdesk">Posisi / jabatan</label>
                            <select class="form-control-modern @error('jobdesk') is-invalid @enderror"
                              id="prof-jobdesk" name="jobdesk">
                              <option value="">Belum ditentukan</option>
                              @foreach (['MANAGER', 'STAFF', 'ASISTEN TRAINER', 'KARYAWAN'] as $posisi)
                                <option value="{{ $posisi }}" @selected(old('jobdesk', $user->jobdesk) === $posisi)>{{ $posisi }}</option>
                              @endforeach
                            </select>
                            @error('jobdesk')
                              <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                          </div>
                        @endif

                        <div class="mis-isian">
                          <label class="mis-label" for="tanggal_lahir">Tanggal lahir</label>
                          <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                            class="form-control-modern @error('tanggal_lahir') is-invalid @enderror"
                            value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}"
                            max="{{ \Carbon\Carbon::now()->subYears(17)->format('Y-m-d') }}">
                          @error('tanggal_lahir')
                            <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                          @else
                            <p class="mis-bantuan">Boleh dikosongkan. Minimal 17 tahun.</p>
                          @enderror
                        </div>
                      </div>
                    </div>

                    @if ($user->level !== 'user')
                      <div class="prof-bagian">
                        <div class="prof-bagian-kepala">
                          <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-wallet"></i></span>
                          <h4 class="prof-bagian-judul">Rekening penggajian</h4>
                          <p class="prof-bagian-sub">Ke rekening inilah gaji Anda dikirim.</p>
                        </div>

                        <div class="mis-kisi-isian">
                          {{-- Bank penggajian dikunci ke BRI: gaji dikirim lewat satu bank,
                               jadi rekening yang didaftarkan harus rekening BRI. Dulu di sini ada
                               kotak ketik berisi 58 bank, padahal memilih bank lain tidak pernah
                               boleh. Penguncian sebenarnya ada di peladen; ini hanya tampilannya. --}}
                          <div class="mis-isian">
                            <label class="mis-label"><i class="fas fa-lock"></i> Nama bank</label>
                            <div class="prof-statis">
                              <span class="mis-medali mini mis-biru" aria-hidden="true"><i class="fas fa-university"></i></span>
                              <div>
                                <p class="prof-statis-label">Ditetapkan perusahaan</p>
                                <p class="prof-statis-nilai">{{ config('bank')['002'] }}</p>
                              </div>
                            </div>
                            <p class="mis-bantuan">Gaji dikirim lewat BRI, jadi rekeningnya harus rekening BRI.</p>
                          </div>

                          <div class="mis-isian">
                            <label class="mis-label" for="norek">Nomor rekening</label>
                            <input type="text" id="norek" name="norek"
                              class="form-control-modern @error('norek') is-invalid @enderror"
                              value="{{ old('norek', $user->norek) }}" placeholder="Nomor rekening"
                              maxlength="40" inputmode="numeric" oninput="formatNoRek(this)">
                            @error('norek')
                              <p class="prof-salah"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                          </div>
                        </div>
                      </div>
                    @endif

                    <div class="prof-bagian">
                      <div class="prof-bagian-kepala">
                        <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-user-shield"></i></span>
                        <h4 class="prof-bagian-judul">Ditetapkan oleh admin</h4>
                        <p class="prof-bagian-sub">Hanya bisa diubah lewat halaman pengelolaan pengguna.</p>
                      </div>

                      <div class="mis-kisi-isian prof-kisi-admin">
                        <div class="prof-statis">
                          <span class="mis-medali mini mis-hijau" aria-hidden="true"><i class="fas fa-user-check"></i></span>
                          <div>
                            <p class="prof-statis-label">Status akun</p>
                            <p class="prof-statis-nilai">{{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</p>
                          </div>
                        </div>

                        <div class="prof-statis">
                          <span class="mis-medali mini mis-ungu" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                          <div>
                            <p class="prof-statis-label">Peran di sistem</p>
                            <p class="prof-statis-nilai">{{ $labelPeran }}</p>
                          </div>
                        </div>

                        <div class="prof-statis">
                          <span class="mis-medali mini mis-jingga" aria-hidden="true"><i class="fas fa-user-tag"></i></span>
                          <div>
                            <p class="prof-statis-label">Jenis akun</p>
                            <p class="prof-statis-nilai">{{ $labelJenis }}</p>
                          </div>
                        </div>

                        @if ($user->level !== 'user')
                          <div class="prof-statis">
                            <span class="mis-medali mini mis-biru" aria-hidden="true"><i class="fas fa-building"></i></span>
                            <div>
                              <p class="prof-statis-label">Perusahaan</p>
                              <p class="prof-statis-nilai">{{ $user->company ?: 'Tidak ada' }}</p>
                            </div>
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="prof-aksi">
                      <p class="prof-aksi-catatan">
                        <i class="fas fa-info-circle mis-ikon-ungu"></i>
                        Semua kolom di atas boleh dikosongkan.
                      </p>
                      <button type="submit" class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-save"></i> Simpan perubahan
                      </button>
                    </div>
                  </form>
                </div>

                {{-- ------------------------------------- tab 2: kata sandi --}}
                <div class="tab-pane fade" id="settings" role="tabpanel">
                  <form id="register-form" action="{{ route('account.profil.reset.password') }}" method="POST">
                    @csrf

                    <div class="prof-bagian">
                      <div class="prof-bagian-kepala">
                        <span class="mis-medali kecil mis-jingga" aria-hidden="true"><i class="fas fa-key"></i></span>
                        <h4 class="prof-bagian-judul">Ganti kata sandi</h4>
                        <p class="prof-bagian-sub">Setelah berhasil, Anda akan diminta masuk ulang.</p>
                      </div>

                      <div class="mis-kisi-isian">
                        <div class="mis-isian mis-isian-penuh">
                          <label class="mis-label" for="old-password">
                            Kata sandi lama <span class="prof-wajib" aria-hidden="true">*</span>
                          </label>
                          <div class="prof-sandi">
                            <input type="password" class="form-control-modern" id="old-password"
                              name="old_password" placeholder="••••••••" autocomplete="current-password" required>
                            <i class="fas fa-eye password-toggle-inside" id="old-password-toggle"></i>
                          </div>
                        </div>

                        <div class="mis-isian">
                          <label class="mis-label" for="password">
                            Kata sandi baru <span class="prof-wajib" aria-hidden="true">*</span>
                          </label>
                          <div class="prof-sandi">
                            <input type="password" class="form-control-modern" name="password" id="password"
                              placeholder="••••••••" autocomplete="new-password" required>
                            <i class="fas fa-eye password-toggle-inside" id="password-toggle"></i>
                          </div>
                        </div>

                        <div class="mis-isian">
                          <label class="mis-label" for="password_confirmation">
                            Ulangi kata sandi baru <span class="prof-wajib" aria-hidden="true">*</span>
                          </label>
                          <div class="prof-sandi">
                            <input type="password" class="form-control-modern" name="password_confirmation"
                              id="password_confirmation" placeholder="••••••••" autocomplete="new-password" required>
                            <i class="fas fa-eye password-toggle-inside" id="password-confirmation-toggle"></i>
                          </div>
                        </div>
                      </div>

                      <div class="prof-aksi">
                        <p class="prof-aksi-catatan">
                          <i class="fas fa-shield-alt mis-ikon-hijau"></i>
                          Minimal 8 huruf dan angka, dan jangan ulangi sandi dari layanan lain.
                        </p>
                        <button type="submit" class="mis-tombol mis-tombol-ungu">
                          <i class="fas fa-key"></i> Perbarui kata sandi
                        </button>
                      </div>
                    </div>
                  </form>
                </div>

                {{-- --------------------------------------- tab 3: PIN masuk --}}
                <div class="tab-pane fade" id="pin" role="tabpanel">
                  <livewire:akun.pengaturan-pin />
                </div>

                {{-- --------------------------------------- tab 4: keamanan --}}
                <div class="tab-pane fade" id="keamanan" role="tabpanel">
                  <livewire:akun.keamanan-akun />
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ========================================================== modal --}}

      <div id="customPopupEmail" class="custom-popup">
        <div class="custom-popup-content">
          <button type="button" class="custom-popup-close" id="customPopupCloseEmail" aria-label="Tutup"><i class="fas fa-times"></i></button>
          <div class="prof-modal-kepala">
            <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-envelope"></i></span>
            <h5 class="prof-modal-judul">Ganti alamat email</h5>
            <p class="prof-modal-sub">Alamat baru wajib diverifikasi ulang.</p>
          </div>
          <form action="{{ route('account.pengguna.update.datadiri', $user->id) }}" method="POST">
            @csrf
            <div class="mis-isian prof-rapat">
              <label class="mis-label" for="prof-email-baru">
                Alamat email baru <span class="prof-wajib" aria-hidden="true">*</span>
              </label>
              <input type="email" class="form-control-modern" id="prof-email-baru" name="email"
                value="{{ $user->email }}" required>
            </div>
            <div class="mis-isian prof-rapat">
              <label class="mis-label" for="prof-sandi-email">
                Kata sandi akun Anda <span class="prof-wajib" aria-hidden="true">*</span>
              </label>
              <input type="password" class="form-control-modern" id="prof-sandi-email" name="kata_sandi_email"
                placeholder="••••••••" autocomplete="current-password" required>
              <p class="mis-bantuan">Diminta karena email dipakai untuk memulihkan akun.</p>
            </div>
            <button type="submit" class="mis-tombol mis-tombol-ungu prof-penuh">
              <i class="fas fa-save"></i> Simpan alamat email
            </button>
          </form>
        </div>
      </div>

      <div id="customPopup" class="custom-popup" style="display:none;">
        <div class="custom-popup-content">
          <button type="button" class="custom-popup-close" id="customPopupClose" aria-label="Tutup"><i class="fas fa-times"></i></button>
          <div class="prof-modal-kepala">
            <span class="mis-medali kecil mis-kuning" aria-hidden="true"><i class="fas fa-shield-alt"></i></span>
            <h5 class="prof-modal-judul">Masukkan kode verifikasi</h5>
            <p class="prof-modal-sub">Enam angka yang baru dikirim ke email Anda.</p>
          </div>
          <form id="verification-form" action="{{ route('account.profil.verify.code') }}" method="POST">
            @csrf
            <div class="mis-isian prof-rapat">
              <input type="text" name="verification_code" class="form-control-modern prof-kode"
                placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
            </div>
            <button type="submit" class="mis-tombol mis-tombol-ungu prof-penuh">
              <i class="fas fa-check-circle"></i> Verifikasi sekarang
            </button>
          </form>
        </div>
      </div>

    </div>
  </section>
</div>

<!--================== FORMAT NOMOR REKENING ==================-->
<script>
  /*
   * Nomor rekening hanya dibersihkan dari karakter selain angka.
   *
   * Sebelumnya ia dipaksa ke pola 4-2-6-2-1, yaitu format BRI. Akibatnya
   * nomor 15 angka milik bank mana pun tampil sebagai "1234-56-789012-34-5",
   * padahal tiap bank punya panjang dan pengelompokan sendiri — Mandiri 13
   * angka, BCA 10, BNI 10. Tidak ada satu pola yang benar untuk semuanya,
   * jadi lebih baik tidak memaksakan pola apa pun.
   */
  function formatNoRek(input) {
    input.value = input.value.replace(/\D/g, '');
  }
</script>

<!--================== FORMAT NOMOR TELEPON ==================-->
<script>
  function formatPhoneNumber(input) {
    // Menghapus semua karakter non-digit
    var phoneNumber = input.value.replace(/\D/g, '');

    // Menentukan panjang nomor telepon
    var phoneNumberLength = phoneNumber.length;

    // Memeriksa panjang nomor telepon dan menerapkan format yang sesuai
    if (phoneNumberLength === 11) {
      phoneNumber = phoneNumber.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
    } else if (phoneNumberLength === 12) {
      phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
    } else if (phoneNumberLength === 13) {
      phoneNumber = phoneNumber.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
    }

    // Mengatur nilai input dengan nomor telepon yang diformat
    input.value = phoneNumber;
  }
</script>

<!--================== UNGGAH FOTO ==================-->
<script>
  document.getElementById('foto').addEventListener('change', function() {
    const fileInput = this;
    const btn = document.getElementById('updatePhotoBtn');
    const namaBerkas = document.getElementById('prf-nama-berkas');
    const pratinjau = document.getElementById('prf-pratinjau');
    const fotoAwal = pratinjau ? pratinjau.src : null;
    const maxFileSize = 3 * 1024 * 1024; // 3MB
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Kembali ke keadaan semula: tombol mati, nama berkas dan foto seperti
    // sebelum orang memilih apa pun.
    function batalkan() {
      fileInput.value = '';
      btn.disabled = true;
      namaBerkas.textContent = 'Pilih foto baru';
    }

    if (fileInput.files.length > 0) {
      const file = fileInput.files[0];
      const extension = file.name.split('.').pop().toLowerCase();

      // 1. Validasi Ekstensi
      if (!allowedExtensions.includes(extension)) {
        misToast('gagal', 'Hanya JPG, PNG, atau GIF yang bisa dipakai.');
        batalkan();
        return;
      }

      // 2. Validasi Ukuran
      if (file.size > maxFileSize) {
        misToast('gagal', 'Foto terlalu besar. Maksimal 3 MB.');
        batalkan();
        return;
      }

      // Jika lolos semua validasi, aktifkan tombol. Fotonya langsung
      // ditampilkan supaya orang tahu yang mana yang akan tersimpan.
      btn.disabled = false;
      namaBerkas.textContent = file.name;

      if (pratinjau) {
        const alamat = URL.createObjectURL(file);
        pratinjau.src = alamat;
        pratinjau.addEventListener('load', function bersihkan() {
          URL.revokeObjectURL(alamat);
          pratinjau.removeEventListener('load', bersihkan);
        });
      }
    } else {
      batalkan();

      if (pratinjau && fotoAwal) {
        pratinjau.src = fotoAwal;
      }
    }
  });
</script>

<!--================== VERIFIKASI EMAIL ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btnVerify = document.getElementById('btn-verify-email');
    const container = document.getElementById('container-verify-btn');
    const customPopup = document.getElementById('customPopup');
    const verificationForm = document.getElementById('verification-form');

    // 1. FUNGSI COUNTDOWN
    function startCountdown(duration) {
      if (!btnVerify || !container) return;

      const countdownSpan = document.createElement('span');
      countdownSpan.className = 'btn btn-warning w-100 disabled';
      countdownSpan.style.cssText = 'padding: 13px; font-size: 12px; border-radius: 14px;';

      container.replaceChild(countdownSpan, btnVerify);

      let timer = duration;
      const interval = setInterval(() => {
        countdownSpan.textContent = `Tunggu ${timer} detik`;
        localStorage.setItem('countdownRemaining', timer);
        localStorage.setItem('countdownStartDate', new Date().toISOString());

        if (--timer < 0) {
          clearInterval(interval);
          countdownSpan.replaceWith(btnVerify);
          localStorage.removeItem('countdownRemaining');
          localStorage.removeItem('countdownStartDate');
        }
      }, 1000);
    }

    // Lanjutkan countdown jika direfresh
    const savedRemaining = parseInt(localStorage.getItem('countdownRemaining')) || 0;
    if (savedRemaining > 0) {
      startCountdown(savedRemaining);
    }

    // 2. EVENT KLIK MINTA KODE KE EMAIL
    if (btnVerify) {
      btnVerify.addEventListener('click', function(e) {
        e.preventDefault();

        // Cegah klik berulang
        if (localStorage.getItem('countdownRemaining') > 0) return;

        // Mulai countdown 120 detik
        startCountdown(60);

        // Fetch ke server minta email
        fetch("{{ route('account.profil.verify.email') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.statusterkirim === 'success') {
              // Toast, bukan modal: kotak isian kodenya langsung dibuka supaya
              // orang tidak perlu menekan OK dulu sebelum bisa mengetik.
              misToast('berhasil', 'Kode dikirim. Periksa email Anda.');
              customPopup.style.display = 'block';
            } else {
              misToast('gagal', data.message || 'Kode gagal dikirim.');
            }
          })
          .catch(() => {
            misToast('gagal', 'Kode gagal dikirim. Periksa sambungan Anda.');
          });
      });
    }

    // 3. EVENT SUBMIT FORM DI DALAM MODAL
    if (verificationForm) {
      verificationForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const verificationCode = this.querySelector('input[name="verification_code"]').value;

        fetch(this.action, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              verification_code: verificationCode,
              _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.statusvalid === 'success') {
              misToast('berhasil', data.message);
              customPopup.style.display = 'none';
              // Dimuat ulang supaya lencana "terverifikasi" ikut berubah.
              setTimeout(function () { window.location.reload(); }, 1200);
            } else {
              misToast('gagal', data.message);
            }
          })
          .catch(() => {
            misToast('gagal', 'Kode gagal diperiksa. Coba lagi sebentar.');
          });
      });
    }

    // 4. TUTUP MODAL JIKA KLIK X ATAU AREA LUAR
    const closeButton = document.getElementById('customPopupClose');
    if (closeButton) {
      closeButton.addEventListener('click', () => customPopup.style.display = 'none');
    }
    window.addEventListener('click', (event) => {
      if (event.target === customPopup) {
        customPopup.style.display = 'none';
      }
    });
  });
</script>

<!--================== GANTI KATA SANDI ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');

    // 1. FUNGSI TOGGLE PASSWORD (SHOW/HIDE)
    function setupPasswordToggle(inputId, toggleId) {
      const input = document.getElementById(inputId);
      const toggle = document.getElementById(toggleId);

      if (input && toggle) {
        toggle.addEventListener('click', function() {
          const isPassword = input.type === 'password';
          input.type = isPassword ? 'text' : 'password';

          // Ganti Icon
          this.classList.toggle('fa-eye');
          this.classList.toggle('fa-eye-slash');

          // Efek warna saat aktif
          this.style.color = isPassword ? '#4e73df' : '#94a3b8';
        });
      }
    }

    // Inisialisasi untuk ketiga field
    setupPasswordToggle('old-password', 'old-password-toggle');
    setupPasswordToggle('password', 'password-toggle');
    setupPasswordToggle('password_confirmation', 'password-confirmation-toggle');


    // 2. LOGIC SUBMIT FORM (VALIDASI, AJAX, & AUTO-LOGOUT)
    if (registerForm) {
      registerForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        // A. Validasi Client-side: Kecocokan Password Baru
        if (password !== passwordConfirmation) {
          misToast('gagal', 'Ulangan kata sandi tidak sama.');
          return;
        }

        // B. Kirim Data via Fetch API
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        fetch(this.action, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
          })
          .then(response => response.json())
          .then(res => {
            if (res.statuserrorreset === 'error') {
              // Jika password lama salah atau ada error validasi server
              misToast('gagal', res.message);
            } else if (res.statussuksesreset === 'success') {
              // Jika Berhasil -> Beri notifikasi lalu Auto-Logout
              // Toast dulu, keluar belakangan: kalau langsung keluar, orang
              // tidak sempat membaca kenapa ia tiba-tiba ada di halaman masuk.
              misToast('berhasil', 'Kata sandi diperbarui. Silakan masuk lagi.', 2600);

              setTimeout(function () {
                const logoutForm = document.getElementById('logout-form');
                if (logoutForm) {
                  logoutForm.submit();
                } else {
                  window.location.href = '/logout';
                }
              }, 2600);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            misToast('gagal', 'Sambungan terganggu. Coba lagi sebentar.');
          });
      });
    }
  });
</script>


<!--================== PEMBERITAHUAN DARI PELADEN ==================-->
<script>
  /*
   * Semua pesan hasil simpan lewat satu pintu: toast.
   * Sebelumnya tiap pesan memakai modal SweetAlert yang menutupi layar dan
   * memuat ulang halaman sesudahnya, sehingga pengguna kehilangan posisinya.
   */
  document.addEventListener('DOMContentLoaded', function () {
    @if (session('statusdataprofil'))
      misToast('berhasil', @json(session('statusdataprofil')));
    @endif

    @if (session('statusdatabank'))
      misToast('berhasil', 'Data rekening berhasil diperbarui.');
    @endif

    @if (session('success'))
      misToast('berhasil', @json(session('success')));
    @endif

    @if (session('statusauthorized'))
      misToast('peringatan', 'Anda tidak berhak mengubah alamat email ini.');
    @endif

    @if (session('erroremailterpakai'))
      misToast('gagal', 'Email itu sudah dipakai akun lain.');
    @endif

    @if (session('errorsandiemail'))
      misToast('gagal', @json(session('errorsandiemail')));
    @endif

    @if (session('errortanggallahir'))
      misToast('gagal', @json(session('errortanggallahir')));
    @endif

    @if (session('error'))
      misToast('gagal', @json(session('error')));
    @endif

    @if ($errors->any())
      misToast('gagal', @json($errors->count() === 1 ? $errors->first() : 'Ada ' . $errors->count() . ' isian yang perlu diperbaiki.'));
    @endif
  });
</script>

<!--================== JENDELA GANTI EMAIL ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const buka = document.getElementById('openPopupButtonEmail');
    const jendela = document.getElementById('customPopupEmail');
    const tutup = document.getElementById('customPopupCloseEmail');

    if (!buka || !jendela) {
      return;
    }

    function bukaJendela() {
      jendela.style.display = 'block';
      // Kursor langsung di isian pertama: satu ketukan lebih sedikit, dan
      // pembaca layar tahu fokusnya sudah pindah ke dalam jendela.
      const pertama = jendela.querySelector('input:not([type=hidden])');
      if (pertama) {
        pertama.focus();
        pertama.select();
      }
    }

    function tutupJendela() {
      jendela.style.display = 'none';
      buka.focus();
    }

    buka.addEventListener('click', bukaJendela);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && jendela.style.display === 'block') {
        tutupJendela();
      }
    });

    if (tutup) {
      tutup.addEventListener('click', tutupJendela);
    }

    window.addEventListener('click', function (e) {
      if (e.target === jendela) {
        jendela.style.display = 'none';
      }
    });
  });
</script>

@stop
