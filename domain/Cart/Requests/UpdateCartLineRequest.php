<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartLineRequest extends FormRequest
{
    public function rules()
    {
        $cartLine = $this->route('cartline');

        return [
            'type' => [
                'required',
                Rule::in(['quantity', 'remarks']),
            ],
            'quantity' => [
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    return $this->input('type') === 'quantity';
                }),
                'min:1',
                function ($attribute, $value, $fail) use ($cartLine) {

                    $purchasable = $cartLine->purchasable;

                    if ($value > $purchasable->stock) {
                        $fail('Quantity exceeds stock');
                        return;
                    }
                }
            ],
            'remarks' => [
                'nullable',
                'array',
                Rule::requiredIf(function () {
                    return $this->input('type') === 'remarks';
                }),
            ],
            'media' => 'nullable|array',
            'media.*' => 'url',
        ];
    }
}
