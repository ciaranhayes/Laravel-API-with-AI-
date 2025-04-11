<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;


class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|integer'
        ]);

        $chatHistory = [];

        if (Auth::check() && $request->filled('session_id')) {
            $userId = Auth::id();
            $sessionId = $request->session_id;

            $chatHistory = \App\Models\ChatHistory::where('user_id', $userId)
                ->where('session_id', $sessionId)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        $response = Http::post('http://localhost:11434/api/generate', [
            'model' => 'mistral',
            'prompt' => $request->message,
            'stream' => false
        ]);

        $responseData = $response->json();

        return response()->json([
            'history' => $chatHistory,
            'response' => $responseData['response'] ?? null,
        ]);
    }
}

