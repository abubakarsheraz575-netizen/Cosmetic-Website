<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('discount_type', 16);
            $table->decimal('discount_value', 10, 2);
            $table->unsignedInteger('usage_limit');
            $table->unsignedInteger('uses_count')->default(0);
            $table->unsignedInteger('reserved_count')->default(0);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->decimal('percentage', 7, 4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('is_active');
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('coupon_code', 32);
            $table->decimal('discount_amount', 10, 2);
            $table->string('status', 16)->default('reserved');
            $table->dateTime('redeemed_at')->nullable();
            $table->timestamps();
            $table->index(['coupon_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            $table->string('coupon_code', 32)->nullable()->after('discount');
            $table->decimal('tax_total', 10, 2)->default(0)->after('shipping');
            $table->json('tax_breakdown')->nullable()->after('tax_total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount', 'coupon_code', 'tax_total', 'tax_breakdown']);
        });

        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('coupons');
    }
};
