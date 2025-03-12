<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use App\Features\ECommerce\ECommerceBase;
use App\FilamentTenant\Widgets\Report as ReportWidget;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament-tenant.pages.report';

    protected static ?int $navigationSort = 999;

    public static function shouldRegisterNavigation(): bool
    {
        return TenantFeatureSupport::active(ECommerceBase::class);
    }

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('eCommerce');
    }

    #[\Override]
    protected function getHeaderWidgets(): array
    {
        return [
            ReportWidget\TotalSales::class,
            ReportWidget\ConversionRate::class,
            ReportWidget\MostSoldProduct::class,
            ReportWidget\LeastSoldProduct::class,
            ReportWidget\TotalOrder::class,
            ReportWidget\AverageOrderValue::class,
            ReportWidget\MostFavoriteProduct::class,
        ];
    }
}
