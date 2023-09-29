<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceOrder extends ViewRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Group::make()
                        // ->schema($this->getSections())
                        ->columnSpan(2),
                    // OrderResource::summaryCard(),
                ])->columns(3),
        ];
    }
}
