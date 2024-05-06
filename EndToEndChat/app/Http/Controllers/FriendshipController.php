<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Friendship;
use App\Models\User;
use App\Models\Block;

class FriendshipController extends Controller
{
    public function getBlockedUsers()
    {
        $userId = auth()->id();

        $blockedUsers = User::join('blocks', 'users.id', '=', 'blocks.blocked_id')
            ->where('blocks.blocker_id', $userId)
            ->get(['users.id', 'users.name']);

        return response()->json([
            'blockedUsers' => $blockedUsers
        ]);
    }


    public function removeFriend($friendId)
    {
        $userId = auth()->id();

        $friendship = Friendship::where(function ($query) use ($userId, $friendId) {
            $query->where('requester_id', $userId)->where('addressee_id', $friendId);
        })->orWhere(function ($query) use ($userId, $friendId) {
            $query->where('requester_id', $friendId)->where('addressee_id', $userId);
        })->first();


        if ($friendship) {
            $friendship->delete();
            return response()->json(['message' => 'Friend removed successfully.'], 200);
        } else {
            return response()->json(['message' => 'Friendship not found.'], 404);
        }
    }


    public function getFriendshipInfo($friendId)
    {
        try {
            $userId = auth()->id();  // Authenticate user ID
            $friendship = Friendship::where(function ($query) use ($userId, $friendId) {
                $query->where('requester_id', $userId)->where('addressee_id', $friendId);
            })->orWhere(function ($query) use ($userId, $friendId) {
                $query->where('requester_id', $friendId)->where('addressee_id', $userId);
            })->first();
    
            if (!$friendship) {
                return response()->json(['message' => 'Friendship not found'], 404);
            }
    
            return response()->json([
                'updatedAt' => $friendship->updated_at  // Return only the last updated timestamp
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }    
    




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

        // Check if they are already friends
        $existingFriendship = Friendship::where(function ($query) use ($addressee) {
            $query->where('requester_id', auth()->id())->where('addressee_id', $addressee->id)->where('status', 'accepted')->exists();
        })->orWhere(function ($query) use ($addressee) {
            $query->where('requester_id', $addressee->id)->where('addressee_id', auth()->id());
        })->where('status', 'accepted')->exists();

        if ($existingFriendship) {
            return response()->json(['message' => 'You are already friends.'], 409);
        }
        
        // Check if there is an existing request from the other user
        $reverseRequest = Friendship::where('requester_id', $addressee->id)
                                    ->where('addressee_id', auth()->id())
                                    ->first();
        
        if ($reverseRequest) {
            $reverseRequest->update(['status' => 'accepted']);
            return response()->json(['message' => 'Friend request accepted automatically.']);
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
        $sentRequests = User::join('friendships', 'users.id', '=', 'friendships.addressee_id')
                            ->where('friendships.requester_id', $userId)
                            ->where('friendships.status', 'pending')
                            ->get(['friendships.id as friendship_id', 'users.id as addressee_id', 'users.name as addressee_name', 'friendships.status']);
    
        return response()->json($sentRequests);
    }    
    

    public function getReceivedRequests()
    {
        $userId = auth()->id();
        $receivedRequests = User::join('friendships', 'users.id', '=', 'friendships.requester_id')
                                ->where('friendships.addressee_id', $userId)
                                ->where('friendships.status', 'pending')
                                ->get([
                                    'friendships.id as friendship_id',
                                    'users.id as requester_id', 
                                    'users.name as requester_name', 
                                    'friendships.status'
                                ]);
    
        return response()->json($receivedRequests);
    }
    


    public function cancelFriendRequest($id)
    {
        $userId = auth()->id();
        $friendship = Friendship::where('id', $id)
                                ->where('requester_id', $userId)
                                ->first();

        if (!$friendship) {
            return response()->json(['message' => 'Friend request not found or you do not have permission to cancel it.'], 404);
        }

        $friendship->delete();
        return response()->json(['message' => 'Friend request canceled successfully.']);
    }

    

    public function getFriends()
    {
        $userId = auth()->id();
        $blockedUsers = Block::where('blocker_id', $userId)->pluck('blocked_id')->toArray();
        
        $friends = Friendship::where(function ($query) use ($userId) {
            $query->where('requester_id', $userId)
                  ->orWhere('addressee_id', $userId);
        })
        ->whereNotIn('requester_id', $blockedUsers)
        ->whereNotIn('addressee_id', $blockedUsers)
        ->where('status', 'accepted')
        ->get()
        ->map(function ($friendship) use ($userId) {
            $friendId = $friendship->requester_id == $userId ? $friendship->addressee_id : $friendship->requester_id;
            $friendName = User::where('id', $friendId)->value('name');
            return [
                'id' => $friendId,
                'name' => $friendName,
            ];
        });
    
        return response()->json($friends);
    }
    

    public function rejectFriendRequest($requestId)
    {

        $friendship = Friendship::find($requestId);

        if (!$friendship) {
            return response()->json(['message' => 'Friend request not found.'], 404);
        }

        $friendship->delete();

        return response()->json(['message' => 'Friend request successfully rejected and deleted.']);
    }


    public function acceptFriendRequest($requestId)
    {
        $friendship = Friendship::find($requestId);
    
        if (!$friendship) {
            return response()->json(['message' => 'Friend request not found'], 404);
        }
    
        if ($friendship->addressee_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        if ($friendship->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending'], 409);
        }
    
        $friendship->update(['status' => 'accepted']);
        
        return response()->json(['message' => 'Friend request accepted']);
    }
    

    public function getPublicKeyById($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json(['public_key' => $user->publicKey]);
    }
}