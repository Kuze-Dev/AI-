<?php

declare(strict_types=1);

namespace Domain\RewardPoint\Actions;

use App\Settings\RewardPointsSettings;
use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Domain\RewardPoint\Models\PointEarning;
use Illuminate\Support\Carbon;

class EarnPointAction
{
    public function __construct(private readonly RewardPointsSettings $rewardPoint) {}

    /** Execute create collection query. */
    public function execute(Customer $customer, Order $order): PointEarning
    {
        $earnedPoints = ($order->total / $this->rewardPoint->minimum_amount) * $this->rewardPoint->equivalent_point;

        $pointEarning = new PointEarning();

        $pointEarning->create([
            'customer_type' => $customer->getMorphClass(),
            'customer_id' => $customer->getKey(),
            'order_type' => $order->getMorphClass(),
            'order_id' => $order->getKey(),
            'earned_points' => $earnedPoints,
            'date_earned' => Carbon::parse(now()),
        ]);

        return $pointEarning;
    }
}
