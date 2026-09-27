<x-layouts.auth judulHalaman="Ketentuan & Privasi" kelasHalaman="halaman-masuk"
    merekJudul="Ketentuan & privasi." merekTeks="Ringkasan hak dan kewajiban saat memakai layanan Rumah Scopus Foundation.">

    <div class="kartu kartu-lebar naskah">
        <h2 class="judul-form">Ketentuan Layanan</h2>
        <p class="teks-bantu">Berlaku bagi setiap orang yang membuat akun pada sistem ini.</p>

        <h3>1. Akun</h3>
        <p>
            Anda wajib memberikan data yang benar saat mendaftar dan menjaga kerahasiaan kata sandi. Seluruh
            aktivitas yang terjadi melalui akun Anda menjadi tanggung jawab Anda. Segera hubungi kami bila Anda
            menduga akun dipakai orang lain.
        </p>

        <h3>2. Penggunaan yang wajar</h3>
        <p>
            Akun dipakai untuk mengakses layanan Rumah Scopus Foundation. Dilarang memakai sistem ini untuk
            mencoba masuk ke akun orang lain, mengambil data secara otomatis dalam jumlah besar, atau mengganggu
            jalannya layanan.
        </p>

        <h3>3. Penonaktifan akun</h3>
        <p>
            Kami dapat menonaktifkan akun yang melanggar ketentuan ini atau yang terindikasi disalahgunakan.
            Anda juga dapat meminta akun Anda dinonaktifkan dengan menghubungi kami.
        </p>

        <h3>4. Perubahan ketentuan</h3>
        <p>
            Ketentuan ini dapat diperbarui sewaktu-waktu. Perubahan yang berdampak besar akan kami beri tahukan
            melalui email yang terdaftar.
        </p>

        <hr class="pemisah-naskah">

        <h2 class="judul-form">Kebijakan Privasi</h2>
        <p class="teks-bantu">Menjelaskan data apa yang kami simpan dan untuk apa.</p>

        <h3>1. Data yang kami kumpulkan</h3>
        <ul>
            <li>Data akun: nama lengkap, username, alamat email, dan nomor telepon (bila diisi).</li>
            <li>Data teknis: alamat IP, jenis peramban, serta waktu dan hasil percobaan masuk.</li>
            <li>Khusus karyawan: data kepegawaian seperti presensi, cuti, dan gaji, sesuai keperluan pekerjaan.</li>
        </ul>

        <h3>2. Tujuan penggunaan</h3>
        <p>
            Data dipakai untuk mengelola akun dan hak akses, menjalankan layanan yang Anda ikuti, mengirim
            pemberitahuan penting seperti verifikasi email dan atur ulang kata sandi, serta menjaga keamanan
            sistem.
        </p>

        <h3>3. Siapa yang dapat melihat</h3>
        <p>
            Akses dibatasi menurut peran pengguna. Pengurus dan admin hanya dapat melihat data yang relevan
            dengan tugasnya. Kami tidak memperjualbelikan data Anda.
        </p>

        <h3>4. Keamanan</h3>
        <p>
            Kata sandi disimpan dalam bentuk acak satu arah, tidak pernah dalam bentuk aslinya, dan tidak dapat
            dibaca oleh siapa pun termasuk admin. Tautan atur ulang kata sandi hanya berlaku terbatas waktu dan
            sekali pakai.
        </p>

        <h3>5. Penyimpanan data</h3>
        <p>
            Data akun disimpan selama akun masih aktif. Bila akun dinonaktifkan, sebagian data tetap kami simpan
            sepanjang masih diperlukan untuk keperluan pencatatan keuangan dan kepegawaian.
        </p>

        <h3>6. Hak Anda</h3>
        <p>
            Anda berhak meminta salinan, perbaikan, atau penghapusan data pribadi Anda. Permintaan tersebut dapat
            diajukan melalui kontak di bawah, dan akan kami tindak lanjuti sepanjang tidak bertentangan dengan
            kewajiban pencatatan kami.
        </p>

        <h3 id="kontak">7. Kontak</h3>
        <p>
            Rumah Scopus Foundation — <a href="mailto:{{ config('mail.from.address') }}" class="tautan">{{ config('mail.from.address') }}</a>
        </p>

        <p class="kaki-kartu">
            <a href="{{ url()->previous() === url()->current() ? route('register') : url()->previous() }}" class="tautan">Kembali</a>
        </p>
    </div>
</x-layouts.auth>
