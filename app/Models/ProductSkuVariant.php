<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductSkuVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'barcode', 'stock'];

    protected function casts(): array
    {
        return ['stock' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_sku_variant_options',
            'product_sku_variant_id',
            'product_variant_id'
        )->with(['attribute']);
    }
}
