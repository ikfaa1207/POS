<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinalizeInvoiceRequest;
use App\Models\Invoice;
use App\Services\Invoices\FinalizeInvoice;

class InvoiceFinalizeController extends Controller
{
    public function __invoke(FinalizeInvoiceRequest $request, Invoice $invoice, FinalizeInvoice $service)
    {
        $service->execute(
            $invoice,
            $request->string('finalize_token'),
            $request->integer('lock_version'),
            $request->user()->id
        );

        return redirect()->route('invoices.show', $invoice);
    }
}
