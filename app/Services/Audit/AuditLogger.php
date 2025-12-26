<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Support\BranchContext;

class AuditLogger
{
    public function __construct(private BranchContext $branchContext)
    {
    }

    public function log(
        int $userId,
        string $action,
        string $entityType,
        int $entityId,
        ?array $before,
        ?array $after,
        ?int $companyId = null,
        ?int $branchId = null,
        ?int $terminalId = null
    ): AuditLog {
        $companyId ??= $this->branchContext->requireCompanyId();
        $branchId ??= $this->branchContext->requireBranchId();

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before' => $before,
            'after' => $after,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'terminal_id' => $terminalId ?? $this->branchContext->terminalId(),
            'created_at' => now(),
        ]);
    }
}
