<?php

namespace Domain\OpenAi\Context;

use App\Models\Content;

class ContentsContextBuilder
{
    /**
     * Build a combined context array for all contents.
     *
     * Output shape:
     * [
     *   content_id => [
     *     section_state_name => [
     *       field_state_name => value (or null)
     *     ]
     *   ]
     * ]
     *
     * @param  iterable<\App\Models\Content>  $contents
     * @return array
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
