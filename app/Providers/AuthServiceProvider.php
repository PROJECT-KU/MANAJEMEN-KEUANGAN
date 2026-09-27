<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Rute Passport (/oauth/*) dimatikan.
     *
     * API aplikasi ini dihapus pada 27 September 2026 dan tidak ada klien
     * OAuth yang dipakai, jadi membiarkan belasan endpoint /oauth/* terbuka
     * hanya menambah permukaan serangan tanpa manfaat. Paketnya tetap
     * terpasang supaya pencabutan token saat kata sandi diganti tidak galat.
     */
    public function register(): void
    {
        Passport::ignoreRoutes();
    }

    public function boot(): void
    {
        //
    }
}
