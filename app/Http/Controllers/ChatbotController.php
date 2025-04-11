<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatHistory;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $userId = Auth::id();
        $sessionId = $request->session_id ?? uniqid();

        // Build chat history for user if logged in
        $chatHistory = [];
        if ($userId) {
            $chatHistory = ChatHistory::where('user_id', $userId)
                ->where('session_id', $sessionId)
                ->orderBy('created_at')
                ->get()
                ->flatMap(fn($chat) => [
                    ['role' => 'user', 'content' => $chat->user_message],
                    ['role' => 'assistant', 'content' => $chat->bot_response],
                ])
                ->toArray();
        }

        // Append current user message
        $messages = array_merge($chatHistory, [
            ['role' => 'user', 'content' => $request->message]
        ]);

        // Send to Ollama
        $response = Http::post('http://localhost:11434/api/chat', [
            'model' => 'mistral',
            'messages' => $messages,
            'stream' => false,
        ]);

        $responseData = $response->json();

        // Force API to always give a response
        $botResponse = $responseData['response']
        ?? ($responseData['message']['content'] ?? '[No response from model]');

        // Save to DB
        ChatHistory::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'user_message' => $request->message,
            'bot_response' => $botResponse,
        ]);

        // Return back to frontend
        return response()->json([
            'response' => $botResponse,
            'session_id' => $sessionId,
        ]);
    }
}
