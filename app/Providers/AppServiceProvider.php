<?php

namespace App\Providers;

use App\Events\InventoryAdjusted;
use App\Events\InvoiceFinalized;
use App\Events\InvoiceVoided;
use App\Events\PaymentPosted;
use App\Listeners\RecordDomainEvent;
use App\Listeners\RecordPaymentLedger;
use App\Listeners\RecordSalesLedger;
use App\Support\BranchContext;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BranchContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::listen(InvoiceFinalized::class, RecordDomainEvent::class);
        Event::listen(InvoiceFinalized::class, RecordSalesLedger::class);
        Event::listen(InvoiceVoided::class, RecordDomainEvent::class);
        Event::listen(PaymentPosted::class, RecordDomainEvent::class);
        Event::listen(PaymentPosted::class, RecordPaymentLedger::class);
        Event::listen(InventoryAdjusted::class, RecordDomainEvent::class);

        DB::listen(function (QueryExecuted $query): void {
            $threshold = config('app.slow_query_ms', 200);
            if ($query->time < $threshold) {
                return;
            }

            Log::warning('slow_query', [
                'time_ms' => $query->time,
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'connection' => $query->connectionName,
            ]);
        });
    }
}
