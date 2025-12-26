<?php

namespace App\Listeners;

use App\Contracts\DomainEvent;
use App\Jobs\RecordDomainEventJob;

class RecordDomainEvent
{
    public function handle(DomainEvent $event): void
    {
        RecordDomainEventJob::dispatch(
            $event->eventUuid(),
            $event->name(),
            $event->payload(),
            $event->companyId(),
            $event->branchId(),
            $event->terminalId(),
            $event->userId(),
            $event->occurredAt()->toISOString()
        );
    }
}
