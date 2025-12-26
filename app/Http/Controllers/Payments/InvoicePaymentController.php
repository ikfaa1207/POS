<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordPaymentRequest;
use App\Models\Invoice;
use App\Services\Payments\RecordPayment;

class InvoicePaymentController extends Controller
{
    public function store(RecordPaymentRequest $request, Invoice $invoice, RecordPayment $service)
    {
        $service->execute($invoice, $request->validated(), $request->user()->id);

        return redirect()->route('invoices.show', $invoice);
    }
}
