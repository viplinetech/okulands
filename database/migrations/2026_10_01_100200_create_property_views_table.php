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
        // Counts how often a referred visitor looks at one property. Drives "hot lead" alerts:
        // a serious buyer who keeps coming back is flagged to the realtor who referred them.
        Schema::create('property_views', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_token');
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('views')->default(1);
            $table->timestamp('alerted_at')->nullable();
            $table->timestamps();

            $table->unique(['visitor_token', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_views');
    }
};
