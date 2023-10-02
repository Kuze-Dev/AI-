<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Shipping;

use Domain\Cart\Models\CartLine;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Collection;

class ShippingRateRequest extends FormRequest
{
    /** @var \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    private Collection $cartLinesCache;

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
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {

                    $cartLineIds = $value;

                    $cartLines = $this->getCartLines();

                    if (count($cartLineIds) !== $cartLines->count()) {
                        $fail('Invalid cart line IDs.');
                    }
                },
            ],
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

    /** @return \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    public function getCartLines(): Collection
    {
        if (empty($this->cartLinesCache)) {
            $cartLineIds = $this->validated('cart_line_ids');

            $this->cartLinesCache = CartLine::query()
                ->with('purchasable')
                ->whereHas('cart', function ($query) {
                    $query->where('session_id', $this->bearerToken());
                })
                ->whereNull('checked_out_at')
                ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
                ->get();
        }

        return $this->cartLinesCache;
    }

    public function toRecieverDTO(): ReceiverData
    {
        $validated = $this->validated();

        return ReceiverData::fromArray($validated['receiver']);
    }

    public function toShippingAddressDto(): ShippingAddressData
    {
        $validated = $this->validated();

        return ShippingAddressData::fromRequestData($validated['destination_address']);
    }
}
