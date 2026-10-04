<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battery_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_id')->unique()->constrained('products')->onDelete('cascade');
            $table->enum('chemistry', ['Lead Acid', 'Tubular', 'Lithium Ion', 'LiFePO4', 'E-Rickshaw', 'EV'])->default('LiFePO4');
            $table->decimal('nominal_voltage', 8, 2); // e.g. 12.8, 25.6, 51.2
            $table->decimal('capacity_ah', 8, 2); // e.g. 100, 150, 200
            $table->decimal('energy_wh', 10, 2)->default(0.00); // Voltage * Ah
            $table->string('bms_model', 100)->nullable();
            $table->decimal('max_charging_current', 8, 2)->default(50.00);
            $table->decimal('max_discharge_current', 8, 2)->default(100.00);
            $table->unsignedSmallInteger('warranty_months')->default(36);
            $table->unsignedSmallInteger('free_replacement_months')->default(24);
            $table->unsignedSmallInteger('pro_rata_months')->default(12);
            $table->string('cell_type', 100)->nullable(); // Prismatic 3.2V 100Ah
            $table->unsignedSmallInteger('cell_count')->default(16);
            $table->timestamps();
        });

        Schema::create('battery_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('battery_model_id')->constrained('battery_models')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('customer_ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->onDelete('set null');
            $table->string('serial_number', 100)->unique();
            $table->string('cell_batch_number', 100)->nullable();
            $table->date('mfg_date');
            $table->date('dispatch_date')->nullable();
            $table->enum('qc_status', ['PASSED', 'FAILED', 'PENDING'])->default('PASSED');
            $table->enum('current_status', ['IN_STOCK', 'DISPATCHED', 'INSTALLED', 'IN_SERVICE', 'REPLACED', 'SCRAPPED'])->default('IN_STOCK');
            $table->timestamps();

            $table->index(['company_id', 'serial_number']);
            $table->index(['company_id', 'current_status']);
        });

        Schema::create('battery_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battery_serial_id')->constrained('battery_serials')->onDelete('cascade');
            $table->date('test_date');
            $table->decimal('open_circuit_voltage', 8, 2);
            $table->decimal('pack_voltage', 8, 2);
            $table->decimal('internal_resistance_mohm', 8, 2); // mΩ
            $table->decimal('actual_capacity_ah', 8, 2);
            $table->decimal('specific_gravity', 6, 3)->nullable(); // for lead acid
            $table->boolean('charge_test_passed')->default(true);
            $table->boolean('discharge_test_passed')->default(true);
            $table->boolean('bms_comm_passed')->default(true);
            $table->enum('qc_result', ['PASS', 'FAIL'])->default('PASS');
            $table->string('technician_name', 100)->nullable();
            $table->string('certificate_no', 100)->unique()->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('battery_warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('battery_serial_id')->unique()->constrained('battery_serials')->onDelete('cascade');
            $table->foreignId('customer_ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->string('invoice_no', 50)->nullable();
            $table->date('purchase_date');
            $table->date('warranty_start_date');
            $table->date('warranty_end_date');
            $table->string('warranty_type', 50)->default('Standard Comprehensive');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'warranty_end_date']);
        });

        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('battery_warranty_id')->constrained('battery_warranties')->onDelete('cascade');
            $table->string('claim_no', 50)->unique();
            $table->date('claim_date');
            $table->text('complaint_description');
            $table->text('diagnosis')->nullable();
            $table->enum('action_taken', ['PENDING', 'REPAIR', 'REPLACEMENT', 'REJECTED'])->default('PENDING');
            $table->foreignId('replacement_serial_id')->nullable()->constrained('battery_serials')->onDelete('set null');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'CLOSED'])->default('PENDING');
            $table->date('resolution_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('battery_warranties');
        Schema::dropIfExists('battery_tests');
        Schema::dropIfExists('battery_serials');
        Schema::dropIfExists('battery_models');
    }
};
