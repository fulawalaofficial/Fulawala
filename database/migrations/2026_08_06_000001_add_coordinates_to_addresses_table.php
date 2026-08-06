<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            if (!Schema::hasColumn('addresses', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('landmark');
            }

            if (!Schema::hasColumn('addresses', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $columns = [];

            if (Schema::hasColumn('addresses', 'latitude')) {
                $columns[] = 'latitude';
            }

            if (Schema::hasColumn('addresses', 'longitude')) {
                $columns[] = 'longitude';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
