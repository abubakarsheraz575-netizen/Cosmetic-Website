<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('delay');   /* delay | info | good */
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* ⭐ Dynamic shipping settings — defaults */
        \App\Models\Setting::set('free_ship_over', '50');
        \App\Models\Setting::set('ship_cost', '4.99');
        \App\Models\Setting::set('delivery_min_days', '5');
        \App\Models\Setting::set('delivery_max_days', '7');
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_notices');
    }
};