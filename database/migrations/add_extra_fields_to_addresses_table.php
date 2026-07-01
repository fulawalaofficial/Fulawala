<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('addresses', 'address_type')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->string('address_type')->default('home');
            });
        }

        if (!Schema::hasColumn('addresses', 'name')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->string('name')->nullable();
            });
        }

        if (!Schema::hasColumn('addresses', 'number')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->string('number')->nullable();
            });
        }

        $oldColumns = [
            'house_no',
            'apartment_name',
            'flat_no',
            'temple_name',
            'contact_number',
        ];

        foreach ($oldColumns as $column) {
            if (Schema::hasColumn('addresses', $column)) {
                Schema::table('addresses', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('addresses', 'number')) {
                $table->dropColumn('number');
            }
        });
    }
};