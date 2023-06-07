<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\PageResource;
use App\Settings\CMSSettings;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;
use Exception;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\URL;

/**
 * @property \Domain\Page\Models\Page $record
 */
class EditPage extends EditRecord
{
    use LogsFormActivity {
        afterSave as protected afterSaveOverride;
    }

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
                ->label(__('Preview'))
                ->visible(fn (CMSSettings $cmsSettings) => filled($cmsSettings->front_end_preview_page_url))
                ->action(function (CMSSettings $cmsSettings) {
                    if ($cmsSettings->front_end_preview_page_url === null) {
                        return;
                    }

                    $previewPageUrl = str_replace('{slug}', $this->record->slug, $cmsSettings->front_end_preview_page_url);
                    $showPageApiUrl = URL::temporarySignedRoute('tenant.api.pages.show', now()->addMinutes(15), [$this->record->slug], false);
                    $queryString = parse_url($showPageApiUrl, PHP_URL_QUERY);

                    $this->redirect($previewPageUrl . '?' . $queryString);
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
        $this->afterSaveOverride();

        $this->record->refresh();
        $this->hasCachedForms = false;

        $this->fillForm();
    }
}
