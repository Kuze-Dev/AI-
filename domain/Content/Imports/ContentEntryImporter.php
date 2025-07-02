<?php

declare(strict_types=1);

namespace Domain\Content\Imports;

use App\Features\CMS\Internationalization;
use App\Features\CMS\SitesManagement;
use Domain\Content\Actions\CreateContentEntryAction;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Site\Models\Site;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Support\RouteUrl\Models\RouteUrl;

/**
 * @property-read ContentEntry $record
 */
class ContentEntryImporter extends Importer
{
    protected static ?string $model = ContentEntry::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [

            ImportColumn::make('content')
                ->example('blog')
                ->rules(['required', 'exists:contents,slug'])
                ->requiredMapping(),

            ImportColumn::make('title')
                ->example('My Blog Post')
                ->requiredMapping()
                ->rules(['required', function (
                    string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator
                ) {

                    if (! TenantFeatureSupport::someAreActive([
                        SitesManagement::class,
                        Internationalization::class,
                    ])) {

                        if (ContentEntry::where('title', $value)
                            ->whereHas('content', fn ($e) => $e->where('slug', $validator->getData()['content']))
                            ->exists()) {

                            Notification::make()
                                ->title(trans('Content Entry Import Error'))
                                ->body(fn () => trans('the title :value is already been used.', ['value' => $value]))
                                ->danger()
                                ->when(config('queue.default') === 'sync',
                                    fn (Notification $notification) => $notification
                                        ->persistent()
                                        ->send(),
                                    fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                );

                            $fail('The title is already been used for this contentEntry.');
                        }
                    }

                }]),

            ImportColumn::make('route_url')
                ->example('/blog/my-blog-post')
                ->rules(
                    ['nullable',
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
                    ])
                ->helperText('if null route_url will be generated based on title'),

            ImportColumn::make('published_at')
                ->example('05/15/2025')
                ->rules(['nullable']),

            ImportColumn::make('data')
                ->helperText('JSON data for the content entry')
                ->rules([
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
                ])
                ->requiredMapping(),

            ImportColumn::make('status')
                ->example('1')
                ->helperText('if disabled just set to null or empty')
                ->rules(['nullable'])
                ->requiredMapping(),

            ImportColumn::make('locale')
                ->example('en')
                ->rules(['nullable', 'string']),

            ImportColumn::make('sites')
                ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator): void {
                        if (is_null($value) && TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {

                            Notification::make()
                                ->title(trans('Content Entry Import Error'))
                                ->body('Sites field is required.')
                                ->danger()
                                ->when(config('queue.default') === 'sync',
                                    fn (Notification $notification) => $notification
                                        ->persistent()
                                        ->send(),
                                    fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                );

                            $fail('Sites field is required.');
                        }

                        if (! is_null($value)) {
                            $siteIDs = Site::whereIn('domain', explode(',', $value))->pluck('id')->toArray();

                            if (count($siteIDs) !== count(explode(',', $value))) {

                                Notification::make()
                                    ->title(trans('Content Entry Import Error'))
                                    ->body("Item from Site list ( {$value} ) Not Found.")
                                    ->danger()
                                    ->when(config('queue.default') === 'sync',
                                        fn (Notification $notification) => $notification
                                            ->persistent()
                                            ->send(),
                                        fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                    );

                                $fail("Site {$value} not found.");
                            }
                        }

                    },
                ])
                ->requiredMapping(
                    fn () => TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)
                ),

            ImportColumn::make('taxonomy_terms')
                ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator): void {

                        if (! is_null($value)) {
                            $taxonomy_term_Ids = TaxonomyTerm::whereIn('slug', explode(',', $value))->pluck('id')->toArray();

                            if (count($taxonomy_term_Ids) !== count(explode(',', $value))) {

                                Notification::make()
                                    ->title(trans('Taxonomy Term Import Error'))
                                    ->body("Taxonomy Term ( {$value} ) Not Found.")
                                    ->danger()
                                    ->when(config('queue.default') === 'sync',
                                        fn (Notification $notification) => $notification
                                            ->persistent()
                                            ->send(),
                                        fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                    );

                                $fail("Taxonomy Term {$value} not found.");
                            }
                        }

                    },
                ]),

        ];
    }

    #[\Override]
    public function resolveRecord(): ContentEntry
    {
        // if (is_null($this->data['slug'])) {
        return new ContentEntry;
        // }

        // return ContentEntry::where('slug', $this->data['slug'])->first() ?? new ContentEntry;
    }

    #[\Override]
    public function fillRecord(): void
    {
        /** Disabled Filament Built in Record Creation Handle the Content
         * Creation thru Domain Level Action
         */
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function saveRecord(): void
    {

        if ($this->record->exists) {
            return;
        }

        /** @var array $siteIDs */
        $siteIDs = (array_key_exists('sites', $this->data) && ! is_null($this->data['sites'])) ?
            Site::whereIn('domain', explode(',', $this->data['sites']))->pluck('id')->toArray() :
            [];

        /** @var array $taxonomyIds */
        $taxonomyIds = (array_key_exists('taxonomy_terms', $this->data) && ! is_null($this->data['taxonomy_terms'])) ?
            TaxonomyTerm::whereIn('slug', explode(',', $this->data['taxonomy_terms']))->pluck('id')->toArray() :
             [];

        $publiishedat = now()->parse($this->data['published_at']);

        $contentEntryData = ContentEntryData::fromArray([
            'title' => $this->data['title'],
            'locale' => $this->data['locale'] ?? null,
            'route_url' => [
                'url' => $this->data['route_url'] ?? '/'.$this->data['content'].'/'.Str::slug($this->data['title']),
                'is_override' => ! is_null($this->data['route_url']),

            ],
            'author_id' => filament_admin()->id,
            'published_at' => $publiishedat,
            'status' => $this->data['status'] ? true : false,
            'meta_data' => [
                'title' => $this->data['title'],
                'description' => $this->data['title'],
            ],
            'data' => $this->data['data'] ? json_decode($this->data['data'], true) : [],
            'sites' => $siteIDs,
            'taxonomy_terms' => $taxonomyIds,
        ]);

        $content = Content::where('slug', $this->data['content'])->firstorfail();

        try {

            app(CreateContentEntryAction::class)->execute($content, $contentEntryData);

        } catch (\Throwable $th) {

            // Delete the latest content entry related to this content
            $latestEntry = $content->contentEntries()->latest('id')->first();

            if ($latestEntry) {
                $latestEntry->delete();
            }

            Notification::make()
                ->danger()
                ->title(trans('Import Error'))
                ->body(trans('There was an error while importing the content entry on :entry_title , '.$th->getMessage(),
                    [
                        'entry_title' => $this->data['title'],
                    ]
                )
                )->sendToDatabase(filament_admin());

            $this->import->delete();

        }

    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Content import has completed and '.
            number_format($import->successful_rows).' '.
            Str::of('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.Str::of('row')
                ->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
