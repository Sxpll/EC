<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');

    // Admin routes
    Route::get('/admin/chats', [ChatController::class, 'index'])->name('admin.chats');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manageUsers');
    Route::put('/admin/user/{id}', [AdminController::class, 'updateUser'])->name('admin.updateUser');
    Route::delete('/admin/user/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.storeUser');
    Route::get('/admin/history', [AdminController::class, 'showHistory'])->name('admin.history');
    Route::get('/admin/user/{id}', [AdminController::class, 'getUser'])->name('admin.getUser');
    Route::get('/admin/user/{id}/history', [AdminController::class, 'showHistory'])->name('admin.userHistory');
    Route::get('/admin/check-new-messages', [ChatController::class, 'checkNewMessages']);

    // Product routes
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/{id}/images', [ProductController::class, 'showImages']);
    Route::get('/products/{id}/attachments', [ProductController::class, 'showAttachments']);
    Route::delete('/products/{productId}/images/{imageId}', [ProductController::class, 'deleteImage'])->name('products.images.delete');
    Route::delete('/products/{productId}/attachments/{attachmentId}', [ProductController::class, 'deleteAttachment'])->name('products.attachments.delete');
    Route::post('/products/{product}/images', [ProductController::class, 'storeImages']);
    Route::post('/products/{product}/attachments', [ProductController::class, 'storeAttachments']);
    Route::post('/products/{id}/activate', [ProductController::class, 'activate'])->name('products.activate');
    Route::get('/products/{id}/history', [ProductController::class, 'fetchHistory'])->name('products.history');
    Route::get('/products/{id}/archived-categories', [ProductController::class, 'getArchivedCategories']);
    Route::get('/products2', [ProductController::class, 'publicIndex'])->name('products.publicIndex');


    // Categories routes
    Route::get('/categories/get-tree', [CategoryController::class, 'getTree'])->name('categories.getTree');
    Route::post('/categories/update-hierarchy', [CategoryController::class, 'updateHierarchy'])->name('categories.updateHierarchy');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::patch('/categories/{id}/activate', [CategoryController::class, 'activate'])->name('categories.activate');
    Route::get('/categories/{id}/products', [CategoryController::class, 'getProducts'])->name('categories.getProducts');
    Route::post('/categories/move-products', [CategoryController::class, 'moveProductsToNewSubcategory'])->name('categories.moveProducts');

    // Resource route for categories should be at the end
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Chat routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/filter', [ChatController::class, 'filterChats'])->name('chat.filterChats');
    Route::get('/user-chats', [ChatController::class, 'userChats'])->name('chat.userChats');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{id}/send-message', [ChatController::class, 'sendMessage'])->name('chat.sendMessage');
    Route::post('/chat/{id}/take', [ChatController::class, 'takeChat'])->name('chat.takeChat');
    Route::post('/chat/create', [ChatController::class, 'createChat'])->name('chat.createChat');
    Route::put('/chat/{id}/manage', [ChatController::class, 'manageChat'])->name('chat.manage');
    Route::get('/chat/{id}/messages', [ChatController::class, 'getMessages']);

    // Notifications routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

    // User dashboard
    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
});
