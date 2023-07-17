<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

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
            'state_id' => [
                'nullable',
                'int',
                function ($attribute, $value, $fail) {
                    $customerId = auth()->user()?->id;

                    $customer = Customer::query()
                        ->whereHas('addresses', function ($query) use ($value) {
                            $query->where('state_id', $value);
                        })
                        ->whereId($customerId)
                        ->count();

                    if ($customer == 0) {
                        $fail('Invalid state id');
                    }
                },
            ],
            'country_id' => [
                'required',
                'int',
                function ($attribute, $value, $fail) {
                    $customerId = auth()->user()?->id;

                    $customer = Customer::query()
                        ->whereHas('addresses', function ($query) use ($value) {
                            $query->whereHas('state', function ($subQuery) use ($value) {
                                $subQuery->where('country_id', $value);
                            });
                        })
                        ->whereId($customerId)
                        ->count();

                    if ($customer == 0) {
                        $fail('Invalid country id');
                    }
                },
            ],
            'discount_code' => [
                'nullable',
                'string',
            ],
        ];
    }
}
