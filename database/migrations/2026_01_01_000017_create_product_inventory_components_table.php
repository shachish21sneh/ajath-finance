<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add has_inventory_components flag to products table if not exists
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'has_inventory_components')) {
                $table->boolean('has_inventory_components')->default(false)->after('description');
            }
        });

        // Create product_inventory_components table
        Schema::create('product_inventory_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('component_product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('hsn_code', 20)->nullable();
            $table->decimal('gst_rate', 5, 2)->default(0.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->timestamps();

            $table->index(['product_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inventory_components');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'has_inventory_components')) {
                $table->dropColumn('has_inventory_components');
            }
        });
    }
};
