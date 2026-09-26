<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
     * Register any authentication / authorization services.
     *
     * Rute Passport (/oauth/*) sudah didaftarkan otomatis sejak Passport 11,
     * jadi Passport::routes() tidak dipanggil lagi.
     */
    public function boot(): void
    {
        //
    }
}
