<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uqc_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('code', 20);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('uqc_id')->nullable()->after('symbol')->constrained('uqc_masters')->onDelete('set null');
        });

        // Seed official Indian GST Unique Quantity Codes (UQC)
        $standardUqcs = [
            ['code' => 'BAG', 'name' => 'Bags'],
            ['code' => 'BAL', 'name' => 'Bale'],
            ['code' => 'BDL', 'name' => 'Bundles'],
            ['code' => 'BKL', 'name' => 'Buckles'],
            ['code' => 'BOX', 'name' => 'Box'],
            ['code' => 'BTL', 'name' => 'Bottles'],
            ['code' => 'CAN', 'name' => 'Cans'],
            ['code' => 'CBM', 'name' => 'Cubic Meter'],
            ['code' => 'CCM', 'name' => 'Cubic Centimeter'],
            ['code' => 'CMS', 'name' => 'Centimeter'],
            ['code' => 'CTN', 'name' => 'Cartons'],
            ['code' => 'DOZ', 'name' => 'Dozen'],
            ['code' => 'DRM', 'name' => 'Drum'],
            ['code' => 'GGK', 'name' => 'Great Gross'],
            ['code' => 'GMS', 'name' => 'Grams'],
            ['code' => 'GRS', 'name' => 'Gross'],
            ['code' => 'GYD', 'name' => 'Gross Yards'],
            ['code' => 'KGS', 'name' => 'Kilograms'],
            ['code' => 'KLR', 'name' => 'Kilolitre'],
            ['code' => 'KME', 'name' => 'Kilometre'],
            ['code' => 'MLT', 'name' => 'Millilitre'],
            ['code' => 'MTR', 'name' => 'Meters'],
            ['code' => 'MTS', 'name' => 'Metric Ton'],
            ['code' => 'NOS', 'name' => 'Numbers'],
            ['code' => 'OTH', 'name' => 'Others'],
            ['code' => 'PAC', 'name' => 'Packs'],
            ['code' => 'PCS', 'name' => 'Pieces'],
            ['code' => 'PRS', 'name' => 'Pairs'],
            ['code' => 'QTL', 'name' => 'Quintal'],
            ['code' => 'ROL', 'name' => 'Rolls'],
            ['code' => 'SET', 'name' => 'Sets'],
            ['code' => 'SQF', 'name' => 'Square Feet'],
            ['code' => 'SQM', 'name' => 'Square Meters'],
            ['code' => 'SQY', 'name' => 'Square Yards'],
            ['code' => 'TBS', 'name' => 'Tablets'],
            ['code' => 'TGM', 'name' => 'Ten Grams'],
            ['code' => 'THD', 'name' => 'Thousands'],
            ['code' => 'TON', 'name' => 'Tonnes'],
            ['code' => 'TUB', 'name' => 'Tubes'],
            ['code' => 'UGS', 'name' => 'US Gallons'],
            ['code' => 'UNT', 'name' => 'Units'],
            ['code' => 'YDS', 'name' => 'Yards'],
        ];

        $now = now();
        $records = array_map(function ($item) use ($now) {
            return [
                'company_id' => null,
                'code' => $item['code'],
                'name' => $item['name'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $standardUqcs);

        DB::table('uqc_masters')->insert($records);
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['uqc_id']);
            $table->dropColumn('uqc_id');
        });

        Schema::dropIfExists('uqc_masters');
    }
};
