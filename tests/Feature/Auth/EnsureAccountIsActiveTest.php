<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureAccountIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->pending()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_active_verified_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_pending_user_api_request_returns_json_403(): void
    {
        $user = User::factory()->pending()->create();

        $this->actingAs($user)
            ->getJson('/api/dashboard')
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Akun Anda masih menunggu persetujuan Super Admin.',
            ]);
    }
}
