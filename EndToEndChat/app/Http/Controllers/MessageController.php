<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
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
        })->orderBy('created_at', 'asc')
          ->paginate(1);
    
        return response()->json($messages);
    }    
}