<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\Invoices\CreateDraftInvoice;
use App\Services\Invoices\GetInvoiceDetail;
use App\Services\Invoices\ListInvoices;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(ListInvoices $invoices): Response
    {
        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices->execute(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Invoices/Create', [
            'clients' => Client::query()
                ->orderBy('name')
                ->get(['id', 'name', 'is_walk_in']),
        ]);
    }

    public function store(StoreInvoiceRequest $request, CreateDraftInvoice $service)
    {
        $invoice = $service->execute($request->integer('client_id'), $request->user()->id);

        return redirect()->route('invoices.show', $invoice);
    }

    public function show(GetInvoiceDetail $service, Invoice $invoice): Response
    {
        $detail = $service->execute($invoice);

        return Inertia::render('Invoices/Show', [
            'invoice' => $detail['invoice'],
            'paidAmount' => $detail['paid_amount'],
            'balance' => $detail['balance'],
            'paymentStatus' => $detail['payment_status'],
            'products' => Product::query()
                ->orderBy('name')
                ->get(['id', 'name', 'price']),
        ]);
    }
}
