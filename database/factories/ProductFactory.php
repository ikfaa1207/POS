<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####')),
            'description' => $this->faker->optional()->sentence(),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'track_inventory' => false,
            'stock_qty' => null,
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
