<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Company;
use App\Models\Terminal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndWalkInSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::findOrCreate('Owner');
        $managerRole = Role::findOrCreate('Manager');
        $salesRole = Role::findOrCreate('Sales');

        $permissions = [
            'dashboard.view',
            'client.view',
            'client.create',
            'client.update',
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'invoice.view',
            'invoice.create',
            'invoice.edit',
            'invoice.finalize',
            'invoice.void',
            'payment.record',
            'payment.reverse',
            'inventory.adjust',
            'reports.view',
            'user.invite',
            'user.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $ownerRole->syncPermissions($permissions);
        $managerRole->syncPermissions([
            'dashboard.view',
            'client.view',
            'client.create',
            'client.update',
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'invoice.view',
            'invoice.create',
            'invoice.edit',
            'invoice.finalize',
            'invoice.void',
            'payment.record',
            'payment.reverse',
            'inventory.adjust',
            'reports.view',
            'user.invite',
            'user.manage',
        ]);
        $salesRole->syncPermissions([
            'client.view',
            'client.create',
            'invoice.view',
            'invoice.create',
            'invoice.edit',
            'invoice.finalize',
            'payment.record',
        ]);

        $owner = User::first();
        if (! $owner) {
            $owner = User::create([
                'name' => 'Owner',
                'email' => 'owner@example.com',
                'password' => Hash::make('password'),
            ]);
        }

        if (! $owner->hasRole('Owner')) {
            $owner->assignRole($ownerRole);
        }

        $company = Company::first();
        if (! $company) {
            $company = Company::create([
                'name' => 'Default Company',
                'code' => 'DEFAULT',
                'created_by' => $owner->id,
            ]);
        }

        $branch = Branch::where('company_id', $company->id)->first();
        if (! $branch) {
            $branch = Branch::create([
                'company_id' => $company->id,
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'created_by' => $owner->id,
            ]);
        }

        $terminal = Terminal::where('branch_id', $branch->id)->first();
        if (! $terminal) {
            Terminal::create([
                'branch_id' => $branch->id,
                'name' => 'Default Web Terminal',
                'identifier' => 'web-default',
                'is_default_web' => true,
                'created_by' => $owner->id,
            ]);
        }

        if (! $owner->company_id) {
            $owner->forceFill([
                'company_id' => $company->id,
                'current_branch_id' => $branch->id,
            ])->save();
        }

        $owner->branches()->syncWithoutDetaching([$branch->id]);

        Client::firstOrCreate(
            ['is_walk_in' => true],
            [
                'name' => 'Walk-in',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'created_by' => $owner->id,
            ]
        );
    }
}
