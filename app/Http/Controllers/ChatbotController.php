<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatHistory;
use Ramsey\Uuid\Uuid;

class ChatbotController extends Controller {
    public function chat(Request $request) {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $user = Auth::user();
        $userId = $user?->id;

        $chatHistory = [];
        $sessionId = $request->session_id;
        
        if ($userId && !$sessionId) {
            $sessionId = (string) Uuid::uuid4();
        }

        if ($sessionId) {
            $chatHistory = ChatHistory::where('session_id', $sessionId)
                ->get()
                ->map(fn($chat) => [
                    ['role' => 'user', 'content' => $chat->user_message],
                    ['role' => 'assistant', 'content' => $chat->bot_response],
                ])
                ->flatten(1)
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
        $botResponse = $responseData['message']['content'] ?? '[No response from model]';

        if ($userId && $sessionId) {
            ChatHistory::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'user_message' => $request->message,
                'bot_response' => $botResponse,
            ]);
        }

        $responseForUser = [
            'response' => $botResponse,
        ];

        if ($user) {
            $responseForUser['session_id'] = $sessionId;
        }

        return response()->json([
            'response' => $botResponse,
            'chat_history' => $chatHistory,
            'session_id' => $sessionId,
        ]);
    }
}
