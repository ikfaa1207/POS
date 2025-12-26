<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->city().' Branch',
            'code' => Str::upper($this->faker->bothify('BR-###')),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($branch) {
            \App\Models\Terminal::factory()->state([
                'branch_id' => $branch->id,
                'is_default_web' => true,
                'identifier' => 'web-'.$branch->id,
            ])->create();
        });
    }
}
