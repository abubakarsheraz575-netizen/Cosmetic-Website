<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Announcement extends Model
{
    protected $fillable = ['title', 'message', 'type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function scopeForCustomer(Builder $query, int $userId): Builder
    {
        $purchasedCategoryIds = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.user_id', $userId)
            ->where('orders.payment_status', 'paid')
            ->where('orders.status', '!=', 'cancelled')
            ->whereNotNull('products.category_id')
            ->select('products.category_id');

        return $query
            ->where('announcements.is_active', true)
            ->whereHas('categories', fn (Builder $categories) => $categories->whereIn('categories.id', $purchasedCategoryIds))
            ->with('categories:id,name')
            ->latest('announcements.created_at');
    }
}
