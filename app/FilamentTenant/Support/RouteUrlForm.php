<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\Rules\RouteUrlRule;
use Filament\Forms;
use Filament\Forms\Components\Section;

class RouteUrlForm extends Section
{
    public function setUp(): void
    {
        parent::setUp();

        $this->statePath('route_url');
    }

    /** @param class-string $modelUsed */
    public function applySchema(string $modelUsed): static
    {
        $this->schema([
            Forms\Components\TextInput::make('url')
                ->formatStateUsing(
                    fn (?HasRouteUrl $record) => $record?->getActiveRouteUrl()->url
                )
                ->nullable()
                ->string()
                ->alphaDash()
                ->rule(fn (?HasRouteUrl $record) => new RouteUrlRule($modelUsed, $record)),
        ]);

        return $this;
    }
}
