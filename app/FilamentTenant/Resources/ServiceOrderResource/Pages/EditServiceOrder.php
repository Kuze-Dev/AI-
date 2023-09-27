<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use App\FilamentTenant\Resources\ServiceResource;
use Domain\Service\Actions\UpdateServiceAction;
use Domain\Service\DataTransferObjects\ServiceData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditServiceOrder extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

}
