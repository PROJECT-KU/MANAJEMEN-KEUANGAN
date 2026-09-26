<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * PIN hanya enam angka, jadi ruang tebakannya kecil. Aturan ini menolak
 * pola yang paling sering dipakai — angka berulang, urutan naik/turun,
 * pasangan berulang, daftar PIN populer, dan tanggal lahir pemilik akun —
 * supaya jalan pintas ini tidak jadi jalan masuk yang mudah ditebak.
 */
class PinAman implements ValidationRule
{
    /** PIN enam angka yang paling sering muncul pada kebocoran data. */
    private const POPULER = [
        '123456', '654321', '111111', '000000', '121212', '123123', '112233',
        '123321', '159753', '147258', '102030', '101010', '202020', '696969',
        '666666', '777777', '888888', '999999', '555555', '444444', '333333',
        '222222', '789456', '456789', '987654', '135790', '246810', '080808',
    ];

    public function __construct(private ?string $tanggalLahir = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pin = is_string($value) ? trim($value) : '';

        if ($pin === '' || ! preg_match('/^\d{6}$/', $pin)) {
            // Panjang dan jenis karakter sudah diperiksa aturan lain; di sini
            // cukup berhenti supaya pesannya tidak menumpuk.
            return;
        }

        if (in_array($pin, self::POPULER, true)) {
            $fail('PIN ini terlalu umum dan mudah ditebak. Pilih kombinasi angka lain.');

            return;
        }

        if (preg_match('/^(\d)\1{5}$/', $pin)) {
            $fail('PIN tidak boleh berisi angka yang sama semua.');

            return;
        }

        if ($this->berurutan($pin)) {
            $fail('PIN tidak boleh berupa angka berurutan seperti 123456 atau 654321.');

            return;
        }

        if (preg_match('/^(\d{2})\1{2}$/', $pin) || preg_match('/^(\d{3})\1$/', $pin)) {
            $fail('PIN tidak boleh berupa pola berulang seperti 121212 atau 123123.');

            return;
        }

        if ($this->samaDenganTanggalLahir($pin)) {
            $fail('PIN tidak boleh sama dengan tanggal lahir Anda karena mudah ditebak.');
        }
    }

    /** Naik atau turun satu angka terus-menerus. */
    private function berurutan(string $pin): bool
    {
        $naik = true;
        $turun = true;

        for ($i = 1; $i < 6; $i++) {
            $selisih = (int) $pin[$i] - (int) $pin[$i - 1];

            if ($selisih !== 1) {
                $naik = false;
            }

            if ($selisih !== -1) {
                $turun = false;
            }
        }

        return $naik || $turun;
    }

    private function samaDenganTanggalLahir(string $pin): bool
    {
        if (! $this->tanggalLahir) {
            return false;
        }

        try {
            $tanggal = \Illuminate\Support\Carbon::parse($this->tanggalLahir);
        } catch (\Throwable $e) {
            return false;
        }

        // Bentuk yang biasa dipakai orang: 170899, 990817, 1708 + tahun pendek.
        $pola = [
            $tanggal->format('dmy'),
            $tanggal->format('ymd'),
            $tanggal->format('mdy'),
            $tanggal->format('dm') . $tanggal->format('y'),
        ];

        return in_array($pin, $pola, true);
    }
}
