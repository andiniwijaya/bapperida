<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->unique()->company(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Department $department): void {
            $department->is_active ??= true;
        });
    }

    public function inactive(): static
    {
        return $this->afterMaking(function (Department $department): void {
            $department->is_active = false;
        });
    }

    public function bapperida(): static
    {
        return $this->state([
            'code' => 'BAPPERIDA',
            'name' => 'Badan Perencanaan Pembangunan Riset dan Inovasi Daerah',
        ]);
    }
}
