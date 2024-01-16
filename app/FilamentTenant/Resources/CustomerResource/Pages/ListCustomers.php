<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Actions\CreateCustomerAction;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Export\Exports;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use HalcyonAgile\FilamentImport\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

//use Support\Excel\Actions\ExportAction;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            // ImportAction::make()
            //     ->model(Customer::class)
            //     ->uniqueBy('email')
            //     ->tags([
            //         'tenant:'.(tenant('id') ?? 'central'),
            //     ])
            //     ->processRowsUsing(
            //         function (array $row): Customer {
            //             $data = [
            //                 'email' => $row['email'],
            //                 'first_name' => $row['first_name'] ?? '',
            //                 'last_name' => $row['last_name'] ?? '',
            //                 'mobile' => $row['mobile'] ? (string) $row['mobile'] : null,
            //                 'gender' => $row['gender'] ?? null,
            //                 'status' => $row['status'] ?? null,
            //                 'birth_date' => $row['birth_date'] ?? '',
            //                 'tier_id' => isset($row['tier'])
            //                     ? (Tier::whereName($row['tier'])->first()?->getKey())
            //                     : null,
            //             ];
            //             unset($row);
            //             $customer = Customer::whereEmail($data['email'])->first();
            //             if ($customer?->register_status === RegisterStatus::REGISTERED) {
            //                 $data['password'] = $customer->password;
            //                 return app(EditCustomerAction::class)
            //                     ->execute($customer, CustomerData::fromArrayRegisteredImportByAdmin($data));
            //             }
            //             return app(CreateCustomerAction::class)
            //                 ->execute(CustomerData::fromArrayRegisteredImportByAdmin($data));
            //         }
            //     )
            //     ->withValidation(
            //         rules: [
            //             'email' => [
            //                 'required',
            //                 Rule::email(),
            //                 'distinct',
            //             ],
            //             'first_name' => 'required|string|min:3|max:100',
            //             'last_name' => 'required|string|min:3|max:100',
            //             'mobile' => 'nullable|min:3|max:100',
            //             'gender' => ['nullable', Rule::enum(Gender::class)],
            //             'status' => ['nullable', Rule::enum(Status::class)],
            //             'birth_date' => 'nullable|date',
            //             'tier' => [
            //                 'nullable',
            //                 Rule::exists(Tier::class, 'name'),
            //             ],
            //         ],
            //     ),
            //            ExportAction::make()
            //                ->model(Customer::class)
            //                ->queue()
            //                ->query(
            //                    fn (Builder $query) => $query
            //                        ->with('tier')
            //                        ->latest()
            //                )
            //                ->mapUsing(
            //                    ['CUID', 'Email', 'First Name',  'Last Name', 'Mobile', 'Gender', 'Status', 'Birth Date', 'Tier', 'Created At'],
            //                    fn (Customer $customer): array => [
            //                        $customer->cuid,
            //                        $customer->email,
            //                        $customer->first_name,
            //                        $customer->last_name,
            //                        $customer->mobile,
            //                        $customer->gender?->value,
            //                        $customer->status?->value,
            //                        $customer->birth_date?->format(config('tables.date_format')),
            //                        $customer->tier?->name,
            //                        $customer->created_at?->format(config('tables.date_time_format')),
            //                    ]
            //                ),
            Exports::headerList([RegisterStatus::REGISTERED]),
            Actions\CreateAction::make(),
        ];
    }
}
