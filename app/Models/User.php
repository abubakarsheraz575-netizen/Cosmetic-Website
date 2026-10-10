<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;   // ← ADD
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_blocked', 'phone', 'skin_type', 'hair_type', 'shade_match', 'loves'])]
class User extends Authenticatable implements MustVerifyEmail   // ← ADD implements
{
    use HasFactory, Notifiable;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',   // ← make sure this exists
            'password'          => 'hashed',
            'is_blocked'        => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function supportConversation(): HasOne
    {
        return $this->hasOne(SupportConversation::class);
    }

    public function customerCarts(): HasMany
    {
        return $this->hasMany(CustomerCart::class);
    }

    public function submittedProducts()
    {
        return $this->hasMany(Product::class, 'submitted_by');
    }

    public function marketplaceWithdrawals()
    {
        return $this->hasMany(MarketplaceWithdrawal::class, 'vendor_id');
    }

    public function marketplaceMessages()
    {
        return $this->hasMany(MarketplaceMessage::class, 'vendor_id');
    }

    /* ══════════ BRANDED EMAILS (Cosmetics teal theme) ══════════ */

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
