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
use Illuminate\Support\Facades\Request;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use Carbon\Carbon;
use Domain\Collection\Actions\UpdateCollectionEntryAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EditCollectionEntry extends EditRecord
{
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
    public function mount($record): void
    {
        $this->ownerRecord = static::getResource()::resolveRecordRouteBinding(Request::route('ownerRecord'));
        $this->record = app(CollectionEntry::class)->resolveRouteBinding($record);

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(CollectionEntry::class, [""]);
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
            Card::make([
                TextInput::make('title')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (CollectionEntry $record) => $record !== null),
                DateTimePicker::make('published_at')
                    ->minDate(Carbon::now()->startOfDay())
                    ->timezone(Auth::user()?->timezone)
                    ->when(fn (self $livewire) => $livewire->ownerRecord->hasPublishDates()),
                Select::make('taxonomy_term_id')
                    ->relationship('taxonomyTerm', 'name')
                    ->options(
                        collect($this->ownerRecord->taxonomy->taxonomyTerms)
                            ->mapWithKeys(fn ($terms) => [
                                $terms->id => Str::headline($terms->name),
                            ])
                    )
                    ->saveRelationshipsUsing(null)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive(),
            ]),
            SchemaFormBuilder::make('data', fn () => $this->ownerRecord->blueprint->schema),
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
                    taxonomy_term_id: (int) $data['taxonomy_term_id'],
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
