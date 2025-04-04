<?php

declare(strict_types=1);

namespace Domain\Page\Imports;

use Domain\Form\Models\Form;
use Domain\Page\Actions\CreateBlockAction;
use Domain\Page\DataTransferObjects\BlockData;
use Domain\Page\Models\Block;
use Domain\Site\Models\Site;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * @property-read Form $record
 */
class BlockImporter extends Importer
{
    protected static ?string $model = Block::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [

            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['unique:blocks,name']),

            ImportColumn::make('blueprint_id')
                ->requiredMapping(),

            ImportColumn::make('component')
                ->requiredMapping(),

            ImportColumn::make('is_fixed_content')
                ->requiredMapping(),

            ImportColumn::make('sites')
                ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator): void {
                        if (is_null($value) && TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {

                            Notification::make()
                                ->title(trans('Block Import Error'))
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
                                    ->title(trans('Block Import Error'))
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

            ImportColumn::make('image')
                ->requiredMapping(),

            ImportColumn::make('data')
                ->requiredMapping(),

        ];
    }

    #[\Override]
    public function resolveRecord(): Block
    {

        if (is_null($this->data['name'])) {
            return new Block;
        }

        return Block::where('name', $this->data['name'])->first() ?? new Block;
    }

    #[\Override]
    public function fillRecord(): void
    {
        /** Disabled Filament Built in Record Creation Handle the Forms
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

        $blockData = new BlockData(
            name: $this->data['name'],
            blueprint_id: $this->data['blueprint_id'],
            component: $this->data['component'],
            is_fixed_content: $this->data['is_fixed_content'] ?? false,
            sites: $siteIDs,
            image: [$this->data['image']],
            data: json_decode($this->data['data'], true),
        );

        app(CreateBlockAction::class)->execute($blockData);
    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Block import has completed and '.
            number_format($import->successful_rows).' '.
            Str::of('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.Str::of('row')
                ->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
