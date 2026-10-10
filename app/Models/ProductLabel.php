<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductLabel extends Model
{
    public const TYPES = ['hot', 'new', 'sale'];

    protected $fillable = ['name', 'type'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_product_label');
    }
}
