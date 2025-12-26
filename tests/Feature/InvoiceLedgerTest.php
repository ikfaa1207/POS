<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\User;
use App\Services\Invoices\AddInvoiceItem;
use App\Services\Invoices\CreateDraftInvoice;
use App\Services\Invoices\FinalizeInvoice;
use App\Services\Invoices\GetInvoiceDetail;
use App\Services\Payments\RecordPayment;
use App\Services\Payments\ReversePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_invoice_with_items(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $user->id]);

        $invoice = (new CreateDraftInvoice())->execute($client->id, $user->id);

        app(AddInvoiceItem::class)->execute($invoice, [
            'description' => 'Service A',
            'quantity' => '2',
            'unit_price' => '25.00',
            'lock_version' => 0,
        ], $user->id);

        $invoice->refresh();

        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
        $this->assertSame('50.00', $invoice->total_amount);
        $this->assertCount(1, $invoice->items);
    }

    public function test_finalized_invoice_cannot_be_mutated(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $user->id]);

        $invoice = (new CreateDraftInvoice())->execute($client->id, $user->id);

        app(AddInvoiceItem::class)->execute($invoice, [
            'description' => 'Service A',
            'quantity' => '1',
            'unit_price' => '100.00',
            'lock_version' => 0,
        ], $user->id);

        $invoice->refresh();

        app(FinalizeInvoice::class)->execute(
            $invoice,
            $invoice->finalize_token,
            $invoice->lock_version,
            $user->id
        );

        $this->expectException(\DomainException::class);
        $invoice->update(['number' => 'INV-MUTATION']);
    }

    public function test_partial_payment_reduces_balance(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $user->id]);

        $invoice = (new CreateDraftInvoice())->execute($client->id, $user->id);

        app(AddInvoiceItem::class)->execute($invoice, [
            'description' => 'Service A',
            'quantity' => '1',
            'unit_price' => '200.00',
            'lock_version' => 0,
        ], $user->id);

        $invoice->refresh();
        app(FinalizeInvoice::class)->execute(
            $invoice,
            $invoice->finalize_token,
            $invoice->lock_version,
            $user->id
        );

        $invoice->refresh();
        app(RecordPayment::class)->execute($invoice, [
            'method' => 'cash',
            'amount' => '50.00',
            'lock_version' => $invoice->lock_version,
        ], $user->id);

        $detail = (new GetInvoiceDetail())->execute($invoice->refresh());

        $this->assertSame('50.00', $detail['paid_amount']);
        $this->assertSame('150.00', $detail['balance']);
    }

    public function test_balance_accuracy_with_reversal(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $user->id]);

        $invoice = (new CreateDraftInvoice())->execute($client->id, $user->id);

        app(AddInvoiceItem::class)->execute($invoice, [
            'description' => 'Service A',
            'quantity' => '1',
            'unit_price' => '120.00',
            'lock_version' => 0,
        ], $user->id);

        $invoice->refresh();
        app(FinalizeInvoice::class)->execute(
            $invoice,
            $invoice->finalize_token,
            $invoice->lock_version,
            $user->id
        );

        $invoice->refresh();
        $paymentOne = app(RecordPayment::class)->execute($invoice, [
            'method' => 'card',
            'amount' => '60.00',
            'lock_version' => $invoice->lock_version,
        ], $user->id);

        $invoice->refresh();
        $paymentTwo = app(RecordPayment::class)->execute($invoice, [
            'method' => 'cash',
            'amount' => '20.00',
            'lock_version' => $invoice->lock_version,
        ], $user->id);

        app(ReversePayment::class)->execute($paymentTwo, $user->id);

        $detail = (new GetInvoiceDetail())->execute($invoice->refresh());

        $this->assertSame('60.00', $detail['paid_amount']);
        $this->assertSame('60.00', $detail['balance']);
    }
}
