<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MessageController;

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
});

Route::get('/chat/{friendId}', [MessageController::class, 'getChatMessages']);

Route::get('/user/public-key/{id}', [FriendshipController::class, 'getPublicKeyById']);

Route::patch('/messages/read/{id}', [MessageController::class, 'markAsRead']);