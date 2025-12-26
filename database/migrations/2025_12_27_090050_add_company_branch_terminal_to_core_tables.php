<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('terminal_id')->nullable()->after('branch_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('terminal_id')->nullable()->after('branch_id');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('terminal_id')->nullable()->after('branch_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('terminal_id')->nullable()->after('branch_id');
        });

        $now = now();
        $ownerId = DB::table('users')->orderBy('id')->value('id');

        $companyId = DB::table('companies')->value('id');
        if (! $companyId) {
            $companyId = DB::table('companies')->insertGetId([
                'name' => 'Default Company',
                'code' => 'DEFAULT',
                'created_by' => $ownerId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $branchId = DB::table('branches')
            ->where('company_id', $companyId)
            ->value('id');
        if (! $branchId) {
            $branchId = DB::table('branches')->insertGetId([
                'company_id' => $companyId,
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'created_by' => $ownerId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $terminalId = DB::table('terminals')->value('id');
        if (! $terminalId) {
            $terminalId = DB::table('terminals')->insertGetId([
                'branch_id' => $branchId,
                'name' => 'Default Web Terminal',
                'identifier' => 'web-default',
                'is_default_web' => true,
                'created_by' => $ownerId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('users')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId, 'current_branch_id' => $branchId]);

        $userIds = DB::table('users')->pluck('id')->all();
        foreach ($userIds as $userId) {
            DB::table('branch_user')->insertOrIgnore([
                'branch_id' => $branchId,
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('clients')->whereNull('company_id')->update([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        DB::table('products')->whereNull('company_id')->update([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        DB::table('invoices')->whereNull('company_id')->update([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'terminal_id' => $terminalId,
        ]);

        DB::table('payments')->whereNull('company_id')->update([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'terminal_id' => $terminalId,
        ]);

        DB::table('inventory_movements')->whereNull('company_id')->update([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'terminal_id' => $terminalId,
        ]);

        DB::table('audit_logs')->whereNull('company_id')->update([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'terminal_id' => $terminalId,
        ]);

        DB::statement('ALTER TABLE clients ALTER COLUMN company_id SET NOT NULL');
        DB::statement('ALTER TABLE clients ALTER COLUMN branch_id SET NOT NULL');
        DB::statement('ALTER TABLE products ALTER COLUMN company_id SET NOT NULL');
        DB::statement('ALTER TABLE products ALTER COLUMN branch_id SET NOT NULL');
        DB::statement('ALTER TABLE invoices ALTER COLUMN company_id SET NOT NULL');
        DB::statement('ALTER TABLE invoices ALTER COLUMN branch_id SET NOT NULL');
        DB::statement('ALTER TABLE invoices ALTER COLUMN terminal_id SET NOT NULL');
        DB::statement('ALTER TABLE payments ALTER COLUMN company_id SET NOT NULL');
        DB::statement('ALTER TABLE payments ALTER COLUMN branch_id SET NOT NULL');
        DB::statement('ALTER TABLE payments ALTER COLUMN terminal_id SET NOT NULL');
        DB::statement('ALTER TABLE inventory_movements ALTER COLUMN company_id SET NOT NULL');
        DB::statement('ALTER TABLE inventory_movements ALTER COLUMN branch_id SET NOT NULL');
        DB::statement('ALTER TABLE audit_logs ALTER COLUMN company_id SET NOT NULL');
        DB::statement('ALTER TABLE audit_logs ALTER COLUMN branch_id SET NOT NULL');

        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->index(['company_id', 'branch_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->index(['company_id', 'branch_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('terminal_id')->references('id')->on('terminals')->restrictOnDelete();
            $table->index(['company_id', 'branch_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('terminal_id')->references('id')->on('terminals')->restrictOnDelete();
            $table->index(['company_id', 'branch_id']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('terminal_id')->references('id')->on('terminals')->nullOnDelete();
            $table->index(['company_id', 'branch_id']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('terminal_id')->references('id')->on('terminals')->nullOnDelete();
            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['terminal_id']);
            $table->dropColumn(['company_id', 'branch_id', 'terminal_id']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['terminal_id']);
            $table->dropColumn(['company_id', 'branch_id', 'terminal_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['terminal_id']);
            $table->dropColumn(['company_id', 'branch_id', 'terminal_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['terminal_id']);
            $table->dropColumn(['company_id', 'branch_id', 'terminal_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });
    }
};
