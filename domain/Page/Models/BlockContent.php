<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use Domain\Blueprint\Models\BlueprintData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Domain\Page\Models\BlockContent
 *
 * @property int $id
 * @property int $block_id
 * @property int $page_id
 * @property mixed|null $data
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Page\Models\Block $block
 *
 * @method static Builder|BlockContent newModelQuery()
 * @method static Builder|BlockContent newQuery()
 * @method static Builder|BlockContent ordered(string $direction = 'asc')
 * @method static Builder|BlockContent query()
 * @method static Builder|BlockContent whereBlockId($value)
 * @method static Builder|BlockContent whereCreatedAt($value)
 * @method static Builder|BlockContent whereData($value)
 * @method static Builder|BlockContent whereId($value)
 * @method static Builder|BlockContent whereOrder($value)
 * @method static Builder|BlockContent wherePageId($value)
 * @method static Builder|BlockContent whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class BlockContent extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = [
        'block_id',
        'page_id',
        'data',
        'order',
    ];

    protected $with = [
        'block',
    ];

    /** @return BelongsTo<Block, self> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class)->with(['blueprint']);
    }

    /** @return MorphMany<BlueprintData> */
    public function blueprintData(): MorphMany
    {
        return $this->morphMany(BlueprintData::class, 'model');
    }

    /** @return Builder<BlockContent> */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('page_id', $this->page_id);
    }

    /** @return Attribute<array|null, static> */
    protected function data(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->block->is_fixed_content ? $this->block->data : json_decode($value ?? '', true),
            set: fn (?array $value) => $this->block->is_fixed_content ? null : json_encode($value)
        );
    }
}
