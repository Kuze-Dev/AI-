<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\Shopconfiguration\PaymentGateway\BankTransfer;
use App\Features\Shopconfiguration\PaymentGateway\OfflineGateway;
use App\Features\Shopconfiguration\PaymentGateway\PaypalGateway;
use App\Features\Shopconfiguration\PaymentGateway\StripeGateway;
use App\Features\Shopconfiguration\PaymentGateway\VisionpayGateway;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\PaymentMethod\Models\PaymentMethod;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class PaymentMethodPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (! tenancy()->tenant?->features()->someAreActive([
            PaypalGateway::class,
            OfflineGateway::class,
            StripeGateway::class,
            BankTransfer::class,
            VisionpayGateway::class,
        ])) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        if ($paymentMethod->trashed()) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
