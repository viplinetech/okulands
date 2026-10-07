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
            $table->string('hero_headline')->nullable();
            $table->text('hero_subheadline')->nullable();
            $table->text('about_headline')->nullable();
            $table->longText('about_body')->nullable();
            $table->string('about_image')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->json('values')->nullable();
            $table->json('commitments')->nullable();
            $table->json('stats')->nullable();
            $table->json('faqs')->nullable();
            $table->json('team')->nullable();
            $table->json('banners')->nullable();
            $table->string('office_hours')->nullable();
            $table->text('map_embed_url')->nullable();
            $table->string('rc_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_headline', 'hero_subheadline', 'about_headline', 'about_body', 'about_image',
                'mission', 'vision', 'values', 'commitments', 'stats', 'faqs', 'team', 'banners',
                'office_hours', 'map_embed_url', 'rc_number',
            ]);
        });
    }
};
