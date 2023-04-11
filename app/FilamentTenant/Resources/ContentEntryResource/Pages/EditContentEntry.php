<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentEntryResource\Pages;

use Domain\Content\DataTransferObjects\ContentEntryData;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use Domain\Content\Actions\UpdateContentEntryAction;
use Domain\Content\Models\Content;
use Filament\Pages\Actions;
use Filament\Pages\Actions\DeleteAction;

/** @method class-string<\Illuminate\Database\Eloquent\Model> getModel() */
class EditContentEntry extends EditRecord
{
    protected static string $resource = ContentEntryResource::class;

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
        $this->ownerRecord = app(Content::class)
            ->resolveRouteBinding($ownerRecord)
            ?->load('taxonomies.taxonomyTerms');

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Content::class, ['']);
        }

        parent::mount($record);
    }

    /** @param string $key */
    protected function resolveRecord($key): Model
    {
        $record = $this->ownerRecord->resolveChildRouteBinding('contentEntries', $key, null);

        if ($record === null) {
            throw (new ModelNotFoundException())->setModel($this->getModel(), [$key]);
        }

        return $record;
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function configureDeleteAction(DeleteAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canDelete($this->getRecord()))
            ->record($this->getRecord())
            ->recordTitle($this->getRecordTitle())
            ->successRedirectUrl(static::getResource()::getUrl('index', [$this->ownerRecord]));
    }

    protected function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [
                ContentResource::getUrl('index') => ContentResource::getBreadcrumb(),
                ContentResource::getUrl('edit', ['record' => $this->ownerRecord]) => $this->ownerRecord->name,
                $resource::getUrl('index', [$this->ownerRecord]) => $resource::getBreadcrumb(),
                $this->getRecordTitle(),
            ],
            (filled($breadcrumb) ? [$breadcrumb] : []),
        );
    }

    /**
     * Execute database transaction
     * for updating content entries.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateContentEntryAction::class)
                ->execute($this->record, ContentEntryData::fromArray($data))
        );
    }
}
