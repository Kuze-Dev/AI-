<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Auth\Address;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\State;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'state_id' => [
                'required',
                Rule::exists(State::class, (new State())->getRouteKeyName()),
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
