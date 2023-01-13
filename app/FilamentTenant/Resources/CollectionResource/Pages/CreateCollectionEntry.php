<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Carbon\Carbon;
use Domain\Collection\Actions\CreateCollectionEntryAction;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Domain\Collection\Models\Collection;
use Filament\Forms\Components\Grid;

class CreateCollectionEntry extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    public mixed $ownerRecord;

    public function mount(string $ownerRecord = ''): void
    {
        $this->ownerRecord = static::getResource()::resolveRecordRouteBinding($ownerRecord);

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Collection::class, ['']);
        }

        parent::mount();
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('configure')
                ->icon('heroicon-s-cog')
                ->url(route('filament-tenant.resources.' . self::$resource::getSlug() . '.edit', $this->ownerRecord)),
        ];
    }

    protected function getTitle(): string
    {
        return trans('Create :label Collection Entry', [
            'label' => $this->ownerRecord->name,
        ]);
    }

    public function getModel(): string
    {
        return CollectionEntry::class;
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(12)
                ->schema([
                    Card::make([
                        TextInput::make('title')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        TextInput::make('slug')
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (?CollectionEntry $record) => $record !== null),
                    ])
                        ->columnSpan($this->getMainColumnOffset()),
                    Card::make([
                        DateTimePicker::make('published_at')
                            ->default(Carbon::now())
                            ->minDate(Carbon::now()->startOfDay())
                            ->timezone(Auth::user()?->timezone)
                            ->when(fn (self $livewire) => $livewire->ownerRecord->hasPublishDates()),
                    ])
                        ->columnSpan(4)
                        ->when(fn (self $livewire) => ! empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()),
                ]),

            SchemaFormBuilder::make('data', fn () => $this->ownerRecord->blueprint->schema),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateCollectionEntryAction::class)
                ->execute($this->ownerRecord, new CollectionEntryData(
                    title: $data['title'],
                    slug: $data['slug'],
                    published_at: Carbon::parse($data['published_at']),
                    taxonomy_terms: $data['taxonomy_terms'] ?? [],
                    data: $data['data']
                ))
        );
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->ownerRecord]);
    }

    protected function generateTaxonomySelections(): void
    {

    }

    protected function getMainColumnOffset(): int
    {
        if ( ! empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()) {
            return 8;
        }

        return 12;
    }
}
