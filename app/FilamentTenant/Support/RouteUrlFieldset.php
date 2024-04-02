<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\Features\CMS\Internationalization;
use App\Features\CMS\SitesManagement;
use Closure;
use Domain\Internationalization\Models\Locale;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Support\RouteUrl\Contracts\HasRouteUrl;
use Support\RouteUrl\Rules\UniqueActiveRouteUrlRule;

class RouteUrlFieldset extends Group
{
    protected ?Closure $generateModelForRouteUrlUsing = null;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->statePath('route_url');

        $this->id('route_url');

        $this->registerListeners([
            'route_url::update' => [
                function (self $component, ...$eventParameters): void {
                    $component->evaluate(function (HasRouteUrl|string $model, \Filament\Forms\Get $get, \Filament\Forms\Set $set, array $state) use ($eventParameters) {
                        if ((bool) $get('is_override')) {
                            return;
                        }
                        /** @var string */
                        $locale = $get('locale');
                        $defaultLocale = Locale::where('is_default', true)->first()?->code;

                        if ($eventParameters && $eventParameters[0] === 'input') {
                            /** @var string */
                            $inputUrl = $get('route_url.url');
                            $inputUrl = Str::startsWith($inputUrl, '/') ?
                                Str::contains($inputUrl, "/$locale/") ? Str::replace("/$locale/", '/', $inputUrl) : $inputUrl
                                : '/'.$inputUrl;

                            $newUrl = $locale !== $defaultLocale && TenantFeatureSupport::active(Internationalization::class) ?
                                "/$locale$inputUrl" : $inputUrl;

                            $set('route_url.url', $newUrl);

                            return;
                        }

                        $newUrl = $model::generateRouteUrl($this->getModelForRouteUrl(), $get('data', true));
                        $newUrl = $locale !== $defaultLocale && TenantFeatureSupport::active(Internationalization::class) ? "/$locale$newUrl" : $newUrl;

                        $set('route_url.url', $newUrl);
                    });
                },
            ],
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
                ->readOnly(fn (\Filament\Forms\Get $get) => ! (bool) $get('is_override'))
                // ->disabled(fn (\Filament\Forms\Get $get) => ! (bool) $get('is_override'))
                ->formatStateUsing(fn (?HasRouteUrl $record) => $record?->activeRouteUrl?->url)
                ->lazy()
                ->required()
                ->string()
                ->maxLength(255)
                ->startsWith('/')
                ->rule(
                    fn (?HasRouteUrl $record) => TenantFeatureSupport::inactive(SitesManagement::class) ?
                        new UniqueActiveRouteUrlRule($record) : null
                    // new MicroSiteUniqueRouteUrlRule($record, $get('sites'))
                )
                ->afterStateUpdated(fn () => $this->dispatchEvent('route_url::update', 'input')),
        ]);

        $this->generateModelForRouteUrlUsing(function (HasRouteUrl|string $model) {
            return $model instanceof Model ? $model : new $model();
        });
    }

    public function getModelForRouteUrl(): Model
    {
        return $this->evaluate($this->generateModelForRouteUrlUsing);
    }

    public function generateModelForRouteUrlUsing(Closure $callback): self
    {
        $this->generateModelForRouteUrlUsing = $callback;

        return $this;
    }
}
