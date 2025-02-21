<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\TenantResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

/**
 * @property-read \Domain\Tenant\Models\Tenant $record
 */
class CreateTenant extends CreateRecord
{
    use LogsFormActivity;
    use Support;

    protected static string $resource = TenantResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    public function afterCreate(): void
    {
        /** @var array{feature: array} $data */
        $data = $this->form->getRawState();

        $this->record->syncFeature(self::getNormalizedFeatureNames($data['features']));
    }
}
