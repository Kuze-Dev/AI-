<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MediaresourceResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\MediaresourceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaresource extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = MediaresourceResource::class;

    protected function getActions(): array
    {
        return [
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
