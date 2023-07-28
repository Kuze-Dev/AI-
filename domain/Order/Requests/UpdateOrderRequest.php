<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Domain\PaymentMethod\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        //mali
        $paymentMethods = PaymentMethod::whereNotIn('gateway', ['manual', 'bank-transfer'])->get();

        $slugs = $paymentMethods->pluck('slug')->toArray();
        array_push($slugs, 'status', 'bank-transfer');

        return [
            'type' => [
                'required',
                Rule::in($slugs),
            ],
            'status' => [
                'nullable',
                Rule::in(['Cancelled', 'Fulfilled']),
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
