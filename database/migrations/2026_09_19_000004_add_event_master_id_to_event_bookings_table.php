<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_bookings', function (Blueprint $table) {
            $table->foreignId('event_master_id')
                ->nullable()
                ->after('user_id')
                ->constrained('event_masters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_master_id');
        });
    }
};
