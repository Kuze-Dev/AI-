<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceResource;
use Domain\Service\Actions\CreateServiceAction;
use Domain\Service\DataTransferObjects\ServiceData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;

class CreateService extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceResource::class;

    /** @throws Throwable */
    public function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateServiceAction::class)->execute(ServiceData::fromArray($data)));
    }

    /** @throws Exception */
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
}
