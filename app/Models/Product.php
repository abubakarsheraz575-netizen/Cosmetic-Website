<?php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'category_id', 'brand_id', 'size_id',
        'price', 'discount', 'discount_type', 'sale_price', 'sale_discount', 'sale_discount_type', 'sale_starts_at', 'sale_ends_at',
        'sku', 'barcode', 'shipping_weight', 'weight_unit', 'shipping_length',
        'shipping_width', 'shipping_height', 'dimension_unit', 'stock', 'emoji', 'is_active',
        'submitted_by', 'review_status', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price'     => 'float',
            'discount'  => 'float',
            'discount_type' => 'string',
            'sale_price' => 'float',
            'sale_discount' => 'float',
            'sale_discount_type' => 'string',
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
            'shipping_weight' => 'float',
            'shipping_length' => 'float',
            'shipping_width' => 'float',
            'shipping_height' => 'float',
            'reviewed_at' => 'datetime',
        ];
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function size()     { return $this->belongsTo(Size::class); }
    public function images() { return $this->hasMany(ProductImage::class)->orderByDesc('is_cover')->orderBy('sort_order'); }
    public function variants() { return $this->hasMany(ProductVariant::class)->with('attribute'); }
    public function skuVariants(): HasMany { return $this->hasMany(ProductSkuVariant::class); }
    public function specificationValues(): HasMany { return $this->hasMany(ProductSpecificationValue::class)->with('attribute'); }
    public function labels() { return $this->belongsToMany(ProductLabel::class, 'product_product_label'); }
    public function collections() { return $this->belongsToMany(ProductCollection::class, 'product_collection_items'); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function submitter() { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function reviews()
{
    return $this->hasMany(Review::class);
}

    public function getFinalPriceAttribute(): float
    {
        $now = now();
        $starts = $this->sale_starts_at;
        $ends = $this->sale_ends_at;
        $saleIsActive = (! $starts || $starts->lte($now)) && (! $ends || $ends->gt($now));

        if ($this->sale_price !== null) {
            if ($saleIsActive) {
                return round(max(0, $this->sale_price), 2);
            }
        }

        if ($this->sale_discount !== null && $saleIsActive) {
            return $this->priceAfterDiscount($this->sale_discount, $this->sale_discount_type);
        }

        return $this->priceAfterDiscount($this->discount, $this->discount_type);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->final_price < round((float) $this->price, 2);
    }

    private function priceAfterDiscount(?float $discount, ?string $type): float
    {
        $discountAmount = $type === 'fixed'
            ? (float) $discount
            : (float) $this->price * (float) $discount / 100;

        return round(max(0, $this->price - $discountAmount), 2);
    }

}