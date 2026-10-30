<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingController;

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
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::resource('post-categories', PostCategoryController::class);
    Route::resource('posts', PostController::class);
    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
Route::resource('social-media-links', \App\Http\Controllers\SocialMediaLinkController::class);
});
