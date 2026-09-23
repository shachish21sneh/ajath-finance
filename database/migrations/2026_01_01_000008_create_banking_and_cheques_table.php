<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('cascade');
            $table->string('account_name', 150);
            $table->string('account_number', 50);
            $table->string('bank_name', 100);
            $table->string('ifsc_code', 20)->nullable();
            $table->string('branch', 100)->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['company_id', 'account_number']);
        });

        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->date('statement_date');
            $table->decimal('statement_balance', 15, 2)->default(0.00);
            $table->decimal('book_balance', 15, 2)->default(0.00);
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamps();
        });

        Schema::create('cheque_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->string('cheque_number', 50);
            $table->date('cheque_date');
            $table->string('party_name', 150);
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['issued', 'received'])->default('issued');
            $table->enum('status', ['pending', 'cleared', 'bounced', 'cancelled'])->default('pending');
            $table->date('clearing_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'cheque_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheque_entries');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_accounts');
    }
};
