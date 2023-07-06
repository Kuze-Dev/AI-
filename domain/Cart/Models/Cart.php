<?php

declare(strict_types=1);

namespace Domain\Cart\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
    ];

    public function cart_lines()
    {
        return $this->hasMany(CartLine::class);
    }
}
