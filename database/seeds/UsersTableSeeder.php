<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            // MANAGER / ADMIN
            [
                'full_name'  => 'Berto Juni Krisnanto',
                'email'      => 'bertojunikrisnanto@gmail.com',
                'username'   => 'admin',
                'password'   => Hash::make('Password123'),
                'avatar'     => '898192462.png',
                'email_verified_at' => now(),
                'level'      => 'manager',
                'jenis'      => 'bisnis',
                'company'    => 'Rumah Scopus',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // KARYAWAN
            [
                'full_name'  => 'Karyawan Rumah Scopus',
                'email'      => 'karyawan@rumahscopus.com',
                'username'   => 'karyawan',
                'password'   => Hash::make('Password123'),
                'avatar'     => 'default.png',
                'email_verified_at' => now(),
                'level'      => 'karyawan',
                'jenis'      => 'bisnis',
                'company'    => 'Rumah Scopus',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // USER / MEMBER
            [
                'full_name'  => 'User Rumah Scopus',
                'email'      => 'user@rumahscopus.com',
                'username'   => 'user',
                'password'   => Hash::make('Password123'),
                'avatar'     => 'default.png',
                'email_verified_at' => now(),
                'level'      => 'user',
                'jenis'      => 'perorangan',
                'company'    => null,
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
