<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Features\Service\ServiceBase;
use App\Settings\ServiceSettings as ServiceCategorySettings;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ServiceSettings extends TenantBaseSettings
{
    protected static string $settings = ServiceCategorySettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'Service Settings';

    public static function authorizeAccess(): bool
    {
        return parent::authorizeAccess() && tenancy()->tenant?->features()->active(ServiceBase::class);
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                Select::make('service_category')
                    ->placeholder(trans('Select Category'))
                    ->options(Taxonomy::pluck('name', 'id'))
                    ->columnSpan('full'),
            ]),
            Section::make('Service Order Section')
            ->schema([
                TextInput::make('days_before_due_date_notification')
                    ->placeholder(trans('Days Before Due Date Notification'))
                    ->numeric()
                    ->minValue(1)
                    ->columnSpan('full'),
            ]),
        ];
    }
}
