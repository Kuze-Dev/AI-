<?php

declare(strict_types=1);

namespace Support\MetaData\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Support\MetaData\Models\MetaData
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string|null $title
 * @property string|null $author
 * @property string|null $description
 * @property string|null $keywords
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Model|Eloquent $model
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData query()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class MetaData extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'title',
        'author',
        'description',
        'keywords',
    ];

    protected $with = [
        'media',
    ];

    /** @return MorphTo<Model, self> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->registerMediaConversions(fn () => $this->addMediaConversion('original'));
    }
}
