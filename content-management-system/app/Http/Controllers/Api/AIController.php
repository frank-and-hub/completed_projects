<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenAIService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    // protected $openAI;

    public function __construct(protected OpenAIService $openAI)
    {
        // $this->openAI = $openAI;
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        $result = $this->openAI->generateText($request->input('prompt'));

        return response()->json(['response' => $result]);
    }
}
