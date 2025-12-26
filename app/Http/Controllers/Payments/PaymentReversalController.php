<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\ReversePayment;
use Illuminate\Http\Request;

class PaymentReversalController extends Controller
{
    public function __invoke(Request $request, Payment $payment, ReversePayment $service)
    {
        $service->execute($payment, $request->user()->id);

        return redirect()->route('invoices.show', $payment->invoice_id);
    }
}
