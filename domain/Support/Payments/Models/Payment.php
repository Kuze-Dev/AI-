<?php

declare(strict_types=1);

namespace Domain\Support\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Eloquent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasUuids;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'id',
        'payable_type',
        'payable_id',
        'payment_method_id',
        'gateway',
        'amount',
        'status',
        'transaction_id'
    ];

    protected $with = [
        'media',
    ];

    /** @return MorphTo<Model, self> */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();
    }
}
