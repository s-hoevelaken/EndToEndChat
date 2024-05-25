<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\BlockController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/friend-requests/sent', [FriendshipController::class, 'getSentRequests']);
    Route::get('/friend-requests/received', [FriendshipController::class, 'getReceivedRequests']);
    Route::get('/friends', [FriendshipController::class, 'getFriends']);
    Route::get('/blocked-users', [FriendshipController::class, 'getBlockedUsers']);
    Route::get('/friendship-info/{friendId}', [FriendshipController::class, 'getFriendshipInfo']);
    Route::delete('/remove-friend/{friendId}', [FriendshipController::class, 'removeFriend']);
    
    Route::get('/chat/{friendId}', [MessageController::class, 'getChatMessages']);
    Route::get('/user/public-key/{id}', [FriendshipController::class, 'getPublicKeyById']);
    Route::patch('/messages/read/{id}', [MessageController::class, 'markAsRead']);
    Route::get('/last-message/{friendId}', [MessageController::class, 'getLastMessage']);
    Route::delete('/friend-requests/reject/{id}', [FriendshipController::class, 'rejectFriendRequest']);
    Route::delete('/friend-requests/cancel/{id}', [FriendshipController::class, 'cancelFriendRequest']);
    Route::post('/block', [BlockController::class, 'blockUser']);
    Route::post('/unblock', [BlockController::class, 'unblockUser']);
});
