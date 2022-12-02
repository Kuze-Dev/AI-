<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Auth;

use Domain\Role\Database\Seeders\PermissionSeeder as BasePermissionSeeder;

class PermissionSeeder extends BasePermissionSeeder
{
    protected function permissionsByGuard(): array
    {
        return [
            'admin' => [
                ...$this->generateFilamentResourcePermissions(
                    'admin',
                    except: [
                        'deleteAny',
                        'restoreAny',
                        'forceDeleteAny',
                    ],
                    hasSoftDeletes: true,
                    customPermissions: [
                        'resendVerification',
                        'sendPasswordReset',
                    ]
                ),
                ...$this->generateFilamentResourcePermissions('role', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('activity', only: ['viewAny', 'view']),
                ...$this->generatePermissionGroup('settings', ['site']),
                ...$this->generateFilamentResourcePermissions('blueprint', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('page', except: ['deleteAny'], customPermissions: ['configure']),
                ...$this->generateFilamentResourcePermissions('form', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('formSubmission', only: ['viewAny', 'view']),
            ],
        ];
    }
}
