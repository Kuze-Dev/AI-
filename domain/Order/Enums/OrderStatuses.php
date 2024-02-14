<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

use Domain\Order\Models\Order;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum OrderStatuses: string implements HasColor, HasLabel
{
    case PROCESSING = 'processing';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case FULFILLED = 'fulfilled';
    case FORPAYMENT = 'for_payment';
    case FORAPPROVAL = 'for_approval';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FORAPPROVAL => 'warning',
            self::REFUNDED,
            self::CANCELLED => 'danger',
            self::FULFILLED,
            self::DELIVERED => 'success',
            self::PACKED,
            self::PROCESSING,
            self::SHIPPED => 'primary',
            default => 'secondary',
        };
    }

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }

    public static function forOrderUpdate(Order $order)
    {
        return collect(self::cases())
            ->when(
                ! $order->is_paid,
                fn (Collection $cases) => $cases->reject(
                    fn (self $case) => $case === self::FULFILLED
                )
            );
    }
}
