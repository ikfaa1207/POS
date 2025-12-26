<?php

namespace App\Contracts;

use Carbon\CarbonInterface;

interface DomainEvent
{
    public function eventUuid(): string;
    public function name(): string;
    public function payload(): array;
    public function companyId(): int;
    public function branchId(): int;
    public function terminalId(): ?int;
    public function userId(): ?int;
    public function occurredAt(): CarbonInterface;
}
