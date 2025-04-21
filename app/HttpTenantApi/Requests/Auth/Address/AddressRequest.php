<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Auth\Address;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Customer\Models\Customer;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'country_id' => [
                'required',
                Rule::exists(Country::class, (new Country)->getRouteKeyName()),
            ],
            'state_id' => [
                'required',
                Rule::exists(State::class, (new State)->getRouteKeyName())
                    ->where(function (Builder $query) {

                        $country = app(Country::class)
                            ->resolveRouteBinding($this->input('country_id'));

                        return $query->where('country_id', $country?->getKey());
                    }),
            ],
            'address_line_1' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'label_as' => ['required', Rule::enum(AddressLabelAs::class)],
            'is_default_shipping' => 'nullable|bool',
            'is_default_billing' => 'nullable|bool',
        ];
    }

    public function toDTO(?Customer $customer = null, ?Address $address = null): AddressData
    {
        $validated = $this->validated();

        $default_shipping = true;
        $default_billing = true;

        if (! is_null($customer) && isset($validated['is_default_shipping'], $validated['is_default_billing'])) {
            $default_shipping = $validated['is_default_shipping'];
            $default_billing = $validated['is_default_billing'];
        }

        if (! is_null($address) && isset($address->is_default_billing, $address->is_default_shipping)) {
            $default_shipping = $address->is_default_shipping;
            $default_billing = $address->is_default_billing;
        }

        return new AddressData(
            state_id: (int) $validated['state_id'],
            label_as: $validated['label_as'],
            address_line_1: $validated['address_line_1'],
            zip_code: $validated['zip_code'],
            city: $validated['city'],
            is_default_shipping: $default_shipping,
            is_default_billing: $default_billing,
            customer_id: ($customer ? $customer->getKey() : null) ?? ($address && $address->customer ? $address->customer->getKey() : null),
        );
    }

    public function toGuestDTO(): AddressData
    {
        $validated = $this->validated();

        return new AddressData(
            state_id: (int) $validated['state_id'],
            label_as: $validated['label_as'],
            address_line_1: $validated['address_line_1'],
            zip_code: $validated['zip_code'],
            city: $validated['city'],
        );
    }
}
