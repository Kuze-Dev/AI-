<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Domain\Page\Models\PageSlice
 *
 * @property int $id
 * @property int $slice_id
 * @property int $page_id
 * @property array|null $data
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Page\Models\Slice $slice
 * @method static Builder|SliceContent newModelQuery()
 * @method static Builder|SliceContent newQuery()
 * @method static Builder|SliceContent ordered(string $direction = 'asc')
 * @method static Builder|SliceContent query()
 * @method static Builder|SliceContent whereCreatedAt($value)
 * @method static Builder|SliceContent whereData($value)
 * @method static Builder|SliceContent whereId($value)
 * @method static Builder|SliceContent whereOrder($value)
 * @method static Builder|SliceContent wherePageId($value)
 * @method static Builder|SliceContent whereSliceId($value)
 * @method static Builder|SliceContent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SliceContent extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = [
        'slice_id',
        'data',
        'order',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /** @return BelongsTo<Slice, SliceContent> */
    public function slice(): BelongsTo
    {
        return $this->belongsTo(Slice::class);
    }

    /** @return Builder<SliceContent> */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('page_id', $this->page_id);
    }
}
