<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Arr;
use Domain\Taxonomy\DataTransferObjects\TaxonomyTermData;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;

class SyncTermTreeAction
{
    protected Taxonomy $taxonomy;

    /** @param  array<TaxonomyTermData>  $taxonomyTermDataSet */
    public function execute(Taxonomy $taxonomy, array $taxonomyTermDataSet): Taxonomy
    {
        $this->taxonomy = $taxonomy;

        $this->pruneMissingTerms($taxonomyTermDataSet);

        $this->syncTerms($taxonomyTermDataSet);

        return $this->taxonomy;
    }

    /** @param  array<TaxonomyTermData>  $taxonomyTermDataSet */
    protected function pruneMissingTerms(array $taxonomyTermDataSet): void
    {
        $flatTerms = $this->flatMapTerms($taxonomyTermDataSet);

        $termsForPruning = $this->taxonomy->taxonomyTerms()
            ->whereNotIn('id', Arr::pluck($flatTerms, 'id'))
            ->get();

        foreach ($termsForPruning as $term) {
            $term->delete();
        }
    }

    /** @param  array<TaxonomyTermData>  $taxonomyTermDataSet */
    protected function syncTerms(array $taxonomyTermDataSet, ?TaxonomyTerm $parentTerm = null): void
    {
        $termIds = [];

        foreach ($taxonomyTermDataSet as $term) {
            $termIds[] = $this->createOrUpdateTerm($term, $parentTerm)->id;
        }

        TaxonomyTerm::setNewOrder($termIds);
    }

    protected function createOrUpdateTerm(TaxonomyTermData $termData, ?TaxonomyTerm $parentTerm = null): TaxonomyTerm
    {
        /** @var TaxonomyTerm $term */
        $term = $this->taxonomy->taxonomyTerms()->where('id', $termData->id)->firstOrNew();

        $term->fill([
            'name' => $termData->name,
            'parent_id' => $parentTerm?->id,
            'data' => $termData->data,
        ])->save();

        if (! empty($termData->children)) {
            $this->syncTerms($termData->children, $term);
        }

        return $term;
    }

    protected function flatMapTerms(array $taxonomyTermDataSet): array
    {
        return Arr::collapse(Arr::map($taxonomyTermDataSet, $this->inlineChildren(...)));
    }

    protected function inlineChildren(TaxonomyTermData $termData): array
    {
        if (! empty($termData->children)) {
            $children = Arr::map($termData->children, $this->inlineChildren(...));
        }

        return [$termData, ...Arr::collapse($children ?? [])];
    }
}
