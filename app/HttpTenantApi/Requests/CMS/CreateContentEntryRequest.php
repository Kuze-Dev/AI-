<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\CMS;

use App\Features\CMS\Internationalization;
use App\Features\CMS\SitesManagement;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Site\Models\Site;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Support\RouteUrl\Models\RouteUrl;

class CreateContentEntryRequest extends FormRequest
{
    /**
     * The authenticated user.
     */
    protected ?\Domain\Admin\Models\Admin $user = null;

    public function __construct()
    {
        parent::__construct();

        /** @var \Domain\Admin\Models\Admin|null */
        $admin = Auth::user();

        $this->user = $admin;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ($this->user?->hasPermissionTo('contentEntry.create', 'admin-api') ||
            $this->user?->hasRole(config()->string('domain.role.super_admin'))) ? true : false;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'exists:contents,slug'],
            'title' => ['required', function (
                string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator
            ) {

                if (! TenantFeatureSupport::someAreActive([
                    SitesManagement::class,
                    Internationalization::class,
                ])) {

                    if (ContentEntry::where('title', $value)
                        ->whereHas('content', fn ($e) => $e->where('slug', $validator->getData()['content']))
                        ->exists()) {

                        $fail('The title is already been used for this contentEntry.');
                    }
                } elseif (TenantFeatureSupport::active(Internationalization::class) && TenantFeatureSupport::inactive(SitesManagement::class)) {
                    if (ContentEntry::where('title', $value)
                        ->where('locale', $validator->getData()['locale'] ?? 'en')
                        ->whereHas('content', fn ($e) => $e->where('slug', $validator->getData()['content']))
                        ->exists()) {

                        $fail('The title is already been used for this contentEntry.');
                    }
                }

            }],
            'route_url' => ['nullable',
                'string',
                function (
                    string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator
                ): void {

                    if (! is_null($value)) {

                        $ignoreModel_ids = [];

                        if (TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {

                            if (is_null($validator->getData()['sites'])) {
                                $fail('Sites field is required.');

                                return;
                            }

                            $content_slug = $validator->getData()['content'];

                            $content = Cache::remember(
                                "content_slug_{$content_slug}",
                                now()->addMinutes(15),
                                fn () => Content::with('blueprint')->where('slug', $content_slug)->firstorfail()
                            );

                            $ignoreModel_ids = ContentEntry::where('content_id', '!=', $content->id)
                                ->wherehas('routeUrls', fn ($query) => $query->where('url', $value)
                                )
                                ->whereHas('sites',
                                    fn ($query) => $query->whereNotIN('domain', explode(',', $validator->getData()['sites'])
                                    )
                                )->get()->pluck('id')->toArray();

                        }

                        $query = RouteUrl::whereUrl($value)
                            ->whereIn(
                                'id',
                                RouteUrl::query()
                                    ->select('id')
                                    ->where(
                                        'updated_at',
                                        fn ($query) => $query->select(DB::raw('MAX(`updated_at`)'))
                                            ->from((new RouteUrl)->getTable(), 'sub_query_table')
                                            ->whereColumn('sub_query_table.model_type', 'route_urls.model_type')
                                            ->whereColumn('sub_query_table.model_id', 'route_urls.model_id')
                                    )
                            );

                        if (! empty($ignoreModel_ids)) {
                            $query->whereNot(
                                function ($query) use ($ignoreModel_ids): EloquentBuilder {
                                    return $query
                                        ->where('model_type', app(ContentEntry::class)->getMorphClass())
                                        ->whereIn('model_id', $ignoreModel_ids);
                                }
                            );

                        }

                        if ($query->exists()) {
                            $fail(trans('The :value is already been used.', ['value' => $value]));
                        }
                    }

                },
            ],
            'status' => ['nullable'],
            'locale' => ['nullable', 'string'],
            'published_at' => ['nullable'],
            'data' => ['nullable',
                function (
                    string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator
                ) {
                    if (! is_null($value)) {

                        $content_slug = $validator->getData()['content'];

                        $content = Cache::remember(
                            "content_slug_{$content_slug}",
                            now()->addMinutes(15),
                            fn () => Content::with('blueprint')->where('slug', $content_slug)->firstorfail()
                        );

                        $decodedData = json_decode($value, true);

                        if (! is_array($decodedData)) {
                            $fail('The data field must be a valid JSON object.');

                            return;
                        }

                        $sectionValidator = Validator::make($decodedData, $content->blueprint->schema->getStrictValidationRules());

                        if ($sectionValidator->fails()) {
                            foreach ($sectionValidator->errors()->all() as $errorMessage) {
                                $fail($errorMessage);
                            }
                        }

                    }

                },
            ],
            'sites' => [
                function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator): void {
                    if (is_null($value) && TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {

                        $fail('Sites field is required.');
                    }

                    if (! is_null($value)) {
                        $siteIDs = Site::whereIn('domain', explode(',', $value))->pluck('id')->toArray();

                        if (count($siteIDs) !== count(explode(',', $value))) {

                            $fail("Site {$value} not found.");
                        }
                    }

                },
            ],
            'taxonomy_terms' => [
                function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator): void {

                    if (! is_null($value)) {
                        $taxonomy_term_Ids = TaxonomyTerm::whereIn('slug', explode(',', $value))->pluck('id')->toArray();

                        if (count($taxonomy_term_Ids) !== count(explode(',', $value))) {

                            $fail("Taxonomy Term {$value} not found.");
                        }
                    }

                },
            ],
        ];

    }
}
