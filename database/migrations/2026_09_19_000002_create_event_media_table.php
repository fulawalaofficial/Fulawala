<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_master_id')->constrained('event_masters')->cascadeOnDelete();
            $table->string('media_type', 20)->index(); // photo | video
            $table->string('path')->nullable();
            $table->text('external_url')->nullable();
            $table->string('title')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_media');
    }
};
