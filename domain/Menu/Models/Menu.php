<?php

declare(strict_types=1);

namespace Domain\Menu\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'title',
        'schema'
    ];

    protected $casts = [
        'schema' => 'array'
    ];
}
