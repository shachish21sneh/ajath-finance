<?php

namespace App\Repositories\Eloquent;

use App\Models\Ledger;
use App\Repositories\Contracts\LedgerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LedgerRepository implements LedgerRepositoryInterface
{
    public function getByCompany(int $companyId): Collection
    {
        return Ledger::with(['group', 'taxMaster'])
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?Ledger
    {
        return Ledger::with(['group', 'taxMaster', 'entries'])->find($id);
    }

    public function create(array $data): Ledger
    {
        return Ledger::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $ledger = Ledger::find($id);
        return $ledger ? $ledger->update($data) : false;
    }
}
