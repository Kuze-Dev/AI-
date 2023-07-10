<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Auth\Address;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
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
                Rule::exists(Country::class, (new Country())->getRouteKeyName()),
            ],
            'state_id' => [
                'required',
                Rule::exists(State::class, (new State())->getRouteKeyName())
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
        ];
    }

    public function toDTO(): AddressData
    {
        $validated = $this->validated();

        return new AddressData(
            label_as: $validated['label_as'],
            address_line_1: $validated['address_line_1'],
            state_id: (int) $validated['state_id'],
            zip_code: $validated['zip_code'],
            city: $validated['city'],
            is_default_shipping: false,
            is_default_billing: false,
        );
    }
}
