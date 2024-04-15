<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Friendship;
use App\Models\User;

class FriendshipController extends Controller
{
    public function sendRequestByName(Request $request)
    {   
        $validated = $request->validate([
            'name' => 'required|string|exists:users,name',
        ], [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.exists' => 'This name does not exist in our records.',
        ]);
        
    
        $addressee = User::where('name', $validated['name'])->first();
    
        if (!$addressee) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    
        // Prevent sending a request to oneself
        if ($addressee->id === auth()->id()) {
            return response()->json(['message' => 'You cannot send a friend request to yourself.'], 400);
        }
    
        // Check if the request already exists
        $existingRequest = Friendship::where('requester_id', auth()->id())
                                     ->where('addressee_id', $addressee->id)
                                     ->first();
    
        if ($existingRequest) {
            return response()->json(['message' => 'Friend request already sent.'], 409);
        }
    
        Friendship::create([
            'requester_id' => auth()->id(),
            'addressee_id' => $addressee->id,
            'status' => 'pending',
        ]);
    
        return response()->json(['message' => 'Friend request sent successfully.']);
    }   


    public function getSentRequests()
    {
        $userId = auth()->id();
        $sentRequests = Friendship::where('requester_id', $userId)
                                  ->where('status', 'pending')
                                  ->get();
        return response()->json($sentRequests);
    }

    public function getReceivedRequests()
    {
        $userId = auth()->id();
        $receivedRequests = Friendship::where('addressee_id', $userId)
                                      ->where('status', 'pending')
                                      ->get();
        return response()->json($receivedRequests);
    }

    public function getFriends()
    {
        $userId = auth()->id();
        $friends = Friendship::where(function ($query) use ($userId) {
            $query->where('requester_id', $userId)
                  ->orWhere('addressee_id', $userId);
        })
        ->where('status', 'accepted')
        ->get()
        ->map(function ($friendship) use ($userId) {
            // Determine the friend's user ID
            $friendId = $friendship->requester_id == $userId ? $friendship->addressee_id : $friendship->requester_id;
            // Fetch the friend's name from the users table
            $friendName = User::where('id', $friendId)->value('name');
            return [
                'id' => $friendId,
                'name' => $friendName,
            ];
        });
    
        return response()->json($friends);
    }
    



    public function acceptFriendRequest($requestId)
    {
        $friendship = Friendship::findOrFail($requestId);

        if ($friendship->addressee_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $friendship->update(['status' => 'accepted']);
        
        return response()->json(['message' => 'Friend request accepted']);
    }
}