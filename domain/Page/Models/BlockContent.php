<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Domain\Page\Models\BlockContent
 *
 * @property-read \Domain\Page\Models\Block|null $block
 * @method static Builder|BlockContent newModelQuery()
 * @method static Builder|BlockContent newQuery()
 * @method static Builder|BlockContent ordered(string $direction = 'asc')
 * @method static Builder|BlockContent query()
 * @mixin \Eloquent
 */
class BlockContent extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = [
        'block_id',
        'data',
        'order',
    ];

    protected $with = [
        'block',
    ];

    /** @return BelongsTo<Block, self> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
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
