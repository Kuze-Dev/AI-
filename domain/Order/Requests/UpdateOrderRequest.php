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
            'status' => [
                'required',
                Rule::in(['For Cancellation', 'Fulfilled']),
            ],
            'notes' => [
                'nullable',
                'string',
                'min:1',
                'max:500',
            ],
        ];
    }
}
