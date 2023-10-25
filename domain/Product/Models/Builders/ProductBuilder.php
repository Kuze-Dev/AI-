<?php

declare(strict_types=1);

namespace Domain\Product\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of \Domain\Product\Models\Product
 *
 * @extends Builder<TModelClass>
 */
class ProductBuilder extends Builder
{
    /** @return self<\Domain\Product\Models\Product> */
    public function whereTaxonomyTerms(string $taxonomy, array $terms): self
    {
        return $this->whereHas(
            'taxonomyTerms',
            function (Builder $query) use ($taxonomy, $terms) {
                $query->whereIn('slug', $terms)
                    ->whereHas(
                        'taxonomy',
                        fn ($query) => $query->where('slug', $taxonomy)
                    );
            }
        );
    }
}
