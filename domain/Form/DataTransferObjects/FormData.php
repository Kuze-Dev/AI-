<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

readonly class FormData
{
    /** @param  \Domain\Form\DataTransferObjects\FormEmailNotificationData[]  $form_email_notifications */
    public function __construct(
        public string $blueprint_id,
        public string $name,
        public bool $store_submission = false,
        public bool $uses_captcha = false,
        public array $form_email_notifications = [],
        public array $sites = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            blueprint_id: $data['blueprint_id'],
            name: $data['name'],
            store_submission: $data['store_submission'] ?? false,
            uses_captcha: $data['uses_captcha'] ?? false,
            form_email_notifications: array_map(
                fn (array $formEmailNotificationData) => new FormEmailNotificationData(
                    id: $formEmailNotificationData['id'] ?? null,
                    to: $formEmailNotificationData['to'],
                    cc: $formEmailNotificationData['cc'] ?? [],
                    bcc: $formEmailNotificationData['bcc'] ?? [],
                    sender: $formEmailNotificationData['sender'],
                    sender_name: $formEmailNotificationData['sender_name'],
                    reply_to: $formEmailNotificationData['reply_to'] ?? [],
                    subject: $formEmailNotificationData['subject'],
                    template: $formEmailNotificationData['template'],
                    has_attachments: $formEmailNotificationData['has_attachments'],
                ),
                $data['form_email_notifications'] ?? []
            ),
            sites: $data['sites'] ?? []
        );
    }
}
