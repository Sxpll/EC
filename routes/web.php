<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manageUsers');
        Route::post('/admin/user/{id}', [AdminController::class, 'updateUser'])->name('admin.updateUser');
        Route::delete('/admin/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    });

    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
});
