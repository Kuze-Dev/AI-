<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Site\Traits\Sites;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Page\Models\Block
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $component
 * @property bool $is_fixed_content
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Page\Models\BlockContent> $blockContents
 * @property-read int|null $block_contents_count
 * @property-read \Domain\Blueprint\Models\Blueprint $blueprint
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Block newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block query()
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereComponent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereIsFixedContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteRestrict(['blockContents'])]
class Block extends Model implements HasMedia
{
    use ConstraintsRelationships;

    /** @use InteractsWithMedia<\Spatie\MediaLibrary\MediaCollections\Models\Media> */
    use InteractsWithMedia;

    use LogsActivity;
    use Sites;

    protected $fillable = [
        'blueprint_id',
        'name',
        'component',
        'is_fixed_content',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_fixed_content' => 'bool',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, $this> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Page\Models\BlockContent, $this> */
    public function blockContents(): HasMany
    {
        return $this->hasMany(BlockContent::class);
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->registerMediaConversions(fn () => $this->addMediaConversion('original'));
    }
}
