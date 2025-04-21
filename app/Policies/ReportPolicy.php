<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\ECommerce\ECommerceBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Admin\Models\Admin;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (TenantFeatureSupport::inactive(ECommerceBase::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasAllPermissions(['order.viewAny', 'order.reports'])) {
            return true;
        }

        return $this->checkWildcardPermissions($admin);
    }
}
