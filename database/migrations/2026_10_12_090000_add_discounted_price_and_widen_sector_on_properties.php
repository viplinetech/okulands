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
        // Change sector from a 3-value enum to a plain varchar so any service slug
        // (real-estate, construction, agriculture, title-verification, …) can be stored.
        // Existing records keep their current values ('real_estate' etc.) and sectorLabel()
        // handles both old underscore keys and new hyphen service slugs transparently.
        // MySQL uses MODIFY COLUMN; SQLite (test env) already accepts any string in a "enum" column.
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE properties MODIFY COLUMN sector VARCHAR(100) NOT NULL DEFAULT 'real_estate'");
        } else {
            Schema::table('properties', function (Blueprint $table) {
                $table->string('sector', 100)->default('real_estate')->change();
            });
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('discounted_price', 15, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('discounted_price');
        });
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE properties MODIFY COLUMN sector ENUM('real_estate','construction','agriculture') NOT NULL");
        }
    }
};
