<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add GPS columns required by the Address model.
     *
     * Both columns are nullable because:
     * - Auto/GPS address can save latitude + longitude.
     * - Manual address can be saved without GPS.
     */
    public function up(): void
    {
        $hasLatitude = Schema::hasColumn('addresses', 'latitude');
        $hasLongitude = Schema::hasColumn('addresses', 'longitude');

        if (!$hasLatitude || !$hasLongitude) {
            Schema::table('addresses', function (Blueprint $table) use (
                $hasLatitude,
                $hasLongitude
            ) {
                if (!$hasLatitude) {
                    $table
                        ->decimal('latitude', 10, 7)
                        ->nullable()
                        ->after('landmark');
                }

                if (!$hasLongitude) {
                    $table
                        ->decimal('longitude', 10, 7)
                        ->nullable()
                        ->after('latitude');
                }
            });
        }

        /*
         * If the columns already exist, make sure they can store NULL.
         *
         * This is required for the Manual Entry flow where GPS is optional.
         * Laravel 10/11 can normally change these columns directly.
         */
        if (Schema::hasColumn('addresses', 'latitude')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table
                    ->decimal('latitude', 10, 7)
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('addresses', 'longitude')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table
                    ->decimal('longitude', 10, 7)
                    ->nullable()
                    ->change();
            });
        }
    }

    /**
     * Roll back only the GPS columns introduced/managed by this migration.
     */
    public function down(): void
    {
        $columns = [];

        if (Schema::hasColumn('addresses', 'latitude')) {
            $columns[] = 'latitude';
        }

        if (Schema::hasColumn('addresses', 'longitude')) {
            $columns[] = 'longitude';
        }

        if ($columns !== []) {
            Schema::table('addresses', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
