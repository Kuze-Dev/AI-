<?php

declare(strict_types = 1);

namespace Domain\Collection\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionEntry extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'data',
        'collection_id',
        'order'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    /**
     * @return BelongsTo
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    } 
}