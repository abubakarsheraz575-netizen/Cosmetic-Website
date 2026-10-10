<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'order_id', 'product_id', 'product_sku_variant_id', 'product_name', 'product_price',
    'qty', 'line_total', 'emoji', 'product_sku', 'product_barcode', 'variant_options',
    'shipping_weight', 'weight_unit', 'shipping_length', 'shipping_width',
    'shipping_height', 'dimension_unit',
])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'product_price' => 'decimal:2',
            'line_total'    => 'decimal:2',
            'qty'           => 'integer',
            'variant_options' => 'array',
            'shipping_weight' => 'decimal:3',
            'shipping_length' => 'decimal:2',
            'shipping_width' => 'decimal:2',
            'shipping_height' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function skuVariant()
    {
        return $this->belongsTo(ProductSkuVariant::class, 'product_sku_variant_id');
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }
}