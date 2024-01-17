<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Domain\Admin\Actions\CreateAdminAction;
use Domain\Admin\Actions\UpdateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Domain\Admin\Models\Admin;
use Domain\Role\Models\Role;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use HalcyonAgile\FilamentExport\Actions\ExportAction;
use HalcyonAgile\FilamentImport\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\ValidationRules\Rules\Delimited;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->model(Admin::class)
                ->uniqueBy('email')
                ->tags([
                    'tenant:'.(tenant('id') ?? 'central'),
                ])
                ->processRowsUsing(
                    function (array $row): Admin {
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
                )
                ->withValidation(
                    rules: [
                        'email' => [
                            'required',
                            Rule::email(),
                            'prohibited_if:email,'.Admin::whereKey(1)->value('email'),
                            'distinct',
                        ],
                        'first_name' => 'required|string|min:3|max:100',
                        'last_name' => 'required|string|min:3|max:100',
                        'active' => 'nullable|in:Yes,No',
                        'roles' => [
                            'nullable',
                            new Delimited([Rule::exists(Role::class, 'name')]),
                        ],
                        'timezone' => 'nullable|timezone',
                    ],
                ),
            ExportAction::make()
                ->model(Admin::class)
                ->queue()
                ->query(fn (Builder $query) => $query->with('roles')->whereKeyNot(1)->latest())
                ->mapUsing(
                    ['Email', 'First Name',  'Last Name', 'Active', 'Roles', 'Created At'],
                    fn (Admin $admin): array => [
                        $admin->email,
                        $admin->first_name,
                        $admin->last_name,
                        $admin->active ? 'Yes' : 'No',
                        $admin->getRoleNames()->implode(', '),
                        $admin->created_at?->format(config('tables.date_time_format')),
                    ]
                )
                ->tags([
                    'tenant:'.(tenant('id') ?? 'central'),
                ])
                ->withActivityLog(
                    event: 'exported',
                    description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                ),
            Actions\CreateAction::make(),
        ];
    }
}
