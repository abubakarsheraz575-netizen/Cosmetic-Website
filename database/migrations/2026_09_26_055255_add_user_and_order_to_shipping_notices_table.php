<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_notices', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id');   /* null = global notice */
            $table->foreignId('order_id')->nullable()->after('user_id'); /* kis order ka notice */
        });
    }

    public function down(): void
    {
        Schema::table('shipping_notices', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'order_id']);
        });
    }
};