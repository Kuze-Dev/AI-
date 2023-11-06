<?php

declare(strict_types=1);

namespace Domain\Tier\Enums;

enum TierApprovalStatus: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
