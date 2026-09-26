<?php

namespace Tests\Feature\Auth;

use App\Mail\VerifikasiEmailMail;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ApiPendaftaranTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        RateLimiter::clear('api.register|127.0.0.1');
    }

    private function isian(array $ubah = []): array
    {
        $unik = substr(uniqid(), -6);

        return array_merge([
            'full_name' => 'Pengguna API',
            'username' => 'apiuji' . $unik,
            'email' => $unik . '@contoh.test',
            'password' => 'SandiUji2026',
            'jenis' => 'perorangan',
        ], $ubah);
    }

    public function test_pendaftaran_api_tidak_bisa_menentukan_level_sendiri(): void
    {
        // Sebelum diperbaiki, menyertakan "level":"manager" langsung
        // menghasilkan akun manager karena User::create($request->all()).
        $isian = $this->isian(['level' => 'manager', 'company' => 'rumahscopus']);

        $res = $this->postJson('/api/v1/register', $isian);

        $res->assertStatus(201);
        $pengguna = User::where('username', $isian['username'])->first();

        $this->assertSame('user', $pengguna->level);
        $this->assertNull($pengguna->company);
    }

    public function test_pendaftaran_api_mengirim_tautan_verifikasi(): void
    {
        $isian = $this->isian();

        $this->postJson('/api/v1/register', $isian)->assertStatus(201);

        $pengguna = User::where('username', $isian['username'])->first();
        $this->assertNull($pengguna->email_verified_at);
        Mail::assertSent(VerifikasiEmailMail::class, fn ($surat) => $surat->hasTo($pengguna->email));
    }

    public function test_pendaftaran_api_menolak_kata_sandi_lemah(): void
    {
        $res = $this->postJson('/api/v1/register', $this->isian(['password' => 'pendek']));

        $res->assertStatus(401);   // controller lama memakai 401 untuk galat validasi
        $this->assertStringContainsString('password', strtolower($res->getContent()));
    }

    public function test_pendaftaran_api_menolak_jenis_asing(): void
    {
        $this->postJson('/api/v1/register', $this->isian(['jenis' => 'apa-saja']))->assertStatus(401);
    }

    public function test_respons_tidak_membocorkan_seluruh_baris_pengguna(): void
    {
        $res = $this->postJson('/api/v1/register', $this->isian());

        $data = $res->json('data');
        $this->assertEqualsCanonicalizing(
            ['id', 'full_name', 'username', 'email', 'level', 'verified'],
            array_keys($data)
        );
    }
}
