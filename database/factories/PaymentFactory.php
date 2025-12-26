<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'method' => 'cash',
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'note' => $this->faker->optional()->sentence(),
            'reversal_of_id' => null,
            'company_id' => function (array $attributes) {
                return Invoice::query()->find($attributes['invoice_id'])->company_id;
            },
            'branch_id' => function (array $attributes) {
                return Invoice::query()->find($attributes['invoice_id'])->branch_id;
            },
            'terminal_id' => function (array $attributes) {
                return Invoice::query()->find($attributes['invoice_id'])->terminal_id;
            },
            'created_by' => User::factory(),
        ];
    }
}
