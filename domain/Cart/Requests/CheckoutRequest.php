<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Actions\PurchasableCheckerAction;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {

                    //auth check
                    // $checkAuth = app(PurchasableCheckerAction::class)->checkAuth($value);
                    // if ($checkAuth !== count($value)) {
                    //     $fail('Invalid cart line IDs.');
                    // }

                    // //stock check
                    // $checkStocks = app(PurchasableCheckerAction::class)->checkStock($value);
                    // if ($checkStocks !== count($value)) {
                    //     $fail('Invalid stocks');
                    // }
                },
            ],
        ];
    }
}
