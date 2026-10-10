<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('description', 320)->nullable();
            $table->longText('content');
            $table->string('status', 20)->default('draft')->index();
            $table->string('template', 24)->default('default');
            $table->string('image_path', 500)->nullable();
            $table->string('breadcrumb_style', 16)->default('start');
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_pages');
    }
};
