{{--
  Satu isian borang rincian pendaftaran.

  Yang harus dikirim pemanggilnya:
    $kolom     nama kolomnya
    $jenis     'teks' | 'uang' | 'angka' | 'tanggal' | 'waktu'
    $tulisan   label yang dibaca orang
    $nilai     nilai sekarang
    $penuh     true kalau isiannya memakai lebar penuh kisinya
    $rentang   berapa lajur yang ditempatinya (boleh tidak dikirim; bawaannya 1)
    $uang      true kalau nominal — diberi awalan Rp di dalam kotaknya
    $angkatan  pilihan angkatan, hanya dipakai kolom kategori_id

  Satuan seperti Rp menempel DI DALAM kotaknya sebagai awalan, bukan jadi
  label sendiri — aturan yang sudah berlaku di layar Tarif layanan.
--}}
@php
    $id = 'rin-' . $kolom;

    // Nominal ditampilkan sebagai bilangan bulat tanpa pemisah: pemisahnya
    // dibuang lagi saat disimpan, dan menampilkannya berpemisah membuat orang
    // mengira formatnya yang disimpan.
    $tampil = $uang ? (string) (int) $nilai : $nilai;

    $jenisKotak = match ($jenis) {
        'tanggal' => 'date',
        'waktu' => 'time',
        'angka' => 'number',
        default => 'text',
    };
@endphp
{{-- Rentangnya lewat gaya sebaris, bukan kelas: jumlah lajurnya dihitung
     per bagian dari jumlah isiannya, jadi nilainya tidak terbatas pada
     beberapa kelas yang ditulis di muka. --}}
<div class="mis-isian {{ $penuh ? 'rin-isian-penuh' : '' }}"
    @if (! $penuh && ($rentang ?? 1) > 1) style="grid-column: span {{ $rentang }};" @endif>
    <label class="mis-label" for="{{ $id }}">{{ $tulisan }}</label>

    @if ($kolom === 'kategori_id')
        <select class="form-control-modern" id="{{ $id }}" name="{{ $kolom }}" required>
            @foreach ($angkatan as $a)
                <option value="{{ $a->id }}" @selected((string) $nilai === (string) $a->id)>
                    {{ \Illuminate\Support\Str::limit($a->nama, 60) }}@if ($a->total_kuota !== null) &mdash; sisa {{ (int) $a->sisa_kuota }}/{{ (int) $a->total_kuota }}@endif
                </option>
            @endforeach
        </select>
        {{-- Pilihannya DIBATASI angkatan layanan ini; memindahkan pendaftaran
             ke angkatan layanan lain membuat kuota keduanya salah tanpa ada
             yang menolak. --}}
        <p class="mis-bantuan">Hanya angkatan layanan ini yang bisa dipilih.</p>
    @elseif ($kolom === 'note' || $kolom === 'desc_kendala')
        <textarea class="form-control-modern" id="{{ $id }}" name="{{ $kolom }}" rows="3">{{ $tampil }}</textarea>
    @else
        <input type="{{ $jenisKotak }}" class="form-control-modern" id="{{ $id }}" name="{{ $kolom }}"
            value="{{ $tampil }}"
            @if ($jenis === 'angka') min="1" max="99" @endif
            @if ($uang) inputmode="numeric" @endif
            @if (in_array($kolom, ['nama', 'nama_pemesan', 'email', 'email_pemesan', 'telp', 'telp_pemesan'], true)) required @endif>
        @if ($uang)
            <p class="mis-bantuan">Dalam rupiah, tanpa titik.</p>
        @endif
    @endif
</div>
