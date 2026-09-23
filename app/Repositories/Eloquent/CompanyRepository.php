<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function all(): Collection
    {
        return Company::with('activeFinancialYear')->orderBy('name')->get();
    }

    public function find(int $id): ?Company
    {
        return Company::with(['financialYears', 'branches'])->find($id);
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $company = Company::find($id);
        return $company ? $company->update($data) : false;
    }
}
