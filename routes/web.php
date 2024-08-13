<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manageUsers');
    Route::put('/admin/user/{id}', [AdminController::class, 'updateUser'])->name('admin.updateUser');
    Route::delete('/admin/user/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.storeUser');
    Route::get('/admin/history', [AdminController::class, 'showHistory'])->name('admin.history');
    Route::get('/admin/user/{id}', [AdminController::class, 'getUser'])->name('admin.getUser');
    Route::get('/admin/user/{id}/history', [AdminController::class, 'showHistory'])->name('admin.userHistory');

    Route::get('/chat/filter', [ChatController::class, 'filterChats'])->name('chat.filter');
    Route::get('/chat/{id}/messages', [ChatController::class, 'getMessages']);
    Route::get('/admin/check-new-messages', [ChatController::class, 'checkNewMessages']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);




Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);


    

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/user-chats', [ChatController::class, 'userChats'])->name('chat.userChats');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{id}/send-message', [ChatController::class, 'sendMessage'])->name('chat.sendMessage');
    Route::post('/chat/{id}/take', [ChatController::class, 'takeChat'])->name('chat.takeChat');
    Route::post('/chat/create', [ChatController::class, 'createChat'])->name('chat.createChat');
    Route::put('/chat/{id}/manage', [ChatController::class, 'manageChat'])->name('chat.manage');

    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
});
