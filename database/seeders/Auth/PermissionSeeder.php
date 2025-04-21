<?php

declare(strict_types=1);

namespace Database\Seeders\Auth;

use Domain\Role\Database\Seeders\PermissionSeeder as BasePermissionSeeder;

class PermissionSeeder extends BasePermissionSeeder
{
    #[\Override]
    protected function permissionsByGuard(): array
    {
        return [
            'admin' => [
                ...$this->generateFilamentResourcePermissions(
                    'tenant',
                    except: ['deleteAny'],
                    customPermissions: [
                        'updateFeatures',
                        'canSuspendTenant',
                    ]
                ),
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
                        'impersonate',
                    ]
                ),
                ...$this->generateFilamentResourcePermissions('role', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('activity', only: ['viewAny', 'view']),
                ...$this->generatePermissionGroup('settings', ['site']),
            ],
        ];
    }
}
