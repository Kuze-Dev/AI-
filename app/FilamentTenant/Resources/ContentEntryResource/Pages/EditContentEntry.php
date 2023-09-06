<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentEntryResource\Pages;

use App\Filament\Livewire\Actions\CustomPageActionGroup;
use App\Filament\Pages\Concerns\LogsFormActivity;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use Domain\Content\Actions\CreateContentEntryDraftAction;
use Domain\Content\Actions\UpdateContentEntryAction;
use Domain\Content\Models\Content;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\DeleteAction;
use Illuminate\Http\RedirectResponse;
use Livewire\Redirector;

/** @method class-string<\Illuminate\Database\Eloquent\Model> getModel() */
class EditContentEntry extends EditRecord
{
    use LogsFormActivity;

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
            'content_entries_group_actions' => CustomPageActionGroup::make([
                Action::make('published')
                    ->label(__('Published Draft'))
                    ->action('published')
                    ->hidden(function () {
                        return $this->record->draftable_id == null ? true : false;
                    }),
                Action::make('draft')
                    ->label(__('Save As Draft'))
                    ->action('draft')
                    ->hidden(function () {

                        if($this->record->draftable_id != null) {
                            return true;
                        }

                        return ($this->record->draftable_id == null && $this->record->pageDraft) ? true : false;
                    }),
                Action::make('overwriteDraft')
                    ->label(__('Save As Draft'))
                    ->action('overwriteDraft')
                    ->requiresConfirmation()
                    ->modalHeading('you have existing draft')
                    ->modalSubheading('You have existing draft for this page want to overwrite existing draft?')
                    ->modalCancelAction(function () {
                        return Action::makeModalAction('redirect')
                            ->label(__('Edit Existing Draft'))
                            ->color('secondary')
                            ->url(ContentEntryResource::getUrl('edit', [$this->ownerRecord, $this->record->pageDraft]));
                    })
                    ->hidden(function () {

                        return ($this->record->pageDraft && $this->record->draftable_id == null) ? false : true;
                    }),
                Action::make('save')
                    ->label(__('Save and Continue Editing'))
                    ->action('save')
                    ->keyBindings(['mod+s']),
            ])
                ->view('filament.pages.actions.custom-action-group.index')
                ->setName('page_draft_actions')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label')),
            // Action::make('save')
            //     ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
            //     ->action('save')
            //     ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    public function draft(): RedirectResponse|Redirector|false
    {
        $data = $this->form->getState();

        $record = $this->record;

        $pageData = ContentEntryData::fromArray($data);

        #check if page has existing draft

        if( ! is_null($record->pageDraft)) {

            Notification::make()
                ->danger()
                ->title(trans('Has Draft'))
                ->body(trans('Page :title has a existing draft', ['title' => $record->name]))
                ->send();

            return false;
        }

        $draftpage = app(CreateContentEntryDraftAction::class)->execute($record, $pageData);

        return redirect(ContentEntryResource::getUrl('edit', [$this->ownerRecord, $draftpage]));
    }

    protected function configureDeleteAction(DeleteAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canDelete($this->getRecord()))
            ->record($this->getRecord())
            ->recordTitle($this->getRecord()->getAttribute($this->getResource()::getRecordTitleAttribute()))
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

    /** @param \Domain\Content\Models\ContentEntry $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateContentEntryAction::class)
                ->execute($record, ContentEntryData::fromArray($data))
        );
    }
}
