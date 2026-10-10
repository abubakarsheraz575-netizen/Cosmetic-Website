<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['announcement_id', 'category_id']);
            $table->index(['category_id', 'announcement_id']);
        });

        $associations = DB::table('announcement_product')
            ->join('products', 'products.id', '=', 'announcement_product.product_id')
            ->whereNotNull('products.category_id')
            ->select('announcement_product.announcement_id', 'products.category_id')
            ->distinct()
            ->get();

        foreach ($associations as $association) {
            DB::table('announcement_category')->insertOrIgnore([
                'announcement_id' => $association->announcement_id,
                'category_id' => $association->category_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::dropIfExists('announcement_product');
    }

    public function down(): void
    {
        Schema::create('announcement_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['announcement_id', 'product_id']);
            $table->index(['product_id', 'announcement_id']);
        });

        DB::table('announcement_category')
            ->join('products', 'products.category_id', '=', 'announcement_category.category_id')
            ->select('announcement_category.announcement_id', 'products.id as product_id')
            ->distinct()
            ->orderBy('announcement_category.announcement_id')
            ->each(function ($association) {
                DB::table('announcement_product')->insertOrIgnore([
                    'announcement_id' => $association->announcement_id,
                    'product_id' => $association->product_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::dropIfExists('announcement_category');
    }
};
