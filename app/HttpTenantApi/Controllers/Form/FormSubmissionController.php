<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\FormSubmission\FormSubmissionRequest;
use Domain\Form\Actions\CreateFormSubmissionAction;
use Domain\Form\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

class FormSubmissionController extends Controller
{
    /** @throws Throwable */
    #[Post('form-submissions/{form}')]
    public function __invoke(FormSubmissionRequest $request, Form $form): JsonResponse
    {
        DB::transaction(
            function () use ($request, $form) {
                return app(CreateFormSubmissionAction::class)
                    ->execute(
                        form: $form,
                        data: $request->validated(),
                    );
            }
        );

        return response()
            ->json([
                'message' => 'Successfully unread notification!',
            ], 201);
    }
}
