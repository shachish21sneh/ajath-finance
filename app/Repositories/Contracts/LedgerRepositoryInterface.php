<?php

namespace App\Repositories\Contracts;

use App\Models\Ledger;
use Illuminate\Database\Eloquent\Collection;

interface LedgerRepositoryInterface
{
    public function getByCompany(int $companyId): Collection;
    public function find(int $id): ?Ledger;
    public function create(array $data): Ledger;
    public function update(int $id, array $data): bool;
}
