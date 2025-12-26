<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 5);
        $unitPrice = $this->faker->randomFloat(2, 1, 500);
        $lineTotal = round($quantity * $unitPrice, 2);

        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => function (array $attributes) {
                $invoice = Invoice::query()->find($attributes['invoice_id']);

                return Product::factory()
                    ->state([
                        'branch_id' => $invoice?->branch_id,
                        'created_by' => $invoice?->created_by,
                    ])
                    ->create()
                    ->id;
            },
            'description' => $this->faker->sentence(3),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'created_by' => User::factory(),
        ];
    }
}
