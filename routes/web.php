<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AccountController,
    HomeController,
    AdminController,
    ChatController,
    UserController,
    NotificationController,
    ProductController,
    CategoryController,
    CartController,
    OrderController,
    DiscountCodeController
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\MessageSent;


// Trasy publiczne (dostępne dla wszystkich)
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/products2', [ProductController::class, 'publicIndex'])->name('products.publicIndex');
Route::get('/public/products/{id}', [ProductController::class, 'showProduct'])->name('products.show');

// Koszyk - publiczne
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/contents', [CartController::class, 'contents'])->name('cart.contents');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Zamówienia (publiczne)
Route::get('/order/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/order/store', [OrderController::class, 'store'])->name('orders.store');
Route::get('/order/thankyou', [OrderController::class, 'thankyou'])->name('orders.thankyou');

// Kody rabatowe (publiczne)
Route::post('/cart/apply-discount', [DiscountCodeController::class, 'applyDiscountCode'])->name('cart.applyDiscount');
Route::post('/cart/remove-discount', [DiscountCodeController::class, 'removeDiscount'])->name('cart.removeDiscount');

// Kategorie publiczne
Route::get('/categories/get-tree', [CategoryController::class, 'getTree'])->name('categories.getTree');

// Trasy wymagające zalogowania
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/orders/my-orders', [OrderController::class, 'myOrders'])->name('orders.myOrders');



    Route::post('/send-message', function (Request $request) {
        $message = $request->input('message');
        $user = $request->input('user');

        // Emituj zdarzenie
        event(new MessageSent($user, $message));

        return response()->json(['status' => 'Message sent!']);
    });


    // Konto użytkownika
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');

    // Zarządzanie koszykiem przy logowaniu
    Route::get('/cart/merge-options', [CartController::class, 'mergeOptions'])->name('cart.mergeOptions');
    Route::post('/cart/use-cookie-cart', [CartController::class, 'useCookieCart'])->name('cart.useCookieCart');
    Route::post('/cart/use-database-cart', [CartController::class, 'useDatabaseCart'])->name('cart.useDatabaseCart');
    Route::post('/cart/merge-carts', [CartController::class, 'mergeCarts'])->name('cart.mergeCarts');
    Route::post('/cart/use-selected-cart', [CartController::class, 'useSelectedCart'])->name('cart.useSelectedCart');

    // Kody rabatowe dla zalogowanych użytkowników
    Route::resource('discount_codes', DiscountCodeController::class)->except(['show']);
    Route::get('/my-discount-codes', [DiscountCodeController::class, 'myDiscountCodes'])->name('discount_codes.my_codes');
    Route::get('/admin/discount_codes', [\App\Http\Controllers\DiscountCodeController::class, 'index'])->name('discount_codes.index');

    // Reset kodu odbioru zamówienia
    Route::post('/orders/{orderId}/reset-pickup-code', [OrderController::class, 'resetPickupCode'])->name('orders.resetPickupCode');

    // Zarządzanie produktami, użytkownikami, zamówieniami - tylko dla adminów
    Route::get('/admin/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/admin/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/{id}/images', [ProductController::class, 'showImages']);
    Route::get('/products/{id}/attachments', [ProductController::class, 'showAttachments']);
    Route::put('/admin/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/admin/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/products/{productId}/images/{imageId}', [ProductController::class, 'deleteImage'])->name('products.images.delete');
    Route::delete('/products/{productId}/attachments/{attachmentId}', [ProductController::class, 'deleteAttachment'])->name('products.attachments.delete');
    Route::post('/products/{product}/images', [ProductController::class, 'storeImages']);
    Route::post('/products/{product}/attachments', [ProductController::class, 'storeAttachments']);
    Route::post('/products/{id}/activate', [ProductController::class, 'activate'])->name('products.activate');
    Route::get('/products/{id}/history', [ProductController::class, 'fetchHistory'])->name('products.history');
    Route::get('/products/{id}/archived-categories', [ProductController::class, 'getArchivedCategories']);

    // Admin panel
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
    Route::get('/admin/orders', [OrderController::class, 'adminIndex'])->name('admin.orders');
    Route::post('/admin/orders/{order}/update', [OrderController::class, 'update'])->name('admin.orders.update');
    Route::get('/admin/orders/{id}', [AdminController::class, 'orderDetails'])->name('admin.orderDetails');

    // Kategorie - zarządzanie
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('/categories/update-hierarchy', [CategoryController::class, 'updateHierarchy'])->name('categories.updateHierarchy');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::get('/categories/{id}/products', [CategoryController::class, 'getProducts'])->name('categories.getProducts');
    Route::post('/categories/move-products', [CategoryController::class, 'moveProductsToNewSubcategory'])->name('categories.moveProducts');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');
});

// Trasy chatów (tylko zalogowani)
Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/filter', [ChatController::class, 'filterChats'])->name('chat.filterChats');
    Route::get('/user-chats', [ChatController::class, 'userChats'])->name('chat.userChats');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{id}/send-message', [ChatController::class, 'sendMessage'])->name('chat.sendMessage');
    Route::post('/chat/{id}/take', [ChatController::class, 'takeChat'])->name('chat.takeChat');
    Route::post('/chat/create', [ChatController::class, 'createChat'])->name('chat.createChat');
    Route::put('/chat/{id}/manage', [ChatController::class, 'manageChat'])->name('chat.manage');
    Route::get('/chat/{id}/messages', [ChatController::class, 'getMessages']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
});

// Dashboard użytkownika (tylko zalogowani)
Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard')->middleware('auth');
