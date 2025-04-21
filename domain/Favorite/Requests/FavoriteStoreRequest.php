<?php

declare(strict_types=1);

namespace Domain\Favorite\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class FavoriteStoreRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                Rule::exists('products', 'id'),
                Rule::unique('favorites', 'product_id')
                    ->where(function ($query) {
                        $customer = guest_customer_logged_in();
                        if ($customer) {
                            $query->where('customer_id', $customer->id);
                        }
                    }),

            ],
        ];
    }

    #[\Override]
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
