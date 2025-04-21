<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Tier\Enums\TierApprovalStatus;
use Exception;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

/**
 * @property-read \Domain\Customer\Models\Customer $record
 */
class CreateCustomer extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
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

    #[\Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = Status::INACTIVE;
        $data['register_status'] = RegisterStatus::UNREGISTERED;
        $data['tier_approval_status'] = TierApprovalStatus::APPROVED;

        return parent::mutateFormDataBeforeCreate($data);
    }
}
