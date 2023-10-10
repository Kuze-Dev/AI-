<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\Filament\Livewire\Actions\CustomPageActionGroup;
use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\PageResource;
use App\Settings\CMSSettings;
use App\Settings\SiteSettings;
use Domain\Page\Actions\DeletePageAction;
use Closure;
use Domain\Page\Actions\CreatePageDraftAction;
use Domain\Page\Actions\PublishedPageDraftAction;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Site\Models\Site;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Exception;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\URL;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Illuminate\Http\RedirectResponse;
use Livewire\Redirector;

/**
 * @property \Domain\Page\Models\Page $record
 */
class EditPage extends EditRecord
{
    use LogsFormActivity;

    protected bool $publish_draft = false;

    protected static string $resource = PageResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            'page_actions' => CustomPageActionGroup::make([
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
                    ->modalHeading('Draft for this page already exists')
                    ->modalSubheading('You have an existing draft for this page. Do you want to overwrite the existing draft?')
                    ->modalCancelAction(function () {
                        return Action::makeModalAction('redirect')
                            ->label(__('Edit Existing Draft'))
                            ->color('secondary')
                            ->url(PageResource::getUrl('edit', ['record' => $this->record->pageDraft]));
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
            Actions\DeleteAction::make()->using(function (Page $record) {
                try {
                    return app(DeletePageAction::class)->execute($record);
                } catch (DeleteRestrictedException $e) {
                    return false;
                }
            }),
            Actions\DeleteAction::make(),
            'other_page_actions' => CustomPageActionGroup::make([
                Action::make('preview')
                    ->color('secondary')
                    ->hidden((bool) tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                    ->label(__('Preview Page'))
                    ->url(function (SiteSettings $siteSettings, CMSSettings $cmsSettings) {
                        $domain = $siteSettings->front_end_domain ?? $cmsSettings->front_end_domain;

                        if ( ! $domain) {
                            return null;
                        }

                        $queryString = Str::after(URL::temporarySignedRoute('tenant.api.pages.show', now()->addMinutes(15), [$this->record->slug], false), '?');

                        return "https://{$domain}/preview?slug={$this->record->slug}&{$queryString}";
                    }, true),
                Action::make('preview_microsite_action')
                    ->label('Preview Microsite')
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\SitesManagement::class))
                    ->color('secondary')
                    ->record($this->getRecord())
                    ->modalHeading('Preview Microsite')
                    ->slideOver(true)
                    ->action(function (Page $record, Action $action, array $data): void {

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

                                /** @var Page */
                                $site = $this->getRecord();

                                return $site->sites()->orderby('name')->pluck('name', 'id')->toArray();
                            })
                            ->descriptions(function () {

                                /** @var Page */
                                $site = $this->getRecord();

                                return $site->sites()->orderby('name')->pluck('domain', 'id')->toArray();
                            })
                            ->reactive()
                            ->afterStateUpdated(function (Closure $set, $state, $livewire) {

                                /** @var Site */
                                $site = Site::find($state);

                                $domain = $site->domain;

                                /** @var CustomPageActionGroup */
                                $other_page_actions = $livewire->getCachedActions()['other_page_actions'];

                                $modelAction = $other_page_actions->getActions()['preview_microsite_action'];

                                $modelAction->modalSubmitAction(function () use ($domain) {

                                    $queryString = Str::after(URL::temporarySignedRoute('tenant.api.pages.show', now()->addMinutes(15), [$this->record->slug], false), '?');

                                    return Action::makeModalAction('preview')->url("https://{$domain}/preview?slug={$this->record->slug}&{$queryString}", true);
                                });

                                $set('domain', $domain);
                            }),

                    ]),
                Action::make('clone-page')
                    ->label(__('Clone Page'))
                    ->color('secondary')
                    ->record($this->getRecord())
                    ->url(fn (Page $record) => PageResource::getUrl('create', ['clone' => $record->slug])),
            ])->view('filament.pages.actions.custom-action-group.index')
                ->setName('other_page_draft')
                ->color('secondary')
                ->label(trans('More Actions')),

        ];
    }

    public function micrositePreview(array $data): void
    {
        /** @var Site */
        $site = Site::find($data['preview_microsite']);

        if ($site->domain == null) {

            Notification::make()
                ->danger()
                ->title(trans('No Domain Set'))
                ->body(trans('Please set a domain for :value to preview.', ['value' => $site->name]))
                ->send();

        }

    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param \Domain\Page\Models\Page $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdatePageAction::class)->execute($record, PageData::fromArray($data)));
    }

    protected function afterSave(): void
    {
        $this->record->refresh();
        $this->hasCachedForms = false;

        $this->fillForm();
    }

    public function overwriteDraft(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $record = $this->record;

        $record->pageDraft?->delete();

        $pageData = PageData::fromArray($data);

        $draftpage = app(CreatePageDraftAction::class)->execute($record, $pageData);

        Notification::make()
            ->success()
            ->title(trans('Overwritten Draft'))
            ->body(trans('Page Draft has been overwritten'))
            ->send();

        return redirect(PageResource::getUrl('edit', ['record' => $draftpage]));
    }

    public function published(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $pageDraft = $this->record;

        /** @var Page */
        $parentPage = $pageDraft->parentPage;

        $data['published_draft'] = true;
        $data['published_at'] = now();

        $pageData = PageData::fromArray($data);

        $page = DB::transaction(
            fn () => app(PublishedPageDraftAction::class)->execute(
                $parentPage,
                $pageDraft,
                $pageData
            )
        );

        return redirect(PageResource::getUrl('edit', ['record' => $page]));
    }

    public function draft(): RedirectResponse|Redirector|false
    {
        $data = $this->form->getState();

        $record = $this->record;

        $pageData = PageData::fromArray($data);

        #check if page has existing draft

        if( ! is_null($record->pageDraft)) {

            Notification::make()
                ->danger()
                ->title(trans('Has Draft'))
                ->body(trans('Page :title has a existing draft', ['title' => $record->name]))
                ->send();

            return false;
        }

        $draftpage = app(CreatePageDraftAction::class)->execute($record, $pageData);

        return redirect(PageResource::getUrl('edit', ['record' => $draftpage]));
    }
}
