<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Terminal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'INV-'.$this->faker->unique()->bothify('########'),
            'finalize_token' => $this->faker->uuid(),
            'created_by' => User::factory(),
            'branch_id' => function (array $attributes) {
                $user = User::query()->find($attributes['created_by']);

                return $user?->current_branch_id ?? Branch::factory()->create()->id;
            },
            'client_id' => function (array $attributes) {
                return Client::factory()
                    ->state([
                        'branch_id' => $attributes['branch_id'],
                        'created_by' => $attributes['created_by'],
                    ])
                    ->create()
                    ->id;
            },
            'company_id' => function (array $attributes) {
                $user = User::query()->find($attributes['created_by']);

                if ($user?->company_id) {
                    return $user->company_id;
                }

                return Branch::query()->find($attributes['branch_id'])->company_id;
            },
            'terminal_id' => function (array $attributes) {
                $terminalId = Terminal::query()
                    ->where('branch_id', $attributes['branch_id'])
                    ->where('is_default_web', true)
                    ->value('id');

                if ($terminalId) {
                    return $terminalId;
                }

                return Terminal::factory()
                    ->state(['branch_id' => $attributes['branch_id']])
                    ->create()
                    ->id;
            },
            'status' => InvoiceStatus::Draft,
            'total_amount' => 0,
            'finalized_at' => null,
            'voided_at' => null,
            'lock_version' => 0,
        ];
    }
}
