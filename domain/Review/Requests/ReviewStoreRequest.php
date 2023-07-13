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
            'title' => [
                'required',

            ],
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5'

            ],
            'comment' => [
                'nullable',
            ],
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id'),
                Rule::exists('orders', 'customer_id')
            ],
            'order_id' => [
                'required',
                Rule::exists('orders', 'id'),
                Rule::exists('order_lines', 'order_id')
                ->where(function ($query) {
                    $productId = $this->input('product_id');
                    $query->where('purchasable_id', $productId);
                }),
                function ($attribute, $value, $fail) {  
                    $productId = $this->input('product_id');
                    $orderId = $this->input('order_id');
                    $review = Review::where('product_id', $productId)->where('order_id',$orderId);
                    if($review->exists()){
                        $fail('You already review this product');
                    }
                },
                
            ],
            'product_id' => [
                'required',
                Rule::exists('products', 'id'),
                Rule::exists('order_lines', 'purchasable_id')
            ],
            'product_review_images' => [
                'nullable',
                'array'
            ]
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
