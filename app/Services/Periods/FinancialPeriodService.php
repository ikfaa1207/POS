<?php

namespace App\Services\Periods;

use App\Models\FinancialPeriod;
use Carbon\CarbonInterface;

class FinancialPeriodService
{
    public function assertOpen(int $branchId, CarbonInterface $date): void
    {
        $period = FinancialPeriod::query()
            ->where('branch_id', $branchId)
            ->where('period_date', $date->toDateString())
            ->first();

        if ($period?->closed_at) {
            throw new \DomainException('Financial period is closed.');
        }
    }
}
