<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Models\CartLine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class CheckoutRequest extends FormRequest
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
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {

                    $customerId = auth()->user()->id;

                    $cartLines = CartLine::with('purchasable')->whereIn('id', $value)
                        ->whereHas('cart', function ($query) use ($customerId) {
                            $query->whereCustomerId($customerId);
                        })
                        ->get();

                    foreach ($cartLines as $cartLine) {
                        if ($cartLine->purchasable->allow_customer_remarks) {
                            $remarks = $cartLine->notes;
                            $remarkImage = $cartLine->getFirstMediaUrl('cart_line_notes') ?? null;
                            if (empty($remarks) && empty($remarkImage)) {
                                $fail('Remarks are required for this cart line.');
                            }
                        }
                    }

                    if (count($value) !== $cartLines->count()) {
                        $fail('Invalid cart line IDs.');
                    }
                },
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
