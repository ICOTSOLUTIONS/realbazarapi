<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function allMessages()
    {
        $user = auth()->user();
        $user_id = $user->id;
        $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id) {
            $q->where('sender_id', $user_id);
        })->orWhere(function ($q) use ($user_id) {
            $q->where('receiver_id', $user_id);
        })->get();
        return response()->json(['Message' => "Done", 'chat' => $chat, 'user' => $user]);
    }

    public function chat($receiver_id)
    {
        $user = auth()->user();
        $user_id = $user->id;
        $receiver = User::find($receiver_id);
        $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
            $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
        })->orWhere(function ($q) use ($receiver_id, $user_id) {
            $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
        })->first();
        if (!is_object($chat)) {
            $chat = new Chat();
            $chat->sender_id = $user_id;
            $chat->receiver_id = $receiver_id;
            $chat->save();
        }
        return response()->json(['Message' => "Done", 'chat' => $chat, 'user' => $user, 'receiver' => $receiver]);
    }

    public function message(Request $request)
    {
        $user = auth()->user();
        $user_id = $user->id;
        $receiver_id = $request->receiver_id;
        $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
            $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
        })->orWhere(function ($q) use ($receiver_id, $user_id) {
            $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
        })->first();
        if (!is_object($chat)) {
            $chat = new Chat();
            $chat->sender_id = $user_id;
            $chat->receiver_id = $receiver_id;
            $chat->save();
        }
        event(new MessageEvent($user->name, $request->message, $chat->id));
        return response()->json(['Message' => "done", 'chat' => $chat]);
    }
}
