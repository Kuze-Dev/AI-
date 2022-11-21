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
            Actions\Action::make('configure')
                ->icon('heroicon-s-cog')
                ->url(route('filament-tenant.resources.' . self::$resource::getSlug() . '.configure', $this->record)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    SchemaFormBuilder::make('data', fn (Page $record) => $record->blueprint->schema)
                        ->columnSpan(['lg' => $this->record->hasPublishedAtBehavior() ? 2 : 3]),
                    Forms\Components\Group::make([
                        Forms\Components\Section::make(trans('Behavior'))
                            ->schema([
                                Forms\Components\DatePicker::make('published_at')
                                    ->label('Published date')
                                    ->rule('date'),
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
                    data: $data['data'],
                    published_at: $data['published_at'] ?? null
                ))
        );
    }
}
