<?php

declare(strict_types=1);

namespace Domain\Admin\Imports;

use Domain\Admin\Models\Admin;
use Domain\Role\Models\Role;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\ValidationRules\Rules\Delimited;

/**
 * @property-read \Domain\Admin\Models\Admin&\Illuminate\Contracts\Auth\Authenticatable $record
 */
class AdminImporter extends Importer
{
    protected static ?string $model = Admin::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::email(),
                    'prohibited_if:email,'.Admin::whereKey(1)->value('email'),
                    'distinct',
                ])
                ->example('example@domain.com'),

            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'min:3', 'max:100'])
                ->example('test name'),

            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'min:3', 'max:100'])
                ->example('test last name'),

            ImportColumn::make('active')
                ->ignoreBlankState()
                ->rules(['nullable', 'in:yes,no'])
                ->fillRecordUsing(function (Admin $record, string $state): void {
                    $record->active = $state === 'yes';
                })
                ->example(Arr::random(['yes', 'no'])),

            ImportColumn::make('roles')
                ->rules([
                    'nullable',
                    new Delimited([Rule::exists(Role::class, 'name')]),
                ])
                ->fillRecordUsing(function (): void {
                    // skip process
                })
                ->example('role1,role2,role3'),

            ImportColumn::make('timezone')
                ->ignoreBlankState()
                ->rules(['nullable', 'timezone'])
                ->example(config('domain.admin.default_timezone')),

        ];
    }

    #[\Override]
    public function resolveRecord(): ?Admin
    {
        return Admin::firstOrNew(
            ['email' => $this->data['email']],
            ['password' => Str::password()]
        );
    }

    public function afterCreate(): void
    {
        $this->saveRoles();
        event(new Registered($this->record));
    }

    public function afterUpdate(): void
    {
        $this->saveRoles();
    }

    private function saveRoles(): void
    {
        if (blank($this->data['roles'])) {
            return;
        }

        $this->record->assignRole(explode(',', $this->data['roles']));
    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your admin import has completed and '.
            number_format($import->successful_rows).' '.Str::of('row')
                ->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.
                Str::of('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
