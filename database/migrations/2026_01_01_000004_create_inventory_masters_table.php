<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 50); // e.g. Pieces, Kilograms, Meters
            $table->string('symbol', 20); // e.g. PCS, KGS, MTR
            $table->unsignedTinyInteger('decimal_places')->default(0);
            $table->timestamps();
        });

        Schema::create('stock_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('stock_groups')->onDelete('cascade');
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('stock_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('stock_group_id')->nullable()->constrained('stock_groups')->onDelete('set null');
            $table->foreignId('stock_category_id')->nullable()->constrained('stock_categories')->onDelete('set null');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('tax_master_id')->nullable()->constrained('tax_masters')->onDelete('set null');
            $table->string('name', 200);
            $table->string('sku', 100)->nullable()->index();
            $table->string('barcode', 100)->nullable()->index();
            $table->enum('item_type', ['goods', 'service'])->default('goods');
            $table->string('hsn_code', 20)->nullable()->index();
            $table->string('sac_code', 20)->nullable()->index();
            $table->decimal('purchase_price', 15, 2)->default(0.00);
            $table->decimal('selling_price', 15, 2)->default(0.00);
            $table->decimal('mrp', 15, 2)->default(0.00);
            $table->decimal('opening_stock', 15, 2)->default(0.00);
            $table->decimal('opening_stock_valuation', 15, 2)->default(0.00);
            $table->decimal('current_stock', 15, 2)->default(0.00);
            $table->decimal('reorder_level', 15, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'item_type']);
            $table->index(['company_id', 'name']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->enum('movement_type', ['inward', 'outward', 'transfer_in', 'transfer_out', 'adjustment'])->default('inward');
            $table->decimal('quantity', 15, 2);
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->date('movement_date');
            $table->text('narration')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'product_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('stock_categories');
        Schema::dropIfExists('stock_groups');
        Schema::dropIfExists('units');
    }
};
