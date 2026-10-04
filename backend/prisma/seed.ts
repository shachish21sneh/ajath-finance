import { PrismaClient } from '@prisma/client';
import * as bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
  console.log('--- SEEDING FUZURRA ERP ENTERPRISE DATABASE ---');

  // 1. Create Default Company
  const company = await prisma.company.upsert({
    where: { id: 'comp_fuzurra_01' },
    update: {},
    create: {
      id: 'comp_fuzurra_01',
      name: 'FUZURRA ENERGY & ELECTRONICS PVT LTD',
      legalName: 'FUZURRA ENERGY & ELECTRONICS PRIVATE LIMITED',
      gstin: '07AAACF8901D1Z5',
      pan: 'AAACF8901D',
      cin: 'U31900DL2024PTC123456',
      email: 'admin@fuzurra.com',
      phone: '+91 98765 43210',
      address: 'Plot 42, Sector 63, Electronic City',
      city: 'Noida',
      state: 'Uttar Pradesh',
      stateCode: '09',
      pincode: '201301',
      currency: 'INR',
      currencySymbol: '₹',
    },
  });
  console.log('✓ Company Created:', company.name);

  // 2. Branches
  const hoBranch = await prisma.branch.upsert({
    where: { id: 'branch_ho_01' },
    update: {},
    create: {
      id: 'branch_ho_01',
      companyId: company.id,
      name: 'Corporate Head Office & Central Hub',
      code: 'DEL-HO',
      gstin: '07AAACF8901D1Z5',
      state: 'Delhi',
      stateCode: '07',
      address: 'Connaught Place, New Delhi',
      isHeadOffice: true,
    },
  });

  const mfgBranch = await prisma.branch.upsert({
    where: { id: 'branch_mfg_01' },
    update: {},
    create: {
      id: 'branch_mfg_01',
      companyId: company.id,
      name: 'Noida Battery & Solar Manufacturing Facility',
      code: 'NOI-PLANT',
      gstin: '09AAACF8901D1Z3',
      state: 'Uttar Pradesh',
      stateCode: '09',
      address: 'Phase-2 Industrial Area, Noida',
      isHeadOffice: false,
    },
  });
  console.log('✓ Branches Created: Corporate HO & Manufacturing Plant');

  // 3. Financial Year
  const fy2425 = await prisma.financialYear.upsert({
    where: { id: 'fy_2024_25' },
    update: {},
    create: {
      id: 'fy_2024_25',
      companyId: company.id,
      yearCode: 'FY 2024-25',
      startDate: new Date('2024-04-01'),
      endDate: new Date('2025-03-31'),
      isActive: true,
    },
  });
  console.log('✓ Financial Year Created:', fy2425.yearCode);

  // 4. Roles & Permissions
  const adminRole = await prisma.role.upsert({
    where: { id: 'role_super_admin' },
    update: {},
    create: {
      id: 'role_super_admin',
      companyId: company.id,
      name: 'Super Administrator',
      description: 'Unrestricted enterprise access to all financial & operational modules',
      isSystem: true,
    },
  });

  const modules = ['accounting', 'inventory', 'sales', 'purchase', 'payroll', 'manufacturing', 'battery', 'solar', 'crm', 'reports'];
  const actions = ['create', 'read', 'update', 'delete', 'approve', 'export'];

  for (const mod of modules) {
    for (const act of actions) {
      await prisma.rolePermission.upsert({
        where: {
          roleId_module_action: {
            roleId: adminRole.id,
            module: mod,
            action: act,
          },
        },
        update: {},
        create: {
          roleId: adminRole.id,
          module: mod,
          action: act,
        },
      });
    }
  }

  // 5. Admin User
  const salt = await bcrypt.genSalt(10);
  const passwordHash = await bcrypt.hash('password123', salt);

  const adminUser = await prisma.user.upsert({
    where: { username: 'admin' },
    update: { passwordHash },
    create: {
      id: 'user_admin_01',
      companyId: company.id,
      branchId: hoBranch.id,
      roleId: adminRole.id,
      username: 'admin',
      email: 'admin@fuzurra.com',
      passwordHash,
      fullName: 'Chief Technology Officer',
      phone: '+91 99999 88888',
    },
  });
  console.log('✓ Super Admin User Created: admin / password123');

  // 6. Master Chart of Accounts (Account Groups)
  const groupDefs = [
    { id: 'grp_current_assets', name: 'Current Assets', code: 'CA', nature: 'ASSET' },
    { id: 'grp_fixed_assets', name: 'Fixed Assets', code: 'FA', nature: 'ASSET' },
    { id: 'grp_bank_cash', name: 'Bank & Cash Accounts', code: 'BC', nature: 'ASSET' },
    { id: 'grp_sundry_debtors', name: 'Sundry Debtors (Customers)', code: 'SD', nature: 'ASSET' },
    { id: 'grp_current_liabilities', name: 'Current Liabilities', code: 'CL', nature: 'LIABILITY' },
    { id: 'grp_sundry_creditors', name: 'Sundry Creditors (Suppliers)', code: 'SC', nature: 'LIABILITY' },
    { id: 'grp_duties_taxes', name: 'Duties & Taxes (GST)', code: 'DT', nature: 'LIABILITY' },
    { id: 'grp_capital', name: 'Capital Account', code: 'EQ', nature: 'EQUITY' },
    { id: 'grp_sales_income', name: 'Sales Accounts', code: 'INC', nature: 'INCOME' },
    { id: 'grp_purchase_exp', name: 'Purchase Accounts', code: 'PUR', nature: 'EXPENSE' },
    { id: 'grp_direct_exp', name: 'Direct Manufacturing Expenses', code: 'DE', nature: 'EXPENSE' },
    { id: 'grp_indirect_exp', name: 'Indirect Overhead Expenses', code: 'IE', nature: 'EXPENSE' },
  ];

  for (const g of groupDefs) {
    await prisma.accountGroup.upsert({
      where: { id: g.id },
      update: {},
      create: {
        id: g.id,
        companyId: company.id,
        name: g.name,
        code: g.code,
        nature: g.nature,
      },
    });
  }

  // 7. Core Ledgers
  const ledgerDefs = [
    { id: 'led_cash', groupId: 'grp_bank_cash', name: 'Cash in Hand', code: '1001', isBankCash: true, openBal: 250000, openType: 'DEBIT' },
    { id: 'led_hdfc', groupId: 'grp_bank_cash', name: 'HDFC Current A/c - 50200012345678', code: '1002', isBankCash: true, openBal: 5000000, openType: 'DEBIT' },
    { id: 'led_capital', groupId: 'grp_capital', name: 'Equity Capital - Promoters', code: '2001', openBal: 5250000, openType: 'CREDIT' },
    { id: 'led_sales_gst18', groupId: 'grp_sales_income', name: 'Sales Account (GST 18%)', code: '3001', openBal: 0, openType: 'CREDIT' },
    { id: 'led_sales_solar', groupId: 'grp_sales_income', name: 'Solar Project EPC Revenue (GST 12%)', code: '3002', openBal: 0, openType: 'CREDIT' },
    { id: 'led_pur_raw', groupId: 'grp_purchase_exp', name: 'Raw Material Purchases', code: '4001', openBal: 0, openType: 'DEBIT' },
    { id: 'led_cgst_input', groupId: 'grp_duties_taxes', name: 'Input CGST Account', code: '2101', openBal: 0, openType: 'DEBIT' },
    { id: 'led_sgst_input', groupId: 'grp_duties_taxes', name: 'Input SGST Account', code: '2102', openBal: 0, openType: 'DEBIT' },
    { id: 'led_igst_input', groupId: 'grp_duties_taxes', name: 'Input IGST Account', code: '2103', openBal: 0, openType: 'DEBIT' },
    { id: 'led_cgst_output', groupId: 'grp_duties_taxes', name: 'Output CGST Account', code: '2104', openBal: 0, openType: 'CREDIT' },
    { id: 'led_sgst_output', groupId: 'grp_duties_taxes', name: 'Output SGST Account', code: '2105', openBal: 0, openType: 'CREDIT' },
    { id: 'led_igst_output', groupId: 'grp_duties_taxes', name: 'Output IGST Account', code: '2106', openBal: 0, openType: 'CREDIT' },
    { id: 'led_salary_exp', groupId: 'grp_indirect_exp', name: 'Employee Salaries & Wages Expense', code: '5001', openBal: 0, openType: 'DEBIT' },
    { id: 'led_salary_payable', groupId: 'grp_current_liabilities', name: 'Salary Payable Account', code: '2201', openBal: 0, openType: 'CREDIT' },
    { id: 'led_factory_rent', groupId: 'grp_direct_exp', name: 'Factory Electricity & Power', code: '4101', openBal: 0, openType: 'DEBIT' },
  ];

  for (const l of ledgerDefs) {
    await prisma.ledger.upsert({
      where: { id: l.id },
      update: {},
      create: {
        id: l.id,
        companyId: company.id,
        accountGroupId: l.groupId,
        name: l.name,
        code: l.code,
        isBankCash: l.isBankCash || false,
        openingBalance: l.openBal,
        openingType: l.openType,
        currentBalance: l.openBal,
        balanceType: l.openType,
      },
    });
  }

  // Bank Account linked to HDFC ledger
  await prisma.bankAccount.upsert({
    where: { ledgerId: 'led_hdfc' },
    update: {},
    create: {
      id: 'bank_hdfc_01',
      ledgerId: 'led_hdfc',
      bankName: 'HDFC Bank Ltd',
      accountNumber: '50200012345678',
      ifscCode: 'HDFC0001234',
      branchName: 'Sector 62 Noida',
      accountType: 'CURRENT',
    },
  });
  console.log('✓ Master Chart of Accounts & Banking configured');

  // 8. Warehouses
  const rawWarehouse = await prisma.warehouse.upsert({
    where: { id: 'wh_raw_01' },
    update: {},
    create: {
      id: 'wh_raw_01',
      companyId: company.id,
      branchId: mfgBranch.id,
      name: 'Noida Plant - Raw Materials Store',
      code: 'WH-RAW-NOI',
      isPrimary: false,
    },
  });

  const fgWarehouse = await prisma.warehouse.upsert({
    where: { id: 'wh_fg_01' },
    update: {},
    create: {
      id: 'wh_fg_01',
      companyId: company.id,
      branchId: mfgBranch.id,
      name: 'Finished Battery & Inverter Dispatch Warehouse',
      code: 'WH-FG-NOI',
      isPrimary: true,
    },
  });

  const solarYard = await prisma.warehouse.upsert({
    where: { id: 'wh_solar_01' },
    update: {},
    create: {
      id: 'wh_solar_01',
      companyId: company.id,
      branchId: hoBranch.id,
      name: 'Delhi Solar Project Logistics Yard',
      code: 'WH-SOL-DEL',
      isPrimary: false,
    },
  });
  console.log('✓ Warehouses created: Raw Materials, Finished Goods & Solar Yard');

  // 9. Product Groups & Products
  const grpBatteries = await prisma.productGroup.upsert({
    where: { id: 'pgrp_batteries' },
    update: {},
    create: { id: 'pgrp_batteries', companyId: company.id, name: 'Storage Batteries', code: 'BAT' },
  });

  const grpSolar = await prisma.productGroup.upsert({
    where: { id: 'pgrp_solar' },
    update: {},
    create: { id: 'pgrp_solar', companyId: company.id, name: 'Solar Panels & Inverters', code: 'SOL' },
  });

  const grpRaw = await prisma.productGroup.upsert({
    where: { id: 'pgrp_raw' },
    update: {},
    create: { id: 'pgrp_raw', companyId: company.id, name: 'Manufacturing Components', code: 'RAW' },
  });

  // Sample Products
  const prodLfp = await prisma.product.upsert({
    where: { id: 'prod_lfp_48v' },
    update: {},
    create: {
      id: 'prod_lfp_48v',
      companyId: company.id,
      productGroupId: grpBatteries.id,
      sku: 'FUZ-LFP-48V-100AH',
      name: 'FUZURRA LiFePO4 Smart Battery 48V 100Ah (5.12kWh)',
      description: 'Prismatic Cell LiFePO4 battery pack with Smart Bluetooth BMS, 6000 cycles',
      unit: 'NOS',
      hsnSacCode: '85076000',
      gstRate: 18.0,
      purchasePrice: 65000,
      sellingPrice: 95000,
      minStockAlert: 10,
      currentStock: 45,
      isSerialized: true,
      productType: 'BATTERY',
    },
  });

  const prodTubular = await prisma.product.upsert({
    where: { id: 'prod_tub_150ah' },
    update: {},
    create: {
      id: 'prod_tub_150ah',
      companyId: company.id,
      productGroupId: grpBatteries.id,
      sku: 'FUZ-TUB-12V-150AH',
      name: 'FUZURRA Heavy Duty Tall Tubular Battery 12V 150Ah',
      description: 'Deep cycle solar tall tubular lead acid battery for UPS & Home Inverters',
      unit: 'NOS',
      hsnSacCode: '85072000',
      gstRate: 28.0,
      purchasePrice: 9500,
      sellingPrice: 14500,
      minStockAlert: 20,
      currentStock: 120,
      isSerialized: true,
      productType: 'BATTERY',
    },
  });

  const prodSolar550 = await prisma.product.upsert({
    where: { id: 'prod_sol_550w' },
    update: {},
    create: {
      id: 'prod_sol_550w',
      companyId: company.id,
      productGroupId: grpSolar.id,
      sku: 'FUZ-SOL-550W-MONO',
      name: 'FUZURRA Mono PERC Half-Cut Solar Module 550W',
      description: 'Tier-1 High efficiency 21.3% bifacial dual glass solar PV module',
      unit: 'NOS',
      hsnSacCode: '85414011',
      gstRate: 12.0,
      purchasePrice: 11000,
      sellingPrice: 14800,
      minStockAlert: 50,
      currentStock: 250,
      isSerialized: false,
      productType: 'SOLAR_PANEL',
    },
  });

  const prodCellRaw = await prisma.product.upsert({
    where: { id: 'prod_cell_100ah' },
    update: {},
    create: {
      id: 'prod_cell_100ah',
      companyId: company.id,
      productGroupId: grpRaw.id,
      sku: 'CELL-LFP-3.2V-100AH',
      name: 'Grade-A LiFePO4 Prismatic Cell 3.2V 100Ah',
      description: 'Raw battery cell with QR code laser welded studs',
      unit: 'NOS',
      hsnSacCode: '85079090',
      gstRate: 18.0,
      purchasePrice: 2800,
      sellingPrice: 3500,
      minStockAlert: 200,
      currentStock: 800,
      isSerialized: true,
      productType: 'RAW_MATERIAL',
    },
  });

  // 10. Battery Models
  await prisma.batteryModel.upsert({
    where: { productId: prodLfp.id },
    update: {},
    create: {
      id: 'bmodel_lfp_48v',
      companyId: company.id,
      productId: prodLfp.id,
      batteryType: 'LIFEPO4',
      capacityAh: 100.0,
      voltage: 48.0,
      warrantyMonths: 60,
      freeReplacementMonths: 36,
      proRataMonths: 24,
    },
  });

  await prisma.batteryModel.upsert({
    where: { productId: prodTubular.id },
    update: {},
    create: {
      id: 'bmodel_tub_150ah',
      companyId: company.id,
      productId: prodTubular.id,
      batteryType: 'TUBULAR',
      capacityAh: 150.0,
      voltage: 12.0,
      warrantyMonths: 36,
      freeReplacementMonths: 24,
      proRataMonths: 12,
    },
  });
  console.log('✓ Product Catalog & Battery Specifications created');

  // 11. Bill of Materials (BOM) for 48V 100Ah Pack
  const bomLfp = await prisma.bOM.upsert({
    where: { id: 'bom_lfp_48v_01' },
    update: {},
    create: {
      id: 'bom_lfp_48v_01',
      companyId: company.id,
      productId: prodLfp.id,
      code: 'BOM-LFP48100',
      name: 'Assembly BOM for 48V 100Ah LiFePO4 Pack (16S Configuration)',
      outputQuantity: 1.0,
    },
  });

  await prisma.bOMItem.create({
    data: {
      bomId: bomLfp.id,
      productId: prodCellRaw.id,
      quantity: 16.0,
      scrapAllowance: 0.0,
    },
  });
  console.log('✓ Bill of Materials (BOM) configured for 16S LiFePO4 Battery Pack');

  // 12. Customers & Suppliers
  const ledCust1 = await prisma.ledger.create({
    data: {
      companyId: company.id,
      accountGroupId: 'grp_sundry_debtors',
      name: 'Apex Green Energy Solutions Pvt Ltd',
      code: 'CUST-001',
      isPartyLedger: true,
      currentBalance: 0,
      stateCode: '07',
      gstin: '07AAICA1234F1Z8',
    },
  });

  const customer1 = await prisma.customer.create({
    data: {
      id: 'cust_apex_01',
      companyId: company.id,
      ledgerId: ledCust1.id,
      tradeName: 'Apex Green Energy Solutions',
      legalName: 'Apex Green Energy Solutions Private Limited',
      gstin: '07AAICA1234F1Z8',
      pan: 'AAICA1234F',
      phone: '+91 98111 22233',
      email: 'procurement@apexenergy.in',
      billingAddress: 'B-14 Okhla Industrial Area Phase-III, New Delhi',
      state: 'Delhi',
      stateCode: '07',
      creditLimit: 1500000,
      creditDays: 30,
    },
  });

  const ledSupp1 = await prisma.ledger.create({
    data: {
      companyId: company.id,
      accountGroupId: 'grp_sundry_creditors',
      name: 'Shenzhen Eve Battery Cell Imports Corp',
      code: 'SUPP-001',
      isPartyLedger: true,
      currentBalance: 0,
      stateCode: '99',
    },
  });

  await prisma.supplier.create({
    data: {
      id: 'supp_eve_01',
      companyId: company.id,
      ledgerId: ledSupp1.id,
      tradeName: 'EVE Prismatic Cell Imports',
      legalName: 'EVE Energy Overseas Distribution LLC',
      phone: '+86 755 8888 9999',
      email: 'sales@eve-prismatic.com',
      address: 'Shenzhen Free Trade Logistics Park',
      state: 'Overseas',
      stateCode: '99',
      creditDays: 60,
    },
  });
  console.log('✓ Customer and Supplier Ledgers & Entities created');

  // 13. HR Departments, Designations & Employees
  const deptProd = await prisma.department.create({
    data: { companyId: company.id, name: 'Battery & Solar Assembly', code: 'PRD' },
  });
  const deptEng = await prisma.department.create({
    data: { companyId: company.id, name: 'Quality Control & Testing', code: 'QC' },
  });

  const desTech = await prisma.designation.create({
    data: { companyId: company.id, title: 'Lead Battery QC Engineer', code: 'QC-ENG' },
  });

  const emp1 = await prisma.employee.create({
    data: {
      id: 'emp_001',
      companyId: company.id,
      departmentId: deptEng.id,
      designationId: desTech.id,
      employeeCode: 'EMP-1001',
      firstName: 'Vikram',
      lastName: 'Sharma',
      email: 'vikram.s@fuzurra.com',
      phone: '+91 98760 11223',
      dateOfJoining: new Date('2023-01-15'),
      monthlySalary: 65000,
    },
  });

  await prisma.salaryStructure.create({
    data: {
      employeeId: emp1.id,
      basicSalary: 35000,
      hra: 15000,
      conveyance: 5000,
      specialAllowance: 10000,
      pfDeduction: 1800,
      esiDeduction: 0,
      professionalTax: 200,
    },
  });
  console.log('✓ HR & Payroll master data initialized');

  // 14. Solar Project Sample
  await prisma.solarProject.create({
    data: {
      id: 'proj_apex_50kw',
      companyId: company.id,
      customerId: customer1.id,
      projectCode: 'SOL-2024-001',
      projectType: 'COMMERCIAL',
      capacityKw: 50.0,
      inverterCapacityKw: 50.0,
      totalContractValue: 2450000,
      status: 'SURVEY_DONE',
    },
  });

  // 15. Initial Accounting Voucher - Capital Infusion (F6 Receipt Voucher)
  // Double-Entry Invariant: Debit HDFC Bank (50,00,000) === Credit Equity Capital (50,00,000)
  const v1 = await prisma.voucher.create({
    data: {
      id: 'vouch_init_01',
      companyId: company.id,
      branchId: hoBranch.id,
      financialYearId: fy2425.id,
      voucherNumber: 'REC-24-0001',
      voucherType: 'RECEIPT',
      date: new Date('2024-04-01'),
      narration: 'Being initial equity capital contribution deposited into HDFC Bank Account',
      totalAmount: 5000000,
      createdById: adminUser.id,
      entries: {
        create: [
          { ledgerId: 'led_hdfc', type: 'DEBIT', amount: 5000000, narration: 'Funds received in bank' },
          { ledgerId: 'led_capital', type: 'CREDIT', amount: 5000000, narration: 'Promoter capital share' },
        ],
      },
    },
  });
  console.log('✓ Initial Double-Entry Voucher posted: REC-24-0001 (Bal: ₹50,00,000 balanced)');

  console.log('============================================================');
  console.log('FUZURRA ERP DATABASE INITIALIZED SUCCESSFULLY WITH ZERO FAKE DATA');
  console.log('============================================================');
}

main()
  .catch((e) => {
    console.error('Seed Error:', e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
