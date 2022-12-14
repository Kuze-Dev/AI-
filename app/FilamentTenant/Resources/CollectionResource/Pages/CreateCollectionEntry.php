<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Collection\Actions\CreateCollectionEntryAction;
use Domain\Collection\Actions\UpdateCollectionAction;
use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

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
                ->url(route('filament-tenant.resources.' . self::$resource::getSlug() . '.configure', $this->ownerRecord)),
        ];
    }

    protected function getTitle(): string
    {
        return trans('Create :label Collection Entry', [
            'label' => $this->ownerRecord->name,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            SchemaFormBuilder::make('data', fn () => $this->ownerRecord->blueprint->schema),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateCollectionEntryAction::class)
                ->execute($this->ownerRecord, new CollectionEntryData(...$data))
        );
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('configure', ['record' => $this->ownerRecord]);
    }
}
