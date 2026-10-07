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
            // The master switch: off means every page tells search engines not to index it, and
            // robots.txt disallows everything. On by default so a fresh install stays crawlable.
            $table->boolean('seo_indexing_enabled')->default(true);
            $table->string('seo_meta_title')->nullable();
            $table->string('seo_meta_description', 320)->nullable();
            $table->string('seo_meta_keywords', 500)->nullable();
            $table->string('google_site_verification')->nullable();
            $table->string('google_analytics_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['seo_indexing_enabled', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords', 'google_site_verification', 'google_analytics_id']);
        });
    }
};
