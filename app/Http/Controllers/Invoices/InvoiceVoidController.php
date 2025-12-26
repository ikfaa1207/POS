<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoidInvoiceRequest;
use App\Models\Invoice;
use App\Services\Invoices\VoidInvoice;

class InvoiceVoidController extends Controller
{
    public function __invoke(VoidInvoiceRequest $request, Invoice $invoice, VoidInvoice $service)
    {
        $service->execute($invoice, $request->integer('lock_version'), $request->user()->id);

        return redirect()->route('invoices.show', $invoice);
    }
}
