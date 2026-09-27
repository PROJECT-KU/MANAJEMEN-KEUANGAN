@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Profil | MIS
@stop

@include('account.profil.gaya')

@section('content')
@php
    // Masa kerja hanya bermakna kalau email sudah terverifikasi dan akun
    // aktif; di luar itu kolomnya dikosongkan supaya kartu tidak memuat
    // kalimat panjang sebagai "angka".
    $masaKerja = ($user->email_verified_at && $user->status === 'active') ? $workDuration : null;

    // Kotak kecil di kartu identitas cuma selebar sepertiga kartu, jadi
    // bagian yang bernilai nol dibuang: "2 tahun 0 bulan 0 hari" jadi
    // "2 tahun".
    $masaKerjaRingkas = $masaKerja
        ? (trim((string) preg_replace('/\s*\b0 (tahun|bulan|hari)\b/', '', $masaKerja)) ?: 'Baru')
        : null;

    $labelLevel = [
        'manager' => 'Manager',
        'karyawan' => 'Karyawan',
        'staff' => 'Staff',
        'user' => 'User',
    ][$user->level] ?? '—';

    $labelJenis = [
        'bisnis' => 'Bisnis (Entity)',
        'perorangan' => 'Perorangan (Personal)',
    ][$user->jenis] ?? 'Belum ditentukan';

    $fotoProfil = Auth::user()->gambar
        ? asset('assets/img/profil/' . Auth::user()->gambar)
        : asset('assets/img/profil/no-image.jpg');
@endphp

