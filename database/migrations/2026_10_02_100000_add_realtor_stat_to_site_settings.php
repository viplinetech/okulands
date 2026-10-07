<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Settings for the automatic "Active realtors" counter (label, suffix, existing realtors to add, on/off). */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->json('realtor_stat')->nullable();
        });

        // The realtor figure is now counted automatically: drop any hand-typed "Active realtors" row.
        DB::table('site_settings')->whereNotNull('stats')->get(['id', 'stats'])->each(function ($row) {
            $kept = collect(json_decode($row->stats, true) ?: [])
                ->reject(fn ($s) => preg_match('/realtor/i', (string) ($s['label'] ?? '')))->values()->all();

            DB::table('site_settings')->where('id', $row->id)->update(['stats' => $kept ? json_encode($kept) : null]);
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('realtor_stat');
        });
    }
};
