<?php

namespace Tests\Feature\Auth;

use App\Models\Department;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $bapperidaDepartment = Department::factory()->bapperida()->create();
        Department::factory()->create([
            'is_active' => true,
            'name' => 'Sekretariat',
        ]);

        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Daftar akun baru', false);
        $response->assertSee('Sekretariat', false);
        $response->assertDontSee(
            '<option value="'.$bapperidaDepartment->id.'">Badan Perencanaan Pembangunan Riset dan Inovasi Daerah</option>',
            false
        );
        $response->assertSee('data-lucide="user"', false);
        $response->assertSee('data-lucide="at-sign"', false);
        $response->assertSee('data-lucide="mail"', false);
        $response->assertSee('data-lucide="lock"', false);
        $response->assertSee('Bidang', false);
    }

    public function test_users_cannot_register_with_bapperida_department(): void
    {
        $bapperidaDepartment = Department::factory()->bapperida()->create();

        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'test@example.com',
            'department_id' => $bapperidaDepartment->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('department_id');
        $this->assertGuest();
    }

    public function test_new_users_can_register_and_are_redirected_to_email_verification(): void
    {
        $department = Department::factory()->create(['is_active' => true]);

        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'test@example.com',
            'department_id' => $department->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'staff',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('registration_requests', [
            'status' => 'pending',
        ]);
    }
}
