<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if (count($chat)) return response()->json(['status' => true, 'Message' => "Chat Found", 'chat' => $chat], 200);
        else return response()->json(['status' => false, 'Message' => "Chat not Found", 'chat' => $chat]);
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
        if (!is_object($chat)) return response()->json(['status' => false, 'Message' => "Chat not Found", 'chat' => $chat]);
        // {
        // $receiver = User::find($receiver_id);
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

        // }
        return response()->json(['status' => true, 'Message' => "Done", 'chat' => $chat], 200);
    }

    public function message(Request $request)
    {
        try {
            DB::beginTransaction();
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
            DB::commit();
            return response()->json(['status' => true, 'Message' => "Chat Found", 'chat' => $chat], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }
}
