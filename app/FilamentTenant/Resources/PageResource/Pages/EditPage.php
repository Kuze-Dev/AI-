<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\FilamentTenant\Resources\PageResource;
use App\Settings\CMSSettings;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Exception;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\URL;

/**
 * @property \Domain\Page\Models\Page $record
 */
class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            Action::make('preview')
                ->label(__('Preview Page'))
                ->action(function (CMSSettings $cmsSettings) {
                    $pageUrl = $cmsSettings->front_end_preview_page_url ?? null;

                    if (
                        ! blank($pageUrl) && is_string($pageUrl)
                        && str_contains($pageUrl, '{slug}')
                    ) {
                        $previewPageUrl = str_replace('{slug}', $this->record->slug, $pageUrl);

                        $queryString = Str::after(URL::temporarySignedRoute('tenant.api.pages.show', now()->addMinutes(15), [$this->record->slug], false), '?');

                        $this->redirect($previewPageUrl . '?' . $queryString);
                    }
                }),
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
