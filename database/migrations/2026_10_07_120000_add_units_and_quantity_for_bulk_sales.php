<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Null = a single, indivisible listing (a house, a plot sold whole). Set this for
            // bulk land sold off in units/plots, so the system can track how many remain.
            $table->unsignedInteger('units_total')->nullable()->after('price');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('property_id');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('units_total');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
