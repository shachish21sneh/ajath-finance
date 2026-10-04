<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_id')->unique()->constrained('products')->onDelete('cascade');
            $table->enum('product_type', ['SOLAR_PANEL', 'INVERTER', 'HYBRID_INVERTER', 'BATTERY', 'ACDB', 'DCDB', 'MC4', 'CABLE', 'STRUCTURE'])->default('SOLAR_PANEL');
            $table->decimal('wattage', 8, 2)->nullable(); // e.g. 550W, 580W, 3300W
            $table->decimal('voltage', 8, 2)->nullable();
            $table->decimal('efficiency_percent', 5, 2)->nullable();
            $table->unsignedSmallInteger('warranty_years')->default(25);
            $table->timestamps();
        });

        Schema::create('solar_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('customer_name', 150);
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->decimal('estimated_kw', 8, 2)->default(5.00);
            $table->string('source', 50)->default('Direct Inquiry');
            $table->enum('status', ['NEW', 'CONTACTED', 'SURVEY_SCHEDULED', 'QUOTED', 'NEGOTIATION', 'WON', 'LOST'])->default('NEW');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('solar_site_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('solar_lead_id')->nullable()->constrained('solar_leads')->onDelete('set null');
            $table->foreignId('customer_ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->date('survey_date');
            $table->string('surveyor_name', 100);
            $table->enum('roof_type', ['RCC Flat', 'Metal Sheet', 'Sloped Tile', 'Ground Mounted'])->default('RCC Flat');
            $table->decimal('roof_area_sqft', 10, 2);
            $table->decimal('shadow_free_area_sqft', 10, 2);
            $table->decimal('tilt_angle', 5, 2)->default(28.00);
            $table->string('orientation', 50)->default('South-Facing');
            $table->decimal('sanctioned_load_kw', 8, 2);
            $table->decimal('monthly_consumption_kwh', 10, 2);
            $table->enum('phase', ['Single Phase', 'Three Phase'])->default('Three Phase');
            $table->string('consumer_number', 50)->nullable();
            $table->string('discom_name', 100)->nullable();
            $table->decimal('recommended_capacity_kw', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('solar_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('solar_lead_id')->nullable()->constrained('solar_leads')->onDelete('set null');
            $table->foreignId('customer_ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->string('quotation_no', 50);
            $table->date('quotation_date');
            $table->decimal('system_capacity_kw', 8, 2);
            $table->string('panel_model', 150)->nullable();
            $table->unsignedInteger('panel_qty')->default(0);
            $table->string('inverter_model', 150)->nullable();
            $table->unsignedInteger('inverter_qty')->default(1);
            $table->string('battery_model', 150)->nullable();
            $table->unsignedInteger('battery_qty')->default(0);
            $table->string('structure_type', 100)->default('Elevated Galvanized Iron');
            $table->decimal('system_cost', 15, 2);
            $table->decimal('gst_amount', 15, 2)->default(0.00);
            $table->decimal('subsidy_amount', 15, 2)->default(0.00);
            $table->decimal('net_payable', 15, 2);
            $table->decimal('estimated_monthly_gen_units', 10, 2)->default(0.00);
            $table->decimal('payback_years', 4, 1)->default(3.5);
            $table->enum('status', ['DRAFT', 'SENT', 'ACCEPTED', 'REJECTED'])->default('SENT');
            $table->timestamps();

            $table->unique(['company_id', 'quotation_no']);
        });

        Schema::create('solar_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_ledger_id')->constrained('ledgers')->onDelete('restrict');
            $table->foreignId('quotation_id')->nullable()->constrained('solar_quotations')->onDelete('set null');
            $table->string('project_code', 50);
            $table->string('project_name', 150);
            $table->text('site_address');
            $table->decimal('capacity_kw', 8, 2);
            $table->decimal('total_project_cost', 15, 2);
            $table->enum('subsidy_status', ['NOT_APPLICABLE', 'APPLIED', 'APPROVED', 'DISBURSED'])->default('APPLIED');
            $table->enum('net_metering_status', ['APPLIED', 'FEASIBILITY_APPROVED', 'METER_INSTALLED', 'COMMISSIONED'])->default('APPLIED');
            $table->enum('installation_status', ['NOT_STARTED', 'MATERIAL_DISPATCHED', 'STRUCTURE_COMPLETED', 'WIRING_COMPLETED', 'COMMISSIONED'])->default('NOT_STARTED');
            $table->date('start_date')->nullable();
            $table->date('commissioning_date')->nullable();
            $table->string('installer_lead', 100)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'project_code']);
        });

        Schema::create('solar_amcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('solar_project_id')->constrained('solar_projects')->onDelete('cascade');
            $table->string('amc_code', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('annual_fee', 15, 2)->default(0.00);
            $table->unsignedSmallInteger('visits_per_year')->default(4);
            $table->unsignedSmallInteger('visits_completed')->default(0);
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'RENEWED', 'TERMINATED'])->default('ACTIVE');
            $table->timestamps();

            $table->unique(['company_id', 'amc_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_amcs');
        Schema::dropIfExists('solar_projects');
        Schema::dropIfExists('solar_quotations');
        Schema::dropIfExists('solar_site_surveys');
        Schema::dropIfExists('solar_leads');
        Schema::dropIfExists('solar_products');
    }
};
