<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

use Domain\Service\Models\Service;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum ServiceOrderStatus: string implements HasColor, HasLabel
{
    case INPROGRESS = 'in_progress';
    case FORPAYMENT = 'for_payment';
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case CLOSED = 'closed';
    case FOR_APPROVAL = 'for_approval';

    public static function fromService(Service $service): self
    {
        $status = self::FORPAYMENT;

        if ($service->needs_approval) {
            $status = self::PENDING;
        } elseif (! $service->pay_upfront && ! $service->is_subscription) {
            $status = self::INPROGRESS;
        }

        return $status;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            ServiceOrderStatus::PENDING, ServiceOrderStatus::INPROGRESS => 'warning',
            ServiceOrderStatus::INACTIVE, ServiceOrderStatus::CLOSED => 'danger',
            ServiceOrderStatus::COMPLETED, ServiceOrderStatus::ACTIVE => 'success',
            default => 'secondary',
        };
    }

    public function getLabel(): ?string
    {
        return Str::headline($this->value);
    }
}
