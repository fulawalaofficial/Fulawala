<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('addresses', 'latitude')) {
            Schema::table('addresses', function (Blueprint $table): void {
                $table
                    ->decimal('latitude', 10, 7)
                    ->nullable()
                    ->after('is_default');
            });
        }

        if (!Schema::hasColumn('addresses', 'longitude')) {
            Schema::table('addresses', function (Blueprint $table): void {
                $table
                    ->decimal('longitude', 10, 7)
                    ->nullable()
                    ->after('latitude');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('addresses', 'longitude')) {
            Schema::table('addresses', function (Blueprint $table): void {
                $table->dropColumn('longitude');
            });
        }

        if (Schema::hasColumn('addresses', 'latitude')) {
            Schema::table('addresses', function (Blueprint $table): void {
                $table->dropColumn('latitude');
            });
        }
    }
};
