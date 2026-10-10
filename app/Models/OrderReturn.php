<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['return_number', 'order_id', 'order_item_id', 'user_id', 'quantity', 'reason', 'status', 'admin_note'])]
class OrderReturn extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
