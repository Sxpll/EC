<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
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

    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');

    // Trasy dla chatu
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index'); // Dla adminów do przeglądania wszystkich otwartych chatów
    Route::get('/user-chats', [ChatController::class, 'userChats'])->name('chat.userChats'); // Dla użytkowników do przeglądania ich chatów
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show'); // Wyświetlenie konkretnego chatu
    Route::post('/chat/{id}/send-message', [ChatController::class, 'sendMessage'])->name('chat.sendMessage'); // Wysłanie wiadomości w konkretnym chacie
    Route::post('/chat/{id}/take', [ChatController::class, 'takeChat'])->name('chat.takeChat'); // Przejęcie chatu przez admina
    Route::post('/chat/create', [ChatController::class, 'createChat'])->name('chat.createChat'); // Utworzenie nowego chatu przez użytkownika
});
