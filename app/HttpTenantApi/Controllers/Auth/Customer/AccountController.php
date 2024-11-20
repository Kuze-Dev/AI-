<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\Customer\CustomerBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CustomerResource;
use App\Settings\CustomerSettings;
use Domain\Blueprint\Models\Blueprint;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Throwable;

#[
    Prefix('account'),
    Middleware(['auth:sanctum', 'feature.tenant:'.CustomerBase::class])
]
class AccountController extends Controller
{
    #[Get('/', name: 'account')]
    public function show(): CustomerResource
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        return self::resource($customer);
    }

    /** @throws Throwable */
    #[Put('update', name: 'account.update')]
    public function update(Request $request): CustomerResource
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        // if (app(CustomerSettings::class)->blueprint_id) {
        $customerBlueprint = Blueprint::where('id', app(CustomerSettings::class)->blueprint_id)->first();
        // }

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                Rule::unique(Customer::class)->ignoreModel($customer),
                Rule::email(),
                'max:255',
            ],
            'gender' => ['required', Rule::enum(Gender::class)],
            'mobile' => ['required', 'string' => 'max:255',  Rule::unique(Customer::class)->ignoreModel($customer)],
            'birth_date' => 'required|date',
        ];

        if ($customerBlueprint) {
            $bluprintRules = $customerBlueprint->schema->getValidationRules();

            $rules = array_merge($rules, $bluprintRules);
        }

        $validated = $this->validate($request, $rules);

        $customderBlueprintData = [];
        if ($customerBlueprint) {

            foreach ($customerBlueprint->schema->sections as $section) {
                $customderBlueprintData[$section->state_name] = $validated[$section->state_name];
            }
        }

        $validated['data'] = $customderBlueprintData;

        $customer = DB::transaction(
            fn () => app(EditCustomerAction::class)
                ->execute($customer, CustomerData::formArrayCustomerEditAPI($validated))
        );

        return self::resource($customer);
    }

    private static function resource(Customer $customer): CustomerResource
    {
        return CustomerResource::make(
            QueryBuilder::for(Customer::whereKey($customer))
                ->allowedIncludes([
                    'media',
                    'addresses.state',
                ])
                ->first()
        );
    }
}
