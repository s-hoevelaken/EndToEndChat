<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'sender_symm_key_enc' => 'required|string',
            'recipient_symm_key_enc' => 'required|string',
            'iv' => 'required|string',
        ]);


        $senderId = auth()->id();
        $recipientId = $request->recipient_id;

        $isBlocked = Block::where('blocker_id', $recipientId)
                                                ->where('blocked_id', $senderId)
                                                ->exists();

        if ($isBlocked) {
            return response()->json(['message' => 'Message sending failed. The user has blocked you.'], 403);
        }
        
        
        $message = new Message();
        $message->sender_id = auth()->id();
        $message->recipient_id = $validated['recipient_id'];
        $message->content = $validated['content'];
        $message->sender_symm_key_enc = $validated['sender_symm_key_enc'];
        $message->recipient_symm_key_enc = $validated['recipient_symm_key_enc'];
        $message->iv = $validated['iv'];
        $message->save();
        
        return response()->json(['message' => 'Message sent successfully', 'data' => $message], 200);
    }
    

    public function getChatMessages($friendId)
    {
        $userId = auth()->id();
        $messages = Message::where(function ($q) use ($userId, $friendId) {
            $q->where('sender_id', $userId)->where('recipient_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('sender_id', $friendId)->where('recipient_id', $userId);
        })->orderBy('created_at', 'desc')
          ->paginate(30);
    
        return response()->json($messages);
    }    

    public function markAsRead($id)
    {
        $message = Message::find($id);
    
        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }
    
        if ($message->recipient_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $message->is_read = true;
        $message->save();
    
        return response()->json(['message' => 'Message marked as read'], 200);
    }    


    public function getLastMessage($friendId) 
    {
        $userId = auth()->id();

        $message = Message::where(function ($query) use ($userId, $friendId) {
            $query->where('sender_id', $userId)->where('recipient_id', $friendId);
        })->orWhere(function ($query) use ($userId, $friendId) {
            $query->where('sender_id', $friendId)->where('recipient_id', $userId);
        })->latest('created_at')->first();
    
        if (!$message) {
            return response()->json([
                'message' => 'No messages found',
                'data' => [
                    'content' => '',
                    'created_at' => now()->toDateTimeString()
                ]
            ]);
        }    
       
        return response()->json(['message' => 'Last message retrieved successfully', 'data' => $message]);
    }
}