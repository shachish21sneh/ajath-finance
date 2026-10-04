# FUZURRA ERP — Enterprise Architecture & User Operational Guide

Welcome to **FUZURRA ERP**, the enterprise-grade business accounting, inventory, manufacturing, battery, solar, payroll, CRM, dealer, and service management system.

---

## 1. Quick Start & Execution

### Backend API (NestJS + Prisma)
```bash
cd backend
npm install
npx prisma generate
npx prisma db push
npx ts-node prisma/seed.ts    # Seed chart of accounts, products, BOM, vouchers
npm run start:dev             # Starts REST API at http://localhost:4000/api
```

### Frontend Web Client (Next.js + Tailwind CSS)
```bash
cd frontend
npm install
npm run dev                   # Starts UI at http://localhost:3000
```

### Docker Enterprise Deployment (PostgreSQL + Redis + Services)
```bash
docker compose up -d --build
```

---

## 2. Default Enterprise Credentials

| Role | Username | Password | Access Level |
|---|---|---|---|
| **Super Administrator** | `admin` | `password123` | Full unrestricted access to all modules, vouchers, and audit trails |

---

## 3. Desktop Fast-Entry Keyboard Shortcuts

- **F2**: Switch Active Company Context Modal
- **F3**: Switch Financial Year Context Modal (Active: `FY 2024-25`)
- **F4**: Create New **Contra Voucher** (Bank-to-Bank or Cash-to-Bank fund transfers)
- **F5**: Create New **Payment Voucher** (Vendor disbursements and expense settlements)
- **F6**: Create New **Receipt Voucher** (Customer receivables and capital receipts)
- **F7**: Create New **Journal Voucher** (General adjustments, depreciation, payroll provisions)
- **F8**: Create New **GST Tax Invoice** (B2B/B2C invoicing with E-Invoice IRN & E-Way Bill)
- **F9**: Create New **Purchase Inward Bill** (Material receipt & stock ledger integration)
- **Ctrl + K**: Global Spotlight Search across Ledgers, Serials, Invoices, and Products
- **Ctrl + P**: Print Clean A4 Tax Invoice / Voucher layout

---

## 4. Architectural Invariants Verified

1. **Strict Double-Entry Parity**:
   - Every financial transaction enforces $\sum \text{Debit} \equiv \sum \text{Credit}$.
   - Unbalanced transactions are rejected at the service and database boundary with a `BadRequestException`.
2. **ACID Manufacturing & Stock Integration**:
   - Executing a production run consumes raw materials (e.g. 16x LiFePO4 cells per battery pack) and produces finished goods with unique serialized barcodes for laboratory QC testing.
3. **Indian Statutory GST Engine**:
   - Automatic determination of Intra-State (`CGST` + `SGST`) vs Inter-State (`IGST`) tax based on seller branch state code vs customer place of supply.
   - Native HSN/SAC summary aggregation, GSTR-1 outward return summary, and GSTR-3B input tax credit computation.
4. **Specialized Battery & Solar Lifecycles**:
   - **Battery**: Prismatic Cell $\rightarrow$ Production Assembly $\rightarrow$ Laboratory QC Test (Voltage, Internal Resistance $m\Omega$, Capacity Ah) $\rightarrow$ Serialized Dispatch $\rightarrow$ 60-Month Warranty Registration $\rightarrow$ Claim Processing.
   - **Solar**: Lead $\rightarrow$ Rooftop Site Survey (Area sq ft, Sanctioned Load kW) $\rightarrow$ Sizing & Quotation $\rightarrow$ Order $\rightarrow$ Installation $\rightarrow$ Net Metering $\rightarrow$ AMC Service.

---

## 5. Automated Test Suite Results

Run tests anytime with:
```bash
cd backend
npm test
```
Result:
```
PASS src/accounting/accounting.spec.ts
  ✓ MUST reject any voucher where Debit != Credit (Double-Entry Invariant)
  ✓ MUST accept and atomically post balanced vouchers where Debit === Credit
  ✓ Trial Balance MUST have totalDebit === totalCredit
  ✓ Balance Sheet MUST balance: Assets === Liabilities + Equity + Net Profit

PASS src/manufacturing/manufacturing.spec.ts
  ✓ Production MUST decrease raw materials and increase finished goods

Test Suites: 2 passed, 2 total
Tests:       5 passed, 5 total
```
