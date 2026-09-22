<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_dapat_login_dengan_nis(): void
    {
        User::factory()->siswa()->create(['nis' => '2025001', 'password' => bcrypt('rahasia')]);

        $response = $this->postJson('/api/login', [
            'identifier' => '2025001',
            'password' => 'rahasia',
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_guru_dapat_login_dengan_username(): void
    {
        User::factory()->guru()->create(['username' => 'guru.tkj', 'password' => bcrypt('rahasia')]);

        $response = $this->postJson('/api/login', [
            'identifier' => 'guru.tkj',
            'password' => 'rahasia',
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        User::factory()->admin()->create(['username' => 'admin', 'password' => bcrypt('rahasia')]);

        $response = $this->postJson('/api/login', [
            'identifier' => 'admin',
            'password' => 'salah',
        ]);

        $response->assertUnprocessable();
    }

    public function test_endpoint_terproteksi_menolak_tanpa_token(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_user_dapat_mengambil_profil_dan_logout(): void
    {
        $user = User::factory()->siswa()->create(['nis' => '2025002']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertOk();
    }
}
