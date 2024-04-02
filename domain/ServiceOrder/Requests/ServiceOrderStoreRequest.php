<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceOrderStoreRequest extends FormRequest
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
            'service_id' => [
                'integer',
            ],
            'service_address_id' => [
                'integer',
            ],
            'billing_address_id' => [
                'integer',
            ],
            'is_same_as_billing' => [
                'bool',
            ],
            'schedule' => [
                'date',
            ],
            'form' => [
                'array',
            ],
            'additional_charges' => [
                'array',
            ],

        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    #[\Override]
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
