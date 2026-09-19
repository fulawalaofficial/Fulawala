<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table): void {
            if (!Schema::hasColumn('payments', 'razorpay_order_id')) {
                $table->string('razorpay_order_id', 100)->nullable()->after('amount');
            }

            if (!Schema::hasColumn('payments', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id', 100)->nullable()->after('razorpay_order_id');
            }

            if (!Schema::hasColumn('payments', 'razorpay_signature')) {
                $table->string('razorpay_signature', 255)->nullable()->after('razorpay_payment_id');
            }

            if (!Schema::hasColumn('payments', 'payment_status')) {
                $table->string('payment_status', 30)->default('Pending')->after('razorpay_signature');
            }
        });
    }

    public function down(): void
    {
        /*
         * Intentionally left empty because this migration is supplied as a
         * compatibility migration for production databases that may already
         * contain some of these columns.
         */
    }
};
