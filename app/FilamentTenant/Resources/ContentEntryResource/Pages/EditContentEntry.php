<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentEntryResource\Pages;

use App\Features\CMS\SitesManagement;
use App\Filament\Livewire\Actions\CustomPageActionGroup;
use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use App\Settings\CMSSettings;
use App\Settings\SiteSettings;
use Domain\Content\Actions\CreateContentEntryDraftAction;
use Domain\Content\Actions\PublishedContentEntryDraftAction;
use Domain\Content\Actions\UpdateContentEntryAction;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Site\Models\Site;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Redirector;

/** @method class-string<\Illuminate\Database\Eloquent\Model> getModel()
 *
 * @property \Domain\Content\Models\ContentEntry $record
 */
class EditContentEntry extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ContentEntryResource::class;

    public mixed $ownerRecord;

    /**
     * Override mount and
     * call parent component mount.
     *
     * @param  mixed  $record
     */
    #[\Override]
    public function mount(int|string $record, string $ownerRecord = ''): void
    {
        $this->ownerRecord = app(Content::class)
            ->resolveRouteBinding($ownerRecord)
            ?->load('taxonomies.taxonomyTerms');

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Content::class, ['']);
        }

        parent::mount($record);
    }

    /** @param  string  $key */
    #[\Override]
    protected function resolveRecord($key): Model
    {
        $record = $this->ownerRecord->resolveChildRouteBinding('contentEntries', $key, null);

        if ($record === null) {
            throw (new ModelNotFoundException())->setModel($this->getModel(), [$key]);
        }

        return $record;
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            'content_entries_group_actions' => CustomPageActionGroup::make([
                Action::make('published')
                    ->label(trans('Published Draft'))
                    ->action('published')
                    ->hidden(fn () => $this->record->draftable_id == null ? true : false),
                Action::make('draft')
                    ->label(trans('Save As Draft'))
                    ->action('draft')
                    ->hidden(function () {

                        if ($this->record->draftable_id != null) {
                            return true;
                        }

                        return ($this->record->draftable_id == null && $this->record->pageDraft) ? true : false;
                    }),
                Action::make('overwriteDraft')
                    ->label(trans('Save As Draft'))
                    ->action('overwriteDraft')
                    ->requiresConfirmation()
                    ->modalHeading('Draft for this content already exists')
                    ->modalSubheading('You have an existing draft for this content. Do you want to overwrite the existing draft?')
                    ->modalCancelAction(fn () => Action::makeModalAction('redirect')
                        ->label(trans('Edit Existing Draft'))
                        ->color('gray')
                        ->url(ContentEntryResource::getUrl('edit', [$this->ownerRecord, $this->record->pageDraft])))
                    ->hidden(fn () => ($this->record->pageDraft && $this->record->draftable_id == null) ? false : true),
                Action::make('save')
                    ->label(trans('Save and Continue Editing'))
                    ->action('save')
                    ->keyBindings(['mod+s']),
            ])
                ->view('filament.pages.actions.custom-action-group.index')
                ->setName('page_draft_actions')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label')),
            // Action::make('save')
            //     ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
            //     ->action('save')
            //     ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            'other_page_actions' => CustomPageActionGroup::make([
                Action::make('preview')
                    ->color('gray')
                    ->hidden(TenantFeatureSupport::active(SitesManagement::class))
                    ->label(trans('Preview Page'))
                    ->url(function (SiteSettings $siteSettings, CMSSettings $cmsSettings) {
                        $domain = $siteSettings->front_end_domain ?? $cmsSettings->front_end_domain;

                        if (! $domain) {
                            return null;
                        }

                        $queryString = Str::after(URL::temporarySignedRoute('tenant.api.contents.entries.show', now()->addMinutes(15), [$this->ownerRecord, $this->record], false), '?');

                        return "https://{$domain}/preview?contents={$this->ownerRecord->slug}&slug={$this->record->slug}&{$queryString}";
                    }, true),
                Action::make('preview_microsite_action')
                    ->label('Preview Microsite')
                    ->hidden(TenantFeatureSupport::inactive(SitesManagement::class))
                    ->color('gray')
                    ->record($this->getRecord())
                    ->modalHeading('Preview Microsite')
                    ->slideOver(true)
                    ->action(function (ContentEntry $record, Action $action, array $data): void {

                        /** @var Site */
                        $site = Site::find($data['preview_microsite']);

                        if ($site->domain == null) {

                            Notification::make()
                                ->danger()
                                ->title(trans('No Domain Set'))
                                ->body(trans('Please set a domain for :value to preview.', ['value' => $site->name]))
                                ->send();
                        }
                    })
                    ->form([
                        Radio::make('preview_microsite')
                            ->required()
                            ->options(function () {

                                /** @var ContentEntry */
                                $site = $this->getRecord();

                                return $site->sites()->orderby('name')->pluck('name', 'id')->toArray();
                            })
                            ->descriptions(function () {

                                /** @var ContentEntry */
                                $site = $this->getRecord();

                                return $site->sites()->orderby('name')->pluck('domain', 'id')->toArray();
                            })
                            ->reactive()
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, $state, $livewire) {

                                /** @var Site */
                                $site = Site::find($state);

                                $domain = $site->domain;

                                /** @var CustomPageActionGroup */
                                $other_page_actions = $livewire->getCachedActions()['other_page_actions'];

                                $modelAction = $other_page_actions->getActions()['preview_microsite_action'];

                                $modelAction->modalSubmitAction(function () use ($domain) {

                                    $queryString = Str::after(URL::temporarySignedRoute('tenant.api.contents.entries.show', now()->addMinutes(15), [$this->ownerRecord, $this->record], false), '?');

                                    return Action::makeModalAction('preview')->url("https://{$domain}/preview?contents={$this->ownerRecord->slug}&slug={$this->record->slug}&{$queryString}", true);
                                });

                                $set('domain', $domain);
                            }),

                    ]),

            ])->view('filament.pages.actions.custom-action-group.index')
                ->setName('other_page_draft')
                ->color('gray')
                ->label(trans('More Actions')),
        ];
    }

    public function draft(): RedirectResponse|Redirector|false
    {
        $data = $this->form->getState();

        $record = $this->record;

        $pageData = ContentEntryData::fromArray($data);

        //check if page has existing draft

        if (! is_null($record->pageDraft)) {

            Notification::make()
                ->danger()
                ->title(trans('Has Draft'))
                ->body(trans('Page :title has a existing draft', ['title' => $record->title]))
                ->send();

            return false;
        }

        $draftpage = app(CreateContentEntryDraftAction::class)->execute($record, $pageData);

        return redirect(ContentEntryResource::getUrl('edit', [$this->ownerRecord, $draftpage]));
    }

    public function overwriteDraft(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $record = $this->record;

        $record->pageDraft?->delete();

        $pageData = ContentEntryData::fromArray($data);

        $draftpage = app(CreateContentEntryDraftAction::class)->execute($record, $pageData);

        Notification::make()
            ->success()
            ->title(trans('Overwritten Draft'))
            ->body(trans('Content Entry Draft has been overwritten'))
            ->send();

        return redirect(ContentEntryResource::getUrl('edit', [$this->ownerRecord, $draftpage]));
    }

    public function published(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $pageDraft = $this->record;

        /** @var \Domain\Content\Models\ContentEntry */
        $parentPage = $pageDraft->parentPage;

        $data['published_draft'] = true;

        $contentEntryData = ContentEntryData::fromArray($data);

        $contentEntry = DB::transaction(
            fn () => app(PublishedContentEntryDraftAction::class)->execute(
                $parentPage,
                $pageDraft,
                $contentEntryData
            )
        );

        return redirect(ContentEntryResource::getUrl('edit', [$this->ownerRecord, $contentEntry]));
    }

    #[\Override]
    protected function configureDeleteAction(DeleteAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canDelete($this->getRecord()))
            ->record($this->getRecord())
            ->recordTitle($this->getRecord()->getAttribute($this->getResource()::getRecordTitleAttribute()))
            ->successRedirectUrl(static::getResource()::getUrl('index', [$this->ownerRecord]));
    }

    #[\Override]
    public function getBreadcrumbs(): array
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

    /** @param  \Domain\Content\Models\ContentEntry  $record */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateContentEntryAction::class)
            ->execute($record, ContentEntryData::fromArray($data));
    }
}
