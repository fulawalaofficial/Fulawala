<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasLatitude = Schema::hasColumn('addresses', 'latitude');
        $hasLongitude = Schema::hasColumn('addresses', 'longitude');

        if ($hasLatitude && $hasLongitude) {
            return;
        }

        Schema::table('addresses', function (Blueprint $table) use ($hasLatitude, $hasLongitude): void {
            if (!$hasLatitude) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (!$hasLongitude) {
                $table->decimal('longitude', 11, 7)->nullable();
            }
        });
    }

    public function down(): void
    {
        $columns = [];

        if (Schema::hasColumn('addresses', 'latitude')) {
            $columns[] = 'latitude';
        }

        if (Schema::hasColumn('addresses', 'longitude')) {
            $columns[] = 'longitude';
        }

        if ($columns === []) {
            return;
        }

        Schema::table('addresses', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
