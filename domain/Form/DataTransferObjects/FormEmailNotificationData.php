<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

readonly class FormEmailNotificationData
{
    public function __construct(
        public array $to,
        public string $sender,
        public string $sender_name,
        public string $subject,
        public string $template,
        public bool $has_attachments,
        public ?int $id = null,
        public array $cc = [],
        public array $bcc = [],
        public array $reply_to = [],
    ) {}
}
