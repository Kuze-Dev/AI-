<?php

namespace App\Http\Controllers;

use App\Features\Service\OpenAIService;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;

class OpenAiController extends Controller
{
    #[Get('test-openai', name: 'test.openai')]
    public function test(OpenAIService $openai): JsonResponse
    {
        $result = $openai->analyze('what is ai');

        return response()->json($result);
    }

}
