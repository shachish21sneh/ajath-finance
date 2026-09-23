<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BankingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialYearController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Company & Financial Year Management
    Route::resource('companies', CompanyController::class)->except(['show', 'destroy']);
    Route::post('/companies/{id}/switch', [CompanyController::class, 'switch'])->name('companies.switch');
    Route::get('/financial-years', [FinancialYearController::class, 'index'])->name('financial-years.index');
    Route::post('/financial-years', [FinancialYearController::class, 'store'])->name('financial-years.store');
    Route::post('/financial-years/{id}/switch', [FinancialYearController::class, 'switch'])->name('financial-years.switch');

    // User Management & RBAC
    Route::resource('users', UserController::class)->except(['show', 'destroy']);

    // Chart of Accounts & Masters
    Route::resource('ledgers', LedgerController::class)->except(['show', 'destroy']);
    Route::get('/ledgers/{ledger}/statement', [LedgerController::class, 'statement'])->name('ledgers.statement');
    Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);
    Route::resource('suppliers', SupplierController::class)->except(['show', 'destroy']);
    Route::resource('products', ProductController::class)->except(['show', 'destroy']);
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');

    // Double-Entry Accounting Vouchers
    Route::resource('vouchers', VoucherController::class)->only(['index', 'create', 'store', 'show']);

    // Sales & Billing
    Route::get('/sales', [SalesInvoiceController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SalesInvoiceController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SalesInvoiceController::class, 'store'])->name('sales.store');
    Route::get('/sales/pos', [SalesInvoiceController::class, 'pos'])->name('sales.pos');
    Route::post('/sales/pos', [SalesInvoiceController::class, 'storePos'])->name('sales.pos.store');
    Route::get('/sales/{invoice}', [SalesInvoiceController::class, 'show'])->name('sales.show');

    // Purchase Invoicing & Bills
    Route::resource('purchases', PurchaseInvoiceController::class)->only(['index', 'create', 'store', 'show']);

    // Banking & Cash
    Route::get('/banking', [BankingController::class, 'index'])->name('banking.index');

    // Financial Reports
    Route::get('/reports/day-book', [ReportController::class, 'dayBook'])->name('reports.day-book');
    Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('/reports/gst', [ReportController::class, 'gst'])->name('reports.gst');

    // Global Instant Spotlight Search API
    Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

    // System Settings & Maintenance
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
});
