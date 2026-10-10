<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpecificationValue extends Model
{
    protected $fillable = ['product_id', 'specification_attribute_id', 'value'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(SpecificationAttribute::class, 'specification_attribute_id');
    }
}
