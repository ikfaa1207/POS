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
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('finalize_token')->nullable()->unique()->after('number');
            $table->unsignedInteger('lock_version')->default(0)->after('status');
            $table->timestamp('voided_at')->nullable()->after('finalized_at');
            $table->index(['status', 'finalized_at']);
        });

        DB::statement('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_status_check');
        DB::statement('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_finalized_at_check');
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check CHECK (status IN ('draft','finalized','voided'))");
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_state_timestamps_check CHECK ((status = 'draft' AND finalized_at IS NULL AND voided_at IS NULL) OR (status = 'finalized' AND finalized_at IS NOT NULL AND voided_at IS NULL) OR (status = 'voided' AND finalized_at IS NOT NULL AND voided_at IS NOT NULL))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['status', 'finalized_at']);
            $table->dropColumn(['finalize_token', 'lock_version', 'voided_at']);
        });

        DB::statement('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_status_check');
        DB::statement('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_state_timestamps_check');
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check CHECK (status IN ('draft','finalized'))");
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_finalized_at_check CHECK ((status = 'finalized' AND finalized_at IS NOT NULL) OR (status = 'draft' AND finalized_at IS NULL))");
    }
};
