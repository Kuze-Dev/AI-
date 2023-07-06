<?php

declare(strict_types=1);

namespace Domain\Cart\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

#[OnDeleteCascade(['cart_lines'])]
class Cart extends Model
{
    use HasFactory;
    use ConstraintsRelationships;

    protected $fillable = [
        'customer_id',
    ];

    public function cart_lines()
    {
        return $this->hasMany(CartLine::class);
    }
}
