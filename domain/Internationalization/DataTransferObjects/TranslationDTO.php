<?php

declare(strict_types=1);

namespace Domain\Internationalization\DataTransferObjects;

/**
 * FOR IDE SUPPORT
 */
class TranslationDTO
{
    public function __construct(
        public readonly ?array $data = [],

    ) {
    }
}
