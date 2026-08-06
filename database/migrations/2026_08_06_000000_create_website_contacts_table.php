<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 150)->nullable();
            $table->string('phone', 25);
            $table->string('subject', 180)->nullable();
            $table->string('service', 100)->nullable();
            $table->text('message');
            $table->string('status', 30)->default('new')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('website_contacts'); }
};
