<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Domain\PaymentMethod\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        $order = $this->route('order');

        $paymentMethods = PaymentMethod::whereNotIn('gateway', ['manual', 'bank-transfer'])->get();

        $slugs = $paymentMethods->pluck('slug')->toArray();
        array_push($slugs, 'status', 'bank-transfer');

        return [
            'type' => [
                'required',
                Rule::in($slugs),
                function ($attribute, $value, $fail) use ($order) {

                    if ( ! in_array($value, ['status', 'bank-transfer'])) {

                        $isValid = $order->whereHas('payments', function (Builder $query) use ($value, $order) {
                            $query->where('gateway', $value)->where('payable_id', $order->id);
                        })->first();

                        if ( ! $isValid) {
                            $fail('Invalid request');

                            return;
                        }
                    }
                },
            ],
            'status' => [
                'nullable',
                Rule::in(['cancelled', 'fulfilled']),
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
