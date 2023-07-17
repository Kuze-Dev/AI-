<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Actions\DeleteCustomerAction;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\Actions\ForceDeleteCustomerAction;
use Domain\Customer\Actions\RestoreCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Throwable;
use Exception;

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
                ->action('save')
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
                ->execute($record, CustomerData::fromArrayEditByAdmin($data))
        );
    }
}
