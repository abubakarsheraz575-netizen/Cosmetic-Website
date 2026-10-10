<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('sale_discount', 10, 2)->nullable()->after('sale_price');
            $table->string('sale_discount_type', 8)->default('percent')->after('sale_discount');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['sale_discount', 'sale_discount_type']);
        });
    }
};
