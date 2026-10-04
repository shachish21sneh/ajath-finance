<?php

namespace Database\Seeders;

use App\Enums\EntryType;
use App\Enums\LedgerNature;
use App\Enums\PartyType;
use App\Enums\VoucherType;
use App\Models\Attendance;
use App\Models\BatteryModel;
use App\Models\BatterySerial;
use App\Models\BatteryTest;
use App\Models\BatteryWarranty;
use App\Models\Bom;
use App\Models\BomItem;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CostCentre;
use App\Models\CrmLead;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRecord;
use App\Models\FinancialYear;
use App\Models\FixedAsset;
use App\Models\JobWorker;
use App\Models\JobWorkOrder;
use App\Models\Ledger;
use App\Models\LedgerGroup;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductionMaterial;
use App\Models\ProductionOrder;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Role;
use App\Models\SalaryStructure;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\ServiceTicket;
use App\Models\ServiceVisit;
use App\Models\SolarLead;
use App\Models\SolarProduct;
use App\Models\SolarProject;
use App\Models\SolarQuotation;
use App\Models\SolarSiteSurvey;
use App\Models\StockCategory;
use App\Models\StockGroup;
use App\Models\StockMovement;
use App\Models\TaxMaster;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarrantyClaim;
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
            'description' => 'Full administrative control over all companies, financial vouchers, and operational modules',
        ]);

        $accountantRole = Role::create([
            'name' => 'Chief Accountant',
            'slug' => 'accountant',
            'description' => 'Manages vouchers, ledgers, billing, banking, GST, and financial reports',
        ]);

        $salesRole = Role::create([
            'name' => 'Sales & Channel Manager',
            'slug' => 'sales-manager',
            'description' => 'Manages sales invoices, quotations, dealers, and CRM pipeline',
        ]);

        $modules = ['companies', 'users', 'masters', 'vouchers', 'sales', 'purchases', 'banking', 'payroll', 'manufacturing', 'battery', 'solar', 'service', 'reports', 'settings'];
        foreach ($modules as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permission = Permission::create([
                    'name' => ucfirst($action) . ' ' . ucfirst($module),
                    'slug' => "{$module}.{$action}",
                    'module' => $module,
                    'description' => "Permission to {$action} {$module}",
                ]);

                $superAdminRole->permissions()->attach($permission->id);
                if (!in_array($module, ['settings', 'users'])) {
                    $accountantRole->permissions()->attach($permission->id);
                }
            }
        }

        // 2. Demo Company: FUZURRA INDUSTRIES PVT. LTD.
        $company = Company::create([
            'name' => 'Fuzurra Industries Pvt. Ltd.',
            'legal_name' => 'Fuzurra Industries Private Limited',
            'email' => 'contact@fuzurra.com',
            'phone' => '+91 11 4982 7700',
            'gstin' => '07AAACF1234A1Z5',
            'pan' => 'AAACF1234A',
            'cin' => 'U31900DL2024PTC392810',
            'address' => 'Plot 88, Okhla Industrial Area Phase-III',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'state_code' => '07',
            'pincode' => '110020',
            'country' => 'India',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'is_active' => true,
        ]);

        // Branches
        $branchHO = Branch::create([
            'company_id' => $company->id,
            'name' => 'Delhi Corporate HQ & Plant 1',
            'code' => 'DEL-HO',
            'phone' => '+91 11 4982 7700',
            'gstin' => '07AAACF1234A1Z5',
            'address' => 'Okhla Phase-III, New Delhi',
            'is_main' => true,
        ]);

        $branchMUM = Branch::create([
            'company_id' => $company->id,
            'name' => 'Western Regional Distribution Depot',
            'code' => 'MUM-DEP',
            'phone' => '+91 22 2839 4400',
            'gstin' => '27AAACF1234A1Z3',
            'address' => 'MIDC Industrial Area, Andheri East, Mumbai',
            'is_main' => false,
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

        // 3. System Users
        $adminUser = User::create([
            'name' => 'Anurag Saxena (Managing Director)',
            'email' => 'admin@fuzurra.com',
            'password' => Hash::make('password123'),
            'role_id' => $superAdminRole->id,
            'company_id' => $company->id,
            'phone' => '+91 98100 11223',
            'is_active' => true,
        ]);

        $accountantUser = User::create([
            'name' => 'Meenakshi Iyer (Chief Accountant)',
            'email' => 'accountant@fuzurra.com',
            'password' => Hash::make('password123'),
            'role_id' => $accountantRole->id,
            'company_id' => $company->id,
            'phone' => '+91 98200 44556',
            'is_active' => true,
        ]);

        $salesUser = User::create([
            'name' => 'Vikas Malhotra (Sales VP)',
            'email' => 'sales@fuzurra.com',
            'password' => Hash::make('password123'),
            'role_id' => $salesRole->id,
            'company_id' => $company->id,
            'phone' => '+91 98300 77889',
            'is_active' => true,
        ]);

        // 4. Tax Masters (Indian GST)
        $gst0 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 0% (Exempt)', 'rate' => 0.00, 'cgst_rate' => 0.00, 'sgst_rate' => 0.00, 'igst_rate' => 0.00]);
        $gst5 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 5%', 'rate' => 5.00, 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00]);
        $gst12 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 12%', 'rate' => 12.00, 'cgst_rate' => 6.00, 'sgst_rate' => 6.00, 'igst_rate' => 12.00]);
        $gst18 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 18%', 'rate' => 18.00, 'cgst_rate' => 9.00, 'sgst_rate' => 9.00, 'igst_rate' => 18.00]);
        $gst28 = TaxMaster::create(['company_id' => $company->id, 'name' => 'GST 28%', 'rate' => 28.00, 'cgst_rate' => 14.00, 'sgst_rate' => 14.00, 'igst_rate' => 28.00]);

        // 5. Chart of Accounts Groups
        $gAssets = LedgerGroup::create(['name' => 'Primary Assets', 'slug' => 'assets', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 1]);
        $gCurrentAssets = LedgerGroup::create(['parent_id' => $gAssets->id, 'name' => 'Current Assets', 'slug' => 'current-assets', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 2]);
        $gCash = LedgerGroup::create(['parent_id' => $gCurrentAssets->id, 'name' => 'Cash-in-hand', 'slug' => 'cash-in-hand', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 3]);
        $gBank = LedgerGroup::create(['parent_id' => $gCurrentAssets->id, 'name' => 'Bank Accounts', 'slug' => 'bank-accounts', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 4]);
        $gDebtors = LedgerGroup::create(['parent_id' => $gCurrentAssets->id, 'name' => 'Sundry Debtors', 'slug' => 'sundry-debtors', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 5]);
        $gStock = LedgerGroup::create(['parent_id' => $gCurrentAssets->id, 'name' => 'Stock-in-hand', 'slug' => 'stock-in-hand', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 6]);
        $gFixedAssets = LedgerGroup::create(['parent_id' => $gAssets->id, 'name' => 'Fixed Assets', 'slug' => 'fixed-assets', 'nature' => LedgerNature::ASSET, 'is_system' => true, 'order' => 7]);

        $gLiab = LedgerGroup::create(['name' => 'Primary Liabilities', 'slug' => 'liabilities', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 10]);
        $gCurrentLiab = LedgerGroup::create(['parent_id' => $gLiab->id, 'name' => 'Current Liabilities', 'slug' => 'current-liabilities', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 11]);
        $gCreditors = LedgerGroup::create(['parent_id' => $gCurrentLiab->id, 'name' => 'Sundry Creditors', 'slug' => 'sundry-creditors', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 12]);
        $gDutiesTaxes = LedgerGroup::create(['parent_id' => $gCurrentLiab->id, 'name' => 'Duties & Taxes', 'slug' => 'duties-and-taxes', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 13]);
        $gCapital = LedgerGroup::create(['parent_id' => $gLiab->id, 'name' => 'Capital Account', 'slug' => 'capital-account', 'nature' => LedgerNature::LIABILITY, 'is_system' => true, 'order' => 14]);

        $gIncome = LedgerGroup::create(['name' => 'Primary Income', 'slug' => 'income', 'nature' => LedgerNature::INCOME, 'is_system' => true, 'order' => 20]);
        $gDirectIncome = LedgerGroup::create(['parent_id' => $gIncome->id, 'name' => 'Direct Incomes', 'slug' => 'direct-incomes', 'nature' => LedgerNature::INCOME, 'affects_gross_profit' => true, 'is_system' => true, 'order' => 21]);
        $gIndirectIncome = LedgerGroup::create(['parent_id' => $gIncome->id, 'name' => 'Indirect Incomes', 'slug' => 'indirect-incomes', 'nature' => LedgerNature::INCOME, 'is_system' => true, 'order' => 22]);

        $gExpense = LedgerGroup::create(['name' => 'Primary Expense', 'slug' => 'expense', 'nature' => LedgerNature::EXPENSE, 'is_system' => true, 'order' => 30]);
        $gDirectExpense = LedgerGroup::create(['parent_id' => $gExpense->id, 'name' => 'Direct Expenses', 'slug' => 'direct-expenses', 'nature' => LedgerNature::EXPENSE, 'affects_gross_profit' => true, 'is_system' => true, 'order' => 31]);
        $gIndirectExpense = LedgerGroup::create(['parent_id' => $gExpense->id, 'name' => 'Indirect Expenses', 'slug' => 'indirect-expenses', 'nature' => LedgerNature::EXPENSE, 'is_system' => true, 'order' => 32]);

        // 6. Core Financial Ledgers
        $cashLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gCash->id,
            'name' => 'Cash-in-Hand',
            'code' => 'CASH-01',
            'opening_balance' => 150000.00,
            'opening_balance_type' => 'Dr',
            'current_balance' => 150000.00,
            'party_type' => PartyType::CASH,
            'is_system' => true,
        ]);

        $hdfcLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gBank->id,
            'name' => 'HDFC Current A/c (5020008819)',
            'code' => 'BANK-HDFC',
            'opening_balance' => 1250000.00,
            'opening_balance_type' => 'Dr',
            'current_balance' => 1250000.00,
            'party_type' => PartyType::BANK,
            'bank_name' => 'HDFC Bank Ltd',
            'bank_account_no' => '50200088192810',
            'bank_ifsc' => 'HDFC0000043',
            'bank_branch' => 'Okhla Industrial Area',
            'is_system' => true,
        ]);

        $iciciLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gBank->id,
            'name' => 'ICICI Bank Operating A/c',
            'code' => 'BANK-ICICI',
            'opening_balance' => 850000.00,
            'opening_balance_type' => 'Dr',
            'current_balance' => 850000.00,
            'party_type' => PartyType::BANK,
            'bank_name' => 'ICICI Bank Ltd',
            'bank_account_no' => '002105018291',
            'bank_ifsc' => 'ICIC0000021',
            'bank_branch' => 'Connaught Place',
            'is_system' => true,
        ]);

        $salesLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gDirectIncome->id,
            'name' => 'Domestic Sales Account',
            'code' => 'INC-SALES',
            'party_type' => PartyType::NONE,
            'is_system' => true,
        ]);

        $solarInstallIncomeLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gDirectIncome->id,
            'name' => 'Solar EPC Installation Revenue',
            'code' => 'INC-SOLAR-EPC',
            'party_type' => PartyType::NONE,
            'is_system' => true,
        ]);

        $purchaseLedger = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gDirectExpense->id,
            'name' => 'Material Purchase Account',
            'code' => 'EXP-PURCH',
            'party_type' => PartyType::NONE,
            'is_system' => true,
        ]);

        // Tax Ledgers
        $cgstOutput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Output CGST', 'code' => 'TAX-OUT-CGST', 'party_type' => PartyType::NONE, 'is_system' => true]);
        $sgstOutput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Output SGST', 'code' => 'TAX-OUT-SGST', 'party_type' => PartyType::NONE, 'is_system' => true]);
        $igstOutput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Output IGST', 'code' => 'TAX-OUT-IGST', 'party_type' => PartyType::NONE, 'is_system' => true]);

        $cgstInput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Input CGST', 'code' => 'TAX-IN-CGST', 'party_type' => PartyType::NONE, 'is_system' => true]);
        $sgstInput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Input SGST', 'code' => 'TAX-IN-SGST', 'party_type' => PartyType::NONE, 'is_system' => true]);
        $igstInput = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDutiesTaxes->id, 'name' => 'Input IGST', 'code' => 'TAX-IN-IGST', 'party_type' => PartyType::NONE, 'is_system' => true]);

        $roundOffLedger = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gIndirectExpense->id, 'name' => 'Round Off Account', 'code' => 'EXP-RND', 'party_type' => PartyType::NONE, 'is_system' => true]);

        // Operating Expense Ledgers
        $salaryExpLedger = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gIndirectExpense->id, 'name' => 'Staff Salaries & Wages', 'code' => 'EXP-SALARY', 'party_type' => PartyType::NONE]);
        $rentExpLedger = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gIndirectExpense->id, 'name' => 'Factory & Office Rent', 'code' => 'EXP-RENT', 'party_type' => PartyType::NONE]);
        $electricExpLedger = Ledger::create(['company_id' => $company->id, 'ledger_group_id' => $gDirectExpense->id, 'name' => 'Electricity & Power Charges', 'code' => 'EXP-ELEC', 'party_type' => PartyType::NONE]);

        // Demo Customers (Sundry Debtors)
        $custApex = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gDebtors->id,
            'name' => 'Apex Tech Solutions Pvt Ltd',
            'code' => 'CUST-001',
            'party_type' => PartyType::CUSTOMER,
            'gstin' => '07AABCA1234F1Z8',
            'pan' => 'AABCA1234F',
            'email' => 'procurement@apextech.com',
            'phone' => '+91 98111 22233',
            'address' => 'Plot 12, Nehru Place Tech Park',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'state_code' => '07',
            'credit_limit' => 1000000.00,
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
            'address' => 'Sector 19, Vashi',
            'city' => 'Navi Mumbai',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'credit_limit' => 1500000.00,
            'credit_days' => 45,
        ]);

        // Demo Suppliers (Sundry Creditors)
        $suppSunrise = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gCreditors->id,
            'name' => 'Sunrise Lithium Cells & Components Ltd',
            'code' => 'SUPP-001',
            'party_type' => PartyType::SUPPLIER,
            'gstin' => '07AABCS9876H1Z4',
            'pan' => 'AABCS9876H',
            'email' => 'sales@sunrisecells.com',
            'phone' => '+91 98333 44455',
            'address' => 'Phase-II, Mayapuri Industrial Area',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'state_code' => '07',
            'credit_limit' => 2000000.00,
            'credit_days' => 45,
        ]);

        $suppGlobal = Ledger::create([
            'company_id' => $company->id,
            'ledger_group_id' => $gCreditors->id,
            'name' => 'Global Silicon & Solar Wafer Corp',
            'code' => 'SUPP-002',
            'party_type' => PartyType::SUPPLIER,
            'gstin' => '24AABCG4321J1Z9', // Inter-state Gujarat
            'pan' => 'AABCG4321J',
            'email' => 'dispatch@globalsolar.com',
            'phone' => '+91 98444 55566',
            'address' => 'GIDC Electronic Zone, Gandhinagar',
            'city' => 'Gandhinagar',
            'state' => 'Gujarat',
            'state_code' => '24',
            'credit_limit' => 3000000.00,
            'credit_days' => 60,
        ]);

        // 7. Inventory Units & Godowns
        $unitNos = Unit::create(['company_id' => $company->id, 'name' => 'Numbers', 'symbol' => 'NOS', 'decimal_places' => 0]);
        $unitPcs = Unit::create(['company_id' => $company->id, 'name' => 'Pieces', 'symbol' => 'PCS', 'decimal_places' => 0]);
        $unitMtr = Unit::create(['company_id' => $company->id, 'name' => 'Meters', 'symbol' => 'MTR', 'decimal_places' => 2]);
        $unitSet = Unit::create(['company_id' => $company->id, 'name' => 'Set', 'symbol' => 'SET', 'decimal_places' => 0]);

        $stockGrpBatteries = StockGroup::create(['company_id' => $company->id, 'name' => 'Energy Storage & Batteries']);
        $stockGrpSolar = StockGroup::create(['company_id' => $company->id, 'name' => 'Solar Panels & Inverters']);
        $stockGrpElectrical = StockGroup::create(['company_id' => $company->id, 'name' => 'Electrical Cables & Switchgear']);
        $stockGrpRawMaterials = StockGroup::create(['company_id' => $company->id, 'name' => 'Manufacturing Raw Materials']);

        $whMain = Warehouse::create([
            'company_id' => $company->id,
            'name' => 'Okhla Central Godown (Depot 1)',
            'code' => 'WH-DEL-01',
            'address' => 'Warehouse Complex 7, Okhla Phase III, New Delhi',
            'is_default' => true,
        ]);

        $whMumbai = Warehouse::create([
            'company_id' => $company->id,
            'name' => 'Bhiwandi Western Hub',
            'code' => 'WH-BHI-02',
            'address' => 'Logistics Park, Bhiwandi, Maharashtra',
            'is_default' => false,
        ]);

        // 8. MASTER DEMO PRODUCTS (As explicitly specified in Prompt Section 65)
        // Batteries
        $pBat12V100 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpBatteries->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst28->id, // 28% Lead Acid GST
            'name' => 'Fuzurra 12V 100Ah Battery',
            'sku' => 'FZ-BAT-12V100',
            'barcode' => '8908001001',
            'item_type' => 'goods',
            'hsn_code' => '850720',
            'purchase_price' => 7200.00,
            'selling_price' => 10500.00,
            'mrp' => 12999.00,
            'opening_stock' => 40.00,
            'current_stock' => 40.00,
            'reorder_level' => 10.00,
            'description' => 'Tubular Heavy-Duty Lead Acid Inverter Battery with 36-Month Warranty',
        ]);

        $pBat12_8V100 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpBatteries->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Fuzurra 12.8V 100Ah LiFePO4',
            'sku' => 'FZ-LIFE-12V100',
            'barcode' => '8908001002',
            'item_type' => 'goods',
            'hsn_code' => '850760',
            'purchase_price' => 16500.00,
            'selling_price' => 24000.00,
            'mrp' => 28500.00,
            'opening_stock' => 30.00,
            'current_stock' => 30.00,
            'reorder_level' => 5.00,
            'description' => 'Compact Smart LiFePO4 Battery with Bluetooth BMS and 60-Month Warranty',
        ]);

        $pBat25_6V100 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpBatteries->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Fuzurra 25.6V 100Ah LiFePO4',
            'sku' => 'FZ-LIFE-24V100',
            'barcode' => '8908001003',
            'item_type' => 'goods',
            'hsn_code' => '850760',
            'purchase_price' => 31000.00,
            'selling_price' => 46000.00,
            'mrp' => 54000.00,
            'opening_stock' => 20.00,
            'current_stock' => 20.00,
            'reorder_level' => 5.00,
            'description' => '24V 2.56kWh Lithium Wall-Mount Home Energy Storage Battery',
        ]);

        $pBat51_2V100 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpBatteries->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Fuzurra 51.2V 100Ah LiFePO4',
            'sku' => 'FZ-LIFE-48V100',
            'barcode' => '8908001004',
            'item_type' => 'goods',
            'hsn_code' => '850760',
            'purchase_price' => 62000.00,
            'selling_price' => 89000.00,
            'mrp' => 105000.00,
            'opening_stock' => 15.00,
            'current_stock' => 15.00,
            'reorder_level' => 4.00,
            'description' => '5.12kWh 48V Server-Rack & Solar Hybrid LiFePO4 Battery with CAN/RS485 Comm',
        ]);

        // Inverters
        $pInv1100 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpSolar->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Durasol DSH-1100',
            'sku' => 'DUR-DSH-1100',
            'barcode' => '8908002001',
            'item_type' => 'goods',
            'hsn_code' => '850440',
            'purchase_price' => 6800.00,
            'selling_price' => 9800.00,
            'mrp' => 12500.00,
            'opening_stock' => 25.00,
            'current_stock' => 25.00,
            'reorder_level' => 5.00,
            'description' => '1.1kVA Solar Hybrid Pure Sine Wave Inverter with 40A MPPT Charge Controller',
        ]);

        $pInv3370 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpSolar->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Durasol DSH-3370',
            'sku' => 'DUR-DSH-3370',
            'barcode' => '8908002002',
            'item_type' => 'goods',
            'hsn_code' => '850440',
            'purchase_price' => 38000.00,
            'selling_price' => 56000.00,
            'mrp' => 68000.00,
            'opening_stock' => 12.00,
            'current_stock' => 12.00,
            'reorder_level' => 3.00,
            'description' => '5kW 48V Dual MPPT Hybrid Solar Inverter with Wi-Fi & Grid Export Limitation',
        ]);

        // Solar Panels
        $pPanel550 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpSolar->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst12->id, // 12% Solar Goods GST
            'name' => 'Solar Panel 550W',
            'sku' => 'SOL-PNL-550W',
            'barcode' => '8908003001',
            'item_type' => 'goods',
            'hsn_code' => '854140',
            'purchase_price' => 8800.00,
            'selling_price' => 12500.00,
            'mrp' => 15500.00,
            'opening_stock' => 80.00,
            'current_stock' => 80.00,
            'reorder_level' => 20.00,
            'description' => 'Mono PERC 144-Cell Half-Cut Bifacial High-Efficiency PV Module',
        ]);

        $pPanel580 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpSolar->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst12->id,
            'name' => 'Solar Panel 580W',
            'sku' => 'SOL-PNL-580W',
            'barcode' => '8908003002',
            'item_type' => 'goods',
            'hsn_code' => '854140',
            'purchase_price' => 9600.00,
            'selling_price' => 13800.00,
            'mrp' => 17000.00,
            'opening_stock' => 60.00,
            'current_stock' => 60.00,
            'reorder_level' => 15.00,
            'description' => 'N-Type TopCon 580W Solar Panel with 22.5% Efficiency & 30-Year Warranty',
        ]);

        // Balance of Systems & Electricals
        $pAcdb = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpElectrical->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'ACDB',
            'sku' => 'BOS-ACDB-1P',
            'barcode' => '8908004001',
            'item_type' => 'goods',
            'hsn_code' => '853710',
            'purchase_price' => 1800.00,
            'selling_price' => 2800.00,
            'mrp' => 3500.00,
            'opening_stock' => 50.00,
            'current_stock' => 50.00,
            'reorder_level' => 10.00,
            'description' => 'Single/Three Phase AC Distribution Box with Type 2 SPD and MCB Protection',
        ]);

        $pDcdb = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpElectrical->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'DCDB',
            'sku' => 'BOS-DCDB-2IN',
            'barcode' => '8908004002',
            'item_type' => 'goods',
            'hsn_code' => '853710',
            'purchase_price' => 2200.00,
            'selling_price' => 3400.00,
            'mrp' => 4200.00,
            'opening_stock' => 45.00,
            'current_stock' => 45.00,
            'reorder_level' => 10.00,
            'description' => '2-In 2-Out 1000V DC Distribution Box with Class C Surge Protection',
        ]);

        $pMc4 = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpElectrical->id,
            'unit_id' => $unitSet->id,
            'tax_master_id' => $gst18->id,
            'name' => 'MC4 Connector',
            'sku' => 'BOS-MC4-PAIR',
            'barcode' => '8908004003',
            'item_type' => 'goods',
            'hsn_code' => '853669',
            'purchase_price' => 45.00,
            'selling_price' => 85.00,
            'mrp' => 120.00,
            'opening_stock' => 500.00,
            'current_stock' => 500.00,
            'reorder_level' => 100.00,
            'description' => 'IP68 Waterproof 30A 1000V Male/Female MC4 Solar Pair',
        ]);

        $pSolarCable = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpElectrical->id,
            'unit_id' => $unitMtr->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Solar Cable',
            'sku' => 'CBL-SOL-4MM',
            'barcode' => '8908004004',
            'item_type' => 'goods',
            'hsn_code' => '854449',
            'purchase_price' => 38.00,
            'selling_price' => 58.00,
            'mrp' => 75.00,
            'opening_stock' => 1200.00,
            'current_stock' => 1200.00,
            'reorder_level' => 200.00,
            'description' => '4 sq mm Tinned Copper XLPO Insulated UV Resistant Solar DC Wire',
        ]);

        $pElectricWire = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpElectrical->id,
            'unit_id' => $unitMtr->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Electrical Wire',
            'sku' => 'CBL-FR-2.5MM',
            'barcode' => '8908004005',
            'item_type' => 'goods',
            'hsn_code' => '854449',
            'purchase_price' => 22.00,
            'selling_price' => 35.00,
            'mrp' => 45.00,
            'opening_stock' => 2000.00,
            'current_stock' => 2000.00,
            'reorder_level' => 300.00,
            'description' => '2.5 sq mm Flame Retardant (FR) Multi-Strand House Wiring Cable',
        ]);

        // Manufacturing Raw Materials for LiFePO4 Assembly
        $pCell = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpRawMaterials->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'LiFePO4 Prismatic Cell 3.2V 100Ah',
            'sku' => 'RAW-CELL-3.2V100',
            'barcode' => '8908005001',
            'item_type' => 'goods',
            'hsn_code' => '850790',
            'purchase_price' => 2800.00,
            'selling_price' => 3500.00,
            'opening_stock' => 320.00,
            'current_stock' => 320.00,
            'reorder_level' => 64.00,
            'description' => 'Grade-A 3.2V 100Ah Aluminum Case Prismatic Lithium Cell',
        ]);

        $pBms = Product::create([
            'company_id' => $company->id,
            'stock_group_id' => $stockGrpRawMaterials->id,
            'unit_id' => $unitNos->id,
            'tax_master_id' => $gst18->id,
            'name' => 'Smart BMS 16S 100A CAN/RS485',
            'sku' => 'RAW-BMS-16S100',
            'barcode' => '8908005002',
            'item_type' => 'goods',
            'hsn_code' => '903289',
            'purchase_price' => 4500.00,
            'selling_price' => 6000.00,
            'opening_stock' => 25.00,
            'current_stock' => 25.00,
            'reorder_level' => 5.00,
            'description' => '16-Series Battery Management System with Active Balancing & Inverter Comm',
        ]);

        // 9. MANUFACTURING BILL OF MATERIALS (BOM)
        $bomBattery = Bom::create([
            'company_id' => $company->id,
            'product_id' => $pBat51_2V100->id,
            'bom_code' => 'BOM-FZ-512100',
            'bom_name' => 'Fuzurra 51.2V 100Ah LiFePO4 Smart Battery Assembly',
            'output_qty' => 1.00,
            'labor_cost' => 1800.00,
            'overhead_cost' => 1200.00,
            'total_material_cost' => (16 * 2800.00) + 4500.00, // 16 cells + 1 BMS = 49,300
            'total_unit_cost' => 49300.00 + 1800.00 + 1200.00, // 52,300
            'is_active' => true,
        ]);

        BomItem::create([
            'bom_id' => $bomBattery->id,
            'raw_material_id' => $pCell->id,
            'quantity' => 16.00,
            'unit_cost' => 2800.00,
            'total_cost' => 44800.00,
        ]);

        BomItem::create([
            'bom_id' => $bomBattery->id,
            'raw_material_id' => $pBms->id,
            'quantity' => 1.00,
            'unit_cost' => 4500.00,
            'total_cost' => 4500.00,
        ]);

        // Production Order
        $prodOrder = ProductionOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branchHO->id,
            'bom_id' => $bomBattery->id,
            'warehouse_id' => $whMain->id,
            'order_no' => 'PROD-2026-001',
            'order_date' => '2026-02-10',
            'planned_qty' => 5.00,
            'completed_qty' => 5.00,
            'scrap_qty' => 0.00,
            'total_cost' => 52300.00 * 5,
            'cost_per_unit' => 52300.00,
            'status' => 'completed',
            'start_date' => '2026-02-10',
            'completion_date' => '2026-02-12',
            'remarks' => 'Completed batch testing and cycle QC passed.',
        ]);

        // 10. BATTERY ERP DOMAIN DATA
        $bModel51_2 = BatteryModel::create([
            'company_id' => $company->id,
            'product_id' => $pBat51_2V100->id,
            'chemistry' => 'LiFePO4',
            'nominal_voltage' => 51.20,
            'capacity_ah' => 100.00,
            'energy_wh' => 5120.00,
            'bms_model' => 'Smart BMS 16S 100A CAN/RS485',
            'max_charging_current' => 50.00,
            'max_discharge_current' => 100.00,
            'warranty_months' => 60,
            'free_replacement_months' => 36,
            'pro_rata_months' => 24,
            'cell_type' => 'Prismatic 3.2V 100Ah Grade A',
            'cell_count' => 16,
        ]);

        $bModel12_8 = BatteryModel::create([
            'company_id' => $company->id,
            'product_id' => $pBat12_8V100->id,
            'chemistry' => 'LiFePO4',
            'nominal_voltage' => 12.80,
            'capacity_ah' => 100.00,
            'energy_wh' => 1280.00,
            'bms_model' => 'Bluetooth 4S 50A BMS',
            'max_charging_current' => 30.00,
            'max_discharge_current' => 60.00,
            'warranty_months' => 36,
            'free_replacement_months' => 24,
            'pro_rata_months' => 12,
            'cell_type' => 'Prismatic 3.2V 100Ah',
            'cell_count' => 4,
        ]);

        // Serialized Units
        $serial1 = BatterySerial::create([
            'company_id' => $company->id,
            'battery_model_id' => $bModel51_2->id,
            'warehouse_id' => $whMain->id,
            'customer_ledger_id' => $custApex->id,
            'serial_number' => 'FZ-512100-2026-0001',
            'cell_batch_number' => 'CATL-2026-B1',
            'mfg_date' => '2026-02-12',
            'dispatch_date' => '2026-02-15',
            'qc_status' => 'PASSED',
            'current_status' => 'INSTALLED',
        ]);

        $serial2 = BatterySerial::create([
            'company_id' => $company->id,
            'battery_model_id' => $bModel51_2->id,
            'warehouse_id' => $whMain->id,
            'serial_number' => 'FZ-512100-2026-0002',
            'cell_batch_number' => 'CATL-2026-B1',
            'mfg_date' => '2026-02-12',
            'qc_status' => 'PASSED',
            'current_status' => 'IN_STOCK',
        ]);

        $serial3 = BatterySerial::create([
            'company_id' => $company->id,
            'battery_model_id' => $bModel12_8->id,
            'warehouse_id' => $whMain->id,
            'serial_number' => 'FZ-128100-2026-0003',
            'cell_batch_number' => 'EVE-2026-C4',
            'mfg_date' => '2026-02-14',
            'qc_status' => 'PASSED',
            'current_status' => 'IN_STOCK',
        ]);

        // Laboratory QC Certificate
        BatteryTest::create([
            'battery_serial_id' => $serial1->id,
            'test_date' => '2026-02-13',
            'open_circuit_voltage' => 53.20,
            'pack_voltage' => 53.15,
            'internal_resistance_mohm' => 12.40, // 12.4 mΩ
            'actual_capacity_ah' => 102.50, // Exceeds nominal
            'charge_test_passed' => true,
            'discharge_test_passed' => true,
            'bms_comm_passed' => true,
            'qc_result' => 'PASS',
            'technician_name' => 'Sunil Verma (QC Lead)',
            'certificate_no' => 'QC-2026-0881',
            'remarks' => 'Cell balancing within 5mV deviation. Passed laboratory QC with distinction.',
        ]);

        // Registered Warranty
        $warranty1 = BatteryWarranty::create([
            'company_id' => $company->id,
            'battery_serial_id' => $serial1->id,
            'customer_ledger_id' => $custApex->id,
            'invoice_no' => 'INV-2026-0001',
            'purchase_date' => '2026-02-15',
            'warranty_start_date' => '2026-02-15',
            'warranty_end_date' => '2031-02-14', // 60 months
            'warranty_type' => 'Standard Comprehensive 5-Year',
            'is_active' => true,
        ]);

        // 11. SOLAR ERP DOMAIN DATA
        SolarProduct::create([
            'company_id' => $company->id,
            'product_id' => $pPanel550->id,
            'product_type' => 'SOLAR_PANEL',
            'wattage' => 550.00,
            'voltage' => 49.80,
            'efficiency_percent' => 21.50,
            'warranty_years' => 25,
        ]);

        SolarProduct::create([
            'company_id' => $company->id,
            'product_id' => $pInv3370->id,
            'product_type' => 'HYBRID_INVERTER',
            'wattage' => 5000.00,
            'voltage' => 48.00,
            'efficiency_percent' => 97.60,
            'warranty_years' => 5,
        ]);

        // Solar Lead & Survey
        $sLead = SolarLead::create([
            'company_id' => $company->id,
            'customer_name' => 'Dr. Arvind Mahajan (Rooftop Clinic)',
            'phone' => '+91 98111 99887',
            'email' => 'arvind@mahajanclinic.org',
            'address' => 'Plot 4, Greater Kailash-II',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110048',
            'estimated_kw' => 10.00,
            'source' => 'Website Inbound',
            'status' => 'SURVEY_SCHEDULED',
        ]);

        $sSurvey = SolarSiteSurvey::create([
            'company_id' => $company->id,
            'solar_lead_id' => $sLead->id,
            'customer_ledger_id' => $custApex->id,
            'survey_date' => '2026-02-05',
            'surveyor_name' => 'Priya Nair (Solar Engineer)',
            'roof_type' => 'RCC Flat',
            'roof_area_sqft' => 1500.00,
            'shadow_free_area_sqft' => 1100.00,
            'tilt_angle' => 28.00,
            'orientation' => 'South-Facing',
            'sanctioned_load_kw' => 15.00,
            'monthly_consumption_kwh' => 1250.00,
            'phase' => 'Three Phase',
            'consumer_number' => 'BSES-10029381',
            'discom_name' => 'BSES Rajdhani Power Limited',
            'recommended_capacity_kw' => 10.00,
            'notes' => 'RCC roof in prime condition, unshaded 9am to 5pm. Excellent solar irradiance profile.',
        ]);

        $sQuote = SolarQuotation::create([
            'company_id' => $company->id,
            'solar_lead_id' => $sLead->id,
            'customer_ledger_id' => $custApex->id,
            'quotation_no' => 'SQ-2026-0042',
            'quotation_date' => '2026-02-06',
            'system_capacity_kw' => 10.00,
            'panel_model' => 'Solar Panel 550W Mono PERC',
            'panel_qty' => 18,
            'inverter_model' => 'Durasol DSH-3370 5kW x2',
            'inverter_qty' => 2,
            'battery_model' => 'Fuzurra 51.2V 100Ah LiFePO4',
            'battery_qty' => 2,
            'structure_type' => 'Hot-Dip Galvanized Iron Elevated Structure',
            'system_cost' => 520000.00,
            'gst_amount' => 71760.00,
            'subsidy_amount' => 78000.00, // Max MNRE subsidy
            'net_payable' => 513760.00,
            'estimated_monthly_gen_units' => 1200.00,
            'payback_years' => 3.6,
            'status' => 'ACCEPTED',
        ]);

        $sProject = SolarProject::create([
            'company_id' => $company->id,
            'customer_ledger_id' => $custApex->id,
            'quotation_id' => $sQuote->id,
            'project_code' => 'SP-DEL-2026-01',
            'project_name' => 'Apex Tech Solutions 10kW Rooftop Solar Installation',
            'site_address' => 'Plot 12, Nehru Place Tech Park, New Delhi',
            'capacity_kw' => 10.00,
            'total_project_cost' => 513760.00,
            'subsidy_status' => 'APPROVED',
            'net_metering_status' => 'COMMISSIONED',
            'installation_status' => 'COMMISSIONED',
            'start_date' => '2026-02-08',
            'commissioning_date' => '2026-02-20',
            'installer_lead' => 'Priya Nair (Senior Project Lead)',
        ]);

        // 12. DEALERS & DISTRIBUTORS
        $dealerSurya = Dealer::create([
            'company_id' => $company->id,
            'dealer_code' => 'DLR-DEL-01',
            'name' => 'Rakesh Agarwal',
            'company_name' => 'Surya Shakti Solar & Battery Distributors',
            'territory' => 'Delhi NCR / Western UP',
            'gstin' => '07AABCS1122K1Z9',
            'phone' => '+91 98111 55667',
            'email' => 'suryashakti.delhi@gmail.com',
            'address' => 'Bhagirath Palace, Chandni Chowk, Delhi',
            'credit_limit' => 1000000.00,
            'credit_days' => 45,
            'price_tier' => 'DISTRIBUTOR',
            'outstanding_balance' => 245000.00,
            'status' => 'active',
        ]);

        $dealerPowerGrid = Dealer::create([
            'company_id' => $company->id,
            'dealer_code' => 'DLR-MUM-02',
            'name' => 'Santosh Kulkarni',
            'company_name' => 'PowerGrid Electricals & Inverters',
            'territory' => 'Maharashtra & Goa',
            'gstin' => '27AABCP3344L1Z1',
            'phone' => '+91 98222 66778',
            'email' => 'powergrid.mumbai@gmail.com',
            'address' => 'Lamington Road, Grant Road, Mumbai',
            'credit_limit' => 500000.00,
            'credit_days' => 30,
            'price_tier' => 'DEALER',
            'outstanding_balance' => 110000.00,
            'status' => 'active',
        ]);

        // 13. AFTER-SALES SERVICE TICKETS
        $ticket1 = ServiceTicket::create([
            'company_id' => $company->id,
            'customer_ledger_id' => $custApex->id,
            'ticket_no' => 'SRV-2026-001',
            'customer_name' => 'Apex Tech Solutions Pvt Ltd',
            'phone' => '+91 98111 22233',
            'product_name' => 'Durasol DSH-3370 Hybrid Inverter',
            'serial_number' => 'DUR-3370-2026-019',
            'complaint_details' => 'Customer reported occasional grid synchronization failure during high grid voltage fluctuations.',
            'priority' => 'HIGH',
            'status' => 'RESOLVED',
            'assigned_technician' => 'Sunil Verma (Senior Tech)',
            'created_date' => '2026-02-22',
            'resolved_date' => '2026-02-23',
        ]);

        ServiceVisit::create([
            'service_ticket_id' => $ticket1->id,
            'visit_date' => '2026-02-23',
            'technician_name' => 'Sunil Verma',
            'findings' => 'Grid AC voltage reached 268V exceeding default threshold. Inverter was safely isolating to prevent overvoltage.',
            'action_taken' => 'Upgraded firmware to v2.4, adjusted overvoltage window to DISCOM recommended band 270V. Tested with full 4.8kW load.',
            'parts_used' => 'None (Firmware configuration adjustment)',
            'service_charges' => 0.00,
            'customer_signature_name' => 'Gaurav Jain (IT Head)',
            'status' => 'COMPLETED',
        ]);

        // 14. CRM LEADS
        CrmLead::create([
            'company_id' => $company->id,
            'contact_name' => 'Col. Rajesh Bakshi',
            'company_name' => 'Bakshi Agro Cold Storage',
            'phone' => '+91 98101 44332',
            'email' => 'col.bakshi@bakshiagro.com',
            'source' => 'Trade Expo 2026',
            'product_interest' => '100kW Solar Rooftop + 50kWh LiFePO4 Energy Storage',
            'estimated_value' => 4500000.00,
            'assigned_to' => 'Vikas Malhotra',
            'stage' => 'NEGOTIATION',
            'next_follow_up_date' => '2026-03-05',
            'notes' => 'Board approved proposal. Finalizing contract terms and payment milestone schedule.',
        ]);

        CrmLead::create([
            'company_id' => $company->id,
            'contact_name' => 'Manish Singhal',
            'company_name' => 'Singhal Hospitals',
            'phone' => '+91 98200 88776',
            'email' => 'admin@singhalhospitals.com',
            'source' => 'Referral',
            'product_interest' => 'Fuzurra 51.2V 100Ah LiFePO4 Rack Batteries x 8',
            'estimated_value' => 720000.00,
            'assigned_to' => 'Vikas Malhotra',
            'stage' => 'PROPOSAL',
            'next_follow_up_date' => '2026-03-08',
            'notes' => 'Quotation sent for ICU backup zero-delay power.',
        ]);

        // 15. HR & PAYROLL
        $deptMfg = Department::create(['company_id' => $company->id, 'name' => 'Battery & Solar Manufacturing', 'code' => 'MFG']);
        $deptSolar = Department::create(['company_id' => $company->id, 'name' => 'Solar EPC & Engineering', 'code' => 'EPC']);
        $deptService = Department::create(['company_id' => $company->id, 'name' => 'Customer Service & QC', 'code' => 'SRV']);
        $deptAccounts = Department::create(['company_id' => $company->id, 'name' => 'Finance & Accounts', 'code' => 'FIN']);

        $desigMgr = Designation::create(['company_id' => $company->id, 'title' => 'Production Plant Manager', 'code' => 'PLT-MGR']);
        $desigEng = Designation::create(['company_id' => $company->id, 'title' => 'Senior Solar Project Engineer', 'code' => 'SOL-ENG']);
        $desigTech = Designation::create(['company_id' => $company->id, 'title' => 'Battery Laboratory QC Specialist', 'code' => 'QC-SPEC']);

        $emp1 = Employee::create([
            'company_id' => $company->id,
            'department_id' => $deptMfg->id,
            'designation_id' => $desigMgr->id,
            'branch_id' => $branchHO->id,
            'emp_code' => 'FZ-EMP-001',
            'first_name' => 'Rajesh',
            'last_name' => 'Sharma',
            'email' => 'rajesh.sharma@fuzurra.com',
            'phone' => '+91 98111 88990',
            'pan' => 'ABCPS1234K',
            'joining_date' => '2024-04-01',
            'monthly_salary' => 75000.00,
            'status' => 'active',
        ]);

        SalaryStructure::create([
            'company_id' => $company->id,
            'employee_id' => $emp1->id,
            'basic_salary' => 37500.00,
            'hra' => 18750.00,
            'conveyance' => 7500.00,
            'special_allowance' => 11250.00,
            'pf_deduction' => 1800.00,
            'esi_deduction' => 0.00,
            'pt_deduction' => 200.00,
            'tds_deduction' => 3500.00,
            'gross_salary' => 75000.00,
            'net_salary' => 69500.00,
        ]);

        $emp2 = Employee::create([
            'company_id' => $company->id,
            'department_id' => $deptSolar->id,
            'designation_id' => $desigEng->id,
            'branch_id' => $branchHO->id,
            'emp_code' => 'FZ-EMP-002',
            'first_name' => 'Priya',
            'last_name' => 'Nair',
            'email' => 'priya.nair@fuzurra.com',
            'phone' => '+91 98222 77889',
            'pan' => 'ABCPN5678L',
            'joining_date' => '2024-06-15',
            'monthly_salary' => 55000.00,
            'status' => 'active',
        ]);

        SalaryStructure::create([
            'company_id' => $company->id,
            'employee_id' => $emp2->id,
            'basic_salary' => 27500.00,
            'hra' => 13750.00,
            'conveyance' => 5500.00,
            'special_allowance' => 8250.00,
            'pf_deduction' => 1800.00,
            'esi_deduction' => 0.00,
            'pt_deduction' => 200.00,
            'tds_deduction' => 1500.00,
            'gross_salary' => 55000.00,
            'net_salary' => 51500.00,
        ]);

        $emp3 = Employee::create([
            'company_id' => $company->id,
            'department_id' => $deptService->id,
            'designation_id' => $desigTech->id,
            'branch_id' => $branchHO->id,
            'emp_code' => 'FZ-EMP-003',
            'first_name' => 'Sunil',
            'last_name' => 'Verma',
            'email' => 'sunil.verma@fuzurra.com',
            'phone' => '+91 98333 66778',
            'pan' => 'ABCPV9012M',
            'joining_date' => '2024-08-01',
            'monthly_salary' => 45000.00,
            'status' => 'active',
        ]);

        SalaryStructure::create([
            'company_id' => $company->id,
            'employee_id' => $emp3->id,
            'basic_salary' => 22500.00,
            'hra' => 11250.00,
            'conveyance' => 4500.00,
            'special_allowance' => 6750.00,
            'pf_deduction' => 1800.00,
            'esi_deduction' => 0.00,
            'pt_deduction' => 200.00,
            'tds_deduction' => 500.00,
            'gross_salary' => 45000.00,
            'net_salary' => 42500.00,
        ]);

        // Attendance
        foreach ([$emp1, $emp2, $emp3] as $e) {
            Attendance::create([
                'company_id' => $company->id,
                'employee_id' => $e->id,
                'attendance_date' => now()->toDateString(),
                'status' => 'present',
                'in_time' => '09:00:00',
                'out_time' => '18:00:00',
                'total_hours' => 9.00,
                'remarks' => 'On time',
            ]);
        }

        // Monthly Payroll Batch
        $payrollRun = PayrollRun::create([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'month' => 2, // February
            'year' => 2026,
            'total_gross' => 175000.00,
            'total_deductions' => 11500.00,
            'total_net' => 163500.00,
            'status' => 'disbursed',
            'processed_by' => $adminUser->id,
        ]);

        Payslip::create([
            'company_id' => $company->id,
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $emp1->id,
            'basic' => 37500.00,
            'hra' => 18750.00,
            'conveyance' => 7500.00,
            'special_allowance' => 11250.00,
            'gross_salary' => 75000.00,
            'pf' => 1800.00,
            'esi' => 0.00,
            'pt' => 200.00,
            'tds' => 3500.00,
            'total_deductions' => 5500.00,
            'net_salary' => 69500.00,
            'payment_status' => 'paid',
            'payment_date' => '2026-02-28',
        ]);

        Payslip::create([
            'company_id' => $company->id,
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $emp2->id,
            'basic' => 27500.00,
            'hra' => 13750.00,
            'conveyance' => 5500.00,
            'special_allowance' => 8250.00,
            'gross_salary' => 55000.00,
            'pf' => 1800.00,
            'esi' => 0.00,
            'pt' => 200.00,
            'tds' => 1500.00,
            'total_deductions' => 3500.00,
            'net_salary' => 51500.00,
            'payment_status' => 'paid',
            'payment_date' => '2026-02-28',
        ]);

        Payslip::create([
            'company_id' => $company->id,
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $emp3->id,
            'basic' => 22500.00,
            'hra' => 11250.00,
            'conveyance' => 4500.00,
            'special_allowance' => 6750.00,
            'gross_salary' => 45000.00,
            'pf' => 1800.00,
            'esi' => 0.00,
            'pt' => 200.00,
            'tds' => 500.00,
            'total_deductions' => 2500.00,
            'net_salary' => 42500.00,
            'payment_status' => 'paid',
            'payment_date' => '2026-02-28',
        ]);

        // 16. FIXED ASSETS & OPERATING EXPENSES
        $costMfg = CostCentre::create(['company_id' => $company->id, 'name' => 'Plant 1 Manufacturing Line', 'code' => 'CC-PLANT1', 'type' => 'DIVISION']);
        $costRnd = CostCentre::create(['company_id' => $company->id, 'name' => 'Battery Testing & R&D Lab', 'code' => 'CC-LAB', 'type' => 'DEPARTMENT']);
        $costSolar = CostCentre::create(['company_id' => $company->id, 'name' => 'EPC Field Operations', 'code' => 'CC-EPC', 'type' => 'PROJECT']);

        FixedAsset::create([
            'company_id' => $company->id,
            'asset_name' => 'Automated CNC Fiber Laser Battery Busbar Welder',
            'asset_code' => 'AST-WELD-01',
            'purchase_date' => '2024-05-10',
            'purchase_cost' => 650000.00,
            'location' => 'Plant 1 - Hall B',
            'department' => 'Manufacturing',
            'useful_life_years' => 7,
            'depreciation_method' => 'SLM',
            'depreciation_rate' => 14.28,
            'accumulated_depreciation' => 92820.00,
            'book_value' => 557180.00,
            'status' => 'ACTIVE',
        ]);

        FixedAsset::create([
            'company_id' => $company->id,
            'asset_name' => 'Class AAA Steady-State Solar Simulator & Flash Tester',
            'asset_code' => 'AST-FLASH-02',
            'purchase_date' => '2024-07-20',
            'purchase_cost' => 880000.00,
            'location' => 'QC Lab',
            'department' => 'Quality Assurance',
            'useful_life_years' => 10,
            'depreciation_method' => 'SLM',
            'depreciation_rate' => 10.00,
            'accumulated_depreciation' => 88000.00,
            'book_value' => 792000.00,
            'status' => 'ACTIVE',
        ]);

        $expCatPower = ExpenseCategory::create(['company_id' => $company->id, 'name' => 'Industrial Electricity & Utilities', 'code' => 'EXP-UTIL']);
        $expCatLogistics = ExpenseCategory::create(['company_id' => $company->id, 'name' => 'Freight & Logistics Transport', 'code' => 'EXP-FRT']);

        ExpenseRecord::create([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'expense_category_id' => $expCatPower->id,
            'cost_centre_id' => $costMfg->id,
            'expense_date' => '2026-02-25',
            'amount' => 48500.00,
            'paid_to' => 'BSES Rajdhani Power Limited',
            'payment_mode' => 'BANK_TRANSFER',
            'reference_no' => 'NEFT-88192019',
            'description' => 'Plant 1 high-tension industrial power consumption for February 2026',
            'status' => 'APPROVED',
        ]);

        ExpenseRecord::create([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'expense_category_id' => $expCatLogistics->id,
            'cost_centre_id' => $costSolar->id,
            'expense_date' => '2026-02-27',
            'amount' => 22000.00,
            'paid_to' => 'VRL Logistics Ltd',
            'payment_mode' => 'BANK_TRANSFER',
            'reference_no' => 'CN-99182',
            'description' => 'Dispatched 50x Solar Panels and Inverters to Maharashtra Depot',
            'status' => 'APPROVED',
        ]);

        // 17. REAL DOUBLE-ENTRY SALES & PURCHASE INVOICES (Integrated Accounting & Stock)
        $accountingService = app(AccountingService::class);
        $invoicingService = app(InvoicingService::class);

        // A. Purchase from Sunrise Lithium (Stock Increase + Input Tax + Creditor Payable)
        $invoicingService->createPurchaseInvoice([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'supplier_ledger_id' => $suppSunrise->id,
            'bill_no' => 'SUN-2026-0182',
            'bill_date' => '2026-01-15',
            'due_date' => '2026-02-28',
            'warehouse_id' => $whMain->id,
            'purchase_account_ledger_id' => $purchaseLedger->id,
            'items' => [
                [
                    'product_id' => $pCell->id,
                    'description' => 'LiFePO4 Cells 3.2V 100Ah',
                    'hsn_code' => '850790',
                    'quantity' => 64.00,
                    'unit_price' => 2800.00,
                    'gst_rate' => 18.00,
                ],
                [
                    'product_id' => $pBms->id,
                    'description' => 'Smart BMS 16S 100A',
                    'hsn_code' => '903289',
                    'quantity' => 4.00,
                    'unit_price' => 4500.00,
                    'gst_rate' => 18.00,
                ]
            ],
            'notes' => 'Cell batch CATL-2026-B1 inward receipt and laboratory inspection passed.',
        ]);

        // B. Sales Invoice to Apex Tech Solutions (Intra-State Delhi: CGST + SGST)
        $invoicingService->createSalesInvoice([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'customer_ledger_id' => $custApex->id,
            'sales_account_ledger_id' => $salesLedger->id,
            'invoice_type' => 'tax_invoice',
            'invoice_no' => 'FZ-INV-2026-001',
            'invoice_date' => '2026-02-15',
            'due_date' => '2026-03-15',
            'items' => [
                [
                    'product_id' => $pBat51_2V100->id,
                    'warehouse_id' => $whMain->id,
                    'description' => 'Fuzurra 51.2V 100Ah LiFePO4 Smart Battery Pack (Serial FZ-512100-2026-0001)',
                    'hsn_code' => '850760',
                    'quantity' => 2.00,
                    'unit_price' => 89000.00,
                    'discount_percent' => 5.00,
                    'gst_rate' => 18.00,
                ],
                [
                    'product_id' => $pInv3370->id,
                    'warehouse_id' => $whMain->id,
                    'description' => 'Durasol DSH-3370 Hybrid Solar Inverter 5kW',
                    'hsn_code' => '850440',
                    'quantity' => 1.00,
                    'unit_price' => 56000.00,
                    'discount_percent' => 0.00,
                    'gst_rate' => 18.00,
                ]
            ],
            'notes' => 'Delivered and commissioned with 60-Month Warranty Certificate QC-2026-0881.',
        ]);

        // C. Customer Receipt Voucher (Apex Tech pays ₹ 1,50,000 via HDFC Bank)
        $accountingService->createVoucher([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => VoucherType::RECEIPT->value,
            'voucher_no' => 'RCPT-2026-0001',
            'voucher_date' => '2026-02-25',
            'party_ledger_id' => $custApex->id,
            'reference_no' => 'NEFT-HDFC-99182',
            'narration' => 'Part settlement received from Apex Tech Solutions against Invoice FZ-INV-2026-001',
            'created_by' => $adminUser->id,
        ], [
            [
                'ledger_id' => $hdfcLedger->id,
                'entry_type' => EntryType::DEBIT->value,
                'amount' => 150000.00,
                'narration' => 'HDFC Current A/c credited',
            ],
            [
                'ledger_id' => $custApex->id,
                'entry_type' => EntryType::CREDIT->value,
                'amount' => 150000.00,
                'narration' => 'Apex Tech Solutions account credited (receivable reduced)',
            ]
        ]);

        // D. Supplier Payment Voucher (Fuzurra pays ₹ 1,00,000 to Sunrise Lithium via HDFC Bank)
        $accountingService->createVoucher([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => VoucherType::PAYMENT->value,
            'voucher_no' => 'PMT-2026-0001',
            'voucher_date' => '2026-02-26',
            'party_ledger_id' => $suppSunrise->id,
            'reference_no' => 'RTGS-HDFC-00192',
            'narration' => 'Vendor payment disbursed to Sunrise Lithium Cells against Bill SUN-2026-0182',
            'created_by' => $accountantUser->id,
        ], [
            [
                'ledger_id' => $suppSunrise->id,
                'entry_type' => EntryType::DEBIT->value,
                'amount' => 100000.00,
                'narration' => 'Sunrise Lithium account debited (payable reduced)',
            ],
            [
                'ledger_id' => $hdfcLedger->id,
                'entry_type' => EntryType::CREDIT->value,
                'amount' => 100000.00,
                'narration' => 'HDFC Current A/c debited via RTGS',
            ]
        ]);

        // E. Contra Voucher (Cash withdrawal of ₹ 25,000 from Bank for Factory Petty Cash)
        $accountingService->createVoucher([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => VoucherType::CONTRA->value,
            'voucher_no' => 'CNTR-2026-0001',
            'voucher_date' => '2026-02-27',
            'party_ledger_id' => null,
            'reference_no' => 'CHQ-881921',
            'narration' => 'Self cash withdrawal from HDFC Bank for factory petty cash requirements',
            'created_by' => $accountantUser->id,
        ], [
            [
                'ledger_id' => $cashLedger->id,
                'entry_type' => EntryType::DEBIT->value,
                'amount' => 25000.00,
                'narration' => 'Cash-in-hand received',
            ],
            [
                'ledger_id' => $hdfcLedger->id,
                'entry_type' => EntryType::CREDIT->value,
                'amount' => 25000.00,
                'narration' => 'HDFC Current A/c debited via Self Cheque',
            ]
        ]);
    }
}
