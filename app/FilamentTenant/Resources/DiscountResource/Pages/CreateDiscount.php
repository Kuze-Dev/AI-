<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\DiscountResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\DiscountResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }
}
