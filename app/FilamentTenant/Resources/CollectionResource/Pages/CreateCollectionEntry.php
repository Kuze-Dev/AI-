<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Carbon\Carbon;
use Closure;
use Domain\Collection\Actions\CreateCollectionEntryAction;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Domain\Collection\Models\Collection;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Arr;

class CreateCollectionEntry extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    public mixed $ownerRecord;

    public function mount(string $ownerRecord = ''): void
    {
        $this->ownerRecord = static::getResource()::resolveRecordRouteBinding($ownerRecord)->load('taxonomies.taxonomyTerms');

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Collection::class, ['']);
        }

        parent::mount();
    }

    public function getBreadcrumb(): string
    {
        return trans('Create Collection Entry');
    }

    protected function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [
                $resource::getUrl() => $resource::getBreadcrumb(),
                $resource::getUrl('edit', ['record' => $this->ownerRecord]) => $this->ownerRecord->name,
            ],
            (filled($breadcrumb) ? [$breadcrumb] : []),
        );
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
            Grid::make(['lg' => 3])
                ->schema([
                    Group::make()
                        ->schema([
                            Card::make([
                                TextInput::make('title')
                                    ->unique(ignoreRecord: true)
                                    ->required(),
                                TextInput::make('slug')
                                    ->unique(ignoreRecord: true)
                                    ->disabled(fn (?CollectionEntry $record) => $record !== null),
                            ]),
                            SchemaFormBuilder::make('data', fn () => $this->ownerRecord->blueprint->schema),
                        ])
                        ->tap(function (Group $component) {
                            ! empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()
                                ? $component->columnSpan(['lg' => 2])
                                : $component->columnSpanFull();
                        }),
                    Card::make([
                        DateTimePicker::make('published_at')
                            ->default(Carbon::now())
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
                                )->toArray()
                            )
                            ->dehydrated(false),
                        Hidden::make('taxonomy_terms')
                            ->dehydrateStateUsing(fn (Closure $get) => Arr::flatten($get('taxonomies'), 1)),
                    ])
                        ->columnSpan(['lg' => 1])
                        ->when(fn () => ! empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()),
                ]),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateCollectionEntryAction::class)
                ->execute($this->ownerRecord, new CollectionEntryData(
                    title: $data['title'],
                    slug: $data['slug'],
                    published_at: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
                    taxonomy_terms: $data['taxonomy_terms'] ?? [],
                    data: $data['data']
                ))
        );
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->ownerRecord]);
    }

    // protected function getMainColumnOffset(): int
    // {
    //     if (!empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()) {
    //         return 8;
    //     }

    //     return 12;
    // }

    // protected function getSideColumnOffset(): int
    // {
    //     if (!empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()) {
    //         return 4;
    //     }

    //     return 0;
    // }
}
