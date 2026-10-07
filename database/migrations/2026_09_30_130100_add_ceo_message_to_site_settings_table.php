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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('ceo_name')->nullable();
            $table->string('ceo_title')->nullable();
            $table->string('ceo_photo')->nullable();
            $table->longText('ceo_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['ceo_name', 'ceo_title', 'ceo_photo', 'ceo_message']);
        });
    }
};
