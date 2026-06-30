<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD');

        if (app()->isProduction() && empty($password)) {
            $this->command?->warn('ADMIN_PASSWORD tidak diset. Admin tidak dibuat.');

            return;
        }

        $user = User::updateOrCreate(
            [
                'email' => env('ADMIN_EMAIL', 'admin@bapperida.go.id'),
            ],
            [
                'name' => 'Administrator',
                'username' => env('ADMIN_USERNAME', 'admin'),
                'password' => Hash::make($password ?? 'password123'),
                'department_id' => 1,
            ]
        );

        $user->role = 'admin';
        $user->status = 'active';
        $user->email_verified_at = now();
        $user->must_change_password = app()->isProduction();
        $user->save();
    }
}
