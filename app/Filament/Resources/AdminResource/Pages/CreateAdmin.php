<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\AdminResource;
use Domain\Admin\Actions\CreateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateAdmin extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = AdminResource::class;

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
        return DB::transaction(fn () => app(CreateAdminAction::class)->execute(new AdminData(...$data)));
    }
}
