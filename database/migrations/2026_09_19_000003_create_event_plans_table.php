<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_master_id')->constrained('event_masters')->cascadeOnDelete();
            $table->string('name', 120);
            $table->decimal('price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_plans');
    }
};
