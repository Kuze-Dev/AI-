<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\Settings\ECommerceSettings;
use App\Settings\SiteSettings;
use Domain\Auth\Actions\VerifyEmailAction;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware(['feature.tenant:' . ECommerceBase::class])]
class VerifyEmailController
{
    #[Get('verify/{customer}/{hash}', name: 'customer.verify')]
    public function __invoke(Request $request, Customer $customer): mixed
    {
        /** @var string $hash */
        $hash = $request->route('hash') ?? '';
        if ( ! hash_equals($hash, sha1($customer->getEmailForVerification()))) {
            throw new AuthorizationException();
        }

        $params = http_build_query([
            'status' => app(VerifyEmailAction::class)->execute($customer) === true
                ? 'verified'
                : 'already-verified',
        ]);

        $baseUrl = app(ECommerceSettings::class)->domainWithScheme()
            ?? app(SiteSettings::class)->domainWithScheme();

        return redirect($baseUrl.'/account/verify?'.$params);
    }
}
