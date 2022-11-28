<?php

declare(strict_types=1);

namespace Domain\Form\DataTransferObjects;

class ForSubmissionData
{
    public function __construct(
        public readonly int $form_id,
        public readonly array $data,
    ) {
    }
}
