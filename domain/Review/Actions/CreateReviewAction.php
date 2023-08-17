<?php

declare(strict_types=1);

namespace Domain\Review\Actions;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\OrderLine;
use Domain\Review\DataTransferObjects\CreateReviewData;
use Domain\Review\Models\Review;

use Exception;

class CreateReviewAction
{
    public function execute(CreateReviewData $createReviewData, Customer $customer): Review
    {

            $orderLine = OrderLine::find($createReviewData->order_line_id);
            $orderLine->reviewed_at = now();
    
            $order_id = $orderLine->order_id;
            $data = null;
            $product_id = null;

            if(isset($orderLine->purchasable_data['combination'])) {
                $data = $orderLine->purchasable_data['combination'];
            }
    
            if(isset($orderLine->purchasable_data['product'])) {
                $product_id = $orderLine->purchasable_data['product']['id'];
            } else {
                $product_id = $orderLine->purchasable_data['id'];
            }

            $review = new Review([
                'customer_id' => $customer->id,
                'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                'customer_email' => $customer->email,
                'rating' => $createReviewData->rating,
                'comment' => $createReviewData->comment,
                'order_line_id' => $createReviewData->order_line_id,
                'is_anonymous' => $createReviewData->is_anonymous,
                'order_id' => $order_id,
                'data' => $data,
                'product_id' => $product_id
            ]);


            if (isset($createReviewData->media)) {
                foreach ($createReviewData->media as $imageUrl) {
                    try {
                        $review->addMediaFromUrl($imageUrl)
                            ->toMediaCollection('review_product_media');
                    } catch (Exception $e) {
                    }
                }
            }


            $orderLine->reviewed_at = now();
            $orderLine->save();

            $review->save();
        
            return $review;
    }
}
