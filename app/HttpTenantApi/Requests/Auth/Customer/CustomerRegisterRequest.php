<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Auth\Customer;

use App\Features\Customer\AddressBase;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Auth\Enums\EmailVerificationType;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
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
        $rules = [
            'profile_image' => 'nullable|image',
            'email_verification_type' => ['nullable', Rule::enum(EmailVerificationType::class)],

            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                Rule::email(),
                Rule::unique(Customer::class)
                    ->where('register_status', RegisterStatus::REGISTERED),
                'max:255',
            ],
            'mobile' => 'required|string|max:255|unique:customers,mobile',
            'gender' => ['required', Rule::enum(Gender::class)],
            'tier_id' => [
                'nullable',
                Rule::exists(Tier::class, (new Tier())->getRouteKeyName()),
            ],
            'birth_date' => 'required|date',
            'password' => ['required', 'confirmed', Password::default()],
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
            'billing.country_id.required_if' => trans('validation.required'),
            'billing.state_id.required_if' => trans('validation.required'),
            'billing.address_line_1.required_if' => trans('validation.required'),
            'billing.zip_code.required_if' => trans('validation.required'),
            'billing.city.required_if' => trans('validation.required'),
            'billing.label_as.required_if' => trans('validation.required'),

            'email.required' => trans('The email addresss field is required.'),
            'email.email' => trans('The email address must be valid.'),
            'email.unique' => trans('The email address has already been taken.'),
            'email.max' => trans('The email address must not exceed :max characters.'),

            'mobile.required' => trans('The mobile field is required.'),
            'mobile.string' => trans('The mobile field must be a string.'),
            'mobile.max' => trans('The mobile field must not exceed :max characters.'),
            'mobile.unique' => trans('The mobile number has already been taken.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'shipping.country_id' => 'shipping country',
            'shipping.state_id' => 'shipping state',
            'shipping.address_line_1' => 'shipping address line 1',
            'shipping.zip_code' => 'shipping zip code',
            'shipping.city' => 'shipping city',
            'shipping.label_as' => 'shipping label as',

            'billing.same_as_shipping' => 'billing same as shipping',
            'billing.country_id' => 'billing country',
            'billing.state_id' => 'billing state',
            'billing.address_line_1' => 'shipping address line 1',
            'billing.zip_code' => 'shipping zip code',
            'billing.city' => 'shipping city',
            'billing.label_as' => 'shipping label_as',
        ];
    }
}
