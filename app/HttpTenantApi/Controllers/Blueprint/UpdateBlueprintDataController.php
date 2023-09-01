<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Blueprint;

use App\Features\CMS\CMSBase;
use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Page\Models\BlockContent;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;
use Illuminate\Http\JsonResponse;

#[
    Prefix('blueprint'),
    Middleware('feature.tenant:'. CMSBase::class)
]
class UpdateBlueprintDataController
{
    public function __construct(
        protected CreateBlueprintDataAction $createBlueprintDataAction,
    ) {
    }

    #[Patch('blueprint-data')]
    public function __invoke(): JsonResponse
    {
        $blockContents = BlockContent::all();
        foreach($blockContents as $blockContent) {
            $blockContent->load('block.blueprint');
            $blueprintData = BlueprintData::where('model_id', $blockContent->id)->first();
            if( ! $blueprintData) {
                $this->createBlueprintDataAction->execute($blockContent);
            }
        }

        return response()
            ->json([
                'message' => 'Blueprint_data table has been updated!',
            ]);

    }
}
