<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The current production table has:
     *     number VARCHAR(255) NOT NULL
     *
     * The mobile GPS flow intentionally allows House / Flat / Plot Number
     * to be empty and sends number = null. Therefore the column must allow
     * NULL, otherwise MySQL throws "Column 'number' cannot be null".
     */
    public function up(): void
    {
        if (!Schema::hasColumn('addresses', 'number')) {
            return;
        }

        /*
         * Raw ALTER is deliberately used for MySQL/Hostinger compatibility
         * and avoids requiring doctrine/dbal on older Laravel projects.
         */
        DB::statement(
            'ALTER TABLE `addresses` MODIFY `number` VARCHAR(255) NULL'
        );
    }

    public function down(): void
    {
        if (!Schema::hasColumn('addresses', 'number')) {
            return;
        }

        /*
         * Convert any NULL created after this migration before restoring
         * the old NOT NULL definition.
         */
        DB::table('addresses')
            ->whereNull('number')
            ->update(['number' => '']);

        DB::statement(
            "ALTER TABLE `addresses` MODIFY `number` VARCHAR(255) NOT NULL"
        );
    }
};
