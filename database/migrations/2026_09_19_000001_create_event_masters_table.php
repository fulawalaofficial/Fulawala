<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('starting_price', 12, 2)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_masters');
    }
};
