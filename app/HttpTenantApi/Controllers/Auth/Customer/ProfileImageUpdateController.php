<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use Domain\Customer\Actions\UpdateCustomerProfileImageAction;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[
    Prefix('account/profile-image'),
    Middleware(['auth:sanctum', 'feature.tenant:' . ECommerceBase::class])
]
class ProfileImageUpdateController extends Controller
{
    #[Post('/')]
    public function __invoke(Request $request): mixed
    {
        $profileImage = $this->validate($request, [
            'profile_image' => 'required|image',
        ])['profile_image'];

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();
        if (app(UpdateCustomerProfileImageAction::class)->execute($customer, $profileImage)) {
            return response([
                'message' => trans('Success!'),
            ]);
        }

        return response([
            'message' => trans('Failed!'),
        ], 422);
    }
}
