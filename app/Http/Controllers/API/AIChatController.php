<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AIChatService;

class AIChatController extends Controller
{
    protected $aiChatService;

    public function __construct(AIChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $reply = $this->aiChatService->chat($request->message);

        return response()->json([
            'reply' => $reply
        ]);
    }
}
