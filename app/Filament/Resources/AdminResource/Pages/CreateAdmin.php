<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Domain\Admin\Actions\CreateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateAdminAction::class)->execute(new AdminData(...$data)));
    }
}
