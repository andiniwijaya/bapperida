<?php

namespace Tests\Feature\Auth;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_pending_user_cannot_login_via_api(): void
    {
        $user = User::factory()->pending()->create();

        $response = $this->postJson('/api/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['login']);
    }

    public function test_superadmin_can_approve_pending_registration_once(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $registrationRequest = RegistrationRequest::factory()->create([
            'status' => 'pending',
        ]);

        Sanctum::actingAs($superAdmin);

        $response = $this->patchJson(
            "/api/registration-requests/{$registrationRequest->id}/approve"
        );

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('approved', $registrationRequest->fresh()->status);
        $this->assertSame('active', $registrationRequest->user->fresh()->status);

        $this->patchJson("/api/registration-requests/{$registrationRequest->id}/approve")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['registration_request']);
    }
}
