<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('reset_token', 300)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('code_verified_mail')->nullable();
            $table->timestamp('code_verified_mail_sent_at')->nullable();
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->string('level', 25)->default('user');
            $table->string('jenis', 24)->default('perorangan');
            $table->string('company', 25)->nullable();
            $table->text('alamat_company')->nullable();
            $table->string('telp_company', 200)->nullable();
            $table->string('email_company', 200)->nullable();
            $table->string('logo_company', 200)->nullable();
            $table->string('pj_company', 300)->nullable();
            $table->string('telp', 100)->nullable();
            $table->string('status', 100)->nullable();
            $table->string('notif', 300)->nullable();
            $table->string('tenggat', 300)->nullable();
            $table->string('title', 300)->nullable();
            $table->string('nik', 100)->nullable();
            $table->string('norek', 100)->nullable();
            $table->string('bank', 100)->nullable();
            $table->string('gambar', 300)->nullable();
            $table->text('jobdesk')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
