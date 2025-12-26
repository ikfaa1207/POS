<?php

namespace App\Http\Controllers;

use App\Services\Reports\OutstandingBalancesReport;
use App\Services\Reports\SalesByProductReport;
use App\Services\Reports\TodaySalesReport;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        TodaySalesReport $todaySales,
        OutstandingBalancesReport $outstandingBalances,
        SalesByProductReport $salesByProduct
    ): Response {
        return Inertia::render('Dashboard', [
            'todaySales' => $todaySales->execute(),
            'outstandingInvoices' => $outstandingBalances->execute(),
            'salesByProduct' => $salesByProduct->execute(),
        ]);
    }
}
