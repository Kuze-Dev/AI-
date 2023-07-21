<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Address\Models\State;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartSummaryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'cart_line_ids' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {

                    $cartLineIdArray = explode(',', $value);
                    $cartLineIds = array_map('intval', $cartLineIdArray);

                    $cartLines = CartLine::whereIn('id', $cartLineIds)
                        ->whereHas('cart', function ($query) {
                            $query->whereBelongsTo(auth()->user());
                        })
                        ->whereNull('checked_out_at')
                        ->get();

                    if (count($cartLineIds) !== $cartLines->count()) {
                        $fail('Invalid cart line IDs.');
                    }
                },
            ],
        ];
    }
}
