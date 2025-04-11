<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatHistory;
use Ramsey\Uuid\Uuid;

class ChatbotController extends Controller {
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $user = Auth::user();
        $userId = $user?->id;

        $chatHistory = [];

        if ($userId && !$request->sessionId) {
            $sessionId = (string) Uuid::uuid4();
            $messages = [[
                'role' => 'user',
                'content' => $request->message
            ]];
        } else { 
            $sessionId = $request->session_id;
        }

        if ($sessionId) {
            $chatHistory = ChatHistory::where('session_id', $sessionId)
                ->get()
                ->flatMap(fn($chat) => [
                    ['role' => 'user', 'content' => $chat->user_message],
                    ['role' => 'assistant', 'content' => $chat->bot_response],
                ])
                ->toArray();
        }
        

        $messages = array_merge($chatHistory, [
            ['role' => 'user', 'content' => $request->message]
        ]);

        $response = Http::post('http://localhost:11434/api/chat', [
            'model' => 'mistral',
            'messages' => $messages,
            'stream' => false,
        ]);

        $responseData = $response->json();

        $botResponse = $responseData['response']
        ?? ($responseData['message']['content'] ?? '[No response from model]');

        ChatHistory::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'user_message' => $request->message,
            'bot_response' => $botResponse,
        ]);

        $responseForUser = [
            'response' => $botResponse,
        ];

        if ($user) {
            $responseForUser['session_id'] = $sessionId;
        }

        return response()->json($responseForUser);
    }
}