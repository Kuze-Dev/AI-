<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Closure;
use Domain\Collection\Actions\CreateCollectionEntryAction;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class CreateCollectionEntry extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    public $ownerRecord;

    public function mount(): void
    {
        $key = Request::route('ownerRecord');

        $this->ownerRecord = static::getResource()::resolveRecordRouteBinding($key);

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel($this->getModel(), [$key]);
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
            Card::make([
                TextInput::make('title')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (?CollectionEntry $record) => $record !== null),
                Select::make('taxonomy_term_id')
                        ->relationship('taxonomyTerm', 'name')
                        ->options(
                            collect($this->ownerRecord->taxonomy->taxonomyTerms)
                                ->mapWithKeys(fn ($terms) => [
                                    $terms->id => Str::headline($terms->name)
                                ])
                        )
                        ->saveRelationshipsUsing(null)
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
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
                    taxonomy_term_id: (int) $data['taxonomy_term_id'],
                    data: $data['data']
                ))
        );
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->ownerRecord]);
    }
}
