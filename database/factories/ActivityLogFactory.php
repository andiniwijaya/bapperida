<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'user_role' => 'admin',
            'action' => 'test_action',
            'module' => 'test_module',
            'description' => fake()->sentence(),
            'url' => '/api/test',
            'method' => 'GET',
            'ip_address' => '127.0.0.1',
            'logged_at' => now(),
        ];
    }
}
