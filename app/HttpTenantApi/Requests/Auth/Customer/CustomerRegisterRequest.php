<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Auth\Customer;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\DataTransferObjects\CustomerRegisterData;
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
            'birth_date' => 'required|date',
            'password' => ['required', 'confirmed', Password::default()],

            // shipping address
            'shipping_country_id' => [
                'required',
                Rule::exists(Country::class, (new Country())->getRouteKeyName()),
            ],
            'shipping_state_id' => [
                'required',
                Rule::exists(State::class, (new State())->getRouteKeyName())
                    ->where(function (Builder $query) {

                        $country = app(Country::class)
                            ->resolveRouteBinding($this->input('shipping_country_id'));

                        return $query->where('country_id', $country?->getKey());
                    }),
            ],
            'shipping_address_line_1' => 'required|string|max:255',
            'shipping_zip_code' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_label_as' => ['required', Rule::enum(AddressLabelAs::class)],

            // billing address
            'billing_same_as_shipping' => 'required|bool',
            'billing_country_id' => [
                'required_if:billing_same_as_shipping,0',
                Rule::exists(Country::class, (new Country())->getRouteKeyName()),
            ],
            'billing_state_id' => [
                'required_if:billing_same_as_shipping,0',
                Rule::exists(State::class, (new State())->getRouteKeyName())
                    ->where(function (Builder $query) {

                        $country = app(Country::class)
                            ->resolveRouteBinding($this->input('billing_country_id'));

                        return $query->where('country_id', $country?->getKey());
                    }),
            ],
            'billing_address_line_1' => 'required_if:billing_same_as_shipping,0|string|max:255',
            'billing_zip_code' => 'required_if:billing_same_as_shipping,0|string|max:255',
            'billing_city' => 'required_if:billing_same_as_shipping,0|string|max:255',
            'billing_label_as' => ['required_if:billing_same_as_shipping,0', Rule::enum(AddressLabelAs::class)],
        ];
    }

    public function toDTO(Tier $tier): CustomerRegisterData
    {
        $validated = $this->validated();

        $customerData = new CustomerData(
            tier_id: $tier->getKey(),
            first_name: $validated['first_name'],
            last_name: $validated['last_name'],
            mobile: $validated['mobile'],
            status: Status::ACTIVE,
            birth_date: now()->parse($validated['birth_date']),
            email: $validated['email'],
            password: $validated['password'],
        );

        $shippingAddress = new AddressData(
            label_as: $validated['shipping_label_as'],
            address_line_1: $validated['shipping_address_line_1'],
            state_id: (int) $validated['shipping_state_id'],
            zip_code: $validated['shipping_zip_code'],
            city: $validated['shipping_city'],
            is_default_shipping: true,
            is_default_billing: false,
        );

        $same = $this->boolean('billing_same_as_shipping');

        $billingAddress = new AddressData(
            label_as: $same
                ? $validated['shipping_label_as']
                : $validated['billing_label_as'],
            address_line_1: $same
                ? $validated['shipping_address_line_1']
                : $validated['billing_address_line_1'],
            state_id: $same
                ? (int) $validated['shipping_state_id']
                : (int) $validated['billing_state_id'],
            zip_code: $same
                ? $validated['shipping_zip_code']
                : $validated['billing_zip_code'],
            city: $same
                ? $validated['shipping_city']
                : $validated['billing_city'],
            is_default_shipping: false,
            is_default_billing: true,
        );

        return new CustomerRegisterData(
            customerData: $customerData,
            shippingAddressData: $shippingAddress,
            billingAddressData: $billingAddress
        );
    }
}
