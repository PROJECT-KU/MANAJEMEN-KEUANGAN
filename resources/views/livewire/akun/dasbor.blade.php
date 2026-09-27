<div class="dsb">
    {{-- ============================================================ kepala --}}
    <header class="dsb-kepala">
        <div class="dsb-kepala-teks">
            <p class="dsb-tanggal">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            <h1 class="dsb-judul">{{ $this->sapaan }}, {{ \Illuminate\Support\Str::of($this->pengguna->full_name)->explode(' ')->first() }}.</h1>
            <p class="dsb-sub">
                @if ($this->pengelolaTim)
                    Ringkasan perusahaan {{ $this->pengguna->company ?: 'Anda' }} hari ini.
                @elseif ($this->pengelolaKeuangan)
                    Ringkasan kas dan pekerjaan Anda hari ini.
                @else
                    Ringkasan akun dan kabar terbaru untuk Anda.
                @endif
            </p>
        </div>

        <div class="dsb-kepala-aksi">
            <div class="dsb-pemilih" role="group" aria-label="Pilih rentang waktu">
                @foreach (['bulan' => 'Bulan ini', 'tahun' => 'Tahun ini', 'semua' => 'Semua'] as $nilai => $label)
                    <button type="button" class="{{ $rentang === $nilai ? 'aktif' : '' }}"
                        wire:click="gantiRentang('{{ $nilai }}')" wire:loading.attr="disabled">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <span class="dsb-memuat" wire:loading wire:target="gantiRentang">Memuat…</span>
        </div>
    </header>

    {{-- peringatan verifikasi: tampil hanya bila perlu --}}
    @if (is_null($this->pengguna->email_verified_at))
        <div class="dsb-kabar dsb-kabar-kuning">
            <span class="dsb-kabar-ikon"><i class="fas fa-envelope-open-text"></i></span>
            <div>
                <strong>Email Anda belum diverifikasi.</strong>
                Sebagian fitur dikunci sampai verifikasi selesai.
            </div>
            <a href="{{ route('account.profil.show', $this->pengguna->getKey()) }}" class="dsb-kabar-tombol">Verifikasi</a>
        </div>
    @endif

    {{-- ======================================================== kartu utama --}}
    <section class="dsb-kpi">
        <article class="dsb-kartu dsb-kpi-kartu dsb-ungu">
            <span class="dsb-medali"><i class="fas fa-wallet"></i></span>
            <div class="dsb-kpi-isi">
                <p class="dsb-label">Saldo &middot; {{ $this->kas['label'] }}</p>
                <h2 class="dsb-angka">Rp {{ number_format($this->kas['saldo'], 0, ',', '.') }}</h2>
                <p class="dsb-catatan">
                    Sejak awal: Rp {{ number_format($this->kas['saldo_selama_ini'], 0, ',', '.') }}
                </p>
            </div>
        </article>

        <article class="dsb-kartu dsb-kpi-kartu dsb-hijau">
            <span class="dsb-medali"><i class="fas fa-arrow-down"></i></span>
            <div class="dsb-kpi-isi">
                <p class="dsb-label">Uang masuk</p>
                <h2 class="dsb-angka">Rp {{ number_format($this->kas['masuk'], 0, ',', '.') }}</h2>
                <p class="dsb-catatan">
                    @if (! is_null($this->kas['masuk_selisih']))
                        <span class="dsb-delta {{ $this->kas['masuk_selisih'] >= 0 ? 'naik' : 'turun' }}">
                            <i class="fas fa-caret-{{ $this->kas['masuk_selisih'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($this->kas['masuk_selisih']) }}%
                        </span>
                        dibanding {{ $this->kas['label_banding'] }}
                    @else
                        Hari ini: Rp {{ number_format($this->kas['masuk_hari_ini'], 0, ',', '.') }}
                    @endif
                </p>
            </div>
        </article>

        <article class="dsb-kartu dsb-kpi-kartu dsb-merah">
            <span class="dsb-medali"><i class="fas fa-arrow-up"></i></span>
            <div class="dsb-kpi-isi">
                <p class="dsb-label">Uang keluar</p>
                <h2 class="dsb-angka">Rp {{ number_format($this->kas['keluar'], 0, ',', '.') }}</h2>
                <p class="dsb-catatan">
                    @if (! is_null($this->kas['keluar_selisih']))
                        <span class="dsb-delta {{ $this->kas['keluar_selisih'] <= 0 ? 'naik' : 'turun' }}">
                            <i class="fas fa-caret-{{ $this->kas['keluar_selisih'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($this->kas['keluar_selisih']) }}%
                        </span>
                        dibanding {{ $this->kas['label_banding'] }}
                    @else
                        Hari ini: Rp {{ number_format($this->kas['keluar_hari_ini'], 0, ',', '.') }}
                    @endif
                </p>
            </div>
        </article>

        <article class="dsb-kartu dsb-kpi-kartu dsb-biru">
            <span class="dsb-medali"><i class="fas fa-money-check-alt"></i></span>
            <div class="dsb-kpi-isi">
                <p class="dsb-label">{{ $this->pengelolaTim ? 'Gaji terbayar' : 'Gaji diterima' }} &middot; {{ $this->gaji['tahun'] }}</p>
                <h2 class="dsb-angka">Rp {{ number_format($this->gaji['total'], 0, ',', '.') }}</h2>
                <p class="dsb-catatan">
                    @if ($this->gaji['bulan_terisi'] > 0)
                        Rata-rata Rp {{ number_format($this->gaji['rata'], 0, ',', '.') }} / bulan
                    @else
                        Belum ada gaji terbayar tahun ini.
                    @endif
                </p>
            </div>
        </article>
    </section>

    {{-- ===================================================== dua lajur isi --}}
    <div class="dsb-lajur">

        {{-- --------------------------------------------------- lajur kiri --}}
        <div class="dsb-lajur-utama">

            {{-- grafik gaji --}}
            <section class="dsb-kartu">
                <div class="dsb-kartu-kepala">
                    <div>
                        <h3 class="dsb-kartu-judul"><i class="fas fa-chart-column dsb-ikon-biru"></i> Gaji {{ $this->gaji['tahun'] }}</h3>
                        <p class="dsb-kartu-sub">{{ $this->pengelolaTim ? 'Seluruh karyawan perusahaan' : 'Gaji yang Anda terima' }}</p>
                    </div>
                    <span class="dsb-pil dsb-pil-biru">Total Rp {{ number_format($this->gaji['total'], 0, ',', '.') }}</span>
                </div>

                @if ($this->gaji['total'] > 0)
                    <div class="dsb-batang">
                        @foreach ($this->gaji['per_bulan'] as $bulan => $nilai)
                            @php
                                $tinggi = $this->gaji['tertinggi'] > 0 ? max(($nilai / $this->gaji['tertinggi']) * 100, 4) : 4;
                            @endphp
                            <div class="dsb-batang-kolom">
                                <div class="dsb-batang-isi {{ $nilai > 0 ? 'ada' : '' }}" style="height: {{ $tinggi }}%">
                                    <span class="dsb-batang-tip">Rp {{ number_format($nilai, 0, ',', '.') }}</span>
                                </div>
                                <span class="dsb-batang-label">{{ \Illuminate\Support\Str::substr(\Illuminate\Support\Carbon::create(null, $bulan)->locale('id')->translatedFormat('F'), 0, 3) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="dsb-kosong">
                        <i class="fas fa-chart-column"></i>
                        <p>Belum ada gaji berstatus terbayar pada {{ $this->gaji['tahun'] }}.</p>
                    </div>
                @endif
            </section>

            {{-- kategori pengeluaran --}}
            <section class="dsb-kartu">
                <div class="dsb-kartu-kepala">
                    <div>
                        <h3 class="dsb-kartu-judul"><i class="fas fa-tags dsb-ikon-merah"></i> Pengeluaran terbesar</h3>
                        <p class="dsb-kartu-sub">{{ $this->kas['label'] }}</p>
                    </div>
                    <a href="{{ route('account.credit.index') }}" class="dsb-tautan">Lihat semua</a>
                </div>

                @if ($this->kategoriPengeluaran->isNotEmpty())
                    @php $terbesar = (float) $this->kategoriPengeluaran->max('total'); @endphp
                    <ul class="dsb-daftar-kategori">
                        @foreach ($this->kategoriPengeluaran as $i => $baris)
                            <li>
                                <div class="dsb-kategori-atas">
                                    <span class="dsb-kategori-nama">{{ $baris->name ?: 'Tanpa kategori' }}</span>
                                    <span class="dsb-kategori-nilai">Rp {{ number_format($baris->total, 0, ',', '.') }}</span>
                                </div>
                                <div class="dsb-bar">
                                    <span class="dsb-bar-isi warna-{{ $i % 5 }}"
                                        style="width: {{ $terbesar > 0 ? max(($baris->total / $terbesar) * 100, 3) : 3 }}%"></span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="dsb-kosong">
                        <i class="fas fa-receipt"></i>
                        <p>Belum ada pengeluaran tercatat pada rentang ini.</p>
                    </div>
                @endif
            </section>

            {{-- tim: hanya untuk pengelola --}}
            @if ($this->pengelolaTim)
                <section class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <div>
                            <h3 class="dsb-kartu-judul"><i class="fas fa-users dsb-ikon-ungu"></i> Karyawan</h3>
                            <p class="dsb-kartu-sub">{{ $this->pengguna->company ?: 'Perusahaan Anda' }}</p>
                        </div>
                        <a href="{{ route('account.pengguna.index') }}" class="dsb-tautan">Kelola</a>
                    </div>

                    <div class="dsb-mini">
                        <div class="dsb-mini-kartu dsb-ungu">
                            <span class="dsb-mini-angka">{{ $this->tim['total'] }}</span>
                            <span class="dsb-mini-label">Total</span>
                        </div>
                        <div class="dsb-mini-kartu dsb-hijau">
                            <span class="dsb-mini-angka">{{ $this->tim['aktif'] }}</span>
                            <span class="dsb-mini-label">Aktif</span>
                        </div>
                        <div class="dsb-mini-kartu dsb-merah">
                            <span class="dsb-mini-angka">{{ $this->tim['nonaktif'] }}</span>
                            <span class="dsb-mini-label">Nonaktif</span>
                        </div>
                        <div class="dsb-mini-kartu dsb-kuning">
                            <span class="dsb-mini-angka">{{ $this->tim['belum_verifikasi'] }}</span>
                            <span class="dsb-mini-label">Belum verifikasi</span>
                        </div>
                    </div>

                    @if ($this->tim['terbaru']->isNotEmpty())
                        <ul class="dsb-daftar-orang">
                            @foreach ($this->tim['terbaru'] as $orang)
                                <li>
                                    <span class="dsb-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($orang->full_name, 0, 1)) }}</span>
                                    <span class="dsb-orang-teks">
                                        <strong>{{ $orang->full_name }}</strong>
                                        <small>{{ \Illuminate\Support\Str::ucfirst($orang->level) }} &middot; bergabung {{ \Illuminate\Support\Carbon::parse($orang->created_at)->locale('id')->diffForHumans() }}</small>
                                    </span>
                                    <span class="dsb-pil {{ $orang->status === 'active' ? 'dsb-pil-hijau' : 'dsb-pil-abu' }}">
                                        {{ $orang->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="dsb-kosong">
                            <i class="fas fa-user-plus"></i>
                            <p>Belum ada karyawan baru di perusahaan ini.</p>
                        </div>
                    @endif
                </section>
            @endif
        </div>

        {{-- -------------------------------------------------- lajur kanan --}}
        <aside class="dsb-lajur-samping">

            {{-- presensi --}}
            @if ($this->pengguna->level !== 'user')
                <section class="dsb-kartu dsb-presensi">
                    <div class="dsb-kartu-kepala">
                        <h3 class="dsb-kartu-judul"><i class="fas fa-fingerprint dsb-ikon-ungu"></i> Presensi hari ini</h3>
                    </div>

                    @php $presensi = $this->presensiHariIni; $jam = now()->format('H:i:s'); @endphp

                    <div class="dsb-presensi-keadaan {{ $presensi ? ($presensi->status_pulang ? 'selesai' : 'jalan') : 'belum' }}">
                        <span class="dsb-presensi-ikon">
                            <i class="fas {{ $presensi ? ($presensi->status_pulang ? 'fa-circle-check' : 'fa-person-running') : 'fa-hourglass-half' }}"></i>
                        </span>
                        <div>
                            <strong>
                                @if (! $presensi)
                                    Belum presensi
                                @elseif (! $presensi->status_pulang)
                                    Sedang bekerja
                                @else
                                    Presensi selesai
                                @endif
                            </strong>
                            <small>
                                @if ($presensi)
                                    Masuk {{ \Illuminate\Support\Carbon::parse($presensi->created_at)->format('H:i') }} WIB
                                    @if ($presensi->time_pulang)
                                        &middot; pulang {{ \Illuminate\Support\Carbon::parse($presensi->time_pulang)->format('H:i') }} WIB
                                    @endif
                                @else
                                    Jam presensi 07.00 – 22.00 WIB
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="dsb-presensi-tombol">
                        @if (! $presensi && $jam >= '07:00:00' && $jam <= '22:00:00')
                            <a href="{{ route('account.presensi.create') }}" class="dsb-tombol dsb-tombol-hijau">
                                <i class="fas fa-right-to-bracket"></i> Masuk
                            </a>
                        @else
                            <button class="dsb-tombol dsb-tombol-mati" disabled>
                                <i class="fas fa-right-to-bracket"></i> Masuk
                            </button>
                        @endif

                        @if ($presensi && is_null($presensi->status_pulang) && $jam >= '07:00:00' && $jam <= '22:00:00')
                            <a href="{{ route('account.presensi.edit', $presensi->id) }}" class="dsb-tombol dsb-tombol-jingga">
                                <i class="fas fa-right-from-bracket"></i> Pulang
                            </a>
                        @else
                            <button class="dsb-tombol dsb-tombol-mati" disabled>
                                <i class="fas fa-right-from-bracket"></i> Pulang
                            </button>
                        @endif
                    </div>

                    <div class="dsb-statistik-kecil">
                        <div>
                            <span>{{ $this->kehadiran['hadir_bulan_ini'] }}</span>
                            <small>Hadir bulan ini</small>
                        </div>
                        <div>
                            <span>{{ $this->kehadiran['izin_bulan_ini'] }}</span>
                            <small>Izin</small>
                        </div>
                        <div>
                            <span>{{ $this->kehadiran['lembur_bulan_ini'] }}</span>
                            <small>Lembur</small>
                        </div>
                    </div>
                </section>

                {{-- cuti --}}
                <section class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <h3 class="dsb-kartu-judul"><i class="fas fa-umbrella-beach dsb-ikon-hijau"></i> Hak cuti</h3>
                        <a href="{{ route('account.cuti.index') }}" class="dsb-tautan">Ajukan</a>
                    </div>

                    @if ($this->cuti['boleh'])
                        <div class="dsb-cincin" style="--isi: {{ $this->cuti['jatah'] > 0 ? round(($this->cuti['sisa'] / $this->cuti['jatah']) * 100) : 0 }}">
                            <div class="dsb-cincin-tengah">
                                <strong>{{ $this->cuti['sisa'] }}</strong>
                                <small>hari</small>
                            </div>
                        </div>
                        <p class="dsb-cincin-teks">
                            {{ $this->cuti['terpakai'] }} dari {{ $this->cuti['jatah'] }} hari terpakai tahun ini.
                            @if ($this->cuti['menunggu'] > 0)
                                <br><span class="dsb-pil dsb-pil-kuning">{{ $this->cuti['menunggu'] }} pengajuan menunggu</span>
                            @endif
                        </p>
                    @else
                        <div class="dsb-kosong">
                            <i class="fas fa-hourglass-start"></i>
                            <p>Hak cuti terbuka setelah 1 tahun masa kerja.<br>Masa kerja Anda {{ $this->kehadiran['masa_kerja'] }}.</p>
                        </div>
                    @endif
                </section>

                {{-- tugas --}}
                <section class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <h3 class="dsb-kartu-judul"><i class="fas fa-list-check dsb-ikon-jingga"></i> Tugas berjalan</h3>
                        <a href="{{ route('account.todolist.index') }}" class="dsb-tautan">Semua</a>
                    </div>

                    @if ($this->tugas->isNotEmpty())
                        <ul class="dsb-daftar-tugas">
                            @foreach ($this->tugas as $t)
                                @php
                                    $tenggat = $t->tanggal_deadline ? \Illuminate\Support\Carbon::parse($t->tanggal_deadline) : null;
                                    $lewat = $tenggat && $tenggat->isPast();
                                @endphp
                                <li>
                                    <span class="dsb-titik {{ $lewat ? 'merah' : 'biru' }}"></span>
                                    <span class="dsb-tugas-teks">
                                        <strong>{{ \Illuminate\Support\Str::limit($t->judul_task, 44) }}</strong>
                                        <small>
                                            {{ $t->status }}
                                            @if ($tenggat)
                                                &middot; tenggat {{ $tenggat->locale('id')->translatedFormat('d M') }}
                                                @if ($lewat)
                                                    <span class="dsb-lewat">lewat</span>
                                                @endif
                                            @endif
                                        </small>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="dsb-kosong">
                            <i class="fas fa-mug-hot"></i>
                            <p>Tidak ada tugas berjalan. Nikmati harinya.</p>
                        </div>
                    @endif
                </section>

                {{-- pengajuan perjalanan dinas --}}
                <section class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <h3 class="dsb-kartu-judul"><i class="fas fa-plane-departure dsb-ikon-biru"></i>
                            {{ $this->pengelolaTim ? 'Menunggu persetujuan' : 'Perjalanan dinas saya' }}
                        </h3>
                        <a href="{{ route('account.PerjalananDinas.index') }}" class="dsb-tautan">Buka</a>
                    </div>

                    @if ($this->pengajuan->isNotEmpty())
                        <ul class="dsb-daftar-tugas">
                            @foreach ($this->pengajuan as $p)
                                <li>
                                    <span class="dsb-titik kuning"></span>
                                    <span class="dsb-tugas-teks">
                                        <strong>{{ \Illuminate\Support\Str::limit($p->tempat ?: $p->id_transaksi, 40) }}</strong>
                                        <small>
                                            {{ $this->pengelolaTim ? $p->full_name : \Illuminate\Support\Str::ucfirst($p->status) }}
                                            @if ($p->tanggal_mulai)
                                                &middot; {{ \Illuminate\Support\Carbon::parse($p->tanggal_mulai)->locale('id')->translatedFormat('d M Y') }}
                                            @endif
                                        </small>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="dsb-kosong">
                            <i class="fas fa-check-double"></i>
                            <p>{{ $this->pengelolaTim ? 'Tidak ada pengajuan yang menunggu.' : 'Belum ada perjalanan dinas berjalan.' }}</p>
                        </div>
                    @endif
                </section>
            @else
                {{-- pengguna umum: kartu profil supaya lajur ini tidak kosong --}}
                <section class="dsb-kartu dsb-profil-ringkas">
                    <span class="dsb-avatar besar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($this->pengguna->full_name, 0, 1)) }}</span>
                    <h3 class="dsb-kartu-judul">{{ $this->pengguna->full_name }}</h3>
                    <p class="dsb-kartu-sub">{{ $this->pengguna->email }}</p>
                    <a href="{{ route('account.profil.show', $this->pengguna->getKey()) }}" class="dsb-tombol dsb-tombol-ungu">
                        <i class="fas fa-user-gear"></i> Kelola akun
                    </a>
                </section>
            @endif
        </aside>
    </div>

    {{-- ========================================================== artikel --}}
    <section class="dsb-kartu">
        <div class="dsb-kartu-kepala">
            <div>
                <h3 class="dsb-kartu-judul"><i class="fas fa-newspaper dsb-ikon-jingga"></i> Artikel terbaru</h3>
                <p class="dsb-kartu-sub">Kabar dan tulisan dari Rumah Scopus</p>
            </div>
        </div>

        @if ($this->artikel->isNotEmpty())
            <div class="dsb-artikel">
                @foreach ($this->artikel as $a)
                    <article class="dsb-artikel-kartu">
                        <div class="dsb-artikel-gambar">
                            @if ($a->gambar_depan)
                                <img src="{{ asset('images/' . $a->gambar_depan) }}" alt="" loading="lazy">
                            @else
                                <span class="dsb-artikel-kosong"><i class="fas fa-image"></i></span>
                            @endif
                            @if ($a->kategori)
                                <span class="dsb-artikel-kategori">{{ $a->kategori }}</span>
                            @endif
                        </div>
                        <h4>{{ \Illuminate\Support\Str::limit($a->judul, 60) }}</h4>
                        <small>{{ \Illuminate\Support\Carbon::parse($a->created_at)->locale('id')->translatedFormat('d F Y') }}</small>
                    </article>
                @endforeach
            </div>
        @else
            <div class="dsb-kosong">
                <i class="fas fa-newspaper"></i>
                <p>Belum ada artikel yang diterbitkan.</p>
            </div>
        @endif
    </section>

    {{-- =================================================== menu akses cepat --}}
    @include('account.dashboard.menu-akses-cepat')

    @include('livewire.akun.dasbor-gaya')
</div>
