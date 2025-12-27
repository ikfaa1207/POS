<?php

namespace App\Services\Users;

use App\Models\User;
use App\Support\BranchContext;
use Illuminate\Support\Collection;

class ListUsers
{
    public function __construct(private BranchContext $context)
    {
    }

    public function execute(User $actor): Collection
    {
        $companyId = $this->context->requireCompanyId();

        $query = User::query()
            ->where('company_id', $companyId)
            ->with('roles')
            ->orderBy('name');

        if (! $actor->hasRole('Owner')) {
            $query->whereHas('roles', function ($builder) {
                $builder->where('name', 'Sales');
            })->whereDoesntHave('roles', function ($builder) {
                $builder->whereIn('name', ['Owner', 'Manager']);
            });
        }

        return $query->get(['id', 'name', 'email', 'is_active', 'created_at'])
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'role' => $user->roles->first()?->name ?? 'Sales',
                    'created_at' => $user->created_at?->toISOString(),
                ];
            });
    }
}
