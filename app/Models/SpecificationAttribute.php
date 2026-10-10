<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecificationAttribute extends Model
{
    protected $fillable = ['category_id', 'name', 'type', 'options', 'is_required', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'sort_order' => 'integer',
            'options' => 'array',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productValues()
    {
        return $this->hasMany(ProductSpecificationValue::class, 'specification_attribute_id');
    }
}
