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

    protected $casts = [
        'request_response' => 'array',
    ];
    
}
