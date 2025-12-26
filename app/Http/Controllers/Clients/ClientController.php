<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\Clients\CreateClient;
use App\Services\Clients\UpdateClient;
use App\Services\Invoices\ListClientInvoices;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Clients/Index', [
            'clients' => Client::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone', 'is_walk_in']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request, CreateClient $service)
    {
        $service->execute($request->validated(), $request->user()->id);

        return redirect()->route('clients.index');
    }

    public function show(Client $client, ListClientInvoices $invoices): Response
    {
        return Inertia::render('Clients/Show', [
            'client' => $client,
            'invoices' => $invoices->execute($client),
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client, UpdateClient $service)
    {
        $service->execute($client, $request->validated());

        return redirect()->route('clients.show', $client);
    }
}
