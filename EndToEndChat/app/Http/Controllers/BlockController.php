<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    public function blockUser(Request $request)
    {
        $blockedId = $request->blocked_id;
        if (User::where('id', $blockedId)->exists()) {
            Block::create([
                'blocker_id' => Auth::id(),
                'blocked_id' => $blockedId,
            ]);

            return response()->json(['message' => 'User blocked successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    public function unblockUser(Request $request)
    {
        $blockedId = $request->blocked_id;
        $block = Block::where('blocker_id', Auth::id())->where('blocked_id', $blockedId)->first();

        if ($block) {
            $block->delete();
            return response()->json(['message' => 'User unblocked successfully'], 200);
        }

        return response()->json(['message' => 'Block not found'], 404);
    }
}
