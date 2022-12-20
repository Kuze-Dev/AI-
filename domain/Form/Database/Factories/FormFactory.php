<?php

declare(strict_types=1);

namespace Domain\Form\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Form\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Form\Models\Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'store_submission' => false,
        ];
    }

    public function storeSubmission(bool $storeSubmission = true): self
    {
        return $this->state(
            ['store_submission' => $storeSubmission]
        );
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}
