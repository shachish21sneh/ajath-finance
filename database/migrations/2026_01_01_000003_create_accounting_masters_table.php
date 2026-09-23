<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('name', 100); // e.g. "GST 18%", "GST 12%", "Exempt 0%"
            $table->decimal('rate', 5, 2)->default(0.00);
            $table->decimal('cgst_rate', 5, 2)->default(0.00);
            $table->decimal('sgst_rate', 5, 2)->default(0.00);
            $table->decimal('igst_rate', 5, 2)->default(0.00);
            $table->decimal('cess_rate', 5, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ledger_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('ledger_groups')->onDelete('cascade');
            $table->string('name', 150);
            $table->string('slug', 150);
            $table->enum('nature', ['ASSET', 'LIABILITY', 'INCOME', 'EXPENSE']);
            $table->boolean('affects_gross_profit')->default(false);
            $table->boolean('is_system')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'nature']);
        });

        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('ledger_group_id')->constrained('ledger_groups')->onDelete('restrict');
            $table->foreignId('tax_master_id')->nullable()->constrained('tax_masters')->onDelete('set null');
            $table->string('name', 200);
            $table->string('code', 50)->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->enum('opening_balance_type', ['Dr', 'Cr'])->default('Dr');
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->enum('party_type', ['none', 'customer', 'supplier', 'bank', 'cash', 'employee'])->default('none');
            $table->string('gstin', 20)->nullable()->index();
            $table->string('pan', 15)->nullable()->index();
            $table->string('email', 150)->nullable();
            $table->string('phone', 25)->nullable()->index();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('state_code', 5)->nullable();
            $table->string('pincode', 15)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->integer('credit_days')->default(0);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('bank_ifsc', 20)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'party_type']);
            $table->index(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledgers');
        Schema::dropIfExists('ledger_groups');
        Schema::dropIfExists('tax_masters');
    }
};
