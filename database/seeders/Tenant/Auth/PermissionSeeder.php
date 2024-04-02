<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Auth;

use Domain\Role\Database\Seeders\PermissionSeeder as BasePermissionSeeder;

class PermissionSeeder extends BasePermissionSeeder
{
    #[\Override]
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
                        'customerPrintReceipt',
                    ]
                ),
                ...$this->generateFilamentResourcePermissions('role', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('activity', only: ['viewAny', 'view']),
                ...$this->generatePermissionGroup('cmsSettings', ['site', 'cms', 'form']),
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
                ...$this->generatePermissionGroup('ecommerceSettings', ['e-commerce', 'payments', 'shipping', 'order', 'reward-points']),
                ...$this->generateFilamentResourcePermissions('taxZone', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('country', only: ['viewAny', 'update']),
                ...$this->generateFilamentResourcePermissions('currency', only: ['viewAny', 'update']),
                ...$this->generateFilamentResourcePermissions('product', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('discount', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('paymentMethod', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions(
                    'customer',
                    except: [
                        'deleteAny',
                        'restoreAny',
                        'forceDeleteAny',
                    ],
                    hasSoftDeletes: true,
                    customPermissions: ['sendRegisterInvitation']
                ),
                ...$this->generateFilamentResourcePermissions(
                    'tier',
                    except: [
                        'deleteAny',
                        'restoreAny',
                        'forceDeleteAny',
                    ],
                    hasSoftDeletes: true
                ),
                ...$this->generateFilamentResourcePermissions('address'),
                ...$this->generateFilamentResourcePermissions('paymentMethod', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('shippingMethod', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions(
                    'site',
                    except: [
                        'deleteAny',
                    ],
                    hasSoftDeletes: true,
                    customPermissions: [
                        'siteManager',
                    ]
                ),
                ...$this->generateFilamentResourcePermissions(
                    'order',
                    only: ['view', 'viewAny', 'update'],
                    customPermissions: ['reports']
                ),
                ...$this->generateFilamentResourcePermissions(
                    'service',
                    except: ['deleteAny']
                ),
            ],
        ];
    }
}
