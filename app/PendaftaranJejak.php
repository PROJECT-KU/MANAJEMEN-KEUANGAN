<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu jejak perubahan pada satu pendaftaran.
 *
 * Terpisah dari catatan panitia dengan sengaja — lihat migrasinya.
 */
class PendaftaranJejak extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_jejak';

    /** Hanya created_at; jejak tidak pernah disunting. */
    public const UPDATED_AT = null;

    /*
     * created_at IKUT fillable.
     *
     * Bukan kelalaian: jejak lama yang dipindahkan dari kolom catatan membawa
     * waktunya sendiri, dan tanpa ini Eloquent membuangnya diam-diam lalu
     * menggantinya dengan waktu pemindahan. Terukur saat pertama dicoba:
     * lima jejak yang aslinya pukul 20:45-20:46 semuanya tercatat 22:46.
     */
    protected $fillable = [
        'layanan', 'pendaftaran_id', 'aksi', 'medan', 'dari', 'ke',
        'ringkasan', 'oleh_id', 'oleh_nama', 'created_at',
    ];

    /**
     * Nama medan dalam bahasa yang dibaca panitia.
     *
     * Ditaruh di MODEL JEJAK, bukan di tampilan: yang merakit kalimat jejak
     * adalah model ini, dan nama medan yang dipakainya harus sama dengan yang
     * dipakai saat mencatatnya. Dijaga uji supaya tiap medan yang bisa
     * disunting punya namanya di sini — tanpa itu jejaknya berbunyi
     * "total_keseluruhan_pembayaran", yang bukan bahasa siapa pun.
     */
    public const NAMA_MEDAN = [
        'nama' => 'Nama',
        'nama_pemesan' => 'Nama pemesan',
        'email' => 'Email',
        'email_pemesan' => 'Email pemesan',
        'telp' => 'Nomor WhatsApp',
        'telp_pemesan' => 'Nomor WhatsApp pemesan',
        'affiliasi' => 'Afiliasi',
        'afiliasi_pemesan' => 'Afiliasi pemesan',
        'kategori_id' => 'Angkatan',
        'jumlah_pendaftar' => 'Jumlah orang',
        'ppn' => 'PPN',
        'kode_unik' => 'Kode unik',
        'kode_diskon' => 'Kode diskon',
        'nominal_diskon' => 'Nominal potongan',
        'total_pembayaran' => 'Total bayar',
        'total_keseluruhan_pembayaran' => 'Total keseluruhan',
        'group_wa' => 'Tautan grup WhatsApp',
        'note' => 'Catatan panitia',
        'kendala' => 'Kendala',
        'desc_kendala' => 'Keterangan kendala',
        'tanggal_pemesanan' => 'Tanggal pemesanan',
        'sesi' => 'Sesi',
        'jam_sesi' => 'Jam sesi',
        'waktu_mulai' => 'Mulai',
        'waktu_selesai' => 'Selesai',
        'lokasi' => 'Lokasi',
        'biaya' => 'Biaya',
        'kode_unik_pembayaran' => 'Kode unik',
        'subtotal_pembayaran' => 'Subtotal',
        'sesi_kedua' => 'Sesi kedua',
        'waktu_mulai_kedua' => 'Mulai sesi kedua',
        'waktu_selesai_kedua' => 'Selesai sesi kedua',
        'lokasi_kedua' => 'Lokasi sesi kedua',
        'biaya_kedua' => 'Biaya sesi kedua',
        'kode_unik_pembayaran_kedua' => 'Kode unik sesi kedua',
        'subtotal_pembayaran_kedua' => 'Subtotal sesi kedua',
        'sesi_ketiga' => 'Sesi ketiga',
        'waktu_mulai_ketiga' => 'Mulai sesi ketiga',
        'waktu_selesai_ketiga' => 'Selesai sesi ketiga',
        'lokasi_ketiga' => 'Lokasi sesi ketiga',
        'biaya_ketiga' => 'Biaya sesi ketiga',
        'kode_unik_pembayaran_ketiga' => 'Kode unik sesi ketiga',
        'subtotal_pembayaran_ketiga' => 'Subtotal sesi ketiga',
    ];

    protected $casts = ['created_at' => 'datetime'];

    /** @return \Illuminate\Database\Eloquent\Builder */
    public function scopeMilik($kueri, string $layanan, string $id)
    {
        return $kueri->where('layanan', $layanan)->where('pendaftaran_id', $id);
    }

    /** Terbaru di bawah: jejak dibaca sebagai cerita dari awal. */
    public function scopeTerurut($kueri)
    {
        return $kueri->orderBy('created_at')->orderBy('id');
    }

    /**
     * Satu kalimat yang bisa dibaca panitia.
     *
     * Dirakit di sini, bukan disimpan jadi untaian: kalimatnya boleh
     * diperbaiki kapan saja tanpa menyentuh baris yang sudah tersimpan.
     */
    public function getKalimatAttribute(): string
    {
        if ($this->ringkasan) {
            return $this->ringkasan;
        }

        $siapa = $this->oleh_nama ? ' oleh ' . $this->oleh_nama : '';

        if ($this->aksi === 'status') {
            return 'Status "' . $this->dari . '" → "' . $this->ke . '"' . $siapa;
        }

        if ($this->aksi === 'bayar') {
            return 'Pembayaran Rp ' . number_format((int) $this->ke, 0, ',', '.') . ' dicatat' . $siapa;
        }

        if ($this->aksi === 'ubah-bayar') {
            // Dibedakan dari hapus-lalu-catat-ulang: yang membaca riwayatnya
            // setengah tahun kemudian harus bisa tahu ini koreksi angka,
            // bukan pembayaran yang dibatalkan lalu masuk lagi.
            return 'Catatan pembayaran Rp ' . number_format((int) $this->dari, 0, ',', '.')
                . ' dibetulkan jadi Rp ' . number_format((int) $this->ke, 0, ',', '.') . $siapa;
        }

        if ($this->aksi === 'hapus-bayar') {
            return 'Catatan pembayaran Rp ' . number_format((int) $this->dari, 0, ',', '.')
                . ' dihapus' . $siapa;
        }

        if ($this->aksi === 'refund') {
            return 'Pengembalian dana Rp ' . number_format((int) $this->ke, 0, ',', '.')
                . ' dicatat' . $siapa;
        }

        if ($this->aksi === 'hapus-refund') {
            return 'Catatan pengembalian Rp ' . number_format((int) $this->dari, 0, ',', '.')
                . ' dihapus' . $siapa;
        }

        if ($this->aksi === 'surat') {
            return 'Surat "' . $this->dari . '" terkirim ke ' . $this->ke . $siapa;
        }

        if ($this->aksi === 'surat-gagal') {
            /*
             * Sebabnya ikut disebut. "Surat gagal dikirim" tanpa sebab
             * membuat panitia mengulang-ulang hal yang sama; "alamat emailnya
             * kosong" memberitahunya apa yang harus dibetulkan.
             */
            return 'Surat "' . $this->dari . '" GAGAL dikirim — ' . $this->ke . $siapa;
        }

        /*
         * Pengingat dibedakan dari surat status, meski jalurnya sama.
         *
         * Keduanya "surat terkirim", tapi yang dicari panitia saat membuka
         * riwayat ini biasanya "sudah berapa kali orang ini ditagih" — dan
         * itu tidak terbaca kalau pengingat tercampur dengan surat status.
         */
        if ($this->aksi === 'ingat-bayar') {
            return 'Pengingat pembayaran terkirim ke ' . $this->ke;
        }

        if ($this->aksi === 'ingat-bayar-gagal') {
            return 'Pengingat pembayaran GAGAL dikirim — ' . $this->ke;
        }

        if ($this->aksi === 'peserta') {
            /*
             * Jumlahnya, bukan daftar namanya. Jejak yang memuat sepuluh nama
             * membuat satu baris riwayat setinggi layar, dan yang dicari saat
             * membacanya adalah KAPAN daftarnya berubah — namanya sendiri ada
             * di tab Peserta, selalu yang terbaru.
             */
            return 'Daftar peserta ' . (int) $this->dari . ' → ' . (int) $this->ke . ' nama' . $siapa;
        }

        if ($this->aksi === 'bukti') {
            return 'Bukti bayar diunggahkan' . $siapa;
        }

        if ($this->aksi === 'ganti-bukti') {
            return 'Bukti bayar diganti' . $siapa;
        }

        if ($this->aksi === 'ubah' && $this->medan) {
            $nama = self::NAMA_MEDAN[$this->medan] ?? $this->medan;

            /*
             * Nilai kosong ditulis sebagai kata, bukan dua kutip hampa.
             * "Catatan panitia "" → "sudah dihubungi"" terbaca seperti
             * kesalahan cetak; "(kosong)" terbaca seperti keadaan.
             */
            $dari = ($this->dari === null || $this->dari === '') ? '(kosong)' : '"' . $this->dari . '"';
            $ke = ($this->ke === null || $this->ke === '') ? '(kosong)' : '"' . $this->ke . '"';

            return $nama . ' ' . $dari . ' → ' . $ke . $siapa;
        }

        return ucfirst($this->aksi) . $siapa;
    }
}
