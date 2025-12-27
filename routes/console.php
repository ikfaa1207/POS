<?php

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('users:fix-branches {--dry-run}', function () {
    $defaultBranch = Branch::query()->first();
    if (! $defaultBranch) {
        $this->error('No branches found.');

        return;
    }

    $users = User::query()
        ->whereNull('current_branch_id')
        ->orWhereDoesntHave('branches')
        ->get();

    if ($users->isEmpty()) {
        $this->info('No users missing branch access.');

        return;
    }

    $dryRun = (bool) $this->option('dry-run');

    foreach ($users as $user) {
        $branch = null;
        if ($user->company_id) {
            $branch = Branch::query()
                ->where('company_id', $user->company_id)
                ->first();
        }

        $branch = $branch ?: $defaultBranch;

        $this->line("Fixing user {$user->email} -> branch {$branch->id}");

        if ($dryRun) {
            continue;
        }

        $user->forceFill([
            'company_id' => $user->company_id ?: $branch->company_id,
            'current_branch_id' => $branch->id,
        ])->save();

        $user->branches()->syncWithoutDetaching([$branch->id]);
    }
})->purpose('Attach users without branch access to a default branch');
