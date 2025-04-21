<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Features\CMS\CMSBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Requests\FormSubmission\FormSubmissionRequest;
use Domain\Form\Actions\CreateFormSubmissionAction;
use Domain\Form\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

#[Middleware('feature.tenant:'.CMSBase::class)]
class FormSubmissionController extends Controller
{
    /** @throws Throwable */
    #[Post('forms/{form}/submissions')]
    public function __invoke(FormSubmissionRequest $request, Form $form): JsonResponse
    {

        try {

            DB::transaction(
                fn () => app(CreateFormSubmissionAction::class)
                    ->execute(
                        form: $form,
                        data: Arr::except($request->validated(), 'captcha_token'),
                    )
            );

            return response()
                ->json([
                    'message' => 'Form submitted!',
                ], 201);

        } catch (Throwable $th) {
            return response()
                ->json(
                    [
                        'message' => $th->getMessage(),
                    ],
                    422
                );
        }

    }
}
