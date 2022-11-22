<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\FilamentTenant\Resources\PageResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Page\Actions\UpdatePageContentAction;
use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Models\Page;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;
use Exception;

/**
 * @property-read \Domain\Page\Models\Page $record
 */
class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Actions\Action::make('configure')
                ->icon('heroicon-s-cog')
                ->url(route('filament-tenant.resources.' . self::$resource::getSlug() . '.configure', $this->record)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            SchemaFormBuilder::make('data', fn (Page $record) => $record->blueprint->schema),
        ];
    }

    /**
     * @param \Domain\Page\Models\Page $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdatePageContentAction::class)
                ->execute($record, new PageContentData(
                    data: $data['data'],
                ))
        );
    }
}
