<?php

namespace Database\Factories;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationRequest>
 */
class RegistrationRequestFactory extends Factory
{
    protected $model = RegistrationRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->pending(),
            'status' => 'pending',
        ];
    }
}
