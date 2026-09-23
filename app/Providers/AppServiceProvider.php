<?php

namespace App\Providers;

use App\Helpers\AccountingHelper;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\LedgerRepositoryInterface;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\LedgerRepository;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(LedgerRepositoryInterface::class, LedgerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share active company and active financial year with all blade views
        View::composer('*', function ($view) {
            $company = AccountingHelper::getActiveCompany();
            $financialYear = AccountingHelper::getActiveFinancialYear();
            $view->with([
                'currentCompany' => $company,
                'currentFinancialYear' => $financialYear,
            ]);
        });
    }
}
