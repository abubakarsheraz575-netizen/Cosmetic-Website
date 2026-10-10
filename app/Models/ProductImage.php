<?php
// app/Models/ProductImage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'sort_order', 'is_cover', 'is_legacy_cover'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean', 'is_legacy_cover' => 'boolean'];
    }

    public function product() { return $this->belongsTo(Product::class); }
}