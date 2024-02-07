<?php

declare(strict_types=1);

namespace Domain\Form\Database\Factories;

use Domain\Form\Models\FormEmailNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Form\Models\FormEmailNotification>
 */
class FormEmailNotificationFactory extends Factory
{
    protected $model = FormEmailNotification::class;

    public function definition(): array
    {
        return [
            'to' => $this->emails(),
            'cc' => $this->emails(),
            'bcc' => $this->emails(),
            'sender' => $this->faker->safeEmail(),
            'sender_name' => $this->faker->name(),
            'reply_to' => $this->faker->safeEmail(),
            'subject' => $this->faker->sentence(),
            'template' => $this->faker->word(),
            'has_attachments' => false,
        ];
    }

    public function hasAttachments(bool $state = true): self
    {
        return $this->state(
            ['has_attachments' => $state]
        );
    }

    protected function emails(?int $count = null): array
    {
        return array_map(
            fn () => $this->faker->safeEmail(),
            range(1, $count ?? random_int(1, 5))
        );
    }
}
