<?php

namespace App\Events;

use App\Contracts\DomainEvent;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseDomainEvent implements DomainEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $eventUuid,
        public readonly string $eventName,
        public readonly array $eventPayload,
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly ?int $terminalId,
        public readonly ?int $userId,
        public readonly CarbonInterface $occurredAt
    ) {
    }

    public function eventUuid(): string
    {
        return $this->eventUuid;
    }

    public function name(): string
    {
        return $this->eventName;
    }

    public function payload(): array
    {
        return $this->eventPayload;
    }

    public function companyId(): int
    {
        return $this->companyId;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function terminalId(): ?int
    {
        return $this->terminalId;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function occurredAt(): CarbonInterface
    {
        return $this->occurredAt;
    }
}