<div class="main-content" style="padding-top: 110px; background-color: #f4f7ff; min-height: 100vh;">
  <section class="section">
    <div class="section-body">
      <div class="prf">

        {{-- ========================================================= kepala --}}
        <header class="prf-kepala">
          <span class="prf-kepala-avatar" aria-hidden="true">
            <img src="{{ $fotoProfil }}" alt="">
          </span>

          <div class="prf-kepala-teks">
            <h1 class="prf-judul">Profil saya</h1>
            {{-- Kalimat panjang sengaja tidak ditaruh di sini: begitu ia pindah
                 baris, tanda titiknya tertinggal menggantung di ujung baris
                 sebelumnya. Isinya sudah ada di sub-judul kartu pengaturan. --}}
            <p class="prf-sub">
              <span><i class="fas fa-user-circle"></i>{{ $user->full_name ?: $user->username }}</span>
              <span class="prf-pemisah" aria-hidden="true">&middot;</span>
              <span><i class="fas fa-briefcase"></i>{{ $user->jobdesk ?: 'Tanpa jabatan' }}</span>
              @if ($user->level !== 'user' && $user->company)
                <span class="prf-pemisah" aria-hidden="true">&middot;</span>
                <span><i class="fas fa-building"></i>{{ $user->company }}</span>
              @endif
            </p>
          </div>

          <div class="prf-kepala-aksi">
            @if (Auth::user()->email_verified_at)
              <span class="prf-pil prf-pil-hijau"><i class="fas fa-check-circle"></i> Email terverifikasi</span>
            @else
              <span class="prf-pil prf-pil-kuning"><i class="fas fa-exclamation-circle"></i> Email belum terverifikasi</span>
            @endif

            @if ($user->status === 'active')
              <span class="prf-pil prf-pil-biru"><i class="fas fa-circle"></i> Akun aktif</span>
            @else
              <span class="prf-pil prf-pil-merah"><i class="fas fa-ban"></i> Akun nonaktif</span>
            @endif
          </div>
        </header>

        <div class="prf-tata">

          {{-- ================================================ kolom kiri --}}
          <aside class="prf-sisi">

            {{-- kartu identitas --}}
            <section class="prf-kartu prf-identitas">
              <div class="prf-foto-bingkai">
                <img id="prf-pratinjau" class="prf-foto" src="{{ $fotoProfil }}"
                  alt="Foto profil {{ $user->full_name }}">
                @if (Auth::user()->email_verified_at)
                  <span class="prf-foto-lencana prf-hijau" title="Email sudah terverifikasi">
                    <i class="fas fa-check"></i>
                  </span>
                @else
                  <span class="prf-foto-lencana prf-kuning" title="Email belum terverifikasi">
                    <i class="fas fa-exclamation"></i>
                  </span>
                @endif
              </div>

              <h2 class="prf-nama">{{ $user->full_name ?: $user->username }}</h2>
              <p class="prf-nama-pengguna">&#64;{{ $user->username }}</p>

              <div class="prf-pil-baris">
                <span class="prf-pil prf-pil-ungu"><i class="fas fa-briefcase"></i> {{ $user->jobdesk ?: 'Tanpa jabatan' }}</span>
                @if ($user->level !== 'user' && $user->company)
                  <span class="prf-pil prf-pil-abu"><i class="fas fa-building"></i> {{ $user->company }}</span>
                @endif
              </div>

              <div class="prf-mini-kisi">
                <div class="prf-mini">
                  <span class="prf-mini-ikon prf-ikon-hijau" aria-hidden="true"><i class="fas fa-user-check"></i></span>
                  <p class="prf-mini-angka">{{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</p>
                  <p class="prf-mini-label">Status</p>
                </div>
                <div class="prf-mini">
                  <span class="prf-mini-ikon prf-ikon-ungu" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                  <p class="prf-mini-angka">{{ $labelLevel }}</p>
                  <p class="prf-mini-label">Level</p>
                </div>
                <div class="prf-mini">
                  <span class="prf-mini-ikon prf-ikon-jingga" aria-hidden="true"><i class="fas fa-hourglass-half"></i></span>
                  <p class="prf-mini-angka" title="{{ $masaKerja ?: 'Belum dihitung' }}">{{ $masaKerjaRingkas ?: '—' }}</p>
                  <p class="prf-mini-label">Masa kerja</p>
                </div>
              </div>

              {{-- ganti foto --}}
              <form action="{{ route('account.profil.updatePhoto') }}" method="POST"
                enctype="multipart/form-data" class="prf-unggah-bungkus">
                @csrf
                <input type="file" name="gambar" id="foto" class="prf-berkas"
                  accept="image/jpeg,image/png,image/gif">
                <label for="foto" class="prf-unggah">
                  <span class="prf-medali kecil prf-biru" aria-hidden="true"><i class="fas fa-camera"></i></span>
                  <span class="prf-unggah-teks">
                    <span class="prf-unggah-nama" id="prf-nama-berkas">Pilih foto baru</span>
                    <span class="prf-bantuan">JPG, PNG, atau GIF &middot; maksimal 3 MB</span>
                  </span>
                </label>
                <button type="submit" id="updatePhotoBtn" class="prf-tombol prf-tombol-ungu prf-penuh" disabled>
                  <i class="fas fa-cloud-upload-alt"></i> Simpan foto
                </button>
              </form>
            </section>

            {{-- kartu kontak --}}
            <section class="prf-kartu">
              <div class="prf-kartu-kepala">
                <div>
                  <h3 class="prf-kartu-judul"><i class="fas fa-address-card prf-ikon-biru"></i> Kontak &amp; jabatan</h3>
                  <p class="prf-kartu-sub">Dipakai untuk pemberitahuan dan pemulihan akun.</p>
                </div>
              </div>

              <ul class="prf-daftar">
                <li class="prf-baris">
                  <span class="prf-medali prf-biru" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                  <div class="prf-baris-teks">
                    <p class="prf-label">Alamat email</p>
                    <p class="prf-nilai">{{ Auth::user()->email }}</p>
                  </div>
                  <button type="button" class="prf-ubah" id="openPopupButtonEmail" title="Ubah alamat email">
                    <i class="fas fa-pen"></i>
                  </button>
                </li>

                <li class="prf-baris">
                  <span class="prf-medali prf-hijau" aria-hidden="true"><i class="fab fa-whatsapp"></i></span>
                  <div class="prf-baris-teks">
                    <p class="prf-label">WhatsApp / telepon</p>
                    <p class="prf-nilai">{{ Auth::user()->telp ?: 'Belum diisi' }}</p>
                  </div>
                  <button type="button" class="prf-ubah" id="openPopupButtonTelp" title="Ubah nomor WhatsApp">
                    <i class="fas fa-pen"></i>
                  </button>
                </li>

                @if (Auth::user()->level !== 'user')
                  <li class="prf-baris">
                    <span class="prf-medali prf-ungu" aria-hidden="true"><i class="fas fa-briefcase"></i></span>
                    <div class="prf-baris-teks">
                      <p class="prf-label">Posisi / jabatan</p>
                      <p class="prf-nilai">{{ Auth::user()->jobdesk ?: 'Belum diisi' }}</p>
                    </div>
                    <button type="button" class="prf-ubah" id="openPopupButtonJobdesk" title="Ubah posisi">
                      <i class="fas fa-pen"></i>
                    </button>
                  </li>

                  <li class="prf-baris">
                    <span class="prf-medali prf-jingga" aria-hidden="true"><i class="fas fa-hourglass-half"></i></span>
                    <div class="prf-baris-teks">
                      <p class="prf-label">Lama bergabung</p>
                      <p class="prf-nilai">{{ $workDuration }}</p>
                    </div>
                  </li>
                @endif
              </ul>
            </section>
          </aside>

          {{-- ================================================ kolom kanan --}}
          <div class="prf-utama">
            <div class="prf-tab-kartu">
              <div class="prf-tab-kepala">
                <div class="prf-kartu-kepala">
                  <div>
                    <h3 class="prf-kartu-judul"><i class="fas fa-id-card prf-ikon-ungu"></i> Pengaturan akun</h3>
                    <p class="prf-kartu-sub">Data diri, kata sandi, PIN masuk, dan riwayat keamanan.</p>
                  </div>
                </div>

                <ul class="prf-tab nav nav-pills" id="pills-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="pills-activity-tab" data-toggle="pill" href="#activity" role="tab">
                      <i class="fas fa-user-circle prf-ikon-ungu"></i> Profil saya
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="pills-settings-tab" data-toggle="pill" href="#settings" role="tab">
                      <i class="fas fa-key prf-ikon-jingga"></i> Kata sandi
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="pills-pin-tab" data-toggle="pill" href="#pin" role="tab">
                      <i class="fas fa-mobile-alt prf-ikon-biru"></i> PIN masuk
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="pills-keamanan-tab" data-toggle="pill" href="#keamanan" role="tab">
                      <i class="fas fa-user-shield prf-ikon-hijau"></i> Keamanan
                    </a>
                  </li>
                </ul>
              </div>

              <div class="prf-tab-isi">
                <div class="tab-content" id="profileTabContent">

                  {{-- ------------------------------------- tab 1: data diri --}}
                  <div class="tab-pane fade show active" id="activity" role="tabpanel">
                    @if (Auth::user()->email_verified_at == null)
                      <div class="prf-kabar">
                        <span class="prf-medali kecil prf-kuning" aria-hidden="true"><i class="fas fa-shield-alt"></i></span>
                        <p class="prf-kabar-teks">
                          Email Anda belum diverifikasi. Tanpa verifikasi, akun tidak bisa dipulihkan
                          kalau kata sandi terlupa.
                        </p>
                      </div>
                    @endif

                    <div class="prf-bagian">
                      <div class="prf-bagian-kepala">
                        <span class="prf-medali kecil prf-ungu" aria-hidden="true"><i class="fas fa-id-badge"></i></span>
                        <div>
                          <h4 class="prf-bagian-judul">Profil dasar</h4>
                          <p class="prf-bagian-sub">Nama yang tampil di sistem dan nama untuk masuk.</p>
                        </div>
                      </div>

                      <div class="prf-kisi-isian dua">
                        <div class="prf-isian">
                          <label class="prf-label-isian" for="prf-full-name">Nama lengkap</label>
                          <input class="form-control-modern" id="prf-full-name" type="text" name="full_name"
                            value="{{ $user->full_name }}" form="form-update-data">
                        </div>
                        <div class="prf-isian">
                          <label class="prf-label-isian" for="prf-username">Username</label>
                          <input class="form-control-modern" id="prf-username" type="text" name="username"
                            value="{{ $user->username }}" form="form-update-data">
                          <p class="prf-isian-bantuan">Dipakai untuk masuk, jadi harus unik.</p>
                        </div>
                      </div>

                      <form id="verify-email-form" action="{{ route('account.profil.verify.email') }}" method="POST">
                        @csrf
                        <input type="hidden" name="code_verified_mail" value="{{ Auth::user()->code_verified_mail }}">

                        <div class="prf-kisi-isian dua" style="margin-top: 16px;">
                          <div class="prf-isian prf-terkunci">
                            <label class="prf-label-isian" for="prf-email">
                              <i class="fas fa-lock"></i> Email terdaftar
                            </label>
                            <input class="form-control-modern" id="prf-email" type="text"
                              value="{{ Auth::user()->email }}" readonly>
                            <p class="prf-isian-bantuan">Ubah lewat tombol pensil di kartu kontak.</p>
                          </div>

                          @if (!Auth::user()->email_verified_at)
                            <div class="prf-isian" id="container-verify-btn" style="align-content: end;">
                              <button type="button" id="btn-verify-email" class="prf-tombol prf-tombol-biru prf-penuh">
                                <i class="fas fa-paper-plane"></i> Kirim kode verifikasi
                              </button>
                              <p class="prf-isian-bantuan">Kode 6 angka dikirim ke email di samping.</p>
                            </div>
                          @endif
                        </div>
                      </form>
                    </div>

                    <form id="form-update-data" action="{{ route('account.profil.update') }}" method="POST">
                      @csrf

                      {{-- Bagian ini pembuka <form>, jadi bukan adik dari bagian
                           di atasnya; pemisahnya dipasang sendiri. --}}
                      <div class="prf-bagian prf-bagian-lanjut">
                        <div class="prf-bagian-kepala">
                          <span class="prf-medali kecil prf-biru" aria-hidden="true"><i class="fas fa-user-shield"></i></span>
                          <div>
                            <h4 class="prf-bagian-judul">Akses &amp; kepegawaian</h4>
                            <p class="prf-bagian-sub">Hanya admin atau manager yang bisa mengubah bagian ini.</p>
                          </div>
                        </div>

                        <div class="prf-kisi-isian">
                          <div class="prf-isian">
                            <label class="prf-label-isian"><i class="fas fa-lock"></i> Status akun</label>
                            <div class="prf-statis">
                              @if ($user->status === 'active')
                                <span class="prf-pil prf-pil-hijau"><i class="fas fa-check-circle"></i> Aktif</span>
                              @else
                                <span class="prf-pil prf-pil-merah"><i class="fas fa-ban"></i> Nonaktif</span>
                              @endif
                            </div>
                          </div>

                          <div class="prf-isian">
                            <label class="prf-label-isian"><i class="fas fa-lock"></i> Level sistem</label>
                            <div class="prf-statis">
                              <span class="prf-medali kecil prf-ungu" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                              <p class="prf-statis-teks">{{ $labelLevel }} Sistem</p>
                            </div>
                          </div>

                          @if ($user->level === 'user')
                            <div class="prf-isian">
                              <label class="prf-label-isian" for="tanggal_lahir">Tanggal lahir</label>
                              <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control-modern"
                                value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" required>
                            </div>
                          @else
                            <div class="prf-isian">
                              <label class="prf-label-isian"><i class="fas fa-lock"></i> Kode perusahaan</label>
                              <div class="prf-statis">
                                <span class="prf-medali kecil prf-biru" aria-hidden="true"><i class="fas fa-building"></i></span>
                                <p class="prf-statis-teks">{{ $user->company ?: 'Tidak ada' }}</p>
                              </div>
                            </div>
                          @endif

                          <div class="prf-isian prf-isian-penuh">
                            <label class="prf-label-isian"><i class="fas fa-lock"></i> Jenis akun</label>
                            <div class="prf-statis">
                              <span class="prf-medali kecil prf-jingga" aria-hidden="true"><i class="fas fa-user-tag"></i></span>
                              <p class="prf-statis-teks">{{ $labelJenis }}</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      @if (Auth::user()->level !== 'user')
                        <div class="prf-bagian">
                          <div class="prf-bagian-kepala">
                            <span class="prf-medali kecil prf-hijau" aria-hidden="true"><i class="fas fa-wallet"></i></span>
                            <div>
                              <h4 class="prf-bagian-judul">Data finansial &amp; pribadi</h4>
                              <p class="prf-bagian-sub">Rekening di sini yang dipakai saat penggajian.</p>
                            </div>
                          </div>

                          <div class="prf-kisi-isian">
                            <div class="prf-isian">
                              <label class="prf-label-isian" for="tanggal_lahir">Tanggal lahir</label>
                              <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control-modern"
                                value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}"
                                max="{{ \Carbon\Carbon::now()->subYears(15)->format('Y-m-d') }}" required>
                            </div>

                            <div class="prf-isian">
                              <label class="prf-label-isian" for="norek">No. rekening</label>
                              <input type="text" id="norek" name="norek" class="form-control-modern"
                                value="{{ old('norek', $user->norek) }}" placeholder="Nomor rekening" maxlength="40"
                                onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57"
                                oninput="formatNoRek(this)">
                            </div>

                            <div class="prf-isian">
                              <label class="prf-label-isian" for="bank">Bank</label>
                              <select class="form-control-modern bank" id="bank" name="bank">
                                @include('account.profil.opsi-bank')
                              </select>
                            </div>
                          </div>
                        </div>
                      @endif

                      <div class="prf-aksi">
                        <p class="prf-aksi-catatan">
                          <i class="fas fa-info-circle prf-ikon-ungu"></i>
                          Kolom bergembok hanya bisa diubah oleh admin atau manager.
                        </p>
                        <button type="submit" class="prf-tombol prf-tombol-ungu">
                          <i class="fas fa-save"></i> Simpan perubahan
                        </button>
                      </div>
                    </form>
                  </div>

                  {{-- ---------------------------------- tab 2: kata sandi --}}
                  <div class="tab-pane fade" id="settings" role="tabpanel">
                    <form id="register-form" action="{{ route('account.profil.reset.password') }}" method="POST">
                      @csrf

                      <div class="prf-bagian">
                        <div class="prf-bagian-kepala">
                          <span class="prf-medali kecil prf-jingga" aria-hidden="true"><i class="fas fa-key"></i></span>
                          <div>
                            <h4 class="prf-bagian-judul">Ganti kata sandi</h4>
                            <p class="prf-bagian-sub">Setelah berhasil, Anda akan diminta masuk ulang.</p>
                          </div>
                        </div>

                        <div class="prf-kisi-isian dua">
                          <div class="prf-isian prf-isian-penuh">
                            <label class="prf-label-isian" for="old-password">Kata sandi lama</label>
                            <div class="prf-sandi">
                              <input type="password" class="form-control-modern" id="old-password"
                                name="old_password" placeholder="••••••••" autocomplete="current-password" required>
                              <i class="fas fa-eye password-toggle-inside" id="old-password-toggle"></i>
                            </div>
                          </div>

                          <div class="prf-isian">
                            <label class="prf-label-isian" for="password">Kata sandi baru</label>
                            <div class="prf-sandi">
                              <input type="password" class="form-control-modern" name="password" id="password"
                                placeholder="••••••••" autocomplete="new-password" required>
                              <i class="fas fa-eye password-toggle-inside" id="password-toggle"></i>
                            </div>
                          </div>

                          <div class="prf-isian">
                            <label class="prf-label-isian" for="password_confirmation">Ulangi kata sandi baru</label>
                            <div class="prf-sandi">
                              <input type="password" class="form-control-modern" name="password_confirmation"
                                id="password_confirmation" placeholder="••••••••" autocomplete="new-password" required>
                              <i class="fas fa-eye password-toggle-inside" id="password-confirmation-toggle"></i>
                            </div>
                          </div>
                        </div>

                        <div class="prf-aksi">
                          <p class="prf-aksi-catatan">
                            <i class="fas fa-shield-alt prf-ikon-hijau"></i>
                            Pakai minimal 8 karakter dan jangan ulangi sandi dari layanan lain.
                          </p>
                          <button type="submit" class="prf-tombol prf-tombol-ungu">
                            <i class="fas fa-key"></i> Perbarui kata sandi
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>

                  {{-- ------------------------------------- tab 3: PIN masuk --}}
                  <div class="tab-pane fade" id="pin" role="tabpanel">
                    <livewire:akun.pengaturan-pin />
                  </div>

                  {{-- ------------------------------------- tab 4: keamanan --}}
                  <div class="tab-pane fade" id="keamanan" role="tabpanel">
                    <livewire:akun.keamanan-akun />
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ========================================================= modal --}}

        <div id="customPopupEmail" class="custom-popup">
          <div class="custom-popup-content">
            <span class="custom-popup-close" id="customPopupCloseEmail">&times;</span>
            <div class="prf-modal-kepala">
              <span class="prf-medali prf-biru" aria-hidden="true"><i class="fas fa-envelope"></i></span>
              <div>
                <h5 class="prf-modal-judul">Ubah alamat email</h5>
                <p class="prf-modal-sub">Email baru wajib diverifikasi ulang.</p>
              </div>
            </div>
            <form action="{{ route('account.pengguna.update.datadiri', Auth::user()->id) }}" method="POST">
              @csrf
              <div class="prf-isian" style="margin-bottom: 14px;">
                <label class="prf-label-isian" for="prf-email-baru">Email terbaru</label>
                <input type="email" class="form-control-modern" id="prf-email-baru" name="email"
                  value="{{ Auth::user()->email }}" required>
              </div>
              <div class="prf-isian" style="margin-bottom: 18px;">
                <label class="prf-label-isian" for="prf-sandi-email">Kata sandi akun Anda</label>
                <input type="password" class="form-control-modern" id="prf-sandi-email" name="kata_sandi_email"
                  placeholder="••••••••" autocomplete="current-password" required>
                <p class="prf-isian-bantuan">
                  Diminta karena email dipakai untuk memulihkan akun.
                </p>
              </div>
              <button type="submit" class="prf-tombol prf-tombol-ungu prf-penuh">
                <i class="fas fa-save"></i> Simpan email
              </button>
            </form>
          </div>
        </div>

        <div id="customPopupJobdesk" class="custom-popup">
          <div class="custom-popup-content">
            <span class="custom-popup-close" id="customPopupCloseJobdesk">&times;</span>
            <div class="prf-modal-kepala">
              <span class="prf-medali prf-ungu" aria-hidden="true"><i class="fas fa-briefcase"></i></span>
              <div>
                <h5 class="prf-modal-judul">Ubah posisi / jabatan</h5>
                <p class="prf-modal-sub">Tampil di kartu profil dan daftar karyawan.</p>
              </div>
            </div>
            <form action="{{ route('account.pengguna.update.datadiri', Auth::user()->id) }}" method="POST">
              @csrf
              <div class="prf-isian" style="margin-bottom: 18px;">
                <label class="prf-label-isian" for="prf-jobdesk">Posisi / jabatan</label>
                <select class="form-control-modern" id="prf-jobdesk" name="jobdesk" required>
                  <option value="">-- Pilih jabatan --</option>
                  <option value="MANAGER" {{ Auth::user()->jobdesk == 'MANAGER' ? 'selected' : '' }}>MANAGER</option>
                  <option value="STAFF" {{ Auth::user()->jobdesk == 'STAFF' ? 'selected' : '' }}>STAFF</option>
                  <option value="ASISTEN TRAINER" {{ Auth::user()->jobdesk == 'ASISTEN TRAINER' ? 'selected' : '' }}>ASISTEN TRAINER</option>
                  <option value="KARYAWAN" {{ Auth::user()->jobdesk == 'KARYAWAN' ? 'selected' : '' }}>KARYAWAN</option>
                </select>
              </div>
              <button type="submit" class="prf-tombol prf-tombol-ungu prf-penuh">
                <i class="fas fa-save"></i> Simpan jabatan
              </button>
            </form>
          </div>
        </div>

        <div id="customPopupTelp" class="custom-popup">
          <div class="custom-popup-content">
            <span class="custom-popup-close" id="customPopupCloseTelp">&times;</span>
            <div class="prf-modal-kepala">
              <span class="prf-medali prf-hijau" aria-hidden="true"><i class="fab fa-whatsapp"></i></span>
              <div>
                <h5 class="prf-modal-judul">Ubah nomor WhatsApp</h5>
                <p class="prf-modal-sub">Dipakai untuk pemberitahuan cepat.</p>
              </div>
            </div>
            <form action="{{ route('account.pengguna.update.datadiri', Auth::user()->id) }}" method="POST">
              @csrf
              <div class="prf-isian" style="margin-bottom: 18px;">
                <label class="prf-label-isian" for="prf-telp">Nomor WhatsApp baru</label>
                <input type="text" class="form-control-modern" id="prf-telp" name="telp"
                  value="{{ Auth::user()->telp }}" oninput="formatPhoneNumber(this)">
              </div>
              <button type="submit" class="prf-tombol prf-tombol-ungu prf-penuh">
                <i class="fas fa-save"></i> Simpan nomor
              </button>
            </form>
          </div>
        </div>

        <div id="customPopup" class="custom-popup" style="display:none;">
          <div class="custom-popup-content">
            <span class="custom-popup-close" id="customPopupClose">&times;</span>
            <div class="prf-modal-kepala">
              <span class="prf-medali prf-kuning" aria-hidden="true"><i class="fas fa-shield-alt"></i></span>
              <div>
                <h5 class="prf-modal-judul">Masukkan kode verifikasi</h5>
                <p class="prf-modal-sub">Enam angka yang baru dikirim ke email Anda.</p>
              </div>
            </div>
            <form id="verification-form" action="{{ route('account.profil.verify.code') }}" method="POST">
              @csrf
              <div class="prf-isian" style="margin-bottom: 18px;">
                <input type="text" name="verification_code" class="form-control-modern prf-kode"
                  placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
              </div>
              <button type="submit" class="prf-tombol prf-tombol-ungu prf-penuh">
                <i class="fas fa-check-circle"></i> Verifikasi sekarang
              </button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
  @csrf
