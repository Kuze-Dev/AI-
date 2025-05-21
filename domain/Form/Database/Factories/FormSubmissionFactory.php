<?php

declare(strict_types=1);

namespace Domain\Form\Database\Factories;

use Domain\Form\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Form\Models\FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'data' => [],
        ];
    }
}
