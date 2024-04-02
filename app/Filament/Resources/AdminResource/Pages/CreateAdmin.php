<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\AdminResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Events\Registered;

/**
 * @property-read \Domain\Admin\Models\Admin&\Illuminate\Contracts\Auth\Authenticatable $record
 */
class CreateAdmin extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = AdminResource::class;

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
        event(new Registered($this->record));
    }
}
