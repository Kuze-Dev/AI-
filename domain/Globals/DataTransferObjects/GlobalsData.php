<?php

declare(strict_types=1);

namespace Domain\Globals\DataTransferObjects;

use Domain\Internationalization\DataTransferObjects\TranslationDTO;

class GlobalsData extends TranslationDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $blueprint_id,
        public readonly string $locale,
        public readonly ?array $data = [],
        public readonly array $sites = [],
    ) {}

    public static function fromArray(array $data): self
    {

        return new self(
            name: $data['name'],
            blueprint_id: $data['blueprint_id'],
            locale: $data['locale'] ?? 'en',
            data: $data['data'] ?? [],
            sites: $data['sites'] ?? [],
        );
    }
}
