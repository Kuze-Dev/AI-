<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

use Domain\Form\Models\Form;

class FormSubmissionData
{
    public function __construct(
        public readonly Form $form,
        public readonly array $data,
    ) {
    }
}