</form>
<!--================== SWEET ALERT HARUS VERIFIKASI EMAIL DAHULU ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const button = document.getElementById('edit-jobdesk-button');
    if (button) {
      button.addEventListener('click', function(event) {
        // Check the data-action attribute to determine if the email is verified
        if (button.getAttribute('data-action') === 'verify-email') {
          event.preventDefault(); // Prevent default action
          Swal.fire({
            title: 'Harus verifikasi Email',
            text: 'Anda harus memverifikasi email Anda sebelum mengedit jobdesk Anda.',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        }
      });
    }
  });
  document.addEventListener('DOMContentLoaded', function() {
    const button = document.getElementById('edit-telp-button');
    if (button) {
      button.addEventListener('click', function(event) {
        // Check the data-action attribute to determine if the email is verified
        if (button.getAttribute('data-action') === 'verify-email') {
          event.preventDefault(); // Prevent default action
          Swal.fire({
            title: 'Harus verifikasi Email',
            text: 'Anda harus memverifikasi email Anda sebelum mengedit No Telp Anda.',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        }
      });
    }
  });
</script>
<!--================== END ==================-->

<!--================== FORMAT NO REKENING ==================-->
<script>
  function formatNoRek(input) {
    // Menghapus semua karakter non-digit
    var NoRek = input.value.replace(/\D/g, '');

    // Menggunakan ekspresi reguler untuk memformat nomor telepon
    NoRek = NoRek.replace(/(\d{4})(\d{2})(\d{6})(\d{2})(\d{1})/, '$1-$2-$3-$4-$5');

    // Mengatur nilai input dengan nomor telepon yang diformat
    input.value = NoRek;
  }
</script>
<!--================== END ==================-->

<!--================== MAKSIMAL UPLOAD GAMBAR & FILE YANG DI PERBOLEHKAN ==================-->
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
        Swal.fire({
          icon: 'error',
          title: 'Format Salah',
          text: 'Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan.'
        });
        batalkan();
        return;
      }

      // 2. Validasi Ukuran
      if (file.size > maxFileSize) {
        Swal.fire({
          icon: 'error',
          title: 'File Terlalu Besar',
          text: 'Ukuran foto maksimal adalah 3MB.'
        });
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
<!--================== END ==================-->

<!--================== FORMAT NO TELP ==================-->
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
<!--================== END ==================-->

<!--================== VERIFIKASI EMAIL (FIXED) ==================-->
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
              Swal.fire({
                title: 'Kode Terkirim!',
                text: 'Silakan periksa email Anda dan masukkan kode 6 digit.',
                icon: 'success',
                confirmButtonText: 'OK'
              }).then(() => {
                customPopup.style.display = 'block'; // Munculkan Modal Input Kode
              });
            } else {
              Swal.fire('Error', data.message || 'Gagal mengirim email.', 'error');
            }
          })
          .catch(error => {
            Swal.fire('Error', 'Terjadi kesalahan pada sistem.', 'error');
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
              Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
              }).then(() => {
                customPopup.style.display = 'none';
                window.location.reload(); // Refresh halaman agar centang email hijau
              });
            } else {
              Swal.fire('Gagal!', data.message, 'error');
            }
          })
          .catch(error => {
            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
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
<!--================== END ==================-->

<!--================== RESET PASSWORD ==================-->
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
          Swal.fire({
            icon: 'error',
            title: 'Password Tidak Sesuai',
            text: 'Konfirmasi password baru tidak cocok. Silakan periksa kembali.',
            confirmButtonText: 'OK'
          });
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
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message,
                confirmButtonText: 'Coba Lagi'
              });
            } else if (res.statussuksesreset === 'success') {
              // Jika Berhasil -> Beri notifikasi lalu Auto-Logout
              Swal.fire({
                icon: 'success',
                title: 'Password Diperbarui!',
                text: 'Keamanan akun telah diperbarui. Silakan login kembali.',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                willClose: () => {
                  // Eksekusi form logout tersembunyi
                  const logoutForm = document.getElementById('logout-form');
                  if (logoutForm) {
                    logoutForm.submit();
                  } else {
                    // Fallback jika form tidak ditemukan
                    window.location.href = "/logout";
                  }
                }
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire({
              icon: 'error',
              title: 'System Error',
              text: 'Terjadi gangguan koneksi. Silakan coba lagi.'
            });
          });
      });
    }
  });
