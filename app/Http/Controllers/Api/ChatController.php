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
        return response()->json(['Message' => "Done", 'chat' => $chat]);
    }

    public function chat(Request $request)
    {
        $user = auth()->user();
        $receiver_id = $request->receiver_id;
        $user_id = $user->id;
        $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
            $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
        })->orWhere(function ($q) use ($receiver_id, $user_id) {
            $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
        })->first();
        if (!is_object($chat)) {
            $receiver = User::find($receiver_id);
            // $chat = new Chat();
            // $chat->sender_id = $user_id;
            // $chat->receiver_id = $receiver_id;
            // if ($chat->save()) {
            //     $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
            //         $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
            //     })->orWhere(function ($q) use ($receiver_id, $user_id) {
            //         $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
            //     })->first();
            // }
            return response()->json(['Message' => "Done", 'sender' => $user, 'receiver' => $receiver]);
        }
        return response()->json(['Message' => "Done", 'chat' => $chat]);
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
            if ($chat->save()) {
                $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
                    $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
                })->orWhere(function ($q) use ($receiver_id, $user_id) {
                    $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
                })->first();
            }
        }
        event(new MessageEvent($user->name, $request->message, $chat->id));
        return response()->json(['Message' => "done", 'chat' => $chat]);
    }
}
