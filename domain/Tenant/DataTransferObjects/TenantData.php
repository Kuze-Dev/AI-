<?php

declare(strict_types=1);

namespace Domain\Tenant\DataTransferObjects;

class TenantData
{
    /** @param  array<DomainData>  $domains */
    public function __construct(
        public readonly string $name,
        public readonly bool $is_suspended = true,
        public readonly ?DatabaseData $database = null,
        public readonly ?BucketData $bucket = null,
        public readonly array $domains = [],
        public readonly ?array $features = [],
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
            bucket: filled($data['bucket'] ?? null)
            ? new BucketData(
                driver: $data['bucket']['driver'] ?? null,
                bucket: $data['bucket']['bucket'],
                access_key: $data['bucket']['access_key'] ?? null,
                secret_key: $data['bucket']['secret_key'] ?? null,
                endpoint: $data['bucket']['endpoint'] ?? null,
                url: $data['bucket']['url'] ?? null,
                region: $data['bucket']['region'] ?? null,
                style_endpoint: $data['bucket']['style_endpoint'] ?? false,
            )
            : null,
            domains: array_map(
                fn ($data) => new DomainData(
                    id: $data['id'] ?? null,
                    domain: $data['domain'],
                ),
                $data['domains']
            ),
            features: isset($data['features']) ? array_filter($data['features']) : null
        );
    }

    public function getNormalizedFeatureNames(): array
    {
        $features = $this->features;

        if (is_array($features)) {
            return array_map(
                fn (string $feature) => class_exists($feature) && (method_exists($feature, 'resolve') || method_exists($feature, '__invoke'))
                    ? app($feature)->name ?? $feature
                    : $feature,
                $features,
            );
        }

        return [];
    }
}
