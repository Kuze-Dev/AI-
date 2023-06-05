<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Requests\FormSubmission\FormSubmissionRequest;
use Domain\Form\Actions\CreateFormSubmissionAction;
use Domain\Form\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

class FormSubmissionController extends Controller
{
    /** @throws Throwable */
    #[Post('forms/{form}/submissions')]
    public function __invoke(FormSubmissionRequest $request, Form $form): JsonResponse
    {
        DB::transaction(
            function () use ($request, $form) {
                return app(CreateFormSubmissionAction::class)
                    ->execute(
                        form: $form,
                        data: Arr::except($request->validated(), 'captcha_token'),
                    );
            }
        );

        return response()
            ->json(['message' => 'Form submitted!'], 201);
    }
}
