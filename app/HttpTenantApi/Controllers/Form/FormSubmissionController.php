<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Http\Controllers\Controller;
use Domain\Form\Actions\CreateForSubmissionAction;
use Domain\Form\DataTransferObjects\ForSubmissionData;
use Domain\Form\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

class FormSubmissionController extends Controller
{
    /** @throws Throwable */
    #[Post('form-submissions/{form}')]
    public function __invoke(Request $request, Form $form): JsonResponse
    {
        $fieldAndRules = [];

        foreach ($form->blueprint->schema->sections as $section) {
            foreach ($section->fields as $field) {
                $fieldAndRules[$section->state_name.'.'.$field->state_name] = $field->rules;
            }
        }

        $attributes = $this->validate($request, $fieldAndRules);

        DB::transaction(
            function () use ($form, $attributes) {
                return app(CreateForSubmissionAction::class)
                    ->execute(
                        new ForSubmissionData(
                            form_id: $form->id,
                            data: $attributes,
                        )
                    );
            }
        );

        return response()->json();
    }
}
