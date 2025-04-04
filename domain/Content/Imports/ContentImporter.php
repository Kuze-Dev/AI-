<?php

declare(strict_types=1);

namespace Domain\Content\Imports;

use Domain\Content\Actions\CreateContentAction;
use Domain\Content\DataTransferObjects\ContentData;
use Domain\Content\Enums\PublishBehavior;
use Domain\Content\Models\Content;
use Domain\Site\Models\Site;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * @property-read Content $record
 */
class ContentImporter extends Importer
{
    protected static ?string $model = Content::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [

            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required',
                    function (string $attribute, mixed $value, \Closure $fail) {
                        if (Content::where('name', $value)->exists()) {

                            Notification::make()
                                ->title(trans('Content Import Error'))
                                ->body("Content name {$value} has already been taken.")
                                ->danger()
                                ->persistent()
                                ->send();

                            $fail("The name '{$value}' is already taken. Please choose another.");
                        }
                    },
                ]),

            ImportColumn::make('slug')
                ->requiredMapping(),

            ImportColumn::make('prefix')
                ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator) {

                        $data = $validator->getData();

                        if (
                            \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)
                        ) {

                            $siteIDs = Site::whereIn('domain', explode(',', $data['sites']))->pluck('id');

                            $content = Content::where('prefix', $value)
                                ->whereHas(
                                    'sites',
                                    fn ($query) => $query->whereIn('site_id', $siteIDs)
                                )->count();

                        } else {
                            $content = Content::where('prefix', $value)->count();
                        }

                        if ($content > 0) {

                            Notification::make()
                                ->title(trans('Content Import Error'))
                                ->body("Content prefix {$data['name']} has already been taken.")
                                ->danger()
                                ->persistent()
                                ->send();

                            $fail("Content prefix {$data['name']} has already been taken.");
                        }
                    },
                ])
                ->requiredMapping(),

            ImportColumn::make('blueprint_id')
                ->requiredMapping(),
            ImportColumn::make('visibility')
                ->requiredMapping(),

            ImportColumn::make('past_publish_date_behavior')
                ->castStateUsing(fn (?string $state) => blank($state) ? null : PublishBehavior::from($state))
                ->requiredMapping(),

            ImportColumn::make('future_publish_date_behavior')
                ->castStateUsing(fn (?string $state) => blank($state) ? null : PublishBehavior::from($state))
                ->requiredMapping(),

            ImportColumn::make('sites')
                ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator): void {
                        if (is_null($value) && TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {

                            Notification::make()
                                ->title(trans('Content Import Error'))
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
                                    ->title(trans('Content Import Error'))
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
                ->requiredMapping(),

            ImportColumn::make('taxonomies')
                ->requiredMapping(),

            ImportColumn::make('is_sortable')
                ->requiredMapping(),

        ];
    }

    #[\Override]
    public function resolveRecord(): Content
    {
        if (is_null($this->data['slug'])) {
            return new Content;
        }

        return Content::where('slug', $this->data['slug'])->first() ?? new Content;
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
        $siteIDs = array_key_exists('sites', $this->data) ?
            Site::whereIn('domain', explode(',', $this->data['sites']))->pluck('id')->toArray() :
            [];

        /** @var array $taxonomyIds */
        $taxonomyIds = array_key_exists('taxonomies', $this->data) ?
            Taxonomy::whereIn('slug', explode(',', $this->data['taxonomies']))->pluck('id')->toArray() :
             [];

        $contentData = new ContentData(
            name: $this->data['name'],
            blueprint_id: $this->data['blueprint_id'],
            prefix: $this->data['prefix'],
            visibility: $this->data['visibility'],
            past_publish_date_behavior: $this->data['past_publish_date_behavior'],
            future_publish_date_behavior: $this->data['future_publish_date_behavior'],
            is_sortable: $this->data['is_sortable'] ? true : false,
            sites: $siteIDs,
            taxonomies: $taxonomyIds,
        );

        app(CreateContentAction::class)->execute($contentData);

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
