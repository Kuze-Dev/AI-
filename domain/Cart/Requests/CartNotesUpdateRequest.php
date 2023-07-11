<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Models\CartLine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartNotesUpdateRequest extends FormRequest
{

    public function rules()
    {
        return [
            'cart_line_id' => [
                'required',
                Rule::exists(CartLine::class, 'id'),
            ],
            'meta' => [
                'nullable',
                'array',
            ],
            'file' => 'nullable|array',
            'file.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
