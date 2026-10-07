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
        Schema::table('leads', function (Blueprint $table) {
            $table->text('notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->string('payout_reference')->nullable();
        });

        Schema::table('site_settings', function (Blueprint $table) {
            // Lets the admin close realtor sign-ups instantly (e.g. during an incident).
            $table->boolean('realtor_registration_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('leads', fn (Blueprint $t) => $t->dropColumn(['notes', 'contacted_at']));
        Schema::table('sales', fn (Blueprint $t) => $t->dropColumn(['reference', 'notes']));
        Schema::table('commissions', fn (Blueprint $t) => $t->dropColumn('payout_reference'));
        Schema::table('site_settings', fn (Blueprint $t) => $t->dropColumn('realtor_registration_enabled'));
    }
};
