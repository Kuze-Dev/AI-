<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CartQuantityUpdateRequest extends FormRequest
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
            'cartLineId' => [
                'required',
                Rule::exists('cart_lines', 'id'),
            ],
            'action' => [
                'required',
                Rule::in(['increase', 'decrease', 'edit']),
            ],
            'quantity' => [
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    return $this->input('action') === 'edit';
                }),
                'min:1',
            ],
        ];
    }

    protected function prepareForValidation()
    {

        $this->merge([
            /**
             * @phpstan-ignore-next-line
             *  Cannot cast object|string|null to int.
             * PHPStan doesn't analyze the vendor files.
             */
            'cartLineId' => (int) $this->route('cartLineId'),
        ]);
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
