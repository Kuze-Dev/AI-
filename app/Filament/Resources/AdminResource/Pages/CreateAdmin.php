<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Domain\Admin\Actions\CreateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateAdminAction::class)->execute(new AdminData(...$data));
    }
}
