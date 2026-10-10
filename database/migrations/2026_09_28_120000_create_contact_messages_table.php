<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('email', 150);
                $table->string('subject', 150)->nullable();
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->text('admin_reply')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamps();
                $table->index(['read_at', 'created_at']);
            });

            return;
        }

        Schema::table('contact_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }
            if (! Schema::hasColumn('contact_messages', 'admin_reply')) {
                $table->text('admin_reply')->nullable();
            }
            if (! Schema::hasColumn('contact_messages', 'replied_at')) {
                $table->timestamp('replied_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('contact_messages', 'status')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $table->dropColumn(['read_at', 'admin_reply', 'replied_at']);
            });

            return;
        }

        Schema::dropIfExists('contact_messages');
    }
};
