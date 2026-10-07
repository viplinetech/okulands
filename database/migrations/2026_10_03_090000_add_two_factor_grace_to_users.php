<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** When an admin's grace period for setting up two-factor ends (starts at their first sign-in without it). */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('two_factor_grace_ends_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('two_factor_grace_ends_at');
        });
    }
};
