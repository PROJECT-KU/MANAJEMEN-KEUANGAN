<div>
    @if ($pesan !== '')
        <div class="alert border-0 shadow-sm d-flex align-items-start mb-4"
            style="border-radius: 16px; background: {{ $jenisPesan === 'sukses' ? '#ecfdf5' : '#fff1f2' }};">
            <i class="fas {{ $jenisPesan === 'sukses' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger' }} mr-3 fa-lg mt-1"></i>
            <div class="font-weight-bold text-dark small">{{ $pesan }}</div>
        </div>
    @endif

    {{-- Akhiri sesi di perangkat lain --}}
    <div class="p-3 mb-4" style="border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0;">
        <div class="d-flex align-items-start mb-3">
            <i class="fas fa-power-off text-danger fa-lg mr-3 mt-1"></i>
            <div>
                <div class="font-weight-800 text-dark">Keluarkan saya dari perangkat lain</div>
                <div class="small text-muted">
                    Mengakhiri sesi di semua peramban lain yang masih terbuka. Perangkat yang sedang Anda pakai
                    sekarang tetap masuk. Berguna kalau Anda lupa keluar di komputer bersama.
                </div>
            </div>
        </div>
        <div class="d-flex flex-column flex-sm-row" style="gap: 10px;">
            <input type="password" class="form-control-modern flex-grow-1 @error('kataSandi') is-invalid @enderror"
                wire:model="kataSandi" autocomplete="current-password" placeholder="Kata sandi akun Anda">
            <button type="button" class="btn btn-outline-danger font-weight-bold px-4" style="border-radius: 14px;"
                wire:click="keluarkanPerangkatLain" wire:loading.attr="disabled">
                <i class="fas fa-sign-out-alt mr-2"></i> KELUARKAN
            </button>
        </div>
        @error('kataSandi')
            <div class="text-danger small font-weight-bold mt-2">{{ $message }}</div>
        @enderror
    </div>

    {{-- Riwayat masuk --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="text-uppercase small font-weight-800 text-muted mb-0" style="letter-spacing: 1px;">
            Riwayat Masuk Terakhir
        </h6>
        @if ($gagalTerakhir > 0)
            <span class="badge badge-warning px-3 py-2" style="border-radius: 10px;">
                {{ $gagalTerakhir }} percobaan gagal dalam 30 hari
            </span>
        @endif
    </div>

    @if ($riwayat->isEmpty())
        <div class="text-center text-muted py-4">
            <i class="fas fa-history fa-2x mb-2 d-block" style="opacity: .35;"></i>
            <div class="small">Belum ada catatan masuk.</div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr class="small text-muted text-uppercase" style="letter-spacing: .5px;">
                        <th style="border-top: 0;">Waktu</th>
                        <th style="border-top: 0;">Hasil</th>
                        <th style="border-top: 0;">Alamat IP</th>
                        <th style="border-top: 0;">Perangkat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayat as $baris)
                        <tr>
                            <td class="small">
                                {{ $baris->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                                <span class="text-muted">WIB</span>
                            </td>
                            <td class="small">
                                @if ($baris->berhasil)
                                    <span class="text-success font-weight-bold">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ $baris->alasan ?: 'berhasil' }}
                                    </span>
                                @else
                                    <span class="text-danger font-weight-bold">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        {{ $baris->alasan ?: 'gagal' }}
                                    </span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $baris->ip ?: '-' }}</td>
                            <td class="small text-muted">
                                @if ($baris->perangkat && $baris->perangkat === $perangkatIni)
                                    <span class="badge badge-info px-2" style="border-radius: 8px;">perangkat ini</span>
                                @else
                                    {{ \Illuminate\Support\Str::limit(strip_tags((string) $baris->peramban), 42) ?: '-' }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="small text-muted mt-3 mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Ada baris yang bukan Anda? Segera ganti kata sandi — sesi di perangkat lain akan ikut berakhir dan PIN
            masuk dimatikan.
        </p>
    @endif
</div>
