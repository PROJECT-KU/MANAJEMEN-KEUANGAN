<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * Menolak alamat dari layanan kotak surat sementara, supaya akun layanan
 * berbayar tidak dibuat dengan email yang hilang beberapa menit kemudian.
 */
class BukanEmailSekaliPakai implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! str_contains($value, '@')) {
            return;
        }

        $domain = Str::lower(trim(Str::afterLast($value, '@')));

        if (in_array($domain, config('email_sekali_pakai', []), true)) {
            $fail('Alamat email sekali pakai tidak bisa dipakai mendaftar. Gunakan email pribadi atau email kantor Anda.');
        }
    }
}
