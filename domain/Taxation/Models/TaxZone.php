<?php

declare(strict_types=1);

namespace Domain\Taxation\Models;

use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Support\ConstraintsRelationships\ConstraintsRelationships;

class TaxZone extends Model
{
    use LogsActivity;
    use ConstraintsRelationships;

    protected $fillable = [
        'name',
        'price_display',
        'is_active',
        'is_default',
        'type',
        'percentage',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'price_display' => PriceDisplay::class,
        'type' => TaxZoneType::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(''); // TODO: Set relations
    }

    public function states(): BelongsToMany
    {
        return $this->belongsToMany(''); // TODO: Set relations
    }
}
