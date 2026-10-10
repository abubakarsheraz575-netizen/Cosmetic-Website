<?php
// app/Models/Size.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Size extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'ml', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected function sizeTier(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(
            fn (): string => $this->getSizeTier()
        );
    }

    public function getSizeTier(): string
    {
        return match (true) {
            $this->ml <= 50 => 'Small',
            $this->ml <= 250 => 'Medium',
            default => 'Large',
        };
    }
}