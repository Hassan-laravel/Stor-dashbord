<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Admin\ProductImportController;
use App\Http\Controllers\Admin\BolProductController;
// مسارات تسجيل الدخول (غير محمية)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

// مسار تسجيل الخروج
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// مسارات لوحة التحكم (محمية بـ auth)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('layouts.admin'); // Dashboard
    })->name('dashboard');

    // مسارات البروفايل
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::resource('stores', \App\Http\Controllers\Admin\StoreController::class);
    // مسارات التصنيفات

    Route::resource('categories', CategoryController::class);

    Route::get('products/bulk-import', [ProductImportController::class, 'showBulkForm'])->name('products.bulk-import');
    // مسار الحذف الجماعي
    Route::delete('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    // تنفيذ عملية الجلب والتخزين
    Route::post('products/bulk-import', [ProductImportController::class, 'bulkImport'])->name('products.bulk-import.store');
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::resource('post-categories', PostCategoryController::class);
    Route::resource('posts', PostController::class);
    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::resource('social-media-links', \App\Http\Controllers\SocialMediaLinkController::class);
});
Route::prefix('admin/bol-products')->name('admin.bol-products.')->group(function () {
    Route::get('/', [BolProductController::class, 'index'])->name('index');
    Route::get('/create', [BolProductController::class, 'create'])->name('create');
    Route::post('/', [BolProductController::class, 'store'])->name('store');
    Route::get('/{product}/edit', [BolProductController::class, 'edit'])->name('edit');
    Route::put('/{product}', [BolProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [BolProductController::class, 'destroy'])->name('destroy');
});
// Route::get('/test-bol-api/{ean}', function ($ean) {
//     $controller = new BolProductController();
//     $result = $controller->fetchFromBolApi($ean);
    
//     return response()->json($result);
// });
// Route::get('/test-bol-api/{ean}', function ($ean) {
//     $controller = new BolProductController();
    
//     // سنقوم باستدعاء طريقة جلب الـ Token والـ API مباشرة للفحص
//     // (بإمكانك جعل دالة fetchFromBolApi عامة public أو تجربتها مباشرة)
//     $reflection = new ReflectionMethod(BolProductController::class, 'fetchFromBolApi');
//     $reflection->setAccessible(true);
    
//     $result = $reflection->invoke(new BolProductController(), $ean);
    
//     return response()->json($result);
// });


