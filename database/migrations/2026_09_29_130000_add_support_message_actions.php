<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->foreignId('reply_to_message_id')
                ->nullable()
                ->constrained('support_messages')
                ->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->softDeletes();
        });

        Schema::create('support_message_stars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_message_id')->constrained('support_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['support_message_id', 'user_id']);
        });

        Schema::create('support_message_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_message_id')->constrained('support_messages')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 1000);
            $table->timestamps();
            $table->unique(['support_message_id', 'reporter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_message_reports');
        Schema::dropIfExists('support_message_stars');

        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reply_to_message_id');
            $table->dropColumn(['edited_at', 'is_pinned', 'deleted_at']);
        });
    }
};
