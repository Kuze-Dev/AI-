<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\FilamentTenant\Resources\PageResource;
use App\Settings\CMSSettings;
use Domain\Page\Actions\ClonePageAction;
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
class ClonePage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getTitle(): string
    {
        return "Clone {$this->record->name}";
    }

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->fillForm();

        $this->data['name'] = '';
        $this->data['route_url']['url'] = '';
        $this->data['meta_data']['title'] = '';

        $this->previousUrl = url()->previous();
    }

    public function getBreadcrumb(): string
    {
        return 'Clone';
    }

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
                ->color('secondary')
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
        return DB::transaction(fn () => app(ClonePageAction::class)->execute(PageData::fromArray($data)));
    }

    protected function afterSave(): void
    {
        $this->redirect(route('filament-tenant.resources.pages.index'));
    }
}
