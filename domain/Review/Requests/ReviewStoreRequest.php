<?php

declare(strict_types=1);

namespace Domain\Review\Requests;

use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\OrderLine;
use Domain\Review\Models\Review;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ReviewStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_line_id' => [
                'required',
                Rule::exists('order_lines', 'id'),
                function ($attribute, $value, $fail) {
                    $orderLineId = $this->input('order_line_id');
                    $review = Review::where('order_line_id', $orderLineId);
                    $orderLine = OrderLine::find($orderLineId);
                    if ($orderLine && isset($orderLine->order)) {
                        if ($orderLine->order->status !== OrderStatuses::FULFILLED) {
                            $fail('You cannot review this item; the product hasn\'t been fulfilled yet.');
                        }
                    }

                    if ($review->exists()) {
                        $fail('You already review this product');
                    }
                },
            ],
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'is_anonymous' => [
                'required',
                'bool',
            ],
            'comment' => [
                'nullable',
                'max:1000',
            ],
            'media' => [
                'nullable',
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
