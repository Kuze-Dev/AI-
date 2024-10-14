<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Arr;
use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Internationalization\Actions\HandleDataTranslation;
use Domain\Internationalization\Actions\HandleUpdateDataTranslation;
use Domain\Internationalization\Models\Locale;
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
        protected CreateBlueprintDataAction $createBlueprintDataAction,
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
            $termIds[] = $this->createOrUpdateTerm($this->taxonomy, $term, $parentTerm)->id;
        }

        TaxonomyTerm::setNewOrder($termIds);

        if (
            tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)
        ) {
            $i = 0;
            
            foreach ($termIds as $term_id) {
                $i++;
                $taxTerm = TaxonomyTerm::find($term_id);

                if ($taxTerm->translation_id) {
                    $taxTermCollection = $taxTerm->dataTranslation()
                        ->orwhere('id', $taxTerm->translation_id)
                        ->orwhere('translation_id', $taxTerm->translation_id)
                        ->get();
                }else{
                    $taxTermCollection = $taxTerm->dataTranslation()
                        ->orwhere('id', $taxTerm->translation_id)
                        ->get();
                }
               
                

                foreach ($taxTermCollection as $tax_term_item) {
                    
                    $tax_term_item->order = $taxTerm->order;
                    $tax_term_item->save();
                } 

            }


        }
    }

    protected function createOrUpdateTerm(Taxonomy $taxonomy, TaxonomyTermData $termData, ?TaxonomyTerm $parentTerm = null): TaxonomyTerm
    {

        $term = $taxonomy->taxonomyTerms()->where('id', $termData->id)->first();

        if ($term) {

            $updated_term = $this->updateTaxonomyTerm($term, $termData, $parentTerm);

            $this->updateBlueprintDataAction->execute($updated_term);

            if (
                tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)
            ) {
                app(HandleUpdateDataTranslation::class)->execute($updated_term, $termData);
            }

        } else {

            $term = $this->createTaxonomyTerm($taxonomy, $termData, $parentTerm);

            $this->createBlueprintDataAction->execute($term);

            if (
                tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)
            ) {

                if ($term->translation_id) {

                    $parentModel = TaxonomyTerm::find($term->translation_id)->firstorFail();

                    $termTranslation = $term;

                    app(HandleDataTranslation::class)->execute($parentModel, $termTranslation);
                } else {

                    if ($taxonomy->translation_id) {
                        $taxonomyCollection = $taxonomy->dataTranslation()
                            ->orwhere('id', $taxonomy->translation_id)
                            ->orwhere('translation_id', $taxonomy->translation_id)
                            ->get();
                    } else {

                        $taxonomyCollection = $taxonomy->dataTranslation;
                    }

                    $defaultLocale = Locale::where('is_default', true)->first()?->code;

                    $listLocaleCodes = Locale::all()->pluck('code')->toArray();

                    foreach ($taxonomyCollection as $taxonomy_item) {

                        if ($taxonomy->id == $taxonomy_item->id) {
                            continue;
                        }

                        $termUrl = $termData->is_custom ?
                                '/'.$taxonomy_item->locale.$termData->url :
                              '/'.implode('/', array_diff(
                                  explode('/', trim(parse_url($termData->url, PHP_URL_PATH), '/')), $listLocaleCodes));

                        $newTaxonomyTermData = new TaxonomyTermData(
                            name: $termData->name,
                            data: $termData->data,
                            is_custom: $termData->is_custom,
                            url: $defaultLocale == $taxonomy_item->locale ? $termUrl : "/$taxonomy_item->locale$termUrl",
                            translation_id: (string) $term->id
                            // children: $taxonomy
                        );

                        $new_parent_term = $parentTerm ?
                        $taxonomy_item->taxonomyTerms()->where('translation_id', $parentTerm->id)->first() :
                        null;

                        $translation_term = $this->createTaxonomyTerm($taxonomy_item, $newTaxonomyTermData, $new_parent_term);

                        if ($newTaxonomyTermData->url) {
                            $this->createOrUpdateRouteUrl->execute($translation_term, new RouteUrlData($newTaxonomyTermData->url, $newTaxonomyTermData->is_custom));
                        }

                        $this->createBlueprintDataAction->execute($translation_term);

                        app(HandleDataTranslation::class)->execute($term, $translation_term);

                    }

                }

            }

        }

        if ($termData->url) {
            $this->createOrUpdateRouteUrl->execute($term, new RouteUrlData($termData->url, $termData->is_custom));
        }

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

    protected function createTaxonomyTerm(
        Taxonomy $taxonomy, TaxonomyTermData $termData, ?TaxonomyTerm $parentTerm = null
    ): TaxonomyTerm {
        /** @var Blueprint|null */
        $blueprint = Blueprint::whereId($taxonomy->blueprint_id)->first();

        if (! $blueprint) {
            abort(422, 'Cannot Access Blueprint '.$taxonomy->blueprint_id);
        }

        $sanitizeData = $this->sanitizeBlueprintData(
            $termData->data,
            $blueprint->schema->getFieldStatekeys()
        );

        return $taxonomy->taxonomyTerms()->create([
            'name' => $termData->name,
            'parent_id' => $parentTerm?->id,
            'data' => $sanitizeData,
            'translation_id' => $termData->translation_id,
        ]);

    }

    protected function updateTaxonomyTerm(
        TaxonomyTerm $taxonomyTerm, TaxonomyTermData $termData, ?TaxonomyTerm $parentTerm = null
    ): TaxonomyTerm {
        $taxonomy = $taxonomyTerm->taxonomy;

        /** @var Blueprint|null */
        $blueprint = Blueprint::whereId($taxonomy->blueprint_id)->first();

        if (! $blueprint) {
            abort(422, 'Cannot Access Blueprint '.$taxonomy->blueprint_id);
        }

        $sanitizeData = $this->sanitizeBlueprintData(
            $termData->data,
            $blueprint->schema->getFieldStatekeys()
        );

        $taxonomyTerm->update([
            'name' => $termData->name,
            'parent_id' => $parentTerm?->id,
            'data' => $sanitizeData,
            'translation_id' => $termData->translation_id,
        ]);

        $taxonomyTerm->refresh();

        if (
            tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class) &&
            $parentTerm) {

            if ($taxonomy->translation_id) {
                $taxonomyCollection = $taxonomy->dataTranslation()
                    ->orwhere('id', $taxonomy->translation_id)
                    ->orwhere('translation_id', $taxonomy->translation_id)
                    ->get();
            } else {

                $taxonomyCollection = $taxonomy->dataTranslation;
            }

            $parentClusterIdentifier = array_filter(
                [$parentTerm->id, $parentTerm->translation_id ?? null],
                fn ($value) => ! (is_null($value) || empty($value))
            );

            $termClusterIdentifier = array_filter(
                [$taxonomyTerm->id, $taxonomyTerm->translation_id ?? null],
                fn ($value) => ! (is_null($value) || empty($value))
            );

            $parentTranslationClusterIds = TaxonomyTerm::whereIN('id', $parentClusterIdentifier)
                ->orWhereIN('translation_id', $parentClusterIdentifier)->get()->pluck('id');

            $termTranslationClusterIds = TaxonomyTerm::whereIN('id', $termClusterIdentifier)
                ->orWhereIN('translation_id', $termClusterIdentifier)->get()->pluck('id');

            foreach (
                $taxonomyCollection as $taxonomy_item
            ) {

                if ($taxonomy_item->id == $this->taxonomy->id) {
                    continue;
                }

                $taxonomy_item->load('taxonomyTerms');

                $translation_parent = $taxonomy_item->taxonomyTerms->whereIn('id', $parentTranslationClusterIds)->first();
                $translation_term = $taxonomy_item->taxonomyTerms->whereIn('id', $termTranslationClusterIds)->first();

                $translation_term->update([
                    'parent_id' => $translation_parent->id,
                ]);

            }

        }

        return $taxonomyTerm;

    }
}
