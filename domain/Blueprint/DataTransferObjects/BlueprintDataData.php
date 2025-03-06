<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use App\Settings\CustomerSettings;
use Domain\Blueprint\Enums\FieldType;
use Domain\Content\Models\ContentEntry;
use Domain\Customer\Models\Customer;
use Domain\Globals\Models\Globals;
use Domain\Page\Models\BlockContent;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class BlueprintDataData
{
    public function __construct(
        public readonly string $blueprint_id,
        public readonly int $model_id,
        public readonly string $model_type,
        public readonly string $state_path,
        public readonly null|string|array|bool $value,
        public readonly FieldType $type,
    ) {}

    public static function fromArray(Model $model, array $data): self
    {
        $blueprintId = null;

        if ($model instanceof ContentEntry) {
            $blueprintId = $model->content->blueprint->getKey();
        } elseif ($model instanceof BlockContent) {
            $blueprintId = $model->block->blueprint->getKey();
        } elseif ($model instanceof TaxonomyTerm) {
            $blueprintId = $model->taxonomy->blueprint->getKey();
        } elseif ($model instanceof Globals) {
            $blueprintId = $model->blueprint->getKey();
        } elseif ($model instanceof Customer) {
            $blueprintId = app(CustomerSettings::class)->blueprint_id;
        } else {
            throw new InvalidArgumentException;
        }

        return new self(
            blueprint_id: $blueprintId,
            model_id: $model->getKey(),
            model_type: $model->getMorphClass(),
            state_path: $data['statepath'],
            value: $data['value'],
            type: $data['type']
        );
    }
}
