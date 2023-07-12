<?php

declare(strict_types=1);

namespace Domain\Review\Models;

use Illuminate\Database\Eloquent\Model;


class Review extends Model
{


    protected $fillable = [
        'title',
        'ratings',
        'comment'
    ];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image');
    }
}
