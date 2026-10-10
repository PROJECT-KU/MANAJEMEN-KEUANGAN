<?php

namespace App\Support;

/**
 * Pembuat tautan WhatsApp untuk pesan yang dikirim panitia ke pendaftar.
 *
 * SELALU memakai endpoint api.whatsapp.com, JANGAN wa.me.
 *
 * Alasannya bukan selera: lewat wa.me, emoji dan sebagian tanda baca sering
 * tidak terbaca di WhatsApp Web maupun Desktop — pesannya sampai dalam
 * keadaan rusak, dan panitia tidak pernah tahu karena di layarnya sendiri
 * tampak baik-baik saja. Endpoint api.whatsapp.com menampilkannya konsisten.
 * Pola ini disalin dari proyek yang sudah memakainya, bukan dikarang ulang.
 *
 * Nomornya lewat PesananPelanggan::nomorWa(), bukan dinormalkan di sini.
 * Fungsi itu yang menolak nomor di bawah sembilan angka, dan daftar
 * pendaftaran sudah memakai penolakan yang sama untuk memutuskan kapan
 * menampilkan "nomor ini tidak bisa dihubungi". Dinormalkan sendiri di sini,
 * keduanya akan berselisih dan tombolnya muncul untuk nomor yang pasti gagal.
 */
class TautanWa
{
    /**
     * Tautan WhatsApp siap pakai, atau string kosong bila nomornya tidak ada.
     *
     * String kosong, bukan null, supaya blade bisa memakainya langsung
     * sebagai penanda tampil/sembunyi tombol.
     */
    public static function kirim(?string $nomor, string $pesan = ''): string
    {
        $telepon = PesananPelanggan::nomorWa($nomor);

        if ($telepon === '') {
            return '';
        }

        $tautan = 'https://api.whatsapp.com/send?phone=' . $telepon;

        /*
         * rawurlencode, bukan urlencode: urlencode mengubah spasi jadi '+',
         * dan WhatsApp menampilkan plusnya apa adanya — pesan yang terkirim
         * penuh tanda tambah di antara tiap kata.
         */
        return $pesan === '' ? $tautan : $tautan . '&text=' . rawurlencode($pesan);
    }
}
