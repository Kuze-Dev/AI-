<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\PageResource;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreatePage extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = PageResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(__('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreatePageAction::class)->execute(PageData::fromArray($data)));
    }
}
