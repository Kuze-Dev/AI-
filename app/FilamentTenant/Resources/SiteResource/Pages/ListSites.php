<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SiteResource\Pages;

use App\FilamentTenant\Resources\SiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    /** @return Builder<\Domain\Site\Models\Site> */
    #[\Override]
    protected function getTableQuery(): Builder
    {
        $admin = filament_admin();

        $query = static::getResource()::getEloquentQuery();

        if ($admin->hasRole(config()->string('domain.role.super_admin'))) {
            return static::getResource()::getEloquentQuery();
        }

        return $query->whereHas('siteManager', function ($subquery) use ($admin) {
            $subquery->where('admin_id', $admin->id);
        });
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
