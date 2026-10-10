<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->string('shortcode', 32)->unique();
            $table->string('title', 180);
            $table->string('subtitle', 180)->nullable();
            $table->string('button_label', 60)->nullable();
            $table->string('target_url', 2048);
            $table->string('image_url', 2048);
            $table->string('tablet_image_url', 2048)->nullable();
            $table->boolean('opens_new_tab')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
