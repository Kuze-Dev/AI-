<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\ManipulationType;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @implements Arrayable<string, mixed>
 */
class ConversionData implements Arrayable
{
    /** @param  array<string, ManipulationData>  $manipulations */
    private function __construct(
        public readonly string $name,
        public readonly array $manipulations = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! empty($data['manipulations'] ?? [])) {

            $manipulations = [];
            foreach ($data['manipulations'] as $type => $params) {
                // adding key to replace value when update
                if (is_array($params)) {
                    // from DB
                    $manipulations[$params['type']] = new ManipulationData(ManipulationType::from($params['type']), $params['params']);
                } else {
                    // from input
                    $manipulations[$type] = new ManipulationData(ManipulationType::from($type), Arr::wrap($params));
                }
            }

            $data['manipulations'] = $manipulations;
        }

        return new self(
            name: $data['name'],
            manipulations: $data['manipulations'] ?? [],
        );
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function toArray()
    {
        return (array) $this;
    }
}
