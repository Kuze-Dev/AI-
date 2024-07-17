<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Arr;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Taxonomy\DataTransferObjects\TaxonomyTermData;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;

class SyncTermTreeAction
{
    use SanitizeBlueprintDataTrait;

    protected Taxonomy $taxonomy;

    public function __construct(
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

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

        /** @var Blueprint|null */
        $blueprint = Blueprint::whereId($this->taxonomy->blueprint_id)->first();

        if (! $blueprint) {
            abort(422, 'Cannot Access Blueprint '.$this->taxonomy->blueprint_id);
        }

        $sanitizeData = $this->sanitizeBlueprintData(
            $termData->data,
            $blueprint->schema->getFieldStatekeys()
        );

        $term->fill([
            'name' => $termData->name,
            'parent_id' => $parentTerm?->id,
            'data' => $sanitizeData,
        ])->save();

        if ($termData->url) {
            $this->createOrUpdateRouteUrl->execute($term, new RouteUrlData($termData->url, $termData->is_custom));
        }
        /** @var TaxonomyTerm */
        $model = TaxonomyTerm::with('taxonomy')->where('id', $term->id)->first();

        $this->updateBlueprintDataAction->execute($model);

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
