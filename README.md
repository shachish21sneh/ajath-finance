# Ajath Finance ERP

**Ajath Finance ERP** is a modern, high-speed, cloud-based Accounting and ERP web application built with **Laravel 12**, **MySQL 8**, **Bootstrap 5**, **Alpine.js**, and an ultra-fast desktop keyboard-first workflow engine.

Designed for high-productivity accounting workflows, multi-company consolidation, GST-compliant invoicing, fast retail POS checkout, and comprehensive financial statements (Day Book, Trial Balance, Profit & Loss, Balance Sheet), with 100% original architecture and native compatibility with **Shared Hosting (cPanel)** environments.

---

## Key Features

### 1. Desktop-Speed Keyboard Navigation
- **F2**: Switch Company Context Modal
- **F3**: Switch Financial Year Context Modal
- **F4**: Contra Voucher (Cash / Bank transfers)
- **F5**: Payment Voucher
- **F6**: Receipt Voucher
- **F7**: Journal Voucher (General adjustment)
- **F8**: GST Tax Invoice Generator
- **F9**: Purchase Inward Invoice
- **Ctrl + S**: Save Active Form
- **Ctrl + P**: Print Clean Layout
- **Ctrl + F / Ctrl + K**: Global Spotlight Search across Ledgers, Vouchers, Invoices, Products
- **Enter**: Grid-cell navigation for lightning-fast itemized voucher entry

### 2. Double-Entry Accounting Engine
- Enforces fundamental invariant: `Sum(Debit) === Sum(Credit)` inside database transactions.
- Automatic posting to atomic ledger entries with running balance tracking.
- Preconfigured Chart of Accounts: Assets, Liabilities, Equity, Incomes, and Expenses.
- Multi-company and multi-financial year isolation.

### 3. GST Invoicing & Retail POS
- Automatic Intra-State (`CGST` + `SGST`) vs Inter-State (`IGST`) tax engine.
- Instant Retail POS Billing (`/sales/pos`) with barcode lookup and quick tender change calculator.
- Standard A4 GST Tax Invoice printable generation.

### 4. Financial Statements
- **Day Book**: Filterable chronological transaction journal.
- **Trial Balance**: Group-wise summary validating debit/credit parity.
- **Profit & Loss**: Trading account (Gross Profit) and Income Statement (Net Profit).
- **Balance Sheet**: Assets vs Liabilities & Equity with live net profit integration.
- **Ledger Statements**: Account statement with running balances and opening/closing figures.
- **GST Summary**: Taxable values, CGST, SGST, IGST breakdown, and HSN analysis.

---

## Tech Stack & Architecture

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL 8 / MariaDB (Zero-config SQLite fallback for local testing)
- **Frontend**: Blade templates, Bootstrap 5, Alpine.js, jQuery, DataTables, Chart.js, FontAwesome 6
- **Pattern**: Repository Pattern, Domain Service Classes, Enums, Form Requests
- **Hosting**: Native Shared Hosting (cPanel) support (File Cache, File Sessions, Database Queue, Standard Cron Runner)

---

## Local Development Setup

```bash
# 1. Clone the repository
git clone https://github.com/shachish21sneh/ajath-finance.git
cd ajath-finance

# 2. Install PHP dependencies
composer install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Run database migrations & seed demo data
php artisan migrate --seed

# 5. Start development server
php artisan serve
```

### Demo Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Super Admin** | `admin@ajath.com` | `password` |
| **Accountant** | `accountant@ajath.com` | `password` |
| **Sales Manager** | `sales@ajath.com` | `password` |

---

## Shared Hosting (cPanel) Deployment

1. Set the domain Document Root to `/home/username/finance.fuzurra.in/public` (or create a symlink from `public` to your domain folder).
2. Create a MySQL database and user in cPanel.
3. Configure `.env` with your production MySQL credentials.
4. Run migrations:
   ```bash
   php artisan migrate --force --seed
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Set cPanel Cron job (`* * * * *`):
   ```bash
   * * * * * cd /home/username/finance.fuzurra.in && php artisan schedule:run >> /dev/null 2>&1
   ```

---

## License

Original proprietary codebase built for **Ajath Finance ERP**.
