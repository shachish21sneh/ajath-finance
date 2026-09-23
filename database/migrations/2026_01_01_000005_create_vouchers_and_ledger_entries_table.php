<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->enum('voucher_type', ['CONTRA', 'PAYMENT', 'RECEIPT', 'JOURNAL', 'SALES', 'PURCHASE', 'DEBIT_NOTE', 'CREDIT_NOTE']);
            $table->string('voucher_no', 50);
            $table->date('voucher_date');
            $table->string('reference_no', 100)->nullable();
            $table->foreignId('party_ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->text('narration')->nullable();
            $table->enum('payment_mode', ['cash', 'bank', 'cheque', 'upi', 'credit'])->default('cash');
            $table->string('cheque_no', 50)->nullable();
            $table->date('cheque_date')->nullable();
            $table->enum('status', ['posted', 'draft', 'cancelled'])->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'financial_year_id', 'voucher_type', 'voucher_no'], 'unique_company_fy_voucher');
            $table->index(['company_id', 'voucher_date']);
            $table->index(['company_id', 'voucher_type']);
        });

        Schema::create('voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('restrict');
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->string('narration')->nullable();
            $table->unsignedInteger('line_order')->default(0);
            $table->timestamps();

            $table->index(['voucher_id', 'entry_type']);
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('restrict');
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->default(0.00);
            $table->date('entry_date');
            $table->string('narration')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'ledger_id', 'entry_date'], 'idx_company_ledger_date');
            $table->index(['company_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('voucher_items');
        Schema::dropIfExists('vouchers');
    }
};
