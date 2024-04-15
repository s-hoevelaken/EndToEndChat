<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::post('/friend-request/by-name', [FriendshipController::class, 'sendRequestByName']);
Route::post('/friend-requests/accept/{requestId}', [FriendshipController::class, 'acceptFriendRequest']);

Route::post('/send-message', [MessageController::class, 'sendMessage'])->name('send.message');