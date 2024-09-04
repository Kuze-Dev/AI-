<?php

declare(strict_types=1);

namespace Domain\Tenant\DataTransferObjects;

class MailData
{
    public function __construct(
        public readonly ?string $from_address = null,

    ) {
    }
}
