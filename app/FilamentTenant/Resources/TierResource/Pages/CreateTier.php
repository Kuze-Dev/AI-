<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TierResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TierResource;
use Domain\Tier\Actions\CreateTierAction;
use Domain\Tier\DataTransferObjects\TierData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTier extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = TierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateTierAction::class)
                ->execute(new TierData(...$data))
        );
    }
}
