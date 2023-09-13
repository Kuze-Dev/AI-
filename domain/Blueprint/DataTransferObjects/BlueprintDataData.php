<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\BlockContent;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class BlueprintDataData
{
    public function __construct(
        public readonly string $blueprint_id,
        public readonly int $model_id,
        public readonly string $model_type,
        public readonly string $state_path,
        public readonly ?string $value,
        public readonly FieldType $type,
    ) {
    }

    public static function fromArray(Model $model, string $state_path, FieldType $field_type): self
    {
        $blueprintId = null;

        if ($model instanceof ContentEntry) {
            $blueprintId = $model->content->blueprint->getKey();
        } elseif ($model instanceof BlockContent) {
            $blueprintId = $model->block->blueprint->getKey();
        } else {
            throw new InvalidArgumentException();
        }

        $data = $model->data;

        $keys = explode('.', $state_path);

        foreach ($keys as $key) {
            if ( ! isset($data[$key])) {
            }

            $data = $data[$key];
        }

        $value = is_array($data) ? end($data) : $data;

        return new self(
            blueprint_id: $blueprintId,
            model_id: $model->getKey(),
            model_type: $model->getMorphClass(),
            state_path: $state_path,
            value: $value,
            type: $field_type
        );
    }
}
