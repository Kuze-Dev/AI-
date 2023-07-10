<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Domain\Cart\Models\CartLine;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'addresses.shipping' => [
                'required',
                Rule::exists('addresses', 'id')->where(function ($query) {
                    $customerId = auth()->user()?->id;

                    $query->where('customer_id', $customerId);
                }),
            ],
            'addresses.billing' => [
                'required',
                Rule::exists('addresses', 'id')->where(function ($query) {
                    $customerId = auth()->user()?->id;

                    $query->where('customer_id', $customerId);
                }),
            ],
            'cart_reference' => [
                'required',
            ],
            'cart_line_ids' => [
                'required',
                Rule::exists('cart_lines', 'id'),
                function ($attribute, $value, $fail) {
                    $cartLineIds = $this->input('cart_line_ids');
                    $reference = $this->input('cart_reference');

                    $customerId = auth()->user()?->id;

                    $cartLines = CartLine::whereIn('id', $cartLineIds)
                        ->whereHas('cart', function ($query) use ($customerId) {
                            $query->whereCustomerId($customerId);
                        })
                        ->whereCheckoutReference($reference)
                        ->where('checkout_expiration', '>', now())
                        ->count();

                    $alreadyCheckedOut = CartLine::whereIn('id', $cartLineIds)
                        ->whereNotNull('checked_out_at')
                        ->count();

                    if (!$cartLines) {
                        $fail('These cart lines needs to be checked out');
                        return;
                    }

                    if ($alreadyCheckedOut) {
                        $fail('No active cart lines for check out');
                        return;
                    }
                },
            ],
            'notes' => [
                'nullable',
                'string',
                'min:1',
                'max:500',
            ],
        ];
    }


    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
