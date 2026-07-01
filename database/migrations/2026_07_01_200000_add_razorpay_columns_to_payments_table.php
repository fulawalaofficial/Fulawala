<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->after('amount');
            }

            if (!Schema::hasColumn('payments', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            }

            if (!Schema::hasColumn('payments', 'razorpay_signature')) {
                $table->text('razorpay_signature')->nullable()->after('razorpay_payment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'razorpay_signature')) {
                $table->dropColumn('razorpay_signature');
            }

            if (Schema::hasColumn('payments', 'razorpay_payment_id')) {
                $table->dropColumn('razorpay_payment_id');
            }

            if (Schema::hasColumn('payments', 'razorpay_order_id')) {
                $table->dropColumn('razorpay_order_id');
            }
        });
    }
};