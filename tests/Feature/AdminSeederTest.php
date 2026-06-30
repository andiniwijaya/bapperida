<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_seeder_creates_active_admin_user(): void
    {
        $this->seed(DepartmentSeeder::class);

        $this->seed(AdminSeeder::class);

        $admin = User::query()->where('email', 'admin@bapperida.go.id')->first();

        $this->assertNotNull($admin);
        $this->assertSame('admin', $admin->role);
        $this->assertSame('active', $admin->status);
        $this->assertSame('admin', $admin->username);
        $this->assertSame(Department::query()->where('code', 'BAPPERIDA')->value('id'), $admin->department_id);
        $this->assertNotNull($admin->email_verified_at);
    }

    public function test_admin_seeder_is_idempotent(): void
    {
        $this->seed(DepartmentSeeder::class);

        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->assertSame(1, User::query()->where('role', 'admin')->count());
    }
}
