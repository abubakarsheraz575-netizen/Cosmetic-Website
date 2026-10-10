<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('skin_type')->nullable()->after('is_blocked');
            $table->string('hair_type')->nullable()->after('skin_type');
            $table->string('shade_match')->nullable()->after('hair_type');
            $table->string('loves')->nullable()->after('shade_match');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'skin_type', 'hair_type', 'shade_match', 'loves']);
        });
    }
};