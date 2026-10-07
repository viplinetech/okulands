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
        // Second photo for the crossfading About image on the home page (the first is about_image).
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('about_image_2')->nullable()->after('about_image');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('about_image_2');
        });
    }
};
