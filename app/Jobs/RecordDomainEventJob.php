<?php

namespace App\Jobs;

use App\Models\DomainEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordDomainEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $eventUuid,
        private readonly string $name,
        private readonly array $payload,
        private readonly int $companyId,
        private readonly int $branchId,
        private readonly ?int $terminalId,
        private readonly ?int $userId,
        private readonly string $occurredAt
    ) {
    }

    public function handle(): void
    {
        DomainEvent::firstOrCreate(
            ['event_uuid' => $this->eventUuid],
            [
                'name' => $this->name,
                'payload' => $this->payload,
                'company_id' => $this->companyId,
                'branch_id' => $this->branchId,
                'terminal_id' => $this->terminalId,
                'user_id' => $this->userId,
                'occurred_at' => $this->occurredAt,
            ]
        );
    }
}
