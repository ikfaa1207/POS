<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class SampleProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::query()->role('Owner')->first() ?? User::query()->first();

        if (! $owner) {
            $this->command?->warn('No users found. Run RolesAndWalkInSeeder first.');

            return;
        }

        $batch = now()->format('YmdHis');

        Product::factory()
            ->count(1000)
            ->state(new Sequence(
                fn (Sequence $sequence) => [
                    'name' => 'Sample Product '.str_pad((string) ($sequence->index + 1), 4, '0', STR_PAD_LEFT),
                    'sku' => 'SAMPLE-'.$batch.'-'.str_pad((string) ($sequence->index + 1), 4, '0', STR_PAD_LEFT),
                ]
            ))
            ->create([
                'created_by' => $owner->id,
            ]);
    }
}
