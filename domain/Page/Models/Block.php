<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use Domain\Blueprint\Models\Blueprint;
use Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Domain\Page\Models\Block
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Page\Models\BlockContent> $blockContents
 * @property-read int|null $block_contents_count
 * @property-read Blueprint|null $blueprint
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|Block newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block query()
 * @mixin \Eloquent
 */
#[OnDeleteRestrict(['blockContents'])]
class Block extends Model implements HasMedia
{
    use LogsActivity;
    use ConstraintsRelationships;
    use InteractsWithMedia;

    protected $fillable = [
        'blueprint_id',
        'name',
        'component',
        'is_fixed_content',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'is_fixed_content' => 'bool',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return BelongsTo<Blueprint, Block> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return HasMany<BlockContent> */
    public function blockContents(): HasMany
    {
        return $this->hasMany(BlockContent::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();
    }
}
