<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SiteResource\Pages;

use App\FilamentTenant\Resources\SiteResource;
use Filament\Pages\Actions;
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
        /** @var \Domain\Admin\Models\Admin */
        $user = Auth::user();

        $query = static::getResource()::getEloquentQuery();

        if ($user->hasRole(config('domain.role.super_admin'))) {
            return static::getResource()::getEloquentQuery();
        }

        return $query->whereHas('siteManager', function ($subquery) use ($user) {
            $subquery->where('admin_id', $user->id);
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
