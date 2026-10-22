{{-- Satu borang komentar, dipakai EMPAT tempat.

     Sebelum ini markahnya disalin empat kali: borang utama dan tiga borang
     balasan di tiap tingkat. Empat salinan berarti empat tempat yang harus
     diubah setiap kali aturannya berubah — dan yang terlewat tidak
     mengeluarkan galat apa pun, cuma diam-diam berperilaku lain.

     Disisipkan lewat @include, dan karena itu TANPA @props: direktif itu
     milik komponen <x-...>, dan di berkas yang di-include ia membuat
     halamannya menggantung sampai 500 tanpa pesan yang menyebut sebabnya.
     Pemanggilnya mengirim $artikel, dan $balasKe hanya bila itu balasan.

     $artikel — tulisan yang dikomentari
     $balasKe — id komentar yang dibalas; tidak dikirim berarti komentar baru --}}
@php($balasKe = $balasKe ?? null)

<p>
    Alamat email Anda tidak akan dipublikasikan. Komentarmu tampil setelah
    dicek admin. Bidang yang wajib diisi ditandai <span style="color: red;">*</span>
</p>

{{-- Borangnya dulu TIDAK punya tempat menampilkan galat sama sekali.
     Sesudah validasi dipasang, kiriman yang ditolak kembali ke halaman ini
     tanpa satu pun tanda: orangnya menekan Kirim, halaman memuat ulang, dan
     tidak ada yang menjelaskan apa pun. Diam begitu lebih buruk daripada
     tanpa validasi. --}}
@if ($errors->any())
    <div class="blg-galat">
        <strong><i class="bi bi-exclamation-triangle-fill"></i> Komentarnya belum terkirim</strong>
        <ul>
            @foreach ($errors->all() as $galat)
                <li>{{ $galat }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('blog.store.komentar') }}" method="POST">
    @csrf

    {{-- Perangkap lebah: isian yang tidak pernah dilihat orang. Peramban
         biasa membiarkannya kosong; bot pengisi borang mengisi semua yang
         ditemukannya.

         Disembunyikan lewat POSISI, bukan display:none atau type="hidden":
         sebagian bot sengaja melewati keduanya, dan isian inilah yang justru
         ingin diisi. --}}
    <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
        <label>Alamat kantor</label>
        <input type="text" name="alamat_kantor" tabindex="-1" autocomplete="off">
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label style="font-size: 15px;">Masukkan Nama Anda <span style="color: red;">*</span></label>
            {{-- Batasnya SAMA dengan aturan peladen: 2–80. Yang lama 5–30,
                 jadi nama seperti "Ani" ditolak peramban padahal peladen
                 menerimanya — dan yang ditolak tidak diberi tahu alasannya. --}}
            <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda"
                maxlength="80" minlength="2" value="{{ old('nama') }}" required>
        </div>
        <div class="col-md-6 form-group">
            <label style="font-size: 15px;">Masukkan Email Anda <span style="color: red;">*</span></label>
            {{-- type="email", dan 120 huruf. Yang lama memotong di 30:
                 alamat kampus seperti nama.panjang@mahasiswa.universitas.ac.id
                 tidak muat, dan pengetiknya tidak pernah tahu kenapa. --}}
            <input name="email" type="email" class="form-control" placeholder="Masukkan Email Anda"
                maxlength="120" value="{{ old('email') }}" required>
        </div>
    </div>

    <div class="row">
        <div class="col form-group">
            <label style="font-size: 15px;">Masukkan Website Anda</label>
            <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda"
                maxlength="160" value="{{ old('link_website') }}">
        </div>
    </div>

    <div class="row">
        <div class="col form-group">
            <label style="font-size: 15px;">Masukkan Komentar Anda <span style="color: red;">*</span></label>
            <textarea name="komentar" class="form-control" placeholder="Masukkan Komentar"
                maxlength="3000" minlength="3" required>{{ old('komentar') }}</textarea>
        </div>
    </div>

    <input name="artikel_id" value="{{ $artikel->id }}" type="hidden">

    @if ($balasKe)
        <input name="reply" value="{{ $balasKe }}" type="hidden">
    @endif

    {{-- Isian user_id DIBUANG dari sini.

         Nilainya dulu $artikel->user_id — id PENULIS artikelnya — jadi tiap
         komentar tamu tercatat atas nama penulis tulisan itu. Siapa pun juga
         bisa menggantinya sebelum mengirim. Peladen sekarang mengambilnya
         dari sesi dan mengabaikan isian apa pun yang dikirim.

         categories_artikel_id juga dibuang: peladen membacanya sendiri dari
         tulisannya, bukan dari apa yang dikirim peramban. --}}

    <button type="submit" class="btn btn-primary">Kirim</button>
</form>
