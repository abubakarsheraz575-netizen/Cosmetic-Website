<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_message_user_hides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_message_id')->constrained('support_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['support_message_id', 'user_id']);
            $table->index(['user_id', 'support_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_message_user_hides');
    }
};
