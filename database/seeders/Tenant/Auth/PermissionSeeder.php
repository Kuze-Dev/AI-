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
                        'impersonate',
                    ]
                ),
                ...$this->generateFilamentResourcePermissions('role', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('activity', only: ['viewAny', 'view']),
                ...$this->generatePermissionGroup('settings', ['site', 'cms']),
                ...$this->generateFilamentResourcePermissions('blueprint', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('menu', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('page', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('block', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('form', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('formSubmission', only: ['viewAny', 'view']),
                ...$this->generateFilamentResourcePermissions('taxonomy', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('content', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('contentEntry', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('globals', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions(
                    'customers',
                    except: [
                        'deleteAny',
                        'restoreAny',
                        'forceDeleteAny',
                    ],
                    hasSoftDeletes: true,
                    customPermissions: ['sendRegisterInvitation']
                ),
                ...$this->generateFilamentResourcePermissions(
                    'tiers',
                    except: [
                        'deleteAny',
                        'restoreAny',
                        'forceDeleteAny',
                    ],
                    hasSoftDeletes: true
                ),
                ...$this->generateFilamentResourcePermissions('addresses'),
                ...$this->generateFilamentResourcePermissions(
                    'site',
                    except: [
                        'deleteAny',
                    ],
                    hasSoftDeletes: true,
                    customPermissions: [
                        'siteManager',
                    ],
                ),
            ],
        ];
    }
}
