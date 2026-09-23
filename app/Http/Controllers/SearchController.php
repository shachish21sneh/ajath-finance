<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected SearchService $searchService) {}

    public function search(Request $request): JsonResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if (!$company) {
            return response()->json([]);
        }

        $query = $request->get('q', '');
        $results = $this->searchService->searchEverywhere($company, $query);

        return response()->json($results);
    }
}
