<?php

declare(strict_types=1);

namespace Domain\OpenAi\Context;

use App\Models\Content;

class ContentsContextBuilder
{
    /**
     * @param  iterable<Domain\Content\Models\Content>  $contents
     */
    public static function build(iterable $contents): array
    {
        $result = [];

        foreach ($contents as $content) {
            $blueprint = $content->blueprint;

            if (! $blueprint) {
                continue;
            }

            // decode schema
            if (is_object($blueprint->schema) && method_exists($blueprint->schema, 'toArray')) {
                $decodedSchema = $blueprint->schema->toArray();
            } elseif (is_string($blueprint->schema)) {
                $decodedSchema = json_decode($blueprint->schema, true) ?? [];
            } elseif (is_array($blueprint->schema)) {
                $decodedSchema = $blueprint->schema;
            } else {
                $decodedSchema = (array) $blueprint->schema;
            }

            // actual content data — adjust based on your column name
            $data = $content->data ?? [];

            $result[$content->id] = BlueprintContextBuilder::build($decodedSchema, $data);
        }

        return $result;
    }
}
