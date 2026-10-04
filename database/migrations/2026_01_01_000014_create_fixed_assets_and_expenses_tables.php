<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_centres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->enum('type', ['BRANCH', 'DEPARTMENT', 'PROJECT', 'DIVISION'])->default('DEPARTMENT');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('financial_year_id')->nullable()->constrained('financial_years')->onDelete('set null');
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->foreignId('cost_centre_id')->nullable()->constrained('cost_centres')->onDelete('set null');
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->string('paid_to', 150);
            $table->enum('payment_mode', ['CASH', 'BANK_TRANSFER', 'CHEQUE', 'UPI', 'CREDIT_CARD'])->default('BANK_TRANSFER');
            $table->string('reference_no', 100)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('APPROVED');
            $table->timestamps();
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->string('asset_name', 150);
            $table->string('asset_code', 50);
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->string('serial_number', 100)->nullable();
            $table->string('location', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->unsignedSmallInteger('useful_life_years')->default(5);
            $table->enum('depreciation_method', ['SLM', 'WDV'])->default('SLM'); // Straight Line vs Written Down Value
            $table->decimal('depreciation_rate', 5, 2)->default(10.00); // % per year
            $table->decimal('accumulated_depreciation', 15, 2)->default(0.00);
            $table->decimal('book_value', 15, 2);
            $table->enum('status', ['ACTIVE', 'UNDER_MAINTENANCE', 'DISPOSED', 'WRITTEN_OFF'])->default('ACTIVE');
            $table->timestamps();

            $table->unique(['company_id', 'asset_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('expense_records');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('cost_centres');
    }
};
