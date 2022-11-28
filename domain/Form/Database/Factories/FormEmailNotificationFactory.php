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
            'form_id' => FormFactory::new(),
            'recipient' => $this->emails(),
            'cc' => $this->emails(),
            'bcc' => $this->emails(),
            'reply_to' => $this->faker->safeEmail(),
            'sender' => $this->faker->safeEmail(),
            'template' => $this->faker->word(),
        ];
    }

    public function emails(?int $count = null): string
    {
        $count ??= $this->faker->randomDigitNotZero();

        $emails = [];

        foreach (range(1, $count) as $i) {
            $emails[] = $this->faker->safeEmail();
        }

        return implode(',', $emails);
    }
}
