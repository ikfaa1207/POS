<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\InventoryValuationReport;
use App\Services\Reports\PaymentMismatchReport;
use App\Services\Reports\SalesByDateRangeReport;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function index(
        Request $request,
        SalesByDateRangeReport $salesByDate,
        PaymentMismatchReport $paymentMismatch,
        InventoryValuationReport $inventoryValuation,
        BranchContext $branchContext
    ): Response {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(7)->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();

        $branchId = $branchContext->requireBranchId();

        return Inertia::render('Reports/Index', [
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'salesByDate' => Cache::remember(
                "reports.sales_by_date.{$branchId}.{$startDate}.{$endDate}",
                60,
                fn () => $salesByDate->execute($startDate, $endDate)
            ),
            'paymentMismatches' => Cache::remember(
                "reports.payment_mismatch.{$branchId}.{$startDate}.{$endDate}",
                60,
                fn () => $paymentMismatch->execute($startDate, $endDate)
            ),
            'inventoryValuation' => Cache::remember(
                "reports.inventory_valuation.{$branchId}",
                60,
                fn () => $inventoryValuation->execute()
            ),
        ]);
    }
}
