<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\PageResource;
use App\Settings\CMSSettings;
use App\Settings\SiteSettings;
use Domain\Page\Actions\DeletePageAction;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Exception;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\URL;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

/**
 * @property \Domain\Page\Models\Page $record
 */
class EditPage extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = PageResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make()->using(function (Page $record) {
                try {
                    return app(DeletePageAction::class)->execute($record);
                } catch (DeleteRestrictedException $e) {
                    return false;
                }
            }),
            Action::make('preview')
                ->color('secondary')
                ->label(__('Preview Page'))
                ->url(function (SiteSettings $siteSettings, CMSSettings $cmsSettings) {
                    $domain = $siteSettings->front_end_domain ?? $cmsSettings->front_end_domain;

                    if ( ! $domain) {
                        return null;
                    }

                    $queryString = Str::after(URL::temporarySignedRoute('tenant.api.pages.show', now()->addMinutes(15), [$this->record->slug], false), '?');

                    return "https://{$domain}/preview?slug={$this->record->slug}&{$queryString}";
                }, true),
            Action::make('clone-page')
                ->label(__('Clone Page'))
                ->color('secondary')
                ->record($this->getRecord())
                ->url(fn (Page $record) => PageResource::getUrl('create', ['clone' => $record->slug])),
        ];
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
}
