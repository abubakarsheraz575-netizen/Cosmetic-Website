<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 32)->nullable()->unique();
            $table->string('barcode', 32)->nullable()->unique();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->dateTime('sale_starts_at')->nullable()->index();
            $table->dateTime('sale_ends_at')->nullable()->index();
            $table->decimal('shipping_weight', 10, 3)->nullable();
            $table->string('weight_unit', 8)->default('g');
            $table->decimal('shipping_length', 10, 2)->nullable();
            $table->decimal('shipping_width', 10, 2)->nullable();
            $table->decimal('shipping_height', 10, 2)->nullable();
            $table->string('dimension_unit', 8)->default('cm');
        });

        DB::table('products')->orderBy('id')->chunkById(500, function ($products): void {
            foreach ($products as $product) {
                DB::table('products')->where('id', $product->id)->update([
                    'sku' => 'COS-'.str_pad((string) $product->id, 8, '0', STR_PAD_LEFT),
                    'barcode' => $this->internalBarcode((int) $product->id),
                ]);
            }
        });

        Schema::create('product_sku_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 48)->unique();
            $table->string('barcode', 48)->unique();
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'stock']);
        });

        Schema::create('product_sku_variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_sku_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unique(
                ['product_sku_variant_id', 'product_variant_id'],
                'sku_variant_option_unique'
            );
            $table->index('product_variant_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_sku_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_sku', 48)->nullable();
            $table->string('product_barcode', 48)->nullable();
            $table->json('variant_options')->nullable();
            $table->decimal('shipping_weight', 10, 3)->nullable();
            $table->string('weight_unit', 8)->nullable();
            $table->decimal('shipping_length', 10, 2)->nullable();
            $table->decimal('shipping_width', 10, 2)->nullable();
            $table->decimal('shipping_height', 10, 2)->nullable();
            $table->string('dimension_unit', 8)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_sku_variant_id');
            $table->dropColumn([
                'product_sku',
                'product_barcode',
                'variant_options',
                'shipping_weight',
                'weight_unit',
                'shipping_length',
                'shipping_width',
                'shipping_height',
                'dimension_unit',
            ]);
        });

        Schema::dropIfExists('product_sku_variant_options');
        Schema::dropIfExists('product_sku_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropUnique(['barcode']);
            $table->dropIndex(['sale_starts_at']);
            $table->dropIndex(['sale_ends_at']);
            $table->dropColumn([
                'sku',
                'barcode',
                'sale_price',
                'sale_starts_at',
                'sale_ends_at',
                'shipping_weight',
                'weight_unit',
                'shipping_length',
                'shipping_width',
                'shipping_height',
                'dimension_unit',
            ]);
        });
    }

    private function internalBarcode(int $id): string
    {
        $body = '20'.str_pad((string) $id, 10, '0', STR_PAD_LEFT);
        $sum = 0;
        for ($index = 0; $index < 12; $index++) {
            $sum += (int) $body[$index] * ($index % 2 === 0 ? 1 : 3);
        }

        return $body.((10 - ($sum % 10)) % 10);
    }
};
