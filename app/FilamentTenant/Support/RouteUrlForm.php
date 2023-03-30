<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Filament\Forms;
use Filament\Forms\Components\Section;

class RouteUrlForm extends Section
{
    public function setUp(): void
    {
        parent::setUp();

        $this->schema([
            Forms\Components\TextInput::make('route_url')
                ->formatStateUsing(fn ($record) => $record?->route_url),
        ]);
    }
}
