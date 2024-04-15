<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        // Create and save the message
        $message = new Message();
        $message->sender_id = auth()->id(); // or any other way you're determining the sender
        $message->recipient_id = $validated['recipient_id'];
        $message->content = $validated['content'];
        $message->save();

        // Respond with the created message or a success status
        return response()->json(['message' => 'Message sent successfully', 'data' => $message], 200);
    }



    public function getMessagesWithUser($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', Auth::id())->where('recipient_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)->where('recipient_id', Auth::id());
        })->get();

        return response()->json($messages);
    }

    public function getChatMessages($friendId)
    {
        $userId = auth()->id();
        $messages = Message::where(function ($q) use ($userId, $friendId) {
            $q->where('sender_id', $userId)->where('recipient_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('sender_id', $friendId)->where('recipient_id', $userId);
        })->get();
    
        return response()->json($messages);
    }
}
