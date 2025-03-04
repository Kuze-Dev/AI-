<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Actions\CartPurchasableValidatorAction;
use Domain\Cart\Enums\CartUserType;
use Domain\Cart\Exceptions\InvalidPurchasableException;
use Domain\Customer\Models\Customer;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;
use Throwable;

class CheckoutRequest extends FormRequest
{
    public function rules(#[CurrentUser('sanctum')] ?Customer $customer): array
    {
        return [
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) use ($customer) {

                    $type = $customer ? CartUserType::AUTHENTICATED : CartUserType::GUEST;
                    /** @var int|string $userId */
                    $userId = $customer ? $customer->id : $this->bearerToken();

                    //auth check
                    $checkAuth = app(CartPurchasableValidatorAction::class)->validateAuth($value, $userId, $type);
                    if ($checkAuth !== count($value)) {
                        $fail('Invalid cart line IDs.');
                    }

                    try {
                        //stock check
                        $checkStocks = app(CartPurchasableValidatorAction::class)->validateCheckout($value, $userId, $type);
                        if ($checkStocks !== count($value)) {
                            $fail('Invalid stocks');
                        }
                    } catch (Throwable $th) {
                        if ($th instanceof InvalidPurchasableException) {
                            $fail($th->getMessage());
                        }
                    }
                },
            ],
        ];
    }
}
