<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'type' => [
                'required',
                Rule::in(['status', 'bank_proof']),
            ],
            'status' => [
                'nullable',
                Rule::in(['For Cancellation', 'Fulfilled']),
                Rule::requiredIf(function () {
                    return $this->input('type') === 'status';
                }),
            ],
            'notes' => [
                'nullable',
                'string',
                'min:1',
                'max:500',
            ],
            'proof_of_payment' => 'nullable|string|url',
        ];
    }
}
