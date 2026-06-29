<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('SUPERADMIN_PASSWORD');

        if (app()->isProduction() && empty($password)) {
            $this->command?->warn('SUPERADMIN_PASSWORD tidak diset. Super Admin tidak dibuat.');

            return;
        }

        $user = User::updateOrCreate(
            [
                'email' => env('SUPERADMIN_EMAIL', 'superadmin@bapperida.go.id'),
            ],
            [
                'name' => 'Super Administrator',
                'username' => env('SUPERADMIN_USERNAME', 'superadmin'),
                'password' => Hash::make($password ?? 'password123'),
                'department_id' => 1,
            ]
        );

        $user->role = 'superadmin';
        $user->status = 'active';
        $user->email_verified_at = now();
        $user->must_change_password = app()->isProduction();
        $user->save();
    }
}
