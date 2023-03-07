<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

class FormEmailNotificationData
{
    public function __construct(
        public readonly array $to,
        public readonly string $sender,
        public readonly string $subject,
        public readonly string $template,
        public readonly ?int $id = null,
        public readonly array $cc = [],
        public readonly array $bcc = [],
        public readonly array $reply_to = [],
    ) {
    }
}