</script>
<!--================== END ==================-->

<!--================== POPUP EDIT DATA DIRI ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const openPopupButton = document.getElementById('openPopupButtonEmail');
    const customPopupemail = document.getElementById('customPopupEmail');
    const customPopupClose = document.getElementById('customPopupCloseEmail');

    // Show popup when the pencil icon is clicked
    openPopupButton.addEventListener('click', function() {
      customPopupemail.style.display = 'block';
    });

    // Hide popup when the close button is clicked
    customPopupClose.addEventListener('click', function() {
      customPopupemail.style.display = 'none';
    });

    // Hide popup when clicking outside the popup content
    window.addEventListener('click', function(event) {
      if (event.target === customPopupemail) {
        customPopupemail.style.display = 'none';
      }
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const openPopupButton = document.getElementById('openPopupButtonJobdesk');
    const customPopupemail = document.getElementById('customPopupJobdesk');
    const customPopupClose = document.getElementById('customPopupCloseJobdesk');

    // Show popup when the pencil icon is clicked
    openPopupButton.addEventListener('click', function() {
      customPopupemail.style.display = 'block';
    });

    // Hide popup when the close button is clicked
    customPopupClose.addEventListener('click', function() {
      customPopupemail.style.display = 'none';
    });

    // Hide popup when clicking outside the popup content
    window.addEventListener('click', function(event) {
      if (event.target === customPopupemail) {
        customPopupemail.style.display = 'none';
      }
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const openPopupButton = document.getElementById('openPopupButtonTelp');
    const customPopupemail = document.getElementById('customPopupTelp');
    const customPopupClose = document.getElementById('customPopupCloseTelp');

    // Show popup when the pencil icon is clicked
    openPopupButton.addEventListener('click', function() {
      customPopupemail.style.display = 'block';
    });

    // Hide popup when the close button is clicked
    customPopupClose.addEventListener('click', function() {
      customPopupemail.style.display = 'none';
    });

    // Hide popup when clicking outside the popup content
    window.addEventListener('click', function(event) {
      if (event.target === customPopupemail) {
        customPopupemail.style.display = 'none';
      }
    });
  });
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT DATA PROFIL ==================-->
<script>
  // Function to show SweetAlert messages
  document.addEventListener('DOMContentLoaded', function() {
    @if(session('statusauthorized'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'You are not authorized to update the email',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    }).then(() => {
      location.reload(); // Automatically refresh the page after the alert
    });
    @endif

    @if(session('statusdataprofil'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Data profil berhasil diperbarui',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    }).then(() => {
      location.reload(); // Automatically refresh the page after the alert
    });
    @endif

    @if(session('statusdatabank'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Data bank berhasil diperbarui',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    }).then(() => {
      location.reload(); // Automatically refresh the page after the alert
    });
    @endif

    @if(session('erroremailterpakai'))
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Email sudah terdaftar silahkan gunakan email yang lain',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    }).then(() => {
      location.reload(); // Automatically refresh the page after the alert
    });
    @endif

    {{-- Unggah foto memakai session('success')/('error'); sebelumnya keduanya
         tidak pernah ditampilkan, jadi berhasil dan gagal sama-sama diam. --}}
    @if(session('success'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: '{{ session("success") }}',
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true
    });
    @endif

    @if(session('error'))
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: '{{ session("error") }}',
      showConfirmButton: true,
      confirmButtonColor: '#6366f1'
    });
    @endif

    @if(session('errorsandiemail'))
    Swal.fire({
      icon: 'error',
      title: 'Kata sandi salah',
      text: '{{ session("errorsandiemail") }}',
      showConfirmButton: true,
      confirmButtonColor: '#6366f1'
    });
    @endif
  });

  document.addEventListener('DOMContentLoaded', function() {
    @if(session('errortanggallahir'))
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: '{{ session("errortanggallahir") }}',
      showConfirmButton: true,
      confirmButtonColor: '#6366f1'
    });
    @endif
  });
</script>


<!--================== END ==================-->
@stop