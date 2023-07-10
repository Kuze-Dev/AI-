<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CartQuantityUpdateRequest extends FormRequest
{

    public function rules()
    {
        return [
            'action' => [
                'required',
                Rule::in(['increase', 'decrease', 'edit']),
            ],
            'quantity' => [
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    return $this->input('action') === 'edit';
                }),
                'min:1',
            ],
        ];
    }
}
