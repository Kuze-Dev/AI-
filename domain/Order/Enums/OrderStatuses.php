<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

use Domain\Order\Models\Order;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum OrderStatuses: string implements HasColor, HasLabel //, HasIcon
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

    public function getColor(): string
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

    //    public function getIcon(): ?string
    //    {
    //        return match ($this) {
    //            self::PROCESSING => 'icon-processing',
    //            self::PENDING => 'icon-pending',
    //            self::CANCELLED => 'icon-cancelled',
    //            self::REFUNDED => 'icon-refunded',
    //            self::PACKED => 'icon-packed',
    //            self::SHIPPED => 'icon-shipped',
    //            self::DELIVERED => 'icon-delivered',
    //            self::FULFILLED => 'icon-fulfilled',
    //            self::FORPAYMENT => 'icon-for-payment',
    //            self::FORAPPROVAL => 'icon-for-approval',
    //        };
    //    }
}
