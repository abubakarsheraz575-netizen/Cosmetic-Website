<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'status', 'source', 'consented_at'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime'];
    }
}
