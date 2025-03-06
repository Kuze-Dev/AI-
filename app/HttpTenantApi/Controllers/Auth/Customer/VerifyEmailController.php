<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\Customer\CustomerBase;
use App\Http\Controllers\Controller;
use App\Settings\ECommerceSettings;
use App\Settings\SiteSettings;
use Domain\Auth\Actions\VerifyEmailAction;
use Domain\Auth\Actions\VerifyEmailViaOTPAction;
use Domain\Customer\Actions\CustomerResendEmailVerificationAction;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Throwable;

#[
    Prefix('account/verification'),
    Middleware(['feature.tenant:'.CustomerBase::class])
]
class VerifyEmailController extends Controller
{
    #[Get('{customer}/{hash}', name: 'customer.verification.verify')]
    public function verify(Request $request, Customer $customer): mixed
    {
        /** @var string $hash */
        $hash = $request->route('hash') ?? '';
        if (! hash_equals($hash, sha1($customer->getEmailForVerification()))) {
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

    #[Post('otp', name: 'customer.verification.verify.otp', middleware: 'auth:sanctum')]
    public function verifyViaOTP(Request $request, #[CurrentUser('sanctum')] Customer $customer): mixed
    {
        $otp = $this->validate($request, [
            'otp' => 'required|string',
        ])['otp'];

        if (app(VerifyEmailViaOTPAction::class)->execute($customer, $otp)) {
            return response(['message' => trans('Email verified!')]);
        }

        return response()->noContent();
    }

    /** @throws Throwable */
    #[Post('resend', name: 'customer.verification.resend', middleware: 'auth:sanctum')]
    public function resend(): mixed
    {
        /** @var Customer $customer */
        $customer = Auth::user();

        if (! DB::transaction(
            fn () => app(CustomerResendEmailVerificationAction::class)->execute($customer)
        )) {
            return response()->noContent();
        }

        return response([
            'message' => trans('We have emailed your email verification link!'),
        ], 202);
    }
}
