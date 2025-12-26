<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddInvoiceItemRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Invoices\AddInvoiceItem;
use App\Services\Invoices\RemoveInvoiceItem;

class InvoiceItemController extends Controller
{
    public function store(AddInvoiceItemRequest $request, Invoice $invoice, AddInvoiceItem $service)
    {
        $service->execute($invoice, $request->validated(), $request->user()->id);

        return redirect()->route('invoices.show', $invoice);
    }

    public function destroy(Invoice $invoice, InvoiceItem $item, RemoveInvoiceItem $service)
    {
        $validated = request()->validate([
            'lock_version' => ['required', 'integer', 'min:0'],
        ]);

        $service->execute($invoice, $item, (int) $validated['lock_version']);

        return redirect()->route('invoices.show', $invoice);
    }
}
