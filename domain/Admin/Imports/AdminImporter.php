<?php

declare(strict_types=1);

namespace Domain\Admin\Imports;

use Domain\Admin\Actions\CreateAdminAction;
use Domain\Admin\Actions\UpdateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Domain\Admin\Models\Admin;
use Domain\Role\Models\Role;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\ValidationRules\Rules\Delimited;

class AdminImporter extends Importer
{
    protected static ?string $model = Admin::class;

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
                ]),

            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'min:3', 'max:100']),

            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'min:3', 'max:100']),

            ImportColumn::make('active')
                ->rules(['nullable', 'in:Yes,No']),

            ImportColumn::make('roles')
                ->rules([
                    'nullable',
                    new Delimited([Rule::exists(Role::class, 'name')]),
                ]),

            ImportColumn::make('timezone')
                ->rules(['nullable', 'timezone']),

        ];
    }

    public function resolveRecord(): ?Admin
    {
        $row = $this->data;
        $data = [
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'password' => Str::password(),
            'timezone' => $row['timezone'] ?? null,
            'active' => isset($row['active']) ? ($row['active'] === 'Yes') : null,
            'roles' => isset($row['roles']) ? (Str::of($row['roles'])
                ->explode(',')
                ->map(fn (string $role) => trim($role))
                ->toArray()) : null,
        ];
        unset($row);

        if ($admin = Admin::whereEmail($data['email'])->first()) {
            unset($data['password'], $data['email']);
            $admin = app(UpdateAdminAction::class)->execute($admin, new AdminData(...$data));
        } else {
            $admin = app(CreateAdminAction::class)->execute(new AdminData(...$data));
        }

        return $admin;
    }

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
