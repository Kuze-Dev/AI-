<?php

declare(strict_types=1);

namespace Domain\Tenant\DataTransferObjects;

class TenantData
{
    /** @param array<DomainData> $domains */
    public function __construct(
        public readonly string $name,
        public readonly bool $is_suspended = true,
        public readonly ?DatabaseData $database = null,
        public readonly array $domains = [],
        public readonly array $features = [],
    ) {
    }

    public static function fromArray(array $data): self
    {

        return new self(
            name: $data['name'],
            is_suspended: $data['is_suspended'] ?? false,
            database: filled($data['database'] ?? null)
                ? new DatabaseData(
                    host: $data['database']['host'],
                    port: $data['database']['port'],
                    name: $data['database']['name'],
                    username: $data['database']['username'],
                    password: $data['database']['password'],
                )
                : null,
            domains: array_map(
                fn ($data) => new DomainData(
                    id: $data['id'] ?? null,
                    domain: $data['domain'],
                ),
                $data['domains']
            ),
            features: isset($data['features']) ? array_filter($data['features']) : []
        );
    }

    public function getNormalizedFeatureNames(): array
    {
        return array_map(
            fn (string $feature) => class_exists($feature) && (method_exists($feature, 'resolve') || method_exists($feature, '__invoke'))
                ? app($feature)->name ?? $feature
                : $feature,
            $this->features,
        );
    }
}
