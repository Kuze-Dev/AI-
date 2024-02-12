<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Auth\Customer;

use App\Features\Customer\AddressBase;
use App\Features\Customer\TierBase;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Auth\Enums\EmailVerificationType;
use Domain\Customer\Enums\Gender;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'profile_image' => 'nullable|image',
            'email_verification_type' => ['nullable', Rule::enum(EmailVerificationType::class)],

            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [Rule::when(is_null($this->invited), 'required|unique:customers,email', 'required')],
            'mobile' => [Rule::when(is_null($this->invited), 'required|string|unique:customers,mobile', 'required|string')],
            'gender' => ['required', Rule::enum(Gender::class)],
            'tier_id' => [
                Rule::when(
                    (bool) tenancy()->tenant?->features()->active(TierBase::class),
                    'required',
                    'nullable'
                ),
                Rule::exists(Tier::class, (new Tier())->getRouteKeyName()),
            ],
            'birth_date' => 'required|date',
            'password' => ['required', 'confirmed', Password::default()],
            'invited' => 'nullable|exists:customers,cuid',
        ];

        // Billing and shipping rules
        if (tenancy()->tenant?->features()->active(AddressBase::class)) {
            $rules['billing.same_as_shipping'] = 'required|bool';

            $shippingRules = [
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
            ];

            $billingRules = [
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

            $rules = array_merge($rules, $shippingRules, $billingRules);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'billing.country_id.required_if' => trans('Country field is required.'),
            'billing.state_id.required_if' => trans('State field is required.'),
            'billing.address_line_1.required_if' => trans('Address line 1 field is required.'),
            'billing.zip_code.required_if' => trans('Zip code field is required.'),
            'billing.city.required_if' => trans('City field is required.'),
            'billing.label_as.required_if' => trans('Label as field is required.'),

            'shipping.country_id.required' => trans('Country field is required.'),
            'shipping.state_id.required' => trans('State field is required.'),
            'shipping.address_line_1.required' => trans('Address line 1 is required'),
            'shipping.zip_code.required' => trans('Zip code field is required.'),
            'shipping.city.required' => trans('City field is required.'),
            'shipping.label_as.required' => trans('Label as field is required.'),

            'email.required' => trans('Email addresss field is required.'),
            'email.email' => trans('Email address must be valid.'),
            'email.unique' => trans('Email address has already been taken.'),
            'email.max' => trans('Email address must not exceed :max characters.'),

            'mobile.required' => trans('Mobile field is required.'),
            'mobile.string' => trans('Mobile field must be a string.'),
            'mobile.max' => trans('Mobile field must not exceed :max characters.'),
            'mobile.unique' => trans('Mobile number has already been taken.'),

            'first_name.required' => trans('First name field is required.'),
            'last_name.required' => trans('Last name field is required.'),

            'gender.required' => trans('Last name field is required.'),

            'birth_date.required' => trans('Birthdate field is required.'),

            'password.required' => trans('Password field is required.'),

        ];
    }

    public function attributes(): array
    {
        return [
            'shipping.country_id' => 'country',
            'shipping.state_id' => 'state',
            'shipping.address_line_1' => 'address line 1',
            'shipping.zip_code' => 'zip code',
            'shipping.city' => 'city',
            'shipping.label_as' => 'label_as',

            'billing.same_as_shipping' => 'same as shipping',
            'billing.country_id' => 'country',
            'billing.state_id' => 'state',
            'billing.address_line_1' => 'address line 1',
            'billing.zip_code' => 'zip code',
            'billing.city' => 'city',
            'billing.label_as' => 'label_as',
        ];
    }
}
