<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Terminal;
use App\Models\User;

class BranchContext
{
    private ?int $companyId = null;
    private ?int $branchId = null;
    private ?int $terminalId = null;

    public function initializeForUser(User $user): void
    {
        $branch = null;
        if ($user->current_branch_id) {
            $branch = $user->branches()
                ->where('branches.id', $user->current_branch_id)
                ->first();
        }

        if (! $branch) {
            $branch = $user->branches()->first();
        }
        if (! $branch instanceof Branch) {
            return;
        }

        $terminal = Terminal::query()
            ->where('branch_id', $branch->id)
            ->where('is_default_web', true)
            ->first();

        if (! $terminal) {
            $terminal = Terminal::query()
                ->where('branch_id', $branch->id)
                ->first();
        }

        $this->companyId = $branch->company_id;
        $this->branchId = $branch->id;
        $this->terminalId = $terminal?->id;
    }

    public function companyId(): ?int
    {
        return $this->companyId;
    }

    public function branchId(): ?int
    {
        return $this->branchId;
    }

    public function terminalId(): ?int
    {
        return $this->terminalId;
    }

    public function requireCompanyId(): int
    {
        if (! $this->companyId) {
            $this->resolveFallback();
        }

        if (! $this->companyId) {
            throw new \DomainException('No company context resolved.');
        }

        return $this->companyId;
    }

    public function requireBranchId(): int
    {
        if (! $this->branchId) {
            $this->resolveFallback();
        }

        if (! $this->branchId) {
            throw new \DomainException('No branch context resolved.');
        }

        return $this->branchId;
    }

    public function requireTerminalId(): int
    {
        if (! $this->terminalId) {
            $this->resolveFallback();
        }

        if (! $this->terminalId) {
            throw new \DomainException('No terminal context resolved.');
        }

        return $this->terminalId;
    }

    private function resolveFallback(): void
    {
        $branch = Branch::query()->first();
        if (! $branch) {
            return;
        }

        $terminal = Terminal::query()
            ->where('branch_id', $branch->id)
            ->where('is_default_web', true)
            ->first() ?: Terminal::query()
                ->where('branch_id', $branch->id)
                ->first();

        $this->companyId = $branch->company_id;
        $this->branchId = $branch->id;
        $this->terminalId = $terminal?->id;
    }
}
