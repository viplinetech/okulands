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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'realtor', 'downliner'])->default('downliner')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->string('referral_code')->unique()->after('phone');
            $table->foreignId('referred_by')->nullable()->after('referral_code')
                ->constrained('users')->nullOnDelete();
            $table->decimal('commission_rate_override', 5, 2)->nullable()->after('referred_by');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('commission_rate_override');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn(['role', 'phone', 'referral_code', 'commission_rate_override', 'status']);
        });
    }
};
