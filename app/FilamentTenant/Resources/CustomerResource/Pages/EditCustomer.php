<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Actions\DeleteCustomerAction;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\Actions\ForceDeleteCustomerAction;
use Domain\Customer\Actions\RestoreCustomerAction;
use Domain\Customer\Actions\SendRejectedEmailAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Domain\Tier\Enums\TierApprovalStatus;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Throwable;
use Exception;
use Filament\Notifications\Notification;
use Livewire\Redirector;

class EditCustomer extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [

            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->requiresConfirmation(function ($livewire) {

                    return $livewire->data['tier_approval_status'] == TierApprovalStatus::REJECTED->value ? true : false;

                })
                ->modalHeading(fn ($livewire) => $livewire->data['tier_approval_status'] ? 'Warning' : null)
                ->modalSubheading(fn ($livewire) => $livewire->data['tier_approval_status'] ? 'Rejecting will delete this customer. Would  you like to continue?' : null)
                ->action(fn ($livewire) => $livewire->data['tier_approval_status'] == TierApprovalStatus::REJECTED->value ? $this->deleteIfRejectedCustomer() : $this->save())
                ->keyBindings(['mod+s']),

            Actions\DeleteAction::make()
                ->using(function (Customer $record) {
                    try {
                        return app(DeleteCustomerAction::class)->execute($record);
                    } catch (DeleteRestrictedException $e) {
                        return false;
                    }
                }),
            Actions\ForceDeleteAction::make()
                ->using(function (Customer $record) {
                    try {
                        return app(ForceDeleteCustomerAction::class)->execute($record);
                    } catch (DeleteRestrictedException $e) {
                        return false;
                    }
                }),
            Actions\RestoreAction::make()
                ->using(
                    fn (Customer $record) => app(RestoreCustomerAction::class)
                        ->execute($record)
                ),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param \Domain\Customer\Models\Customer $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(EditCustomerAction::class)
                ->execute($record, CustomerData::fromArrayEditByAdmin($record, $data))
        );
    }

    public function deleteIfRejectedCustomer(): Redirector
    {
        $data = $this->form->getState();

        $record = $this->record;

        if($data['tier_approval_status'] === TierApprovalStatus::REJECTED->value) {

            app(ForceDeleteCustomerAction::class)->execute($record);

            Notification::make()
                ->warning()
                ->title(trans('Customer Deleted'))
                ->send();

            app(SendRejectedEmailAction::class)->execute($record);

            return redirect(CustomerResource::getUrl('index'));
        }

        return redirect(CustomerResource::getUrl('edit', ['record' => $record]));

    }
}
