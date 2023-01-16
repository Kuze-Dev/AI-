<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\CollectionResource\Traits\ColumnOffsets;
use Carbon\Carbon;
use Closure;
use Domain\Collection\Actions\UpdateCollectionEntryAction;
use Domain\Collection\Models\Collection;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class EditCollectionEntry extends EditRecord
{
    use ColumnOffsets;

    protected static string $resource = CollectionResource::class;

    public mixed $ownerRecord;

    /**
     * Override mount and
     * call parent component mount.
     *
     * @param mixed $record
     *
     * @return void
     */
    public function mount($record, string $ownerRecord = ''): void
    {
        $this->ownerRecord = static::getResource()::resolveRecordRouteBinding($ownerRecord)->load('taxonomies.taxonomyTerms');
        $this->record = app(CollectionEntry::class)->resolveRouteBinding($record);

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Collection::class, ['']);
        }

        if ($this->record === null) {
            throw (new ModelNotFoundException())->setModel(CollectionEntry::class, ['']);
        }

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    /** Set the title of the page. */
    protected function getTitle(): string
    {
        return trans('Edit :label Collection Entry', [
            'label' => $this->record->title,
        ]);
    }

    /**
     * Specify reference
     * model used for the page.
     */
    public function getModel(): string
    {
        return CollectionEntry::class;
    }

    /** Build form from blueprint schema. */
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
                            ->minDate(Carbon::now()->startOfDay())
                            ->timezone(Auth::user()?->timezone)
                            ->when(fn (self $livewire) => $livewire->ownerRecord->hasPublishDates()),
                        Group::make()
                            ->statePath('taxonomies')
                            ->schema(
                                fn () => $this->ownerRecord->taxonomies->map(
                                    fn (Taxonomy $taxonomy) => Select::make($taxonomy->name)
                                        ->statePath((string) $taxonomy->id)
                                        ->multiple()
                                        ->options(
                                            $taxonomy->taxonomyTerms->sortBy('name')
                                                ->mapWithKeys(fn (TaxonomyTerm $term) => [$term->id => $term->name])
                                                ->toArray()
                                        )
                                        ->afterStateHydrated(fn (Select $component, CollectionEntry $record) => $component->state($record->taxonomyTerms->where('taxonomy_id', $taxonomy->id)->pluck('id')->toArray()))
                                )->toArray()
                            )
                            ->dehydrated(false),
                        Hidden::make('taxonomy_terms')
                            ->dehydrateStateUsing(fn (Closure $get) => Arr::flatten($get('taxonomies'), 1)),

                    ])
                        ->columnSpan(4)
                        ->when(fn (self $livewire) => ! empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()),
                    SchemaFormBuilder::make('data', fn () => $this->ownerRecord->blueprint->schema)
                        ->columnSpan($this->getMainColumnOffset()),
                ]),
        ];
    }

    /**
     * Specify relationships
     * that are displayed in the page.
     */
    protected function getRelationManagers(): array
    {
        $managers = [
            ActivitiesRelationManager::class,
        ];

        return array_filter(
            $managers,
            function (string|RelationGroup $manager): bool {
                if ($manager instanceof RelationGroup) {
                    return (bool) count($manager->getManagers(ownerRecord: $this->getRecord()));
                }

                return $manager::canViewForRecord($this->getRecord());
            },
        );
    }

    /**
     * Execute database transaction
     * for updating collection entries.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateCollectionEntryAction::class)
                ->execute($this->record, new CollectionEntryData(
                    title: $data['title'],
                    slug: $data['slug'],
                    taxonomy_terms: $data['taxonomy_terms'] ?? [],
                    published_at: Carbon::parse($data['published_at']),
                    data: $data['data']
                ))
        );
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->ownerRecord]);
    }
}
