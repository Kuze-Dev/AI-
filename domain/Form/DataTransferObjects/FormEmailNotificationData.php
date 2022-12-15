<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

class FormEmailNotificationData
{
    public function __construct(
        public readonly string $to,
        public readonly ?string $cc = null,
        public readonly ?string $bcc = null,
        public readonly string $sender,
        public readonly ?string $reply_to = null,
        public readonly string $subject,
        public readonly string $template,
    ) {
    }
}
