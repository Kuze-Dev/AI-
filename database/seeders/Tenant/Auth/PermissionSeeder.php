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
                ...$this->generateFilamentResourcePermissions('blueprint', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('page', except: ['deleteAny'], customPermissions: ['configure']),
            ],
        ];
    }
}
