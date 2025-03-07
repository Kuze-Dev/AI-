<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Requests;

use Domain\ServiceOrder\Enums\Type;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateServiceBillProofOfPaymentRequest extends FormRequest
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
            'referenceId' => [
                'string',
            ],
            'proofOfPayment' => [
                'string',
            ],
            'notes' => [
                'string',
                'nullable',
            ],
            'type' => [
                'required',
                Rule::enum(Type::class),
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
