<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\FilamentTenant\Resources\PageResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Page\Actions\UpdatePageContentAction;
use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Models\Page;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Exception;
use Filament\Forms;

/**
 * @property-read \Domain\Page\Models\Page $record
 */
class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\Card::make([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('blueprint_id')
                            ->relationship('blueprint', 'name')
                            ->disabled(),
                        SchemaFormBuilder::make(
                            'data',
                            fn (Page $record) => $record->blueprint->schema
                        ),
                    ])
                        ->columnSpan(['lg' => $this->record->hasPublishedAtBehavior() ? 2 : 3]),

                    Forms\Components\Group::make([

                        Forms\Components\Section::make(trans('Behavior'))
                            ->schema([
                                Forms\Components\DatePicker::make('published_at')
                                    ->label('Published date')
                                    ->required()
                                    ->rule('date'),
                                Forms\Components\Placeholder::make('past_behavior')
                                    ->content(Str::headline($this->record->past_behavior?->value ?? '')),
                                Forms\Components\Placeholder::make('future_behavior')
                                    ->content(Str::headline($this->record->future_behavior?->value ?? '')),
                            ]),

                    ])
                        ->columnSpan(['lg' => 1])
                        ->visible($this->record->hasPublishedAtBehavior()),

                ])
                ->columns(3),
        ];
    }

    /**
     * @param \Domain\Page\Models\Page $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdatePageContentAction::class)
                ->execute($record, new PageContentData(
                    name: $data['name'],
                    data: $data['data'],
                    published_at: isset($data['published_at'])
                        ? now()->parse($data['published_at'])
                        : null
                ))
        );
    }
}
