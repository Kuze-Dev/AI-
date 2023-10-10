<?php

declare(strict_types=1);

namespace Support\RouteUrl\Rules;

use Support\RouteUrl\Contracts\HasRouteUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class MicroSiteUniqueRouteUrlRule implements ValidationRule
{
    public function __construct(
        protected readonly ?HasRouteUrl $ignoreModel = null,
        protected readonly array $route_url,
    ) {
    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $pages = Page::select('id')->wherehas('sites', function ($q) use ($value) {
            return $q->whereIn('site_id', $value);
        })->wherehas('routeUrls', function ($r) {
            return $r->where('url', $this->route_url['url']);
        })->pluck('id')->toArray();

        $contentEntriesIds = ContentEntry::select('id')->wherehas('sites', function ($q) use ($value) {
            return $q->whereIn('site_id', $value);
        })->wherehas('routeUrls', function ($r) {
            return $r->where('url', $this->route_url['url']);
        })->pluck('id')->toArray();

        $pagesIds = array_merge($pages, $contentEntriesIds);

        $query = RouteUrl::whereUrl($this->route_url['url'])->whereIn('model_id', $pagesIds);

        if ($this->ignoreModel) {

            if ($this->ignoreModel->parentPage) {

                $ignoreModelIds = [
                    $this->ignoreModel->getKey(),
                    $this->ignoreModel->parentPage->getKey(),
                ];

            } else {

                $ignoreModelIds = [
                    $this->ignoreModel->getKey(),
                    $this->ignoreModel->pageDraft?->getKey() ?: null,
                ];

            }

            $query->whereNot(fn (EloquentBuilder $query) => $query
                ->where('model_type',  $this->ignoreModel->getMorphClass())
                ->whereIn('model_id', array_filter($ignoreModelIds)));

        }

        if ($query->exists()) {
            $fail(trans('The :value is already been used.', ['value' => $this->route_url['url']]));
        }
    }
}
