<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\FormResource;
use Domain\Form\Models\Form;
use Spatie\RouteAttributes\Attributes\Get;

class FormController extends Controller
{
    #[Get('forms')]
    public function index()
    {
        return FormResource::collection(Form::with('blueprint')->paginate());
    }

    #[Get('forms/{form}')]
    public function show(Form $form)
    {
        return FormResource::make($form);
    }
}
