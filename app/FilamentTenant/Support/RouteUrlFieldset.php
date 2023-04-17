<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\Rules\UniqueActiveRouteUrlRule;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Illuminate\Database\Eloquent\Model;

class RouteUrlFieldset extends Group
{
    protected ?Closure $generateModelForRouteUrlUsing = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->id('route_url');

        $this->registerListeners([
            'route_url::update' => [
                function (self $component): void {
                    $component->evaluate(function (HasRouteUrl|string $model, Closure $get, Closure $set, array $state) {
                        if ((bool) $get('is_custom_url')) {
                            return;
                        }

                        $set('route_url', $model::generateRouteUrl($this->getModelForRouteUrl(), $state));
                    });
                },
            ],
        ]);

        $this->schema([
            Forms\Components\Toggle::make('is_override')
                ->formatStateUsing(fn (?HasRouteUrl $record) => $record?->activeRouteUrl->is_override)
                ->label(trans('Custom URL'))
                ->reactive()
                ->afterStateUpdated(fn () => $this->dispatchEvent('route_url::update')),
            Forms\Components\TextInput::make('route_url')
                ->disabled(fn (Closure $get) => ! (bool) $get('is_override'))
                ->formatStateUsing(fn (?HasRouteUrl $record) => $record?->activeRouteUrl->url)
                ->lazy()
                ->required()
                ->string()
                ->startsWith('/')
                ->rule(fn (?HasRouteUrl $record) => new UniqueActiveRouteUrlRule($record)),
        ]);

        $this->generateModelForRouteUrlUsing(function (HasRouteUrl|string $model) {
            return $model instanceof Model ? $model : new $model();
        });
    }

    public function generateModelForRouteUrlUsing(Closure $callback): self
    {
        $this->generateModelForRouteUrlUsing = $callback;

        return $this;
    }

    public function getModelForRouteUrl(): Model
    {
        return $this->evaluate($this->generateModelForRouteUrlUsing);
    }
}
