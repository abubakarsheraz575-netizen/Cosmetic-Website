<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'product_attribute_id', 'value', 'description', 'color_hex', 'image_path'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    public function skuVariants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductSkuVariant::class,
            'product_sku_variant_options',
            'product_variant_id',
            'product_sku_variant_id'
        );
    }
}
