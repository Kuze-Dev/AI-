<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

class FormData
{
    /** @param \Domain\Form\DataTransferObjects\FormEmailNotificationData[]|null $form_email_notifications */
    public function __construct(
        public readonly int $blueprint_id,
        public readonly string $name,
        public readonly bool $store_submission,
        public readonly ?string $slug = null,
        public readonly ?array $form_email_notifications = null,
    ) {
    }

    public static function fromArray(array $data, ?array $formEmailNotifications): self
    {
        $formEmailNotificationsData = null;

        if ($formEmailNotifications !== null) {
            $formEmailNotificationsData = array_map(
                fn (array $formEmailNotificationsDatum) => FormEmailNotificationData::fromArray($formEmailNotificationsDatum),
                $formEmailNotifications
            );
        }

        return new self(
            blueprint_id: (int) $data['blueprint_id'],
            name: $data['name'],
            store_submission: $data['store_submission'],
            slug: $data['slug'] ?? null,
            form_email_notifications: $formEmailNotificationsData,
        );
    }
}
