<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportConversation extends Model
{
    protected $fillable = [
        'user_id',
        'last_message_at',
        'user_last_seen_at',
        'admin_last_seen_at',
        'user_typing_until',
        'admin_typing_until',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'user_last_seen_at' => 'datetime',
            'admin_last_seen_at' => 'datetime',
            'user_typing_until' => 'datetime',
            'admin_typing_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(SupportMessage::class, 'conversation_id')->latestOfMany();
    }
}
