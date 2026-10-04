<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->string('dealer_code', 50);
            $table->string('name', 150);
            $table->string('company_name', 150)->nullable();
            $table->string('territory', 100)->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->unsignedSmallInteger('credit_days')->default(30);
            $table->enum('price_tier', ['DISTRIBUTOR', 'DEALER', 'RETAILER'])->default('DEALER');
            $table->decimal('outstanding_balance', 15, 2)->default(0.00);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'dealer_code']);
        });

        Schema::create('dealer_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('dealer_id')->constrained('dealers')->onDelete('cascade');
            $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->onDelete('set null');
            $table->decimal('sales_amount', 15, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 15, 2);
            $table->unsignedSmallInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->enum('status', ['PENDING', 'APPROVED', 'PAID'])->default('PENDING');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('contact_name', 150);
            $table->string('company_name', 150)->nullable();
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->string('source', 50)->default('Website');
            $table->string('product_interest', 150)->nullable();
            $table->decimal('estimated_value', 15, 2)->default(0.00);
            $table->string('assigned_to', 100)->nullable();
            $table->enum('stage', ['NEW', 'CONTACTED', 'PROPOSAL', 'NEGOTIATION', 'WON', 'LOST'])->default('NEW');
            $table->date('next_follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('service_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->string('ticket_no', 50);
            $table->string('customer_name', 150);
            $table->string('phone', 30);
            $table->string('product_name', 150);
            $table->string('serial_number', 100)->nullable();
            $table->text('complaint_details');
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM');
            $table->enum('status', ['OPEN', 'ASSIGNED', 'IN_PROGRESS', 'WAITING', 'RESOLVED', 'CLOSED'])->default('OPEN');
            $table->string('assigned_technician', 100)->nullable();
            $table->date('created_date');
            $table->date('resolved_date')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'ticket_no']);
        });

        Schema::create('service_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_ticket_id')->constrained('service_tickets')->onDelete('cascade');
            $table->date('visit_date');
            $table->string('technician_name', 100);
            $table->text('findings');
            $table->text('action_taken');
            $table->text('parts_used')->nullable();
            $table->decimal('service_charges', 15, 2)->default(0.00);
            $table->string('customer_signature_name', 100)->nullable();
            $table->enum('status', ['COMPLETED', 'PENDING_PARTS', 'ESCALATED'])->default('COMPLETED');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_visits');
        Schema::dropIfExists('service_tickets');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('dealer_commissions');
        Schema::dropIfExists('dealers');
    }
};
