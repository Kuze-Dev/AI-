<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Models\CartLine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkRemoveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $cartLineIds = $value;

                    $cartLines = CartLine::query()
                        ->whereHas('cart', function ($query) {
                            $query->whereBelongsTo(customer_logged_in());
                        })
                        ->whereIn((new CartLine)->getRouteKeyName(), $cartLineIds)
                        ->whereNull('checked_out_at');

                    if (count($cartLineIds) !== $cartLines->count()) {
                        $fail('Cart lines not found');
                    }
                },
            ],
            'cart_line_ids.*' => [
                'required',
                Rule::exists(CartLine::class, (new CartLine)->getRouteKeyName()),
            ],
        ];
    }
}
