<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

class FormData
{
    /** @param \Domain\Form\DataTransferObjects\FormEmailNotificationData[] $form_email_notifications */
    public function __construct(
        public readonly string $blueprint_id,
        public readonly string $name,
        public readonly bool $store_submission,
        public readonly ?string $slug = null,
        public readonly array $form_email_notifications = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            blueprint_id: $data['blueprint_id'],
            name: $data['name'],
            store_submission: $data['store_submission'],
            slug: $data['slug'] ?? null,
            form_email_notifications: array_map(
                fn (array $formEmailNotificationData) => new FormEmailNotificationData(...$formEmailNotificationData),
                $data['form_email_notifications'] ?? []
            ),
        );
    }
}
