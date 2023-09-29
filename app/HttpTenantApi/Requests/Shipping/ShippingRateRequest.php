<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Shipping;

use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Illuminate\Foundation\Http\FormRequest;

class ShippingRateRequest extends FormRequest
{
    /** Determine if the user is authorized to make this request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [

            'courier' => 'required',
            'receiver' => 'array',
            'receiver.first_name' => 'required',
            'receiver.last_name' => 'required',
            'receiver.mobile' => 'nullable',
            'receiver.email' => 'nullable',
            'receiver.tier_id' => 'nullable',
            'destination_address' => 'required|array',
            'destination_address.address' => 'required',
            'destination_address.country' => 'required',
            'destination_address.state' => 'required',
            'destination_address.city' => 'required',
            'destination_address.zipcode' => 'required',

        ];
    }

    public function toRecieverDTO(): ReceiverData
    {
        $validated = $this->validated();

        return ReceiverData::fromArray($validated['receiver']);
    }

    public function toShippingAddressDto(): ShippingAddressData
    {
        $validated = $this->validated();

        return ShippingAddressData::fromArray($validated['destination_address']);
    }
}
