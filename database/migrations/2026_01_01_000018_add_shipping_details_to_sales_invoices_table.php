<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('customer_ledger_id');
            $table->string('shipping_phone', 50)->nullable()->after('shipping_name');
            $table->string('shipping_email')->nullable()->after('shipping_phone');
            $table->string('shipping_gstin', 20)->nullable()->after('shipping_email');
            $table->text('shipping_address')->nullable()->after('shipping_gstin');
            $table->string('shipping_city', 100)->nullable()->after('shipping_address');
            $table->string('shipping_state', 100)->nullable()->after('shipping_city');
            $table->string('shipping_state_code', 10)->nullable()->after('shipping_state');
            $table->string('shipping_pincode', 20)->nullable()->after('shipping_state_code');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_phone',
                'shipping_email',
                'shipping_gstin',
                'shipping_address',
                'shipping_city',
                'shipping_state',
                'shipping_state_code',
                'shipping_pincode',
            ]);
        });
    }
};
