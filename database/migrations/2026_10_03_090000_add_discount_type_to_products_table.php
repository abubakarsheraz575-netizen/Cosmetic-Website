<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->default(0)->change();
            $table->string('discount_type', 16)->default('percent')->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->decimal('discount', 5, 2)->default(0)->change();
        });
    }
};
