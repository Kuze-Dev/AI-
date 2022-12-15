<?php

declare(strict_types = 1);

namespace Domain\Collection\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class CollectionEntry extends Model implements IsActivitySubject
{
    use LogsActivity;

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
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logFillable()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }
    /**
     * @return BelongsTo
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
    
    /**
     * @param Activity $activity
     *
     * @return string
     */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Collection Entry: '.$this->id;
    }
}