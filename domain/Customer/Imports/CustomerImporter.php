<?php

declare(strict_types=1);

namespace Domain\Customer\Imports;

use App\Settings\CustomerSettings;
use Domain\Content\Models\Content;
use Domain\Customer\Models\Customer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * @property-read Customer $record
 */
class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    #[\Override]
    public static function getColumns(): array
    {

        $date_format = app(CustomerSettings::class)->date_format;

        return [
            ImportColumn::make('cuid')
                ->rules([
                    'nullable',
                ]),
            ImportColumn::make('email')
                ->requiredMapping()
                ->example('customer@example.com')
                ->rules(['required',
                    function (string $attribute, mixed $value, \Closure $fail) {
                        if (Customer::where('email', $value)->exists()) {

                            Notification::make()
                                ->title(trans('Customer Import Error'))
                                ->body("Customer email {$value} has already been taken.")
                                ->danger()
                                ->when(config('queue.default') === 'sync',
                                    fn (Notification $notification) => $notification
                                        ->persistent()
                                        ->send(),
                                    fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                );
                            $fail("The email '{$value}' is already taken. Please choose another.");
                        }
                    },
                ]),

            ImportColumn::make('username')
                ->example('username123')
                ->rules([
                    'nullable',
                    function (string $attribute, mixed $value, \Closure $fail) {
                        if (Customer::where('username', $value)->exists()) {

                            Notification::make()
                                ->title(trans('Customer Import Error'))
                                ->body("Customer username {$value} has already been taken.")
                                ->danger()
                                ->when(config('queue.default') === 'sync',
                                    fn (Notification $notification) => $notification
                                        ->persistent()
                                        ->send(),
                                    fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                );

                            $fail("The username '{$value}' is already taken. Please choose another.");
                        }
                    },
                ]),
            ImportColumn::make('first_name')
                ->example('John'),
            ImportColumn::make('last_name')
                ->example('Doe'),
            ImportColumn::make('mobile')
                ->example('09123456789'),
            ImportColumn::make('gender')
                ->example('male')
                ->rules(['nullable', \Illuminate\Validation\Rule::enum(\Domain\Customer\Enums\Gender::class)]),
            ImportColumn::make('birth_date')
                ->example(fn (): string => $date_format === 'default' || is_null($date_format) ? now()->format('Y-m-d') : now()->format($date_format))
                ->rules([
                    'nullable',
                    ($date_format === 'default' ||
                            $date_format === '') ? 'date' : 'date_format:'.$date_format,
                ]),
            ImportColumn::make('password')
                ->example('password123'),
            ImportColumn::make('registered')
                ->rules(['nullable']),
            ImportColumn::make('data')
                ->example(null),
            ImportColumn::make('tier')
                ->rules([
                    'nullable',
                    \Illuminate\Validation\Rule::exists(\Domain\Tier\Models\Tier::class, 'name'),
                ]),

        ];
    }

    #[\Override]
    public function resolveRecord(): Customer
    {

        if (array_key_exists('cuid', $this->data)) {
            if (is_null($this->data['cuid'])) {
                return new Customer;
            }

            return Customer::where('cuid', $this->data['cuid'])->first() ?? new Customer;
        }

        return new Customer;
    }

    #[\Override]
    public function fillRecord(): void
    {
        /** Disabled Filament Built in Record Creation Handle the Content
         * Creation thru Domain Level Action
         */
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function saveRecord(): void
    {

        if ($this->record->exists) {
            return;
        }

        app(\Domain\Customer\Actions\ImportCustomerAction::class)
            ->execute(array_filter($this->data));

    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Customer import has completed and '.
            number_format($import->successful_rows).' '.
            Str::of('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.Str::of('row')
                ->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
