<?php

namespace Database\Seeders;

use App\Enums\EntryType;
use App\Enums\LedgerNature;
use App\Enums\PartyType;
use App\Enums\VoucherType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Ledger;
use App\Models\LedgerGroup;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockCategory;
use App\Models\StockGroup;
use App\Models\TaxMaster;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AccountingService;
use App\Services\InvoicingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions
        $superAdminRole = Role::create([
            'name' => 'Super Administrator',
            'slug' => 'super-admin',
            'description' => 'Full administrative control over all companies and settings',
        ]);

        $accountantRole = Role::create([
            'name' => 'Chief Accountant',
            'slug' => 'accountant',
            'description' => 'Manages vouchers, ledgers, billing, banking, and financial reports',
        ]);

        $salesRole = Role::create([
            'name' => 'Sales Manager',
            'slug' => 'sales-manager',
            'description' => 'Manages sales invoices, quotations, POS, and customers',
        ]);

        $modules = ['companies', 'users', 'masters', 'vouchers', 'sales', 'purchases', 'banking', 'reports', 'settings'];
        foreach ($modules as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permission = Permission::create([
                    'name' => ucfirst($action) . ' ' . ucfirst($module),
                    'slug' => "{$module}.{$action}",
                    'module' => $module,
                    'description' => "Permission to {$action} {$module}",
                ]);

                $superAdminRole->permissions()->attach($permission->id);
                if ($module !== 'settings' && $module !== 'users') {
                    $accountantRole->permissions()->attach($permission->id);
                }
            }
        }

        // 2. Default Company
        $company = Company::create([
            'name' => 'Ajath Enterprises Pvt Ltd',
            'legal_name' => 'Ajath Enterprises Private Limited',
            'email' => 'contact@ajath.com',
            'phone' => '+91 98765 43210',
            'gstin' => '07AAAAA0000A1Z5',
            'pan' => 'AAAAA0000A',
            'address' => 'Plot 42, Cyber Commercial Hub, Connaught Place',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'state_code' => '07',
            'pincode' => '110001',
            'country' => 'India',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'is_active' => true,
        ]);

        // Branch
        Branch::create([
            'company_id' => $company->id,
            'name' => 'Head Office & Primary Depot',
            'code' => 'DEL-HO',
            'phone' => '+91 98765 43210',
            'gstin' => '07AAAAA0000A1Z5',
            'address' => 'Connaught Place, New Delhi',
            'is_main' => true,
        ]);

        // Financial Year
        $fy = FinancialYear::create([
            'company_id' => $company->id,
            'title' => 'FY 2025-2026',
            'start_date' => '2025-04-01',
            'end_date' => '2026-03-31',
            'is_active' => true,
            'is_locked' => false,
        ]);

        // 3. Users
        $adminUser = User::create([
            'name' => 'Deepak Verma (Admin)',
            'email' => 'admin@ajath.com',
            'password' => Hash::make('password'),
            'role_id' => $superAdminRole->id,
            'company_id' => $company->id,
            'phone' => '+91 99999 88888',
            'is_active' => true,
        ]);

        $accountantUser = User::create([
            'name' => 'Priya Sharma (Accountant)',
            'email' => 'accountant@ajath.com',
            'password' => Hash::make('password'),
            'role_id' => $accountantRole->id,
            'company_id' => $company->id,
            'phone' => '+91 88888 77777',
            'is_active' => true,
        ]);

        // 4. Tax Masters
        $gst0 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 0% (Exempt)', 'rate' => 0.00, 'cgst_rate' => 0.00, 'sgst_rate' => 0.00, 'igst_rate' => 0.00]);
        $gst5 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 5%', 'rate' => 5.00, 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00]);
        $gst12 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 12%', 'rate' => 12.00, 'cgst_rate' => 6.00, 'sgst_rate' => 6.00, 'igst_rate' => 12.00]);
        $gst18 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 18%', 'rate' => 18.00, 'cgst_rate' => 9.00, 'sgst_rate' => 9.00, 'igst_rate' => 18.00]);
        $gst28 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 28%', 'rate' => 28.00, 'cgst_rate' => 14.00, 'sgst_rate' => 14.00, 'igst_rate' => 28.00]);

        // 5. Standard Ledger Groups
        // ASSETS
        $gAssets = LedgerGroup::create(['name' => 'Primary Assets', 'slug' => 'assets', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 1]);
        $gCurrentAssets = LedgerGroup::create(['parent_id' => $gAssets->id, 'name' => 'Current Assets', 'slug' => 'current-assets', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 2]);
        $gCash = LedgerGroup::create(['parent_id' => $gCurrentAssets->id, 'name' => 'Cash-in-hand', 'slug' => 'cash-in-hand', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 3]);
        $gBank = LedgerGroup::create(['parent_id' => $gCurrentAssets->id, 'name' => 'Bank Accounts', 'slug' => 'bank-accounts', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 4]);
        $gDebtors = LedgerGroup::create(['parent_id' => $gCurrentAssets->id, 'name' => 'Sundry Debtors', 'slug' => 'sundry-debtors', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 5]);
        $gStock = LedgerGroup::create(['parent_id' => $gCurrentAssets->id, 'name' => 'Stock-in-hand', 'slug' => 'stock-in-hand', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 6]);
        $gFixedAssets = LedgerGroup::create(['parent_id' => $gAssets->id, 'name' => 'Fixed Assets', 'slug' => 'fixed-assets', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 7]);

        // LIABILITIES
        $gLiab = LedgerGroup::create(['name' => 'Primary Liabilities', 'slug' => 'liabilities', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 10]);
        $gCurrentLiab = LedgerGroup::create(['parent_id' => $gLiab->id, 'name' => 'Current Liabilities', 'slug' => 'current-liabilities', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 11]);
        $gCreditors = LedgerGroup::create(['parent_id' => $gCurrentLiab->id, 'name' => 'Sundry Creditors', 'slug' => 'sundry-creditors', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 12]);
        $gDutiesTaxes = LedgerGroup::create(['parent_id' => $gCurrentLiab->id, 'name' => 'Duties & Taxes', 'slug' => 'duties-and-taxes', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 13]);
        $gCapital = LedgerGroup::create(['parent_id' => $gLiab->id, 'name' => 'Capital Account', 'slug' => 'capital-account', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 14]);

        // INCOMES
        $gIncome = LedgerGroup::create(['name' => 'Primary Income', 'slug' => 'income', 'nature' => LedgerNature::INCOME, 'is_system' => true, 'order' => 20]);
        $gDirectIncome = LedgerGroup::create(['parent_id' => $gIncome->id, 'name' => 'Direct Incomes', 'slug' => 'direct-incomes', 'nature' => LedgerNature::INCOME, 'affects_gross_profit' => true, 'is_system' => true, 'order' => 21]);
        $gIndirectIncome = LedgerGroup::create(['parent_id' => $gIncome->id, 'name' => 'Indirect Incomes', 'slug' => 'indirect-incomes', 'nature' => LedgerNature::INCOME, 'is_system' => true, 'order' => 22]);

        // EXPENSES
        $gExpense = LedgerGroup::create(['name' => 'Primary Expense', 'slug' => 'expense', 'nature' => LedgerNature::EXPENSE, 'is_system' => true, 'order' => 30]);
        $gDirectExpense = LedgerGroup::create(['parent_id' => $gExpense->id, 'name' => 'Direct Expenses', 'slug' => 'direct-expenses', 'nature' => LedgerNature::EXPENSE, 'affects_gross_profit' => true, 'is_system' => true, 'order' => 31]);
        $gIndirectExpense = LedgerGroup::create(['parent_id' => $gExpense->id, 'name' => 'Indirect Expenses', 'slug' => 'indirect-expenses', 'nature' => LedgerNature::EXPENSE, 'is_system' => true, 'order' => 32]);

        // 6. Essential Ledgers
        $cashLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gCash->id,
            'name' => 'Cash Account',
            'code' => 'CASH-01',
            'opening_balance' => 50000.00,
            'opening_balance_type' => 'Dr',
            'current_balance' => 50000.00,
            'party_type' => PartyType::CASH,
            'is_system' => true,
        ]);

        $hdfcLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gBank->id,
            'name' => 'HDFC Current A/c (4921)',
            'code' => 'BANK-HDFC',
            'opening_balance' => 250000.00,
            'opening_balance_type' => 'Dr',
            'current_balance' => 250000.00,
            'party_type' => PartyType::BANK,
            'bank_name' => 'HDFC Bank Ltd',
            'bank_account_no' => '50200012345678',
            'bank_ifsc' => 'HDFC0000123',
            'bank_branch' => 'Connaught Place Branch',
            'is_system' => true,
        ]);

        $salesLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gDirectIncome->id,
            'name' => 'Sales Account',
            'code' => 'INC-SALES',
            'party_type' => PartyType::NONE,
            'is_system' => true,
        ]);

        $purchaseLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gDirectExpense->id,
            'name' => 'Purchase Account',
            'code' => 'EXP-PURCH',
            'party_type' => PartyType::NONE,
            'is_system' => true,
        ]);

        // Taxes Ledgers
        $cgstOutput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Output CGST', 'code' => 'TAX-OUT-CGST', 'party_type' => PartyType::NONE, 'is_system' => true]);
        $sgstOutput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Output SGST', 'code' => 'TAX-OUT-SGST', 'party_type' => PartyType::NONE, 'is_system' => true]);
        $igstOutput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Output IGST', 'code' => 'TAX-OUT-IGST', 'party_type' => PartyType::NONE, 'is_system' => true]);

        $cgstInput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Input CGST', 'code' => 'TAX-IN-CGST', 'party_type' => PartyType::NONE, 'is_system' => true]);
        $sgstInput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Input SGST', 'code' => 'TAX-IN-SGST', 'party_type' => PartyType::NONE, 'is_system' => true]);
        $igstInput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Input IGST', 'code' => 'TAX-IN-IGST', 'party_type' => PartyType::NONE, 'is_system' => true]);

        $roundOffLedger = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gIndirectExpense->id, 'name' => 'Round Off Account', 'code' => 'EXP-RND', 'party_type' => PartyType::NONE, 'is_system' => true]);

        // Demo Customers (Sundry Debtors)
        $custApex = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gDebtors->id,
            'name' => 'Apex Tech Solutions Pvt Ltd',
            'code' => 'CUST-001',
            'party_type' => PartyType::CUSTOMER,
            'gstin' => '07AABCA1234F1Z8',
            'pan' => 'AABCA1234F',
            'email' => 'billing@apextech.com',
            'phone' => '+91 98111 22233',
            'address' => 'Building 12, Nehru Place',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'state_code' => '07',
            'credit_limit' => 500000.00,
            'credit_days' => 30,
        ]);

        $custMetro = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gDebtors->id,
            'name' => 'Metro Wholesale Trading Co',
            'code' => 'CUST-002',
            'party_type' => PartyType::CUSTOMER,
            'gstin' => '27AABCM5678G1Z2', // Inter-state Maharashtra
            'pan' => 'AABCM5678G',
            'email' => 'accounts@metrowholesale.in',
            'phone' => '+91 98222 33344',
            'address' => '45 Industrial Area, Andheri East',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'credit_limit' => 1000000.00,
            'credit_days' => 45,
        ]);

        // Demo Suppliers (Sundry Creditors)
        $suppSunrise = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gCreditors->id,
            'name' => 'Sunrise Components Ltd',
            'code' => 'SUPP-001',
            'party_type' => PartyType::SUPPLIER,
            'gstin' => '07AABCS9876H1Z4',
            'pan' => 'AABCS9876H',
            'email' => 'sales@sunrisecomponents.com',
            'phone' => '+91 98333 44455',
            'address' => 'Sector 18, Okhla Industrial Area',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'state_code' => '07',
            'credit_limit' => 750000.00,
            'credit_days' => 30,
        ]);

        $suppGlobal = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gCreditors->id,
            'name' => 'Global Hardware Imports',
            'code' => 'SUPP-002',
            'party_type' => PartyType::SUPPLIER,
            'gstin' => '24AABCG4321J1Z9', // Inter-state Gujarat
            'pan' => 'AABCG4321J',
            'email' => 'dispatch@globalhardware.com',
            'phone' => '+91 98444 55566',
            'address' => 'GIDC Estate, Surat',
            'city' => 'Surat',
            'state' => 'Gujarat',
            'state_code' => '24',
            'credit_limit' => 1200000.00,
            'credit_days' => 60,
        ]);

        // 7. Inventory Units & Masters
        $unitPcs = Unit::create(['company_id' => $company->id, 'name' => 'Pieces', 'symbol' => 'PCS', 'decimal_places' => 0]);
        $unitBox = Unit::create(['company_id' => $company->id, 'name' => 'Box', 'symbol' => 'BOX', 'decimal_places' => 0]);
        $unitKgs = Unit::create(['company_id' => $company->id, 'name' => 'Kilograms', 'symbol' => 'KGS', 'decimal_places' => 2]);

        $stockGrpElectronics = StockGroup::create(['company_id' => $company->id, 'name' => 'Electronics & Networking']);
        $stockGrpFurniture = StockGroup::create(['company_id' => $company->id, 'name' => 'Office Furniture']);
        $stockGrpServices = StockGroup::create(['company_id' => $company->id, 'name' => 'IT Services']);

        $warehouseMain = Warehouse::create([
            'company_id' => $company->id,
            'name' => 'Central Godown (Depot 1)',
            'code' => 'WH-01',
            'address' => 'Warehouse Complex 7, Okhla Phase II, New Delhi',
            'is_default' => true,
        ]);

        // Products
        $pRouter = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpElectronics->id,
            'unit_id' => $unitPcs->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Enterprise Wi-Fi 6 Router AX3000',
            'sku' => 'RT-AX3000',
            'barcode' => '890123450001',
            'item_type' => 'goods',
            'hsn_code' => '851762',
            'purchase_price' => 2200.00,
            'selling_price' => 3500.00,
            'mrp' => 4200.00,
            'opening_stock' => 50.00,
            'opening_stock_valuation' => 110000.00,
            'current_stock' => 50.00,
            'reorder_level' => 10.00,
            'description' => 'Gigabit Dual Band Wi-Fi 6 Router with VPN and QoS support',
        ]);

        $pMonitor = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpElectronics->id,
            'unit_id' => $unitPcs->id,
            'tax_master_id' => $gst18->id,
            'name' => 'UltraHD Business Monitor 27" IPS',
            'sku' => 'MON-27IPS',
            'barcode' => '890123450002',
            'item_type' => 'goods',
            'hsn_code' => '852852',
            'purchase_price' => 11000.00,
            'selling_price' => 16500.00,
            'mrp' => 19999.00,
            'opening_stock' => 25.00,
            'opening_stock_valuation' => 275000.00,
            'current_stock' => 25.00,
            'reorder_level' => 5.00,
            'description' => '4K Color-Accurate Ergonomic Office Display with USB-C Hub',
        ]);

        $pChair = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpFurniture->id,
            'unit_id' => $unitPcs->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Ergonomic High-Back Executive Chair',
            'sku' => 'CHR-EXEC-01',
            'barcode' => '890123450003',
            'item_type' => 'goods',
            'hsn_code' => '940130',
            'purchase_price' => 4500.00,
            'selling_price' => 7200.00,
            'mrp' => 8999.00,
            'opening_stock' => 30.00,
            'opening_stock_valuation' => 135000.00,
            'current_stock' => 30.00,
            'reorder_level' => 5.00,
            'description' => 'Breathable mesh executive chair with adjustable lumbar support',
        ]);

        $pService = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpServices->id,
            'unit_id' => $unitPcs->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Cloud ERP Annual SLA & Support',
            'sku' => 'SRV-ERP-SLA',
            'item_type' => 'service',
            'sac_code' => '998313',
            'purchase_price' => 0.00,
            'selling_price' => 15000.00,
            'mrp' => 15000.00,
            'description' => 'Annual 24x7 priority maintenance and data synchronization support',
        ]);

        // 8. Seed Sample Accounting Vouchers & Sales/Purchases for Day 1 Experience
        $accountingService = app(AccountingService::class);
        $invoicingService = app(InvoicingService::class);

        // Sample Payment Voucher (F5): Paid ₹ 15,000 from Bank to Sunrise Components
        $pmtVoucherNo = $accountingService->getNextVoucherNumber($company, $fy, VoucherType::PAYMENT);
        $accountingService->createVoucher([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => VoucherType::PAYMENT,
            'voucher_no' => $pmtVoucherNo,
            'voucher_date' => now()->subDays(5)->format('Y-m-d'),
            'reference_no' => 'CHQ-881290',
            'party_ledger_id' => $suppSunrise->id,
            'total_amount' => 15000.00,
            'payment_mode' => 'cheque',
            'cheque_no' => '881290',
            'narration' => 'Advance payment against pending PO',
            'status' => 'posted',
            'created_by' => $adminUser->id,
        ], [
            ['ledger_id' => $suppSunrise->id, 'entry_type' => 'debit', 'amount' => 15000.00, 'narration' => 'Payment to Sunrise Components'],
            ['ledger_id' => $hdfcLedger->id, 'entry_type' => 'credit', 'amount' => 15000.00, 'narration' => 'Paid via HDFC Bank Cheque'],
        ]);

        // Sample Receipt Voucher (F6): Received ₹ 20,000 from Apex Tech
        $rcptVoucherNo = $accountingService->getNextVoucherNumber($company, $fy, VoucherType::RECEIPT);
        $accountingService->createVoucher([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => VoucherType::RECEIPT,
            'voucher_no' => $rcptVoucherNo,
            'voucher_date' => now()->subDays(3)->format('Y-m-d'),
            'reference_no' => 'NEFT-AXIS-9921',
            'party_ledger_id' => $custApex->id,
            'total_amount' => 20000.00,
            'payment_mode' => 'bank',
            'narration' => 'NEFT received from Apex Tech',
            'status' => 'posted',
            'created_by' => $adminUser->id,
        ], [
            ['ledger_id' => $hdfcLedger->id, 'entry_type' => 'debit', 'amount' => 20000.00, 'narration' => 'Deposit in HDFC Bank'],
            ['ledger_id' => $custApex->id, 'entry_type' => 'credit', 'amount' => 20000.00, 'narration' => 'Account receipt from customer'],
        ]);

        // Sample Sales Invoice (F8)
        $invoicingService->createSalesInvoice([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'customer_ledger_id' => $custApex->id,
            'invoice_type' => 'tax_invoice',
            'invoice_date' => now()->subDays(2)->format('Y-m-d'),
            'paid_amount' => 10000.00,
            'payment_method' => 'bank_transfer',
            'notes' => 'Delivered to CP office',
        ], [
            [
                'product_id' => $pRouter->id,
                'warehouse_id' => $warehouseMain->id,
                'description' => $pRouter->name,
                'hsn_code' => $pRouter->hsn_code,
                'quantity' => 2,
                'unit_price' => 3500.00,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'gst_rate' => 18.00,
            ],
            [
                'product_id' => $pChair->id,
                'warehouse_id' => $warehouseMain->id,
                'description' => $pChair->name,
                'hsn_code' => $pChair->hsn_code,
                'quantity' => 1,
                'unit_price' => 7200.00,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'gst_rate' => 18.00,
            ]
        ]);

        // Sample Purchase Invoice (F9)
        $invoicingService->createPurchaseInvoice([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'supplier_ledger_id' => $suppSunrise->id,
            'bill_no' => 'SUN-2026-081',
            'bill_date' => now()->subDays(4)->format('Y-m-d'),
            'paid_amount' => 0.00,
            'notes' => 'Received with courier delivery challan',
        ], [
            [
                'product_id' => $pRouter->id,
                'warehouse_id' => $warehouseMain->id,
                'description' => $pRouter->name,
                'hsn_code' => $pRouter->hsn_code,
                'quantity' => 10,
                'unit_price' => 2200.00,
                'discount_amount' => 0,
                'gst_rate' => 18.00,
            ]
        ]);
    }
}
