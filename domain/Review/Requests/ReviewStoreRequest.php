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
