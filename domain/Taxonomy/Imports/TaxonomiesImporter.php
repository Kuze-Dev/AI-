<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Imports;

use Domain\Site\Models\Site;
use Domain\Taxonomy\Actions\CreateTaxonomyAction;
use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Support\RouteUrl\Models\RouteUrl;

/**
 * @property-read Taxonomy $record
 */
class TaxonomiesImporter extends Importer
{
    protected static ?string $model = Taxonomy::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [

            ImportColumn::make('name')
                ->requiredMapping()
                ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator) {

                        $taxo = Taxonomy::where('name', $value)->count();

                        if (! (
                            \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class) ||
                            \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\Internationalization::class))
                        ) {

                            if ($taxo > 0) {
                                $fail("Taxonomy name {$value} has already been taken.");
                            }
                        }

                    },
                ],
                ),

            ImportColumn::make('slug')
                ->requiredMapping(),

            ImportColumn::make('locale')
                ->requiredMapping(),

            ImportColumn::make('blueprint_id')
                ->requiredMapping(),

            ImportColumn::make('has_route')
                ->requiredMapping(),

            ImportColumn::make('is_custom')
                ->requiredMapping(),

            ImportColumn::make('url')
                ->rules([
                        function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator) {

                            $activeRouteUrl = RouteUrl::where('url', $value)->count();

                            if ($activeRouteUrl > 0) {

                                Notification::make()
                                    ->title(trans('Taxonomy Import Error'))
                                    ->body("Taxonomy Url {$value} has already been taken.")
                                    ->danger()
                                    ->when(config('queue.default') === 'sync',
                                        fn (Notification $notification) => $notification
                                            ->persistent()
                                            ->send(),
                                        fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                    );

                                $fail("The url '{$value}' is already taken. Please choose another.");
                            }
                        },
                    ])
                ->requiredMapping(),

            ImportColumn::make('parent_translation')
                ->rules([
                        function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator) {

                            if ($value) {

                                $parentTaxonomy = Taxonomy::where('slug', $value)->count();

                                if ($parentTaxonomy === 0) {

                                    Notification::make()
                                        ->title(trans('Taxonomy Import Error'))
                                        ->body("Taxonomy {$value} Not Found.")
                                        ->danger()
                                        ->when(config('queue.default') === 'sync',
                                            fn (Notification $notification) => $notification
                                                ->persistent()
                                                ->send(),
                                            fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                        );

                                    $fail("Taxonomy '{$value}' Not Found.");
                                }
                            }

                        },
                ])
                ->requiredMapping(),

            ImportColumn::make('sites')
                ->requiredMapping(),

        ];
    }

    #[\Override]
    public function resolveRecord(): Taxonomy
    {

        if (is_null($this->data['slug'])) {
            return new Taxonomy;
        }

        return Taxonomy::where('slug', $this->data['slug'])->first() ?? new Taxonomy;
    }

    #[\Override]
    public function fillRecord(): void
    {
        /** Disabled Filament Built in Record Creation Handle the Taxonomy
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

        $taxonomyData = TaxonomyData::fromArray([
            'name' => $this->data['name'],
            'slug' => $this->data['slug'],
            'locale' => $this->data['locale'],
            'blueprint_id' => $this->data['blueprint_id'],
            'has_route' => (bool) $this->data['has_route'],
            'route_url' => [
                'url' => $this->data['url'],
                'is_override' => $this->data['is_custom'],
            ],
            'parent_translation' => $this->data['parent_translation'],
            'sites' => $siteIDs,
        ]);

        app(CreateTaxonomyAction::class)->execute($taxonomyData);

    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Taxonmy import has completed and '.
            number_format($import->successful_rows).' '.
            Str::of('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.Str::of('row')
                ->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
