<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class CheckoutRequest extends FormRequest
{
    public function rules()
    {
        return [
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {

                    $cartLines = CartLine::with('purchasable')
                        ->whereIn((new CartLine())->getRouteKeyName(), $value)
                        ->whereHas('cart', function ($query) {
                            $query->whereBelongsTo(auth()->user());
                        })
                        ->whereNull('checked_out_at')
                        ->get();

                    if (count($value) !== $cartLines->count()) {
                        $fail('Invalid cart line IDs.');
                    }

                    //stocks checking
                    $cartLinesForCheckout = CartLine::with('purchasable')
                        ->whereHas('cart', function ($query) {
                            $query->whereBelongsTo(auth()->user());
                        })
                        ->whereNull('checked_out_at')
                        ->whereIn((new CartLine())->getRouteKeyName(), $value)
                        ->where(function ($query) {
                            $query->where(function ($subQuery) {
                                $subQuery->where('purchasable_type', Product::class)
                                    ->whereExists(function ($subSubQuery) {
                                        $subSubQuery->select('id')
                                            ->from('products')
                                            ->whereColumn('products.id', 'cart_lines.purchasable_id')
                                            ->where('stock', '>', DB::raw('cart_lines.quantity'));
                                    });
                            })->orWhere(function ($subQuery) {
                                $subQuery->where('purchasable_type', ProductVariant::class)
                                    ->whereExists(function ($subSubQuery) {
                                        $subSubQuery->select('id')
                                            ->from('product_variants')
                                            ->whereColumn('product_variants.id', 'cart_lines.purchasable_id')
                                            ->where('stock', '>', DB::raw('cart_lines.quantity'));
                                    });
                            });
                        })
                        ->get();

                    if ($cartLinesForCheckout->count() !== count($value)) {
                        $fail('Invalid stocks');
                    }
                },
            ],
        ];
    }
}
