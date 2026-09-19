<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('event_bookings', 'event_master_id')) {
            Schema::table('event_bookings', function (Blueprint $table): void {
                $table->foreignId('event_master_id')
                    ->nullable()
                    ->constrained('event_masters')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('event_bookings', 'event_plan_id')) {
            Schema::table('event_bookings', function (Blueprint $table): void {
                $table->foreignId('event_plan_id')
                    ->nullable()
                    ->constrained('event_plans')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('event_bookings', 'address_id')) {
            Schema::table('event_bookings', function (Blueprint $table): void {
                $table->foreignId('address_id')
                    ->nullable()
                    ->constrained('addresses')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('event_bookings', 'address_id')) {
            Schema::table('event_bookings', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('address_id');
            });
        }

        if (Schema::hasColumn('event_bookings', 'event_plan_id')) {
            Schema::table('event_bookings', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('event_plan_id');
            });
        }

        if (Schema::hasColumn('event_bookings', 'event_master_id')) {
            Schema::table('event_bookings', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('event_master_id');
            });
        }
    }
};
