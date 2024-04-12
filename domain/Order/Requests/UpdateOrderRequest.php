<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Domain\Cart\Enums\CartUserType;
use Domain\Cart\Helpers\ValidateRemarksMedia;
use Domain\Order\Enums\OrderStatuses;
use Domain\PaymentMethod\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var \Domain\Order\Models\Order $order */
        $order = $this->route('order');

        $paymentMethods = PaymentMethod::whereNotIn('gateway', ['manual', 'bank-transfer'])->get();

        $slugs = $paymentMethods->pluck('slug')->toArray();
        array_push($slugs, 'status', 'bank-transfer');

        return [
            'type' => [
                'required',
                Rule::in($slugs),
                function ($attribute, $value, $fail) use ($order) {

                    if (! in_array($value, ['status', 'bank-transfer'])) {

                        $type = auth()->user() ? CartUserType::AUTHENTICATED : CartUserType::GUEST;

                        $isValid = $order->whereHas('payments', function (Builder $query) use ($value, $order) {
                            $query->where('gateway', $value)->where('payable_id', $order->id);
                        })
                            ->where(function ($query) use ($type) {
                                if ($type === CartUserType::AUTHENTICATED) {
                                    /** @var \Domain\Customer\Models\Customer $customer */
                                    $customer = auth()->user();
                                    $query->where('customer_id', $customer->id);
                                }
                            })
                            ->first();

                        if (! $isValid) {
                            $fail('Invalid request');

                            return;
                        }
                    }
                },
            ],
            'status' => [
                'nullable',
                Rule::in(['cancelled', 'fulfilled']),
                Rule::requiredIf(fn () => $this->input('type') === 'status'),
                function ($attribute, $value, $fail) use ($order) {
                    if (
                        $value == OrderStatuses::CANCELLED->value &&
                        ! in_array($order->status, [OrderStatuses::PENDING, OrderStatuses::FORPAYMENT])
                    ) {
                        $fail("You can't cancel this order");

                        return;
                    }

                    if (
                        $value == OrderStatuses::FULFILLED->value &&
                        $order->status !== OrderStatuses::DELIVERED
                    ) {
                        $fail("You can't fullfilled this order");

                        return;
                    }
                },
            ],
            'notes' => [
                'nullable',
                'string',
                'min:1',
                'max:255',
            ],
            'proof_of_payment' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    app(ValidateRemarksMedia::class)->execute([$value], $fail);
                },
            ],
        ];
    }
}
