<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_groups', function (Blueprint $table) {
            $table->string('hsn_code', 50)->nullable()->after('name');
            $table->foreignId('tax_master_id')->nullable()->after('hsn_code')->constrained('tax_masters')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('stock_groups', function (Blueprint $table) {
            $table->dropForeign(['tax_master_id']);
            $table->dropColumn(['hsn_code', 'tax_master_id']);
        });
    }
};
