<?php

declare(strict_types=1);

namespace Domain\Customer\DataTransferObjects;

class CustomerNotificationData
{
    private function __construct(
        public string $events,
        public string $subject,
        public string $template,
        public array $reply_to = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            events: $data['events'],
            subject: $data['subject'],
            template: $data['template'],
            reply_to: $data['reply_to'] ?? [],
        );
    }
}
