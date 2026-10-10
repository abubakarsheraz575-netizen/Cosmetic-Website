<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table): void {
            $table->boolean('is_cover')->default(false);
            $table->boolean('is_legacy_cover')->default(false);
        });

        $imagePaths = collect(Storage::disk('public')->allFiles())
            ->filter(fn (string $path): bool => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'webp'], true))
            ->merge(DB::table('product_images')->whereNotNull('path')->pluck('path'))
            ->unique()
            ->values()
            ->all();
        if ($imagePaths === []) {
            return;
        }

        DB::table('products')->orderBy('id')->chunkById(200, function ($products) use ($imagePaths): void {
            foreach ($products as $product) {
                $path = $imagePaths[array_rand($imagePaths)];
                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => 0,
                    'is_cover' => true,
                    'is_legacy_cover' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('product_images')->where('is_legacy_cover', true)->delete();

        Schema::table('product_images', function (Blueprint $table): void {
            $table->dropColumn(['is_cover', 'is_legacy_cover']);
        });
    }
};
