<?php

declare(strict_types=1);

namespace Domain\Review\Requests;

use Domain\Review\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where('status', 'Fulfilled'),
                Rule::exists('order_lines', 'order_id')->where('id', $this->input('order_line_id')),
            ],
            'product_id' => [
                'required',
                Rule::exists('products', 'id'),
            ],
            'order_line_id' => [
                'required',
                Rule::exists('order_lines', 'id'),
                function ($attribute, $value, $fail) {
                    $orderLineId = $this->input('order_line_id');
                    $review = Review::where('order_line_id', $orderLineId);
                    if($review->exists()) {
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
            'anonymous' => [
                'required',
                'bool',
            ],
            'comment' => [
                'nullable',
            ],
            'media' => [
                'nullable',
                'array',
            ],
            'data' => [
                'array',
                'nullable',
            ],
        ];
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
