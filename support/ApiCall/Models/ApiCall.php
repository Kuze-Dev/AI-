<?php

declare(strict_types=1);

namespace Support\ApiCall\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCall extends Model
{
    protected $fillable = [
        'request_url',
        'request_type',
        'request_response',
    ];

    protected function casts(): array
    {
        return [
            'request_response' => 'array',
        ];
    }
}
