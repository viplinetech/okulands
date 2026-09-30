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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('sector', ['real_estate', 'construction', 'agriculture']);
            $table->string('type')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('location');
            $table->string('bedrooms')->nullable();
            $table->string('bathrooms')->nullable();
            $table->string('size')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['available', 'reserved', 'sold'])->default('available');
            $table->boolean('featured')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
