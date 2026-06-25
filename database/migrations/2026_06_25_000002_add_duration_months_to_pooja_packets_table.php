<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pooja_packets', 'duration_months')) {
            Schema::table('pooja_packets', function (Blueprint $table) {
                $table->unsignedInteger('duration_months')->default(1)->after('package_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pooja_packets', 'duration_months')) {
            Schema::table('pooja_packets', function (Blueprint $table) {
                $table->dropColumn('duration_months');
            });
        }
    }
};