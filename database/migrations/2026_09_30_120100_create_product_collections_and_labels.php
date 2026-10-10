<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('product_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['product_collection_id', 'product_id']);
        });

        Schema::create('product_labels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
            $table->unique(['name', 'type']);
        });

        Schema::create('product_product_label', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_label_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['product_label_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_label');
        Schema::dropIfExists('product_labels');
        Schema::dropIfExists('product_collection_items');
        Schema::dropIfExists('product_collections');
    }
};
