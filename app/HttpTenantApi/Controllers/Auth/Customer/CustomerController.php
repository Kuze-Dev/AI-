<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\Customer\CustomerBase;
use App\Http\Controllers\Controller;
use Domain\Customer\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[Middleware('feature.tenant:'.CustomerBase::class)]

class CustomerController extends Controller
{
    #[Post('/customer/is-verified', name: 'customer.is-verified')]
    public function isverified(Request $request): JsonResponse
    {
        $data = $this->validate($request, ['cuid' => 'required']);

        $customer = Customer::whereCuid($data['cuid'])->firstOrFail();

        return response()->json([
            'verified' => $customer->email_verified_at ? true : false,
        ]);
    }
}
