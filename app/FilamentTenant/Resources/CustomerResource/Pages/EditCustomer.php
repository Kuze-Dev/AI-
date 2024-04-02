<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Actions\SendApprovedEmailAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Domain\Customer\Notifications\RejectedRegistrationNotification;
use Domain\Tier\Enums\TierApprovalStatus;
use Domain\Tier\Models\Tier;
use Exception;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

/**
 * @property-read \Domain\Customer\Models\Customer $record
 */
class EditCustomer extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),

            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    #[\Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $customerTier = null;
        if (isset($data['tier_id'])) {
            $customerTier = Tier::whereId($data['tier_id'])->first();
        }

        $tierApprovalStatus = isset($data['tier_approval_status'])
            ? TierApprovalStatus::from($data['tier_approval_status'])
            : null;

        $data['register_status'] = CustomerData::getStatus($customerTier, $tierApprovalStatus, $this->record);

        return parent::mutateFormDataBeforeSave($data);
    }

    public function afterSave(): void
    {
        $customer = $this->record;

        if ($customer->wasChanged('email')) {
            $customer->forceFill(['email_verified_at' => null])
                ->save();

            $customer->sendEmailVerificationNotification();
        }

        if ($customer->tier_approval_status == TierApprovalStatus::APPROVED) {
            app(SendApprovedEmailAction::class)->execute($customer);
            $customer->sendEmailVerificationNotification();
        }
    }

    //    public function deleteIfRejectedCustomer(): Redirector|RedirectResponse
    //    {
    //        ray('aaa');
    //        $data = $this->form->getState();
    //
    //        /** @var \Domain\Customer\Models\Customer $record */
    //        $record = $this->record;
    //
    //        if ($data['tier_approval_status'] === TierApprovalStatus::REJECTED->value) {
    //
    //            $email = $record->email;
    //
    //            \Illuminate\Support\Facades\Notification::route('mail', $email)->notify(new RejectedRegistrationNotification());
    //
    //            $record->forceDelete();
    //
    //            Notification::make()
    //                ->warning()
    //                ->title(trans('Customer Deleted'))
    //                ->send();
    //
    //            return redirect(CustomerResource::getUrl('index'));
    //        }
    //
    //        return redirect(CustomerResource::getUrl('edit', ['record' => $record]));
    //
    //    }
}
