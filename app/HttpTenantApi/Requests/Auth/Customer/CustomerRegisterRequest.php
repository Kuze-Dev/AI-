<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Auth\Customer;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\DataTransferObjects\CustomerRegisterData;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                Rule::unique(Customer::class),
                Rule::email(),
                'max:255',
            ],
            'mobile' => 'required|string|max:255',
            'gender' => ['required', Rule::enum(Gender::class)],
            'birth_date' => 'required|date',
            'password' => ['required', 'confirmed', Password::default()],

            // shipping address
            'shipping.country_id' => [
                'required',
                Rule::exists(Country::class, (new Country())->getRouteKeyName()),
            ],
            'shipping.state_id' => [
                'required',
                Rule::exists(State::class, (new State())->getRouteKeyName())
                    ->where(function (Builder $query) {

                        $country = app(Country::class)
                            ->resolveRouteBinding($this->input('shipping.country_id'));

                        return $query->where('country_id', $country?->getKey());
                    }),
            ],
            'shipping.address_line_1' => 'required|string|max:255',
            'shipping.zip_code' => 'required|string|max:255',
            'shipping.city' => 'required|string|max:255',
            'shipping.label_as' => ['required', Rule::enum(AddressLabelAs::class)],

            // billing address
            'billing.same_as_shipping' => 'required|bool',
            'billing.country_id' => [
                'required_if:billing.same_as_shipping,0',
                Rule::exists(Country::class, (new Country())->getRouteKeyName()),
            ],
            'billing.state_id' => [
                'required_if:billing.same_as_shipping,0',
                Rule::exists(State::class, (new State())->getRouteKeyName())
                    ->where(function (Builder $query) {

                        $country = app(Country::class)
                            ->resolveRouteBinding($this->input('billing.country_id'));

                        return $query->where('country_id', $country?->getKey());
                    }),
            ],
            'billing.address_line_1' => 'required_if:billing.same_as_shipping,0|string|max:255',
            'billing.zip_code' => 'required_if:billing.same_as_shipping,0|string|max:255',
            'billing.city' => 'required_if:billing.same_as_shipping,0|string|max:255',
            'billing.label_as' => ['required_if:billing.same_as_shipping,0', Rule::enum(AddressLabelAs::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'billing.country_id.required_if' => trans('validation.required'),
            'billing.state_id.required_if' => trans('validation.required'),
            'billing.address_line_1.required_if' => trans('validation.required'),
            'billing.zip_code.required_if' => trans('validation.required'),
            'billing.city.required_if' => trans('validation.required'),
            'billing.label_as.required_if' => trans('validation.required'),
        ];
    }

    public function attributes(): array
    {
        return [
            'shipping.country_id' => 'shipping country',
            'shipping.state_id' => 'shipping state',
            'shipping.address_line_1' => 'shipping address_line_1',
            'shipping.zip_code' => 'shipping zip_code',
            'shipping.city' => 'shipping city',
            'shipping.label_as' => 'shipping label_as',

            'billing.same_as_shipping' => 'billing same_as_shipping',
            'billing.country_id' => 'billing country',
            'billing.state_id' => 'billing state',
            'billing.address_line_1' => 'shipping address_line_1',
            'billing.zip_code' => 'shipping zip_code',
            'billing.city' => 'shipping city',
            'billing.label_as' => 'shipping label_as',
        ];
    }

    public function toDTO(Tier $tier): CustomerRegisterData
    {
        $validated = $this->validated();

        $customerData = new CustomerData(
            first_name: $validated['first_name'],
            last_name: $validated['last_name'],
            mobile: $validated['mobile'],
            gender: Gender::from($validated['gender']),
            birth_date: now()->parse($validated['birth_date']),
            status: Status::ACTIVE,
            tier_id: $tier->getKey(),
            email: $validated['email'],
            password: $validated['password']
        );

        $same = $this->boolean('billing.same_as_shipping');

        $shippingAddress = new AddressData(
            label_as: $validated['shipping']['label_as'],
            address_line_1: $validated['shipping']['address_line_1'],
            state_id: (int) $validated['shipping']['state_id'],
            zip_code: $validated['shipping']['zip_code'],
            city: $validated['shipping']['city'],
            is_default_shipping: true,
            is_default_billing: $same,
        );

        $billingAddress = null;
        if ( ! $same) {
            $billingAddress = new AddressData(
                label_as: $validated['billing']['label_as'],
                address_line_1: $validated['billing']['address_line_1'],
                state_id: (int) $validated['billing']['state_id'],
                zip_code: $validated['billing']['zip_code'],
                city:$validated['billing']['city'],
                is_default_shipping: false,
                is_default_billing: true,
            );
        }

        return new CustomerRegisterData(
            customerData: $customerData,
            shippingAddressData: $shippingAddress,
            billingAddressData: $billingAddress
        );
    }
}
