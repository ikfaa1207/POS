<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Services\Invoices\AddInvoiceItem;
use App\Services\Invoices\CreateDraftInvoice;
use App\Services\Invoices\FinalizeInvoice;
use App\Services\Invoices\VoidInvoice;
use App\Services\Payments\RecordPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseTwoHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_finalize_is_blocked_by_lock_version(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $user->id]);

        $invoice = app(CreateDraftInvoice::class)->execute($client->id, $user->id);

        app(AddInvoiceItem::class)->execute($invoice, [
            'description' => 'Service A',
            'quantity' => '1',
            'unit_price' => '100.00',
            'lock_version' => 0,
        ], $user->id);

        $this->expectException(\DomainException::class);

        app(FinalizeInvoice::class)->execute(
            $invoice->refresh(),
            $invoice->finalize_token,
            0,
            $user->id
        );
    }

    public function test_negative_stock_is_prevented_on_finalize(): void
    {
        config()->set('inventory.allow_negative', false);

        $user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $user->id]);
        $product = Product::factory()->create([
            'track_inventory' => true,
            'stock_qty' => 1,
            'created_by' => $user->id,
        ]);

        $invoice = app(CreateDraftInvoice::class)->execute($client->id, $user->id);

        app(AddInvoiceItem::class)->execute($invoice, [
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => '2',
            'unit_price' => '10.00',
            'lock_version' => 0,
        ], $user->id);

        $invoice->refresh();

        $this->expectException(\DomainException::class);

        app(FinalizeInvoice::class)->execute(
            $invoice,
            $invoice->finalize_token,
            $invoice->lock_version,
            $user->id
        );
    }

    public function test_void_creates_reversals_and_inventory_movements(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $user->id]);
        $product = Product::factory()->create([
            'track_inventory' => true,
            'stock_qty' => 5,
            'created_by' => $user->id,
        ]);

        $invoice = app(CreateDraftInvoice::class)->execute($client->id, $user->id);

        app(AddInvoiceItem::class)->execute($invoice, [
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => '2',
            'unit_price' => '10.00',
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
            'amount' => '20.00',
            'lock_version' => $invoice->lock_version,
        ], $user->id);

        $invoice->refresh();
        app(VoidInvoice::class)->execute($invoice, $invoice->lock_version, $user->id);

        $invoice->refresh();
        $product->refresh();

        $this->assertSame(InvoiceStatus::Voided, $invoice->status);
        $this->assertSame('5.00', (string) $product->stock_qty);
        $this->assertCount(2, InventoryMovement::where('invoice_id', $invoice->id)->get());
        $this->assertCount(2, $invoice->payments);
    }

    public function test_audit_log_created_on_finalize(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $user->id]);

        $invoice = app(CreateDraftInvoice::class)->execute($client->id, $user->id);

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

        $this->assertTrue(
            AuditLog::where('action', 'invoice.finalized')
                ->where('entity_id', $invoice->id)
                ->exists()
        );
    }
}
