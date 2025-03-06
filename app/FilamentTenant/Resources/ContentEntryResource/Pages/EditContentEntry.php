<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentEntryResource\Pages;

use App\Features\CMS\SitesManagement;
use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use App\Settings\CMSSettings;
use App\Settings\SiteSettings;
use Domain\Content\Actions\CreateContentEntryDraftAction;
use Domain\Content\Actions\CreateContentEntryTranslationAction;
use Domain\Content\Actions\PublishedContentEntryDraftAction;
use Domain\Content\Actions\UpdateContentEntryAction;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Models\Locale;
use Domain\Site\Models\Site;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Features\SupportRedirects\Redirector;

/** @method class-string<\Illuminate\Database\Eloquent\Model> getModel()
 *
 * @property \Domain\Content\Models\ContentEntry $record
 */
class EditContentEntry extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ContentEntryResource::class;

    public mixed $ownerRecord;

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
            ActionGroup::make([
                Action::make('published')
                    ->label(trans('Published Draft'))
                    ->action('published')
                    ->hidden(fn () => $this->record->draftable_id === null),
                Action::make('draft')
                    ->label(trans('Save As Draft'))
                    ->action('draft')
                    ->hidden(function () {

                        if ($this->record->draftable_id != null) {
                            return true;
                        }

                        return $this->record->draftable_id === null && $this->record->pageDraft;
                    }),
                Action::make('overwriteDraft')
                    ->label(trans('Save As Draft'))
                    ->action(function (Action $action) {
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

                        $action->redirect(ContentEntryResource::getUrl('edit', [$this->ownerRecord, $draftpage]));

                    })
                    ->requiresConfirmation()
                    ->modalHeading('Draft for this content already exists')
                    ->modalDescription('You have an existing draft for this content. Do you want to overwrite the existing draft?')
                    ->modalCancelActionLabel('Redirect')
                    ->modalCancelAction(fn (StaticAction $action) => $action->url(
                        ContentEntryResource::getUrl('edit', [$this->ownerRecord, $this->record->pageDraft])
                    ))
                    ->hidden(fn () => ($this->record->pageDraft && $this->record->draftable_id == null) ? false : true),
                Action::make('save')
                    ->label(trans('Save and Continue Editing'))
                    ->action('save')
                    ->keyBindings(['mod+s']),
            ])
                ->button()
                ->icon('')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label')),
            Actions\DeleteAction::make(),
            ActionGroup::make([
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
                Action::make('createTranslation')
                    ->color('secondary')
                    ->slideOver(true)
                    ->action(function (Action $action) {
                        /** @var array */
                        $data = $action->getFormData();

                        return $this->createTranslation($data);

                    })
                    // ->action('createTranslation')
                    ->hidden((bool) \Domain\Tenant\TenantFeatureSupport::inactive(\App\Features\CMS\Internationalization::class))
                    ->form([
                        Forms\Components\Select::make('locale')
                            ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                            ->default((string) Locale::where('is_default', true)->first()?->code)
                            ->searchable()
                            ->hidden((bool) \Domain\Tenant\TenantFeatureSupport::inactive(\App\Features\CMS\Internationalization::class))
                            ->reactive()
                            ->required(),
                    ]),
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

                                /** @var ContentEntry $site */
                                $site = $this->getRecord();

                                return $site->sites()->orderby('name')->pluck('name', 'id')->toArray();
                            })
                            ->descriptions(function () {

                                /** @var ContentEntry $site */
                                $site = $this->getRecord();

                                return $site->sites()->orderby('name')->pluck('domain', 'id')->toArray();
                            })
                            ->reactive()
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, $state, self $livewire) {

                                /** @var Site $site */
                                $site = Site::find($state);

                                $domain = $site->domain;

                                /** @var Action $modelAction */
                                $modelAction = $livewire->getAction('preview_microsite_action');

                                $modelAction->modalSubmitAction(function (StaticAction $action) use ($domain) {

                                    $queryString = Str::after(URL::temporarySignedRoute('tenant.api.contents.entries.show', now()->addMinutes(15), [$this->ownerRecord, $this->record], false), '?');

                                    return $action->url("https://{$domain}/preview?contents={$this->ownerRecord->slug}&slug={$this->record->slug}&{$queryString}", true);
                                });

                                $set('domain', $domain);
                            }),

                    ]),
                Action::make('clone-page')
                    ->label(trans('Clone Entry'))
                    ->color('secondary')
                    ->record($this->getRecord())
                    ->url(fn (ContentEntry $record) => ContentEntryResource::getUrl('create', [
                        $this->ownerRecord,
                        'clone' => $record->slug,
                    ])),

            ])
                ->button()
                ->color('gray')
                ->icon('')
                ->label(trans('More Actions')),
        ];
    }

    public function draft(): RedirectResponse|Redirector|false
    {
        $data = $this->form->getState();

        $record = $this->record;

        $pageData = ContentEntryData::fromArray($data);

        // check if page has existing draft

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

        /** @var \Domain\Content\Models\ContentEntry $parentPage */
        $parentPage = $pageDraft->parentPage;

        $data['published_draft'] = true;

        $contentEntryData = ContentEntryData::fromArray($data);

        $contentEntry = app(PublishedContentEntryDraftAction::class)->execute(
            $parentPage,
            $pageDraft,
            $contentEntryData
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

    public function createTranslation(array $data): RedirectResponse|Redirector|false
    {
        $record = $this->record;

        $admin = filament_admin();

        if ($record->draftable_id) {

            Notification::make()
                ->danger()
                ->title(trans('Invalid Action'))
                ->body(trans('Cannot Create Translation base on Draft Content'))
                ->send();

            Notification::make()
                ->danger()
                ->title(trans('Invalid Action'))
                ->body(trans('Cannot Create Translation base on Draft Content'))
                ->sendToDatabase($admin);

            return false;
        }
        $formData = $this->form->getState();

        $formData['locale'] = $data['locale'];

        $code = $data['locale'];

        $formData['route_url']['url'] = $this->changeUrlLocale($formData['route_url']['url'], $code);

        $orginalContent = $record->parentTranslation ?? $record;

        $exist = ContentEntry::where(fn ($query) => $query->where('translation_id', $orginalContent->id)->orWhere('id', $orginalContent->id)
        )->where('locale', $data['locale'])->first();

        /** @var \Domain\Internationalization\Models\Locale */
        $locale = Locale::whereCode($data['locale'])->first();

        if ($exist) {

            Notification::make()
                ->danger()
                ->title(trans('Translation Already Exists'))
                ->body(trans('Content Entry :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
                ->send();

            Notification::make()
                ->danger()
                ->title(trans('Translation Already Exists'))
                ->body(trans('Content Entry :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
                ->sendToDatabase($admin);

            return false;
        }

        $contentEntryData = ContentEntryData::fromArray($formData);

        $contentEntryTranslation = app(CreateContentEntryTranslationAction::class)->execute($orginalContent, $contentEntryData);

        Notification::make()
            ->success()
            ->title(trans('Translation Created'))
            ->body(trans('Page Translation :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
            ->sendToDatabase($admin);

        return redirect(ContentEntryResource::getUrl('edit', [$this->ownerRecord, $contentEntryTranslation]));
    }

    protected function changeUrlLocale(string $url, string $locale): string
    {

        $locales = Locale::pluck('code')->toArray();

        // Remove leading and trailing slashes from the URL
        $url = trim($url, '/');

        // Split the URL by "/"
        $segments = explode('/', $url);

        // Check if the first segment is a valid locale code from the array
        if (in_array($segments[0], $locales)) {
            // Replace the existing locale with the new one
            $segments[0] = $locale;
        } else {
            // Prepend the new locale to the URL
            array_unshift($segments, $locale);
        }

        // Rebuild the URL and add a leading "/"
        return '/'.implode('/', $segments);
    }
}
