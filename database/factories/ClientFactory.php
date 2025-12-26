<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'notes' => $this->faker->optional()->sentence(),
            'is_walk_in' => false,
            'created_by' => User::factory(),
            'branch_id' => function (array $attributes) {
                $user = User::query()->find($attributes['created_by']);

                return $user?->current_branch_id ?? Branch::factory()->create()->id;
            },
            'company_id' => function (array $attributes) {
                $user = User::query()->find($attributes['created_by']);

                if ($user?->company_id) {
                    return $user->company_id;
                }

                return Branch::query()->find($attributes['branch_id'])->company_id;
            },
        ];
    }
}
