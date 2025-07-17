<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\BaseCms;

use Illuminate\Support\Facades\Auth;

class BaseCmsController
{
    protected function checkAbilities(string $ability): bool
    {
        /** @var \Domain\Admin\Models\Admin|\Domain\Customer\Models\Customer|\Domain\Tenant\Models\TenantApiKey|null $model */
        $model = Auth::user();

        $canAcccess = true;

        if (config('custom.strict_api')) {

            if ($model === null) {
                $canAcccess = false;
            } else {
                $canAcccess = $model->tokencan($ability);
            }
        }

        if (! $canAcccess) {
            abort(403, 'You do not have permission to access this resource');
        }

        return $canAcccess;

    }
}
