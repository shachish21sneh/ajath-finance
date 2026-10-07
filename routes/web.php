<?php

use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AssetExpenseController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BankingController;
use App\Http\Controllers\BatteryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\FinancialYearController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SolarController;
use App\Http\Controllers\StockGroupController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxMasterController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UqcMasterController;
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
    Route::get('/api/products/search', [ProductController::class, 'searchAjax'])->name('api.products.search');
    Route::resource('products', ProductController::class)->except(['show', 'destroy']);
    Route::resource('stock-groups', StockGroupController::class)->except(['show', 'create', 'edit']);
    Route::resource('units', UnitController::class)->except(['show', 'create', 'edit']);
    Route::resource('taxes', TaxMasterController::class)->except(['show', 'create', 'edit']);
    Route::resource('uqc', UqcMasterController::class)->except(['show', 'create', 'edit']);
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

    // Financial & Operational Reports
    Route::get('/reports/day-book', [ReportController::class, 'dayBook'])->name('reports.day-book');
    Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('/reports/gst', [ReportController::class, 'gst'])->name('reports.gst');
    Route::get('/reports/stock-valuation', [ReportController::class, 'stockValuation'])->name('reports.stock-valuation');
    Route::get('/reports/battery', [ReportController::class, 'batteryReport'])->name('reports.battery');
    Route::get('/reports/solar', [ReportController::class, 'solarReport'])->name('reports.solar');
    Route::get('/reports/payroll', [ReportController::class, 'payrollReport'])->name('reports.payroll');

    // Payroll & HR Management
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/employees', [PayrollController::class, 'storeEmployee'])->name('payroll.employees.store');
    Route::post('/payroll/attendance', [PayrollController::class, 'storeAttendance'])->name('payroll.attendance.store');
    Route::post('/payroll/process', [PayrollController::class, 'processPayroll'])->name('payroll.process');
    Route::get('/payroll/payslips/{payslip}', [PayrollController::class, 'showPayslip'])->name('payroll.payslip');

    // Manufacturing & BOM & Job Work
    Route::get('/manufacturing', [ManufacturingController::class, 'index'])->name('manufacturing.index');
    Route::post('/manufacturing/boms', [ManufacturingController::class, 'storeBom'])->name('manufacturing.boms.store');
    Route::post('/manufacturing/production-orders', [ManufacturingController::class, 'storeProductionOrder'])->name('manufacturing.orders.store');
    Route::post('/manufacturing/production-orders/{order}/complete', [ManufacturingController::class, 'completeProductionOrder'])->name('manufacturing.orders.complete');

    // Battery ERP Domain
    Route::get('/battery', [BatteryController::class, 'index'])->name('battery.index');
    Route::post('/battery/models', [BatteryController::class, 'storeModel'])->name('battery.models.store');
    Route::post('/battery/serials', [BatteryController::class, 'storeSerial'])->name('battery.serials.store');
    Route::get('/battery/serials/{serial}', [BatteryController::class, 'showSerialJson'])->name('battery.serials.show');
    Route::post('/battery/tests', [BatteryController::class, 'storeTest'])->name('battery.tests.store');
    Route::post('/battery/warranties', [BatteryController::class, 'storeWarranty'])->name('battery.warranties.store');
    Route::post('/battery/claims', [BatteryController::class, 'storeClaim'])->name('battery.claims.store');

    // Solar ERP Domain
    Route::get('/solar', [SolarController::class, 'index'])->name('solar.index');
    Route::post('/solar/products', [SolarController::class, 'storeProduct'])->name('solar.products.store');
    Route::post('/solar/leads', [SolarController::class, 'storeLead'])->name('solar.leads.store');
    Route::post('/solar/surveys', [SolarController::class, 'storeSurvey'])->name('solar.surveys.store');
    Route::post('/solar/quotations', [SolarController::class, 'storeQuotation'])->name('solar.quotations.store');
    Route::post('/solar/projects', [SolarController::class, 'storeProject'])->name('solar.projects.store');

    // Dealer & Distributor Management
    Route::get('/dealers', [DealerController::class, 'index'])->name('dealers.index');
    Route::post('/dealers', [DealerController::class, 'store'])->name('dealers.store');

    // Service Management
    Route::get('/service', [ServiceController::class, 'index'])->name('service.index');
    Route::post('/service/tickets', [ServiceController::class, 'storeTicket'])->name('service.tickets.store');
    Route::post('/service/visits', [ServiceController::class, 'storeVisit'])->name('service.visits.store');

    // CRM & Sales Pipeline
    Route::get('/crm', [CrmController::class, 'index'])->name('crm.index');
    Route::post('/crm/leads', [CrmController::class, 'store'])->name('crm.leads.store');
    Route::post('/crm/leads/{lead}/stage', [CrmController::class, 'updateStage'])->name('crm.leads.stage');

    // Fixed Assets & Operating Expenses & Cost Centres
    Route::get('/assets-expenses', [AssetExpenseController::class, 'index'])->name('assets-expenses.index');
    Route::post('/assets-expenses/assets', [AssetExpenseController::class, 'storeAsset'])->name('assets-expenses.assets.store');
    Route::post('/assets-expenses/expenses', [AssetExpenseController::class, 'storeExpense'])->name('assets-expenses.expenses.store');
    Route::post('/assets-expenses/cost-centres', [AssetExpenseController::class, 'storeCostCentre'])->name('assets-expenses.cost-centres.store');

    // AI Business Assistant
    Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai-assistant.index');
    Route::post('/ai-assistant/ask', [AiAssistantController::class, 'query'])->name('ai-assistant.ask');
    Route::post('/api/ai/query', [AiAssistantController::class, 'query'])->name('api.ai.query');

    // Global Instant Spotlight Search API
    Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

    // System Settings & Maintenance
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
});
