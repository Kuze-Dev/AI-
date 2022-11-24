<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

class FormEmailNotificationData
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $template,
        public readonly ?int $id = null,
        public readonly ?string $cc = null,
        public readonly ?string $bcc = null,
        public readonly ?string $reply_to = null,
        public readonly ?string $sender = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            recipient: $data['recipient'],
            template: $data['template'],
            id: $data['id'] ?? null,
            cc: $data['cc'] ?? null,
            bcc: $data['bcc'] ?? null,
            reply_to: $data['reply_to'] ?? null,
            sender: $data['sender'] ?? null,
        );
    }
}
