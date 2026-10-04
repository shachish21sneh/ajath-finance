<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Finished good
            $table->string('bom_code', 50);
            $table->string('bom_name', 150);
            $table->decimal('output_qty', 15, 2)->default(1.00);
            $table->decimal('labor_cost', 15, 2)->default(0.00);
            $table->decimal('overhead_cost', 15, 2)->default(0.00);
            $table->decimal('total_material_cost', 15, 2)->default(0.00);
            $table->decimal('total_unit_cost', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'bom_code']);
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained('products')->onDelete('cascade');
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->decimal('wastage_percent', 5, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('bom_id')->constrained('boms')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->string('order_no', 50);
            $table->date('order_date');
            $table->decimal('planned_qty', 15, 2);
            $table->decimal('completed_qty', 15, 2)->default(0.00);
            $table->decimal('scrap_qty', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->decimal('cost_per_unit', 15, 2)->default(0.00);
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'order_no']);
        });

        Schema::create('production_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->decimal('required_qty', 15, 2);
            $table->decimal('issued_qty', 15, 2)->default(0.00);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('job_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('gstin', 20)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('job_work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('job_worker_id')->constrained('job_workers')->onDelete('cascade');
            $table->string('order_no', 50);
            $table->date('order_date');
            $table->date('expected_return_date')->nullable();
            $table->decimal('charges', 15, 2)->default(0.00);
            $table->enum('status', ['issued', 'partially_received', 'completed', 'cancelled'])->default('issued');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'order_no']);
        });

        Schema::create('job_work_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_work_order_id')->constrained('job_work_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('issued_qty', 15, 2);
            $table->decimal('received_qty', 15, 2)->default(0.00);
            $table->decimal('scrap_qty', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_work_items');
        Schema::dropIfExists('job_work_orders');
        Schema::dropIfExists('job_workers');
        Schema::dropIfExists('production_materials');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('boms');
    }
};
