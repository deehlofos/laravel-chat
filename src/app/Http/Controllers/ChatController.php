<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Показать страницу чата
     */
    public function index()
    {
        return view('chat');
    }

    /**
     * Получить историю сообщений
     */
    public function getMessages()
    {
        $messages = Message::with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    /**
     * Отправить новое сообщение
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Отправляем событие всем КРОМЕ отправителя
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Сообщение отправлено'
        ]);
    }

    /**
     * Получить текущего пользователя
     */
    public function currentUser()
    {
        return response()->json(Auth::user());
    }
}
