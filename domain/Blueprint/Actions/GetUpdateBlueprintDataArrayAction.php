<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Content\Models\ContentEntry;
use Domain\Customer\Models\Customer;
use Domain\Globals\Models\Globals;
use Domain\Page\Models\BlockContent;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;

class GetUpdateBlueprintDataArrayAction
{
    /**
     * @param  ContentEntry|BlockContent|Customer|TaxonomyTerm|Globals  $model
     * @param  BlueprintData[]  $blueprintDataArray  Array of BlueprintData models
     */
    public function execute(Model $model, array $blueprintDataArray): array
    {
        $arrayData = $model->data;

        foreach ($blueprintDataArray as $decopuledData) {
            $statePath = $decopuledData->state_path;
            $newValue = $decopuledData->value;

            if ($decopuledData->type === FieldType::MEDIA->value) {

                // $newValue = $decopuledData->
                $newValue = $decopuledData->value
                    ? json_decode($decopuledData->value, true)
                    : [];
            }

            $keys = explode('.', $statePath);

            $temp = &$arrayData;

            // Traverse the array using the keys from the state path
            foreach ($keys as $key) {
                // If the key doesn't exist, create it as an array
                if (! isset($temp[$key])) {
                    $temp[$key] = [];
                }

                // Move deeper into the array
                $temp = &$temp[$key];
            }

            // Set the final key to the new value
            $temp = $newValue;
        }

        // Return the updated array
        return $arrayData;
    }
}
