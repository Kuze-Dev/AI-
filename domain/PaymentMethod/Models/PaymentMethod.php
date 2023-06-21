<?php

namespace Domain\PaymentMethod\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'gateway',
        'subtitle',
        'status',
        'description',
        'credentials',
    ];

    protected $casts = [
        'credentials' => 'array',
        'status' => 'bool',
    ];


}
