<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceOrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
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

    #[\Override]
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
