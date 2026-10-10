<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCart extends Model
{
    protected $fillable = ['user_id', 'items', 'last_activity'];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'last_activity' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
