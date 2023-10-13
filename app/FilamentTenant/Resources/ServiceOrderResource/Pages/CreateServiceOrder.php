<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use Domain\ServiceOrder\Actions\PlaceServiceOrderAction;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateServiceOrder extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    public function handleRecordCreation(array $data): Model
    {
        $user = Auth::user();

        return DB::transaction(fn () => app(PlaceServiceOrderAction::class)->execute($data, null, $user?->id));
    }

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
