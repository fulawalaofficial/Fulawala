<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('address_type')->default('home');
                $table->string('name');
                $table->string('number');
                $table->text('address');
                $table->string('city');
                $table->string('state');
                $table->string('pincode');
                $table->string('landmark')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });

            return;
        }

        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index();
            }

            if (!Schema::hasColumn('addresses', 'address_type')) {
                $table->string('address_type')->default('home');
            }

            if (!Schema::hasColumn('addresses', 'name')) {
                $table->string('name')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'number')) {
                $table->string('number')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'address')) {
                $table->text('address')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'city')) {
                $table->string('city')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'state')) {
                $table->string('state')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'pincode')) {
                $table->string('pincode')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'landmark')) {
                $table->string('landmark')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'is_default')) {
                $table->boolean('is_default')->default(false);
            }

            if (!Schema::hasColumn('addresses', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        //
    }
};