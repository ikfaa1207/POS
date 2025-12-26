<?php

namespace App\Services\Clients;

use App\Models\Client;

class UpdateClient
{
    public function execute(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }
}
