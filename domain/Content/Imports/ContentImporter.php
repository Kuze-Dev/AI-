<?php

declare(strict_types=1);

namespace Domain\Content\Imports;

use Domain\Blueprint\Models\Blueprint;
use Domain\Content\Actions\CreateContentAction;
use Domain\Content\DataTransferObjects\ContentData;
use Domain\Content\Enums\PublishBehavior;
use Domain\Content\Models\Content;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
                ->requiredMapping(),
                // ->rules(['required', 'string']),

            ImportColumn::make('slug')
                ->requiredMapping(),

            ImportColumn::make('prefix')
                ->requiredMapping(),
                // ->rules(['required', 'string']),

            ImportColumn::make('blueprint_id')
                ->requiredMapping(),
                // ->rules(['required', Rule::exists(Blueprint::class, 'id')]),
            ImportColumn::make('visibility')
                ->requiredMapping(),
                // ->rules(['required']),
                

            // ImportColumn::make('past_publish_date_behavior')
            //     ->fillRecordUsing(fn ($value) => $value ? PublishBehavior::from($value) : null)
            //     ->requiredMapping(),
                

            // ImportColumn::make('future_publish_date_behavior')
            //     ->fillRecordUsing(fn ($value) => $value ? PublishBehavior::from($value) : null)
            //     ->requiredMapping(),
                

            ImportColumn::make('is_sortable')
                ->requiredMapping(),
                
           

          
        ];
    }

    #[\Override]
    public function resolveRecord(): Content
    {
        if (is_null($this->data['slug'])) {
            return new Content();
        }

        return Content::where('slug',$this->data['slug'])->first() ?? new Content();
    }

    // /**
    //  * @throws \Throwable
    //  */
    // #[\Override]
    // public function saveRecord(): void
    // {
       
    //     if ($this->record->exists) {
    //         return;
    //     }

    //     Log::info('Creating Content');

    //     $contentData = new ContentData(
    //         name: $this->data['name'],
    //         blueprint_id: $this->data['blueprint_id'],
    //         prefix: $this->data['prefix'],
    //         visibility: $this->data['visibility'],
    //         // past_publish_date_behavior: PublishBehavior::from($this->data['past_publish_date_behavior']),
    //         // future_publish_date_behavior: PublishBehavior::from($this->data['future_publish_date_behavior']),
    //         is_sortable: $this->data['is_sortable'],
    //         sites: [],
    //         taxonomies: [],
    //     );  

    //     app(CreateContentAction::class)->execute($contentData);

      

    // }

  

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
