<?php

declare(strict_types=1);

namespace Domain\Customer\Imports;

use Domain\Customer\Actions\ImportCustomerAction;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    //    public function getJobQueue(): ?string
    //    {
    //        return 'central';
    //    }

    #[\Override]
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::email(),
                    'distinct',
                ]),

            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'min:3', 'max:100']),

            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'min:3', 'max:100']),

            ImportColumn::make('mobile')
                ->rules(['nullable', 'min:3', 'max:100']),

            ImportColumn::make('gender')
                ->rules(['nullable', Rule::enum(Gender::class)]),

            ImportColumn::make('birth_date')
                ->rules(['nullable', 'date']),

            ImportColumn::make('tier_id')
                ->label('Tier')
                ->rules([
                    'nullable',
                    Rule::exists(Tier::class, 'name'),
                ]),
        ];
    }

    #[\Override]
    public function resolveRecord(): ?Customer
    {
        return app(ImportCustomerAction::class)
            ->execute($this->data);
        //        return Customer::firstOrNew([
        //            'email' => $this->data['email'],
        //        ]);
    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer import has completed and '.
            number_format($import->successful_rows).' '.Str::of('row')
                ->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.
                Str::of('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
