<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentEntryResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use App\FilamentTenant\Support\Traits\HasParentResource;
use Domain\Content\Actions\CreateContentEntryAction;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreateContentEntry extends CreateRecord
{
    // use HasParentResource;
    use LogsFormActivity;

    protected static string $resource = ContentEntryResource::class;

    public mixed $ownerRecord;

    #[\Override]
    public function mount(string $ownerRecord = ''): void
    {
        $this->ownerRecord = app(Content::class)->resolveRouteBinding($ownerRecord)?->load('taxonomies.taxonomyTerms');

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException)->setModel(Content::class, ['']);
        }

        parent::mount();
    }

    #[\Override]
    public function getBreadcrumb(): string
    {
        return trans('Create :label Content Entry', ['label' => $this->ownerRecord->name]);
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [
                ContentResource::getUrl('index') => ContentResource::getBreadcrumb(),
                ContentResource::getUrl('edit', [$this->ownerRecord]) => $this->ownerRecord->name,
                $resource::getUrl('index', [$this->ownerRecord]) => $resource::getBreadcrumb(),
            ],
            (filled($breadcrumb) ? [$breadcrumb] : []),
        );
    }

    #[\Override]
    public function getTitle(): string
    {
        return trans('Create :label Content Entry', [
            'label' => $this->ownerRecord->name,
        ]);
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    #[\Override]
    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateContentEntryAction::class)
            ->execute($this->ownerRecord, ContentEntryData::fromArray($data));
    }

    #[\Override]
    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if ($resource::hasPage('view') && $resource::canView($this->record)) {
            return $resource::getUrl('view', [$this->record]);
        }

        if ($resource::hasPage('edit') && $resource::canEdit($this->record)) {
            return $resource::getUrl('edit', [$this->ownerRecord, $this->record]);
        }

        return $resource::getUrl('index', [$this->ownerRecord]);
    }
}
