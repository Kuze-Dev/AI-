<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Illuminate\Database\Eloquent\Model;
use Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Internationalization\Models\Locale;
use Support\RouteUrl\Rules\UniqueActiveRouteUrlRule;
use Support\RouteUrl\Rules\MicroSiteUniqueRouteUrlRule;

class RouteUrlFieldset extends Group
{
    protected ?Closure $generateModelForRouteUrlUsing = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->statePath('route_url');

        $this->id('route_url');

        $this->registerListeners([
            'route_url::update' => [
                function (self $component): void {
                    $component->evaluate(function (HasRouteUrl|string $model, Closure $get, Closure $set, array $state) {
                        if ((bool) $get('is_override')) {
                            return;
                        }

                        $locale = $get('locale');
                        $defaultLocale = Locale::where('is_default', true)->first()?->code;

                        $newUrl = $model::generateRouteUrl($this->getModelForRouteUrl(), $get('data', true));
                        $newUrl = $locale !== $defaultLocale ? "/$locale$newUrl" : $newUrl;

                        $set('route_url.url', $newUrl);
                    });
                },
            ],
            'route_url::input' => [
                function (self $component): void {
                    $component->evaluate(function (HasRouteUrl|string $model, Closure $get, Closure $set) {
                        if ((bool) $get('is_override')) {
                            return;
                        }
                        $locale = $get('locale');
                        $defaultLocale = Locale::where('is_default', true)->first()?->code;

                        $inputUrl = $get('route_url.url');
                        $inputUrl = stripos($inputUrl[0], '/', ) !== false ? $inputUrl : '/' . $inputUrl; //checks if input has slash at first character
                        $newUrl = $locale !== $defaultLocale ? "/$locale$inputUrl" : $inputUrl;

                        $set('route_url.url', $newUrl);
                    });
                }],
        ]);

        $this->columns('grid-cols-[10rem,1fr] items-center');

        $this->schema([
            Forms\Components\Toggle::make('is_override')
                ->formatStateUsing(fn (?HasRouteUrl $record) => $record?->activeRouteUrl?->is_override)
                ->label(trans('Custom URL'))
                ->reactive()
                ->afterStateUpdated(fn () => $this->dispatchEvent('route_url::update')),
            Forms\Components\TextInput::make('url')
                ->label(trans('URL'))
                ->disabled(fn (Closure $get) => ! (bool) $get('is_override'))
                ->formatStateUsing(fn (?HasRouteUrl $record) => $record?->activeRouteUrl?->url)
                ->lazy()
                ->required()
                ->string()
                ->maxLength(255)
                ->startsWith('/')
                ->rule(
                    fn (?HasRouteUrl $record, Closure $get) => tenancy()->tenant?->features()->inactive(SitesManagement::class) ?
                        new UniqueActiveRouteUrlRule($record) : null
                    // new MicroSiteUniqueRouteUrlRule($record, $get('sites'))
                )
                ->afterStateUpdated(fn () => $this->dispatchEvent('route_url::input')),
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
