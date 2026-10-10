<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('coupon_discount_type', 16)->nullable()->after('coupon_code');
            $table->decimal('coupon_discount_value', 10, 2)->nullable()->after('coupon_discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_discount_type', 'coupon_discount_value']);
        });
    }
};
