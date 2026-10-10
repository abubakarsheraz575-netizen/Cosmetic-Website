<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'percentage', 'is_active'])]
class TaxRate extends Model
{
    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
