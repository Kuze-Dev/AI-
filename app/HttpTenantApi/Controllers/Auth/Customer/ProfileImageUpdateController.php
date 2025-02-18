<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Attributes\CurrentApiCustomer;
use App\Features\Customer\CustomerBase;
use App\Http\Controllers\Controller;
use Domain\Customer\Actions\UpdateCustomerProfileImageAction;
use Domain\Customer\Models\Customer;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[
    Prefix('account/profile-image'),
    Middleware(['auth:sanctum', 'feature.tenant:'.CustomerBase::class])
]
class ProfileImageUpdateController extends Controller
{
    #[Post('/')]
    public function __invoke(Request $request, #[CurrentApiCustomer] Customer $customer): mixed
    {
        $profileImage = $this->validate($request, [
            'profile_image' => 'required|image',
        ])['profile_image'];

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
