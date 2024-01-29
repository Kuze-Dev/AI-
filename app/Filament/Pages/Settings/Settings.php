<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Filament\Resources\RoleResource\Support\PermissionGroup;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class Settings extends Page
{
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.pages.settings';

    public static function getNavigationGroup(): ?string
    {
        return trans('System');
    }

    public function mount(): void
    {
        abort_unless(self::getSettingsPages()->isNotEmpty(), 404);
        abort_unless(self::authorizeAccess(), 403);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::getSettingsPages()->isNotEmpty() && self::authorizeAccess();
    }

    protected static function authorizeAccess(): bool
    {
        $settingsPermissions = app(PermissionRegistrar::class)
            ->getPermissions()
            ->filter(fn (Permission $permission) => Str::startsWith($permission->name, 'settings'));

        if ($settingsPermissions->isEmpty()) {
            return true;
        }

        $settingsPermissionsParts = PermissionGroup::make($settingsPermissions)->getParts();

        if ($settingsPermissionsParts->isEmpty()) {
            return true;
        }

        return Auth::user()?->can('settings.'.$settingsPermissionsParts->join(',')) ?? false;
    }

    public function getBreadcrumbs(): array
    {
        return [
            self::getUrl() => trans('Settings'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.pages.settings*'))
                ->sort(static::getNavigationSort())
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->url(static::getNavigationUrl()),
        ];
    }

    public function getViewData(): array
    {
        return [
            'settings' => self::getSettingsPages(),
        ];
    }

    /** @return Collection<int, class-string<SettingsPage>|SettingsPage> */
    protected static function getSettingsPages(): Collection
    {
        return collect(Filament::getPages())
            ->filter(fn (string $pageClass) => is_subclass_of($pageClass, SettingsPage::class));
    }
}
