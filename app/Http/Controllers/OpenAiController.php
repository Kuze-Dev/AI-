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
        $reply = $openai->chat('what model of open ai I use?');



        return response()->json(['reply' => $reply]);
    }
}
