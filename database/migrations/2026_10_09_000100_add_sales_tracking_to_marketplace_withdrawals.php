<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_withdrawals', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('vendor_id')->constrained()->nullOnDelete();
            $table->string('order_number')->nullable()->after('order_id');
            $table->string('payment_transaction_id')->nullable()->after('order_number');
            $table->timestamp('order_paid_at')->nullable()->after('payment_transaction_id');
            $table->decimal('sales_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('coupon_discount_share', 12, 2)->nullable()->after('sales_amount');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('coupon_discount_share');
            $table->decimal('commission_amount', 12, 2)->nullable()->after('commission_rate');
            $table->index(['vendor_id', 'order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_withdrawals', function (Blueprint $table) {
            $table->dropIndex(['vendor_id', 'order_id', 'status']);
            $table->dropConstrainedForeignId('order_id');
            $table->dropColumn([
                'order_number',
                'payment_transaction_id',
                'order_paid_at',
                'sales_amount',
                'coupon_discount_share',
                'commission_rate',
                'commission_amount',
            ]);
        });
    }
};
