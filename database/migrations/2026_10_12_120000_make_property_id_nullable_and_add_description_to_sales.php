<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the foreign key + NOT NULL constraint so non-property sales (services,
        // construction, etc.) can be recorded and still credit the referring realtor.
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL requires dropping the FK before changing the column.
            DB::statement('ALTER TABLE sales DROP FOREIGN KEY sales_property_id_foreign');
            DB::statement('ALTER TABLE sales MODIFY COLUMN property_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE sales ADD CONSTRAINT sales_property_id_foreign FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE');
        } else {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('property_id')->nullable()->change();
            });
        }

        Schema::table('sales', function (Blueprint $table) {
            // Free-text label used when no property is linked, e.g. "Caterpillar hire – Awka".
            $table->string('description', 200)->nullable()->after('property_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE sales DROP FOREIGN KEY sales_property_id_foreign');
            DB::statement('ALTER TABLE sales MODIFY COLUMN property_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE sales ADD CONSTRAINT sales_property_id_foreign FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE');
        }
    }
};
