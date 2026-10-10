<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_manual_payment')->default(false)->index();
            $table->text('payment_note')->nullable();
            $table->foreignId('payment_recorded_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_recorded_by');
            $table->dropColumn(['is_manual_payment', 'payment_note']);
        });
    }
};
