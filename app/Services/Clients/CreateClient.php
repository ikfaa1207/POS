<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Support\BranchContext;

class CreateClient
{
    public function __construct(private BranchContext $branchContext)
    {
    }

    public function execute(array $data, int $userId): Client
    {
        $data['company_id'] = $this->branchContext->requireCompanyId();
        $data['branch_id'] = $this->branchContext->requireBranchId();
        $data['created_by'] = $userId;

        return Client::create($data);
    }
}
